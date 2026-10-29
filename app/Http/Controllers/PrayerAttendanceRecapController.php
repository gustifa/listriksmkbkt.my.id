<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PrayerAttendanceRecapController extends Controller
{
    // 1. FORM FILTER KELAS & TANGGAL
    public function index()
    {
        $classrooms = Classroom::where('is_active', true)
                               ->orderBy('name', 'asc')
                               ->get();

        return view('report.prayer_recap_filter', compact('classrooms'));
    }

    // HELPER BASE64 UNTUK EMBED LOGO DI BROWSER PREVIEW & DOMPDF
    private function getBase64Image($path)
    {
        if (!$path) return null;

        $possiblePaths = [
            public_path('storage/' . ltrim($path, '/')),
            storage_path('app/public/' . ltrim($path, '/')),
            public_path(ltrim($path, '/')),
        ];

        foreach ($possiblePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeType  = ($extension === 'jpg' || $extension === 'jpeg') ? 'image/jpeg' : 'image/png';
                $data      = file_get_contents($filePath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($data);
            }
        }

        return null;
    }

    // // 2. HELPER PREPARE DATA SHOLAT MINGGUAN
    // private function prepareData(Request $request)
    // {
    //     $selectedDate = $request->input('date', Carbon::now()->format('Y-m-d'));
    //     $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
    //     $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::FRIDAY);

    //     $weekDates = [
    //         'Senin'  => $startOfWeek->copy()->addDays(0)->format('d/m/Y'),
    //         'Selasa' => $startOfWeek->copy()->addDays(1)->format('d/m/Y'),
    //         'Rabu'   => $startOfWeek->copy()->addDays(2)->format('d/m/Y'),
    //         'Kamis'  => $startOfWeek->copy()->addDays(3)->format('d/m/Y'),
    //         'Jumat'  => $startOfWeek->copy()->addDays(4)->format('d/m/Y'),
    //     ];

    //     $classroomId = trim($request->input('classroom_id'));
    //     $classroom   = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
    //         ->where('id', $classroomId)
    //         ->where('is_active', true)
    //         ->first();

    //     if (!$classroom) {
    //         $classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
    //             ->where('is_active', true)
    //             ->orderBy('name', 'asc')
    //             ->first();
    //     }

    //     $students = Student::where('classroom_id', (string) optional($classroom)->id)
    //         ->orderBy('name', 'asc')
    //         ->get();

    //     $studentIds = $students->pluck('id')->toArray();

    //     // Query tabel prayer_attendances
    //     $prayerRecords = DB::table('prayer_attendances')
    //         ->whereIn('student_id', $studentIds)
    //         ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //         ->get();

    //     // Daftar 5 Sholat Wajib
    //     $prayersList = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

    //     // Map data dari DB ke array [$studentId][$dateRaw][$prayerName]
    //     $rawMap = [];
    //     foreach ($prayerRecords as $rec) {
    //         $pName = strtolower(trim($rec->prayer_name));
    //         $rawMap[$rec->student_id][$rec->date][$pName] = $rec;
    //     }

    //     $prayerMap = [];
    //     $summary   = [];
    //     $todayDate = Carbon::now()->format('Y-m-d');

    //     foreach ($students as $student) {
    //         $summary[$student->id] = ['hadir' => 0, 'alpa' => 0];

    //         foreach ($weekDates as $dayName => $dateFormatted) {
    //             $rawDate = Carbon::createFromFormat('d/m/Y', $dateFormatted)->format('Y-m-d');

    //             foreach ($prayersList as $prayer) {
    //                 $record = $rawMap[$student->id][$rawDate][$prayer] ?? null;

    //                 if ($record) {
    //                     $checkIn = $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : '✓';
    //                     $status  = strtolower(trim($record->status ?? 'hadir'));

    //                     $prayerMap[$student->id][$rawDate][$prayer] = [
    //                         'time'   => $checkIn,
    //                         'status' => $status
    //                     ];

    //                     if ($status === 'hadir' || $status === 's') {
    //                         $summary[$student->id]['hadir']++;
    //                     } else {
    //                         $summary[$student->id]['alpa']++;
    //                     }
    //                 } else {
    //                     // Jika belum ada presensi di hari berlalu/hari ini
    //                     if ($rawDate <= $todayDate) {
    //                         $prayerMap[$student->id][$rawDate][$prayer] = [
    //                             'time'   => 'X',
    //                             'status' => 'alpa'
    //                         ];
    //                         $summary[$student->id]['alpa']++;
    //                     } else {
    //                         $prayerMap[$student->id][$rawDate][$prayer] = [
    //                             'time'   => '-',
    //                             'status' => '-'
    //                         ];
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // Settings Logo & Kop
    //     $settingsRaw  = DB::table('settings')->get()->pluck('value', 'key');
    //     $logoLeftVal  = $settingsRaw['logo_left_st'] ?? $settingsRaw['logo_left'] ?? $settingsRaw['logo_kiri'] ?? null;
    //     $logoRightVal = $settingsRaw['logo_right_st'] ?? $settingsRaw['logo_right'] ?? $settingsRaw['logo_kanan'] ?? null;

    //     $settings = [
    //         'provinsi_name'  => $settingsRaw['provinsi_name'] ?? 'PEMERINTAH PROVINSI SUMATERA BARAT',
    //         'school_name'    => $settingsRaw['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI',
    //         'school_address' => $settingsRaw['school_address'] ?? 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kota Bukittinggi',
    //         'kepsek_nama'    => $settingsRaw['ttd_surat'] ?? 'GUSTIAN BUDIARTO, M.Pd.',
    //         'kepsek_nip'     => $settingsRaw['signature_nip'] ?? '19740731 200801 1 002',
    //         'logo_left'      => $this->getBase64Image($logoLeftVal),
    //         'logo_right'     => $this->getBase64Image($logoRightVal),
    //     ];

    //     $officers = [
    //         'walas_nama' => optional(optional($classroom)->homeroomTeacher)->name ?? 'SATRIAL FITRI',
    //         'walas_nip'  => optional(optional($classroom)->homeroomTeacher)->nip ?? '197510042006041000',
    //         'bk_nama'    => optional(optional($classroom)->counselingTeacher)->name ?? 'BENI OKTA',
    //         'bk_nip'     => optional(optional($classroom)->counselingTeacher)->nip ?? '198010042010011015',
    //     ];

    //     return compact('students', 'classroom', 'weekDates', 'startOfWeek', 'endOfWeek', 'prayerMap', 'summary', 'settings', 'officers', 'prayersList');
    // }

    private function prepareData(Request $request)
{
    $selectedDate =$request->input('date', Carbon::now()->format('Y-m-d'));
    $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
    $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::SUNDAY); // Diubah ke Minggu

    // 7 Hari Kerja / Pekan (Senin - Minggu)
    $weekDates = [
        'Senin'  => $startOfWeek->copy()->addDays(0)->format('d/m/Y'),
        'Selasa' => $startOfWeek->copy()->addDays(1)->format('d/m/Y'),
        'Rabu'   => $startOfWeek->copy()->addDays(2)->format('d/m/Y'),
        'Kamis'  => $startOfWeek->copy()->addDays(3)->format('d/m/Y'),
        'Jumat'  => $startOfWeek->copy()->addDays(4)->format('d/m/Y'),
        'Sabtu'  => $startOfWeek->copy()->addDays(5)->format('d/m/Y'),
        'Minggu' => $startOfWeek->copy()->addDays(6)->format('d/m/Y'),
    ];

    $classroomId = trim($request->input('classroom_id'));$classroom   = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
        ->where('id', $classroomId)
        ->where('is_active', true)
        ->first();

    if (!$classroom) {$classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->first();
    }

    $students = Student::where('classroom_id', (string) optional($classroom)->id)
        ->orderBy('name', 'asc')
        ->get();

    $studentIds =$students->pluck('id')->toArray();

    // Query tabel prayer_attendances
    $prayerRecords = DB::table('prayer_attendances')
        ->whereIn('student_id', $studentIds)
        ->whereBetween('date', [$startOfWeek->format('Y-m-d'),$endOfWeek->format('Y-m-d')])
        ->get();

    $prayersList = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

    $rawMap = [];
    foreach ($prayerRecords as $rec) {$pName = strtolower(trim($rec->prayer_name));$rawMap[$rec->student_id][$rec->date][$pName] =$rec;
    }

    $prayerMap = [];
    $summary   = [];$todayDate = Carbon::now()->format('Y-m-d');

    foreach ($students as$student) {
        $summary[$student->id] = ['hadir' => 0, 'alpa' => 0];

        foreach ($weekDates as $dayName =>$dateFormatted) {
            $rawDate = Carbon::createFromFormat('d/m/Y',$dateFormatted)->format('Y-m-d');

            foreach ($prayersList as $prayer) {$record = $rawMap[$student->id][$rawDate][$prayer] ?? null;

                if ($record) {$checkIn = $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : '✓';
                    $status  = strtolower(trim($record->status ?? 'hadir'));

                    $prayerMap[$student->id][$rawDate][$prayer] = [
                        'time'   => $checkIn,
                        'status' => $status
                    ];

                    if ($status === 'hadir' || $status === 's') {
                        $summary[$student->id]['hadir']++;
                    } else {
                        $summary[$student->id]['alpa']++;
                    }
                } else {
                    if ($rawDate <=$todayDate) {
                        $prayerMap[$student->id][$rawDate][$prayer] = [
                            'time'   => 'X',
                            'status' => 'alpa'
                        ];
                        $summary[$student->id]['alpa']++;
                    } else {
                        $prayerMap[$student->id][$rawDate][$prayer] = [
                            'time'   => '-',
                            'status' => '-'
                        ];
                    }
                }
            }
        }
    }

    $settingsRaw  = DB::table('settings')->get()->pluck('value', 'key');
    $logoLeftVal  =$settingsRaw['logo_left_st'] ?? $settingsRaw['logo_left'] ?? $settingsRaw['logo_kiri'] ?? null;
    $logoRightVal =$settingsRaw['logo_right_st'] ?? $settingsRaw['logo_right'] ?? $settingsRaw['logo_kanan'] ?? null;

    $settings = [
        'provinsi_name'  => $settingsRaw['provinsi_name'] ?? 'PEMERINTAH PROVINSI SUMATERA BARAT',
        'school_name'    => $settingsRaw['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI',
        'school_address' => $settingsRaw['school_address'] ?? 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kota Bukittinggi',
        'kepsek_nama'    => $settingsRaw['ttd_surat'] ?? 'GUSTIAN BUDIARTO, M.Pd.',
        'kepsek_nip'     => $settingsRaw['signature_nip'] ?? '19740731 200801 1 002',
        'logo_left'      => $this->getBase64Image($logoLeftVal),
        'logo_right'     => $this->getBase64Image($logoRightVal),
    ];

    $officers = [
        'walas_nama' => optional(optional($classroom)->homeroomTeacher)->name ?? 'SATRIAL FITRI',
        'walas_nip'  => optional(optional($classroom)->homeroomTeacher)->nip ?? '197510042006041000',
        'bk_nama'    => optional(optional($classroom)->counselingTeacher)->name ?? 'BENI OKTA',
        'bk_nip'     => optional(optional($classroom)->counselingTeacher)->nip ?? '198010042010011015',
    ];

    return compact('students', 'classroom', 'weekDates', 'startOfWeek', 'endOfWeek', 'prayerMap', 'summary', 'settings', 'officers', 'prayersList');
}

    // LOG AKTIVITAS
    private function logActivity($action, $classroom, $startOfWeek, $endOfWeek)
    {
        try {
            $className   = $classroom->name ?? 'Semua Kelas';
            $period      = $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y');
            $user        = Auth::user();
            $userName    = $user ? $user->name : 'Sistem/Guest';
            $description = "{$userName} {$action} Rekap Presensi Sholat Kelas {$className} Periode {$period}";

            DB::table('activity_log')->insert([
                'log_name'     => 'prayer_attendance_recap',
                'description'  => $description,
                'subject_type' => 'App\Models\Classroom',
                'subject_id'   => optional($classroom)->id,
                'causer_type'  => $user ? get_class($user) : null,
                'causer_id'    => $user ? $user->id : null,
                'properties'   => json_encode([
                    'classroom'  => $className,
                    'start_date' => $startOfWeek->format('Y-m-d'),
                    'end_date'   => $endOfWeek->format('Y-m-d'),
                    'action'     => $action
                ]),
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]);
        } catch (\Exception $e) {}
    }

    // 3. PREVIEW WEB
    public function preview(Request $request)
    {
        $data = $this->prepareData($request);
        $this->logActivity('melihat pratinjau', $data['classroom'], $data['startOfWeek'], $data['endOfWeek']);

        return view('report.prayer_recap_preview', $data);
    }

    // 4. DOWNLOAD PDF
    public function downloadPdf(Request $request)
    {
        $data = $this->prepareData($request);
        $this->logActivity('mengunduh PDF', $data['classroom'], $data['startOfWeek'], $data['endOfWeek']);

        $pdf = Pdf::loadView('report.prayer_recap_pdf', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
                  ->setWarnings(false);

        return $pdf->download('Rekap_Presensi_Sholat_' . str_replace(' ', '_', $data['classroom']->name ?? 'Kelas') . '.pdf');
    }
}