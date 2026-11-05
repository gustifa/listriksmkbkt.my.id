<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class RekapPembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $teacherId = DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        // 1. Data Filter Kelas & Mapel
        $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
            $query->select('classroom_id')->from('schedules')->where('teacher_id', $teacherId);
        })->get();

        $selectedClassroom = $request->input('classroom_id');

        $subjects = Subject::whereIn('id', function ($query) use ($teacherId, $selectedClassroom) {
            $query->select('subject_id')->from('schedules')->where('teacher_id', $teacherId);
            if ($selectedClassroom) {
                $query->where('classroom_id', $selectedClassroom);
            }
        })->get();

        $selectedSubject = $request->input('subject_id');

        $dates = collect();
        $assignments = collect();
        $rekapData = collect();
        $jurnalMap = [];

        if ($selectedClassroom && $selectedSubject) {
            // A. Dapatkan Tanggal Presensi Unik
            $dates = DB::table('attendances')
                ->where('subject_id', $selectedSubject)
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                })
                ->select('date')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // B. Dapatkan Jurnal Pembelajaran Guru
            $jurnals = DB::table('learning_journals')
                ->where('classroom_id', $selectedClassroom)
                ->where('subject_id', $selectedSubject)
                ->get();
            foreach ($jurnals as $j) {
                $jurnalMap[$j->date] = $j;
            }

            // C. Dapatkan Daftar Tugas
            $assignments = DB::table('assignments')
                ->where('classroom_id', $selectedClassroom)
                ->where('subject_id', $selectedSubject)
                ->orderBy('created_at', 'asc')
                ->get();

            // D. Data Siswa
            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            $totalMeetings = $dates->count();

            // E. Susun Matriks Terpadu (Presensi + Tugas + Performa)
            $rekapData = $students->map(function ($student) use ($dates, $assignments, $selectedSubject, $totalMeetings) {
                // 1. Processing Presensi
                $statusPresensi = [];
                $catatanSiswa   = [];
                $attachmentSiswa = [];
                $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0; $totalCatatan = 0;

                foreach ($dates as $d) {
                    $attendance = DB::table('attendances')
                        ->where('student_id', $student->id)
                        ->where('subject_id', $selectedSubject)
                        ->where('date', $d->date)
                        ->first();

                    if (!$attendance) {
                        $status  = 'A';
                        $catatan = '';
                        $attach  = '';
                        $totalA++;
                    } else {
                        $st = strtolower(trim($attendance->status ?? ''));
                        if (in_array($st, ['hadir', 'h', 'present'])) {
                            $status = 'H'; $totalH++;
                        } elseif (in_array($st, ['sakit', 's', 'sick'])) {
                            $status = 'S'; $totalS++;
                        } elseif (in_array($st, ['izin', 'i', 'permission'])) {
                            $status = 'I'; $totalI++;
                        } else {
                            $status = 'A'; $totalA++;
                        }
                        $catatan = $attendance->note ?? '';
                        $attach  = $attendance->attachment_path ?? '';
                        if (!empty($catatan) || !empty($attach)) { $totalCatatan++; }
                    }
                    $statusPresensi[$d->date]  = $status;
                    $catatanSiswa[$d->date]     = $catatan;
                    $attachmentSiswa[$d->date] = $attach;
                }
                $attendancePercentage = $totalMeetings > 0 ? round(($totalH / $totalMeetings) * 100, 1) : 0;

                // 2. Processing Nilai Tugas
                $nilaiTugas = [];
                $totalNilai = 0;
                $jumlahTugasDinilai = 0;

                foreach ($assignments as $asg) {
                    $submission = DB::table('assignment_submissions')
                        ->where('assignment_id', $asg->id)
                        ->where('student_id', $student->id)
                        ->first();

                    $grade = null;
                    if ($submission) {
                        $grade = $submission->score 
                            ?? $submission->grade 
                            ?? $submission->marks 
                            ?? $submission->point 
                            ?? null;
                    }
                    $nilaiTugas[$asg->id] = $grade;

                    if (!is_null($grade)) {
                        $totalNilai += $grade;
                        $jumlahTugasDinilai++;
                    }
                }
                $avgGrade = $jumlahTugasDinilai > 0 ? round($totalNilai / $jumlahTugasDinilai, 1) : 0;

                // 3. Penentuan Status Peringatan (Warning Status)
                $needAttention = ($attendancePercentage < 75) || ($avgGrade > 0 && $avgGrade < 70);

                // --- PEMBARUAN: Format nomor HP otomatis ke kode negara 62 ---
                $rawPhone = $student->parent_phone ?? $student->phone ?? '';
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $cleanPhone = '62' . $cleanPhone;
                }

                return [
                    'student_id'      => $student->id,
                    'nisn'            => $student->nisn ?? $student->id,
                    'nama'            => $student->name,
                    'phone'           => $cleanPhone,
                    'presensi'        => $statusPresensi,
                    'catatan'         => $catatanSiswa,
                    'attachment'      => $attachmentSiswa,
                    'total_h'         => $totalH,
                    'total_s'         => $totalS,
                    'total_i'         => $totalI,
                    'total_a'         => $totalA,
                    'total_catatan'   => $totalCatatan,
                    'attendance_pct'  => $attendancePercentage,
                    'tugas'           => $nilaiTugas,
                    'avg_grade'       => $avgGrade,
                    'need_attention'  => $needAttention
                ];
            });
        }

        return view('recap.pembelajaran', compact(
            'classrooms', 'subjects', 'selectedClassroom', 
            'selectedSubject', 'dates', 'assignments', 'rekapData', 'jurnalMap', 'activeAcademicYear'
        ));
    }

    // --- FITUR BARU: Presensi Massal Pilihan Siswa ---
    public function bulkStoreAttendance(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|uuid|exists:classrooms,id',
            'subject_id'   => 'required|uuid|exists:subjects,id',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'uuid|exists:students,id',
            'date'         => 'required|date',
            'status'       => 'required|in:H,S,I,A,hadir,sakit,izin,alpa',
            'topic'        => 'nullable|string',
            'activities'   => 'nullable|string',
            'obstacles'    => 'nullable|string',
        ]);

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $statusMap = [
            'H' => 'hadir', 'hadir' => 'hadir',
            'S' => 'sakit', 'sakit' => 'sakit',
            'I' => 'izin',  'izin'  => 'izin',
            'A' => 'alpa',  'alpa'  => 'alpa'
        ];
        $finalStatus = $statusMap[$request->status] ?? 'hadir';

        if ($request->filled('topic')) {
            DB::table('learning_journals')->updateOrInsert(
                [
                    'classroom_id' => $request->classroom_id,
                    'subject_id'   => $request->subject_id,
                    'date'         => $request->date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'teacher_id'       => auth()->id(),
                    'academic_year_id' => $activeAcademicYear?->id,
                    'topic'            => $request->topic,
                    'activities'       => $request->activities,
                    'obstacles'        => $request->obstacles,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        $scheduleId = DB::table('schedules')
            ->where('classroom_id', $request->classroom_id)
            ->where('subject_id', $request->subject_id)
            ->value('id');

        foreach ($request->student_ids as $studentId) {
            DB::table('attendances')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'date'       => $request->date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'schedule_id'      => $scheduleId ?? (string) Str::uuid(),
                    'academic_year_id' => $activeAcademicYear?->id,
                    'status'           => $finalStatus,
                    'check_in_time'    => now()->format('H:i:s'),
                    'recorded_by'      => auth()->user()->name ?? 'teacher_bulk',
                    'is_synced'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        return response()->json([
            'success' => true, 
            'message' => 'Presensi & jurnal pembelajaran berhasil disimpan untuk ' . count($request->student_ids) . ' siswa terpilih!'
        ]);
    }
}