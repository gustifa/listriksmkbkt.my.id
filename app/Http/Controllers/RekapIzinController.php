<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class RekapIzinController extends Controller
{
    // 1. Tampilan Utama Rekapitulasi Izin & Sakit Siswa
    public function index(Request $request)
    {
        $userId = auth()->id();
        $teacherId = DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
            $query->select('classroom_id')->from('schedules')->where('teacher_id', $teacherId);
        })->get();

        if ($classrooms->isEmpty()) {
            $classrooms = Classroom::orderBy('name', 'asc')->get();
        }

        $selectedClassroom = $request->input('classroom_id');
        $dates = collect();
        $rekapData = collect();

        if ($selectedClassroom) {
            $dates = DB::table('izin_siswas')
                ->whereIn('student_id', function($q) use ($selectedClassroom) {
                    $q->select('id')->from('students')->where('classroom_id', $selectedClassroom);
                })
                ->select('date')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            $rekapData = $students->map(function ($student) use ($dates) {
                $izinRecord = [];
                $totalSakit = 0;
                $totalIzin  = 0;

                foreach ($dates as $d) {
                    $izin = DB::table('izin_siswas')
                        ->where('student_id', $student->id)
                        ->where('date', $d->date)
                        ->first();

                    if (!$izin) {
                        $izinRecord[$d->date] = [
                            'status' => '-', 'reason' => '', 'wa' => '', 'id' => null
                        ];
                    } else {
                        $st = strtolower(trim($izin->status ?? ''));
                        $reasonText = strtolower(trim($izin->reason ?? ''));

                        if (str_contains($reasonText, 'sakit') || $st === 'sakit') {
                            $totalSakit++;
                        } else {
                            $totalIzin++;
                        }

                        $izinRecord[$d->date] = [
                            'id'     => $izin->id,
                            'status' => $izin->status,
                            'reason' => $izin->reason ?? '',
                            'wa'     => $izin->wa_number ?? '',
                        ];
                    }
                }

                $rawPhone = $student->parent_phone ?? $student->phone ?? '';
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($cleanPhone, '0')) { $cleanPhone = '62' . substr($cleanPhone, 1); }
                elseif (str_starts_with($cleanPhone, '8')) { $cleanPhone = '62' . $cleanPhone; }

                return [
                    'student_id'     => $student->id,
                    'nisn'           => $student->nisn ?? $student->id,
                    'nama'           => $student->name,
                    'phone'          => $cleanPhone,
                    'izin_list'      => $izinRecord,
                    'total_sakit'    => $totalSakit,
                    'total_izin'     => $totalIzin,
                    'total_pengajuan'=> $totalSakit + $totalIzin,
                ];
            });
        }

        return view('recap.izin', compact(
            'classrooms', 'selectedClassroom', 'dates', 'rekapData', 'activeAcademicYear'
        ));
    }

    // 2. Simpan / Update Data Izin Individual + Sync Otomatis ke Daily Attendance
    public function updateStatus(Request $request)
    {
        $request->validate([
            'student_id' => 'required|uuid',
            'date'       => 'required|date',
            'status'     => 'required|string',
            'jenis_izin' => 'required|in:izin,sakit',
            'reason'     => 'nullable|string',
            'wa_number'  => 'nullable|string',
        ]);

        if ($request->status === 'Hapus') {
            DB::table('izin_siswas')
                ->where('student_id', $request->student_id)
                ->where('date', $request->date)
                ->delete();

            DB::table('daily_attendances')
                ->where('student_id', $request->student_id)
                ->where('date', $request->date)
                ->delete();
        } else {
            $wa = preg_replace('/[^0-9]/', '', $request->wa_number ?? '');
            if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
            elseif (str_starts_with($wa, '8')) { $wa = '62' . $wa; }

            $statusClean = strtolower(trim($request->status));
            $jenisClean  = strtolower(trim($request->jenis_izin));
            $reasonText  = $request->filled('reason') ? $request->reason : ($jenisClean === 'sakit' ? 'Sakit' : 'Izin');

            DB::table('izin_siswas')->updateOrInsert(
                [
                    'student_id' => $request->student_id,
                    'date'       => $request->date,
                ],
                [
                    'id'         => (string) Str::uuid(),
                    'reason'     => $reasonText,
                    'status'     => $statusClean, // 'approved', 'pending', atau 'rejected'
                    'wa_number'  => $wa ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // OTOMATIS SYNC ke daily_attendances berdasarkan Status & Jenisnya
            $this->syncToDailyAttendance($request->student_id, $request->date, $statusClean, $jenisClean, $reasonText);
        }

        return response()->json(['success' => true, 'message' => 'Data izin & presensi harian berhasil diperbarui!']);
    }

    // 3. Simpan Data Izin Massal untuk Siswa Terpilih + Sync Otomatis ke Daily Attendance
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|uuid|exists:classrooms,id',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'uuid|exists:students,id',
            'date'         => 'required|date',
            'status'       => 'required|string',
            'jenis_izin'   => 'required|in:izin,sakit',
            'reason'       => 'nullable|string',
        ]);

        $students    = Student::whereIn('id', $request->student_ids)->get();
        $statusClean = strtolower(trim($request->status));
        $jenisClean  = strtolower(trim($request->jenis_izin));
        $reasonText  = $request->filled('reason') ? $request->reason : ($jenisClean === 'sakit' ? 'Sakit' : 'Izin');

        foreach ($students as $student) {
            $phone = $student->parent_phone ?? $student->phone ?? '';
            $wa = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
            elseif (str_starts_with($wa, '8')) { $wa = '62' . $wa; }

            DB::table('izin_siswas')->updateOrInsert(
                [
                    'student_id' => $student->id,
                    'date'       => $request->date,
                ],
                [
                    'id'         => (string) Str::uuid(),
                    'reason'     => $reasonText,
                    'status'     => $statusClean,
                    'wa_number'  => $wa ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->syncToDailyAttendance($student->id, $request->date, $statusClean, $jenisClean, $reasonText);
        }

        return response()->json(['success' => true, 'message' => 'Izin/Sakit & presensi harian berhasil dicatat untuk ' . count($students) . ' siswa!']);
    }

    /**
     * Helper Function Sinkronisasi ke Tabel daily_attendances
     */
    private function syncToDailyAttendance($studentId, $date, $status, $jenisIzin, $reason = '')
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $statusLower = strtolower(trim($status));
        $jenisLower  = strtolower(trim($jenisIzin));

        // Jika status pengajuan disetujui ('approved' / 'disetujui')
        if (in_array($statusLower, ['approved', 'disetujui', 'izin', 'sakit'])) {
            
            // Status daily_attendances mengikuti bidang Jenis Izin ('sakit' / 'izin')
            $dailyStatus = ($jenisLower === 'sakit' || str_contains(strtolower($reason), 'sakit')) ? 'sakit' : 'izin';

            DB::table('daily_attendances')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'date'       => $date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'academic_year_id' => $activeYear?->id,
                    'arrival_time'     => null,
                    'departure_time'   => null,
                    'status'           => $dailyStatus,
                    'recorded_by'      => auth()->user()->name ?? 'system_izin_auto',
                    'is_synced'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        } else {
            // Jika status pending / rejected, hapus presensi harian
            DB::table('daily_attendances')
                ->where('student_id', $studentId)
                ->where('date', $date)
                ->delete();
        }
    }
}