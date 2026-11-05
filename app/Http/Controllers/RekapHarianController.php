<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class RekapHarianController extends Controller
{
    // 1. Tampilan Utama Rekap Harian
    public function index(Request $request)
    {
        $userId = auth()->id();
        $teacherId = DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        // Ambil kelas yang diajar atau dimiliki guru
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
            // 1. Ambil daftar tanggal dari tabel daily_attendances
            $dates = DB::table('daily_attendances')
                ->whereIn('student_id', function($q) use ($selectedClassroom) {
                    $q->select('id')->from('students')->where('classroom_id', $selectedClassroom);
                })
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where('academic_year_id', $activeAcademicYear->id);
                })
                ->select('date')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // 2. Ambil data siswa
            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            $totalMeetings = $dates->count();

            // 3. Susun Matriks Presensi Datang & Pulang + Persentase
            $rekapData = $students->map(function ($student) use ($dates, $totalMeetings) {
                $dailyRecord = [];
                $totalH = 0; $totalTerlambat = 0; $totalS = 0; $totalI = 0; $totalA = 0;

                foreach ($dates as $d) {
                    $attendance = DB::table('daily_attendances')
                        ->where('student_id', $student->id)
                        ->where('date', $d->date)
                        ->first();

                    if (!$attendance) {
                        $dailyRecord[$d->date] = [
                            'status'         => 'A',
                            'in'             => '',
                            'out'            => '',
                            'raw_arrival'    => '',
                            'raw_departure'  => '',
                            'photo_in'       => null,
                            'photo_out'      => null
                        ];
                        $totalA++;
                    } else {
                        $st = strtolower(trim($attendance->status ?? ''));
                        
                        if (in_array($st, ['hadir', 'h', 'present'])) {
                            $status = 'H'; $totalH++;
                        } elseif (in_array($st, ['terlambat', 't', 'late'])) {
                            $status = 'T'; $totalTerlambat++;
                        } elseif (in_array($st, ['sakit', 's', 'sick'])) {
                            $status = 'S'; $totalS++;
                        } elseif (in_array($st, ['izin', 'i', 'permission'])) {
                            $status = 'I'; $totalI++;
                        } else {
                            $status = 'A'; $totalA++;
                        }

                        $rawArrival   = $attendance->arrival_time ? date('H:i', strtotime($attendance->arrival_time)) : '';
                        $rawDeparture = $attendance->departure_time ? date('H:i', strtotime($attendance->departure_time)) : '';

                        $dailyRecord[$d->date] = [
                            'status'        => $status,
                            'in'            => $rawArrival ?: '-',
                            'out'           => $rawDeparture ?: '-',
                            'raw_arrival'   => $rawArrival,
                            'raw_departure' => $rawDeparture,
                            'photo_in'      => $attendance->photo_in ?? null,
                            'photo_out'     => $attendance->photo_out ?? null,
                        ];
                    }
                }

                // Hitung Persentase Kehadiran Harian (Hadir + Terlambat)
                $percentage = $totalMeetings > 0 ? round((($totalH + $totalTerlambat) / $totalMeetings) * 100, 1) : 0;

                // Format nomor HP ke kode negara 62 untuk WA
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
                    'daily'           => $dailyRecord,
                    'total_h'         => $totalH,
                    'total_t'         => $totalTerlambat,
                    'total_s'         => $totalS,
                    'total_i'         => $totalI,
                    'total_a'         => $totalA,
                    'percentage'      => $percentage,
                ];
            });
        }

        return view('recap.harian', compact(
            'classrooms', 'selectedClassroom', 'dates', 'rekapData', 'activeAcademicYear'
        ));
    }

    // 2. AJAX Method: Edit Absensi Harian Individual
    public function updateStatus(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|uuid',
            'date'           => 'required|date',
            'status'         => 'required|in:H,T,S,I,A',
            'arrival_time'   => 'nullable|string',
            'departure_time' => 'nullable|string',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $dbStatusMap = [
            'H' => 'hadir', 'T' => 'terlambat', 'S' => 'sakit', 'I' => 'izin', 'A' => 'alpa'
        ];

        if ($request->status === 'A' && empty($request->arrival_time) && empty($request->departure_time)) {
            DB::table('daily_attendances')
                ->where('student_id', $request->student_id)
                ->where('date', $request->date)
                ->delete();
        } else {
            DB::table('daily_attendances')->updateOrInsert(
                [
                    'student_id' => $request->student_id,
                    'date'       => $request->date,
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'academic_year_id' => $activeYear?->id,
                    'status'           => $dbStatusMap[$request->status],
                    'arrival_time'     => $request->filled('arrival_time') ? $request->arrival_time : null,
                    'departure_time'   => $request->filled('departure_time') ? $request->departure_time : null,
                    'recorded_by'      => auth()->user()->name ?? 'teacher_manual',
                    'is_synced'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Presensi harian berhasil diperbarui!']);
    }

    // 3. AJAX Method: Presensi Harian Massal
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'classroom_id'   => 'required|uuid|exists:classrooms,id',
            'date'           => 'required|date',
            'status'         => 'required|in:H,T,S,I,A',
            'arrival_time'   => 'nullable|string',
            'departure_time' => 'nullable|string',
        ]);

        $students = Student::where('classroom_id', $request->classroom_id)->get();
        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan di kelas ini.'], 404);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $dbStatusMap = [
            'H' => 'hadir', 'T' => 'terlambat', 'S' => 'sakit', 'I' => 'izin', 'A' => 'alpa'
        ];

        foreach ($students as $student) {
            if ($request->status === 'A' && empty($request->arrival_time) && empty($request->departure_time)) {
                DB::table('daily_attendances')
                    ->where('student_id', $student->id)
                    ->where('date', $request->date)
                    ->delete();
            } else {
                DB::table('daily_attendances')->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'date'       => $request->date,
                    ],
                    [
                        'id'               => (string) Str::uuid(),
                        'academic_year_id' => $activeYear?->id,
                        'status'           => $dbStatusMap[$request->status],
                        'arrival_time'     => $request->filled('arrival_time') ? $request->arrival_time : null,
                        'departure_time'   => $request->filled('departure_time') ? $request->departure_time : null,
                        'recorded_by'      => auth()->user()->name ?? 'teacher_bulk',
                        'is_synced'        => true,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Presensi harian massal berhasil disimpan!']);
    }
}