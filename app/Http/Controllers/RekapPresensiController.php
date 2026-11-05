<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class RekapPresensiController extends Controller
{
    // 1. Tampilan Utama Rekapitulasi Presensi & Jurnal Matriks
    public function index(Request $request)
    {
        $userId = auth()->id();
        $teacherId = DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

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
        $rekapData = collect();
        $jurnalMap = [];

        if ($selectedClassroom && $selectedSubject) {
            $dates = DB::table('attendances')
                ->where('subject_id', $selectedSubject)
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                })
                ->select('date')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $jurnals = DB::table('learning_journals')
                ->where('classroom_id', $selectedClassroom)
                ->where('subject_id', $selectedSubject)
                ->get();
            foreach ($jurnals as $j) {
                $jurnalMap[$j->date] = $j;
            }

            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            $totalMeetings = $dates->count();

            $rekapData = $students->map(function ($student) use ($dates, $selectedSubject, $totalMeetings) {
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
                        
                        if (!empty($catatan) || !empty($attach)) {
                            $totalCatatan++;
                        }
                    }
                    $statusPresensi[$d->date]  = $status;
                    $catatanSiswa[$d->date]     = $catatan;
                    $attachmentSiswa[$d->date] = $attach;
                }

                $percentage = $totalMeetings > 0 ? round(($totalH / $totalMeetings) * 100, 1) : 0;

                // --- PEMBARUAN: Format nomor HP otomatis ke kode negara 62 ---
                $rawPhone = $student->parent_phone ?? $student->phone ?? '';
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $cleanPhone = '62' . $cleanPhone;
                }

                return [
                    'student_id'   => $student->id,
                    'nisn'         => $student->nisn ?? $student->id,
                    'nama'         => $student->name,
                    'phone'        => $cleanPhone,
                    'presensi'     => $statusPresensi,
                    'catatan'      => $catatanSiswa,
                    'attachment'   => $attachmentSiswa,
                    'total_h'      => $totalH,
                    'total_s'      => $totalS,
                    'total_i'      => $totalI,
                    'total_a'      => $totalA,
                    'total_catatan'=> $totalCatatan,
                    'percentage'   => $percentage,
                ];
            });
        }

        return view('recap.presensi', compact(
            'classrooms', 'subjects', 'selectedClassroom', 
            'selectedSubject', 'dates', 'rekapData', 'jurnalMap', 'activeAcademicYear'
        ));
    }

    // 2. Update Status Individual + Catatan & Upload Surat
    public function updateStatus(Request $request)
    {
        $request->validate([
            'student_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date'       => 'required|date',
            'status'     => 'required|in:H,S,I,A',
            'note'       => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $dbStatusMap = ['H' => 'hadir', 'S' => 'sakit', 'I' => 'izin', 'A' => 'alpa'];

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('presensi_surat', 'public');
        }

        if ($request->status === 'A' && empty($request->note) && !$request->hasFile('attachment')) {
            DB::table('attendances')
                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->where('date', $request->date)
                ->delete();
        } else {
            $scheduleId = DB::table('schedules')
                ->where('subject_id', $request->subject_id)
                ->value('id');

            $existing = DB::table('attendances')
                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->where('date', $request->date)
                ->first();

            DB::table('attendances')->updateOrInsert(
                [
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'date'       => $request->date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'schedule_id'      => $scheduleId ?? (string) Str::uuid(),
                    'academic_year_id' => $activeYear?->id,
                    'status'           => $dbStatusMap[$request->status],
                    'note'             => $request->note,
                    'attachment_path'  => $attachmentPath ?? ($existing->attachment_path ?? null),
                    'check_in_time'    => now()->format('H:i:s'),
                    'recorded_by'      => auth()->user()->name ?? 'teacher_manual',
                    'is_synced'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Presensi dan catatan berhasil disimpan!']);
    }

    // 3. Presensi Massal + Jurnal KBM (UPDATED: Dukung Pilihan Siswa Checklist)
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|uuid|exists:classrooms,id',
            'subject_id'   => 'required|uuid|exists:subjects,id',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'uuid|exists:students,id',
            'date'         => 'required|date',
            'status'       => 'required|in:H,S,I,A',
            'topic'        => 'nullable|string',
            'activities'   => 'nullable|string',
            'obstacles'    => 'nullable|string',
        ]);

        $students = Student::whereIn('id', $request->student_ids)->get();
        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Siswa terpilih tidak ditemukan.'], 404);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $dbStatusMap = ['H' => 'hadir', 'S' => 'sakit', 'I' => 'izin', 'A' => 'alpa'];
        $teacherId = auth()->id();

        if ($request->filled('topic')) {
            DB::table('learning_journals')->updateOrInsert(
                [
                    'classroom_id' => $request->classroom_id,
                    'subject_id'   => $request->subject_id,
                    'date'         => $request->date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'teacher_id'       => $teacherId,
                    'academic_year_id' => $activeYear?->id,
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

        foreach ($students as $student) {
            if ($request->status === 'A') {
                DB::table('attendances')
                    ->where('student_id', $student->id)
                    ->where('subject_id', $request->subject_id)
                    ->where('date', $request->date)
                    ->delete();
            } else {
                $existing = DB::table('attendances')
                    ->where('student_id', $student->id)
                    ->where('subject_id', $request->subject_id)
                    ->where('date', $request->date)
                    ->first();

                if ($existing) {
                    DB::table('attendances')
                        ->where('id', $existing->id)
                        ->update([
                            'status'        => $dbStatusMap[$request->status],
                            'check_in_time' => $existing->check_in_time ?? now()->format('H:i:s'),
                            'recorded_by'   => auth()->user()->name ?? 'teacher_manual',
                            'updated_at'    => now(),
                        ]);
                } else {
                    DB::table('attendances')->insert([
                        'id'               => (string) Str::uuid(),
                        'student_id'       => $student->id,
                        'schedule_id'      => $scheduleId ?? (string) Str::uuid(),
                        'subject_id'       => $request->subject_id,
                        'academic_year_id' => $activeYear?->id,
                        'date'             => $request->date,
                        'status'           => $dbStatusMap[$request->status],
                        'check_in_time'    => now()->format('H:i:s'),
                        'recorded_by'      => auth()->user()->name ?? 'teacher_manual',
                        'is_synced'        => true,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi massal dan Jurnal Pembelajaran berhasil disimpan untuk ' . count($students) . ' siswa terpilih!'
        ]);
    }
}