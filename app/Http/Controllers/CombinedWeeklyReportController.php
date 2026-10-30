<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CombinedWeeklyReportController extends Controller
{
    // 1. TAMPILAN FILTER KELAS & TANGGAL
    public function index()
    {
        $classrooms = Classroom::where('is_active', true)
                               ->orderBy('name', 'asc')
                               ->get();

        return view('report.combined_recap_filter', compact('classrooms'));
    }

    // HELPER BASE64 DARI STORAGE DB
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

    // 2. HELPER UNTUK MENYIAPKAN SELURUH DATA 3 HALAMAN
    private function prepareCombinedData(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::SUNDAY);
        $todayDate    = Carbon::now()->format('Y-m-d');

        // Generate Waktu Cetak Dokumen (Hari, Tanggal & Jam)
        $generatedAt  = Carbon::now()->translatedFormat('l, d F Y - H:i:s') . ' WIB';

        // Mapping Tanggal
        $weekDates = [
            'Senin'  => $startOfWeek->copy()->addDays(0)->format('d/m/Y'),
            'Selasa' => $startOfWeek->copy()->addDays(1)->format('d/m/Y'),
            'Rabu'   => $startOfWeek->copy()->addDays(2)->format('d/m/Y'),
            'Kamis'  => $startOfWeek->copy()->addDays(3)->format('d/m/Y'),
            'Jumat'  => $startOfWeek->copy()->addDays(4)->format('d/m/Y'),
            'Sabtu'  => $startOfWeek->copy()->addDays(5)->format('d/m/Y'),
            'Minggu' => $startOfWeek->copy()->addDays(6)->format('d/m/Y'),
        ];

        // Detail Kelas Aktif
        $classroomId = trim($request->input('classroom_id'));
        $classroom   = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
            ->where('id', $classroomId)
            ->where('is_active', true)
            ->first();

        if (!$classroom) {
            $classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->first();
        }

        $students = Student::where('classroom_id', (string) optional($classroom)->id)
            ->orderBy('name', 'asc')
            ->get();
        $studentIds = $students->pluck('id')->toArray();

        // Settings Kop Surat
        $settingsRaw  = DB::table('settings')->get()->pluck('value', 'key');
        $logoLeftVal  = $settingsRaw['logo_left_st'] ?? $settingsRaw['logo_left'] ?? $settingsRaw['logo_kiri'] ?? null;
        $logoRightVal = $settingsRaw['logo_right_st'] ?? $settingsRaw['logo_right'] ?? $settingsRaw['logo_kanan'] ?? null;

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

        // -------------------------------------------------------------------
        // HALAMAN 1: REKAP PEMBELAJARAN (MATRIKS JAM KE 1-10)
        // -------------------------------------------------------------------
        $lessonSlots = DB::table('time_slots')->where('type', 'lesson')->orderBy('start_time', 'asc')->get();
        $dayVariations = [
            1 => ['Senin', 'senin', 'Monday', 'monday', '1'],
            2 => ['Selasa', 'selasa', 'Tuesday', 'tuesday', '2'],
            3 => ['Rabu', 'rabu', 'Wednesday', 'wednesday', '3'],
            4 => ['Kamis', 'kamis', 'Thursday', 'thursday', '4'],
            5 => ['Jumat', 'jumat', 'Friday', 'friday', '5'],
        ];

        $daySubjectMap = [];
        $scheduleJamMap = [];
        $schedulesPerDay = [];
        $daysList = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];

        foreach ($dayVariations as $dayNum => $variations) {
            $indonesianDay = $daysList[$dayNum];
            for ($j = 1; $j <= 10; $j++) { $daySubjectMap[$indonesianDay][$j] = ''; }

            $schedules = Schedule::with(['subject'])
                ->where('classroom_id', (string) optional($classroom)->id)
                ->whereIn('day', $variations)
                ->orderBy('start_time', 'asc')
                ->get();

            $schedulesPerDay[$dayNum] = $schedules;

            foreach ($schedules as $sch) {
                $subjectCode = $sch->subject->code ?? '';
                foreach ($lessonSlots as $index => $slot) {
                    $jamNum = $index + 1;
                    if ($slot->start_time >= $sch->start_time && $slot->end_time <= $sch->end_time) {
                        $daySubjectMap[$indonesianDay][$jamNum] = $subjectCode;
                        $scheduleJamMap[$sch->id][] = $jamNum;
                    }
                }
            }
        }

        $classAttendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $startOfWeek->copy()->addDays(4)->format('Y-m-d')])
            ->get();

        $learningRecapMap = [];
        $learningSummary  = [];

        foreach ($students as $student) {
            $learningSummary[$student->id] = ['sakit' => 0, 'izin' => 0, 'alpa' => 0];
            for ($day = 1; $day <= 5; $day++) {
                for ($jam = 1; $jam <= 10; $jam++) {
                    $learningRecapMap[$student->id][$day][$jam] = '';
                }
            }
        }

        foreach ($classAttendances as $att) {
            $dayOfWeek = Carbon::parse($att->date)->dayOfWeekIso;
            if ($dayOfWeek <= 5 && isset($learningRecapMap[$att->student_id])) {
                $stLower = strtolower(trim($att->status));
                $code = match($stLower) { 
                    'hadir'   => 'v', 
                    'sakit'   => 'S', 
                    'izin'    => 'I', 
                    default   => 'X' 
                };
                $jamIndexes = $scheduleJamMap[$att->schedule_id] ?? [$att->jam_ke ?? 1];

                foreach ($jamIndexes as $jIdx) {
                    if ($jIdx >= 1 && $jIdx <= 10) {
                        $learningRecapMap[$att->student_id][$dayOfWeek][$jIdx] = $code;
                    }
                }
                if ($stLower === 'sakit') $learningSummary[$att->student_id]['sakit']++;
                elseif ($stLower === 'izin') $learningSummary[$att->student_id]['izin']++;
            }
        }

        // Oto-fill 'X' Merah Pembelajaran
        foreach ($students as $student) {
            for ($day = 1; $day <= 5; $day++) {
                $curDate = $startOfWeek->copy()->addDays($day - 1)->format('Y-m-d');
                if ($curDate > $todayDate) continue;

                foreach ($schedulesPerDay[$day] as $sch) {
                    $jamIndexes = $scheduleJamMap[$sch->id] ?? [];
                    if (!empty($jamIndexes)) {
                        $firstJam = $jamIndexes[0];
                        if (empty($learningRecapMap[$student->id][$day][$firstJam])) {
                            foreach ($jamIndexes as $jIdx) {
                                $learningRecapMap[$student->id][$day][$jIdx] = 'X';
                            }
                            $learningSummary[$student->id]['alpa']++;
                        }
                    }
                }
            }
        }

        // -------------------------------------------------------------------
        // HALAMAN 2: REKAP ABSENSI GERBANG (SENIN - JUMAT)
        // -------------------------------------------------------------------
        $dailyRecords = DB::table('daily_attendances')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $startOfWeek->copy()->addDays(4)->format('Y-m-d')])
            ->get();

        $gateDbRecords = [];
        foreach ($dailyRecords as $record) { $gateDbRecords[$record->student_id][$record->date] = $record; }

        $gateMap = [];
        $gateSummary = [];

        foreach ($students as $student) {
            $gateSummary[$student->id] = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

            foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $dayName) {
                $dateFormatted = $weekDates[$dayName];
                $rawDate = Carbon::createFromFormat('d/m/Y', $dateFormatted)->format('Y-m-d');
                $rec = $gateDbRecords[$student->id][$rawDate] ?? null;

                if ($rec) {
                    $arrTime = $rec->arrival_time ? Carbon::parse($rec->arrival_time)->format('H:i') : '-';
                    $depTime = $rec->departure_time ? Carbon::parse($rec->departure_time)->format('H:i') : '-';
                    $st = strtolower(trim($rec->status ?? ''));

                    if (empty($st) || in_array($st, ['tanpa keterangan', 'alpa', 'a', 'x'])) $st = 'alpa';

                    $gateMap[$student->id][$rawDate] = ['arrival' => $arrTime, 'departure' => $depTime, 'status' => $st];

                    if ($st === 'hadir') $gateSummary[$student->id]['hadir']++;
                    elseif ($st === 'terlambat') $gateSummary[$student->id]['terlambat']++;
                    elseif ($st === 'sakit') $gateSummary[$student->id]['sakit']++;
                    elseif ($st === 'izin') $gateSummary[$student->id]['izin']++;
                    else $gateSummary[$student->id]['alpa']++;

                } else {
                    if ($rawDate <= $todayDate) {
                        $gateMap[$student->id][$rawDate] = ['arrival' => '-', 'departure' => '-', 'status' => 'alpa'];
                        $gateSummary[$student->id]['alpa']++;
                    } else {
                        $gateMap[$student->id][$rawDate] = ['arrival' => '-', 'departure' => '-', 'status' => '-'];
                    }
                }
            }
        }

        // -------------------------------------------------------------------
        // HALAMAN 3: REKAP ABSENSI SHOLAT (7 HARI FULL SENIN - MINGGU)
        // -------------------------------------------------------------------
        $prayerRecords = DB::table('prayer_attendances')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->get();

        $prayersList = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
        $prayerDbMap = [];
        foreach ($prayerRecords as $rec) {
            $pName = strtolower(trim($rec->prayer_name));
            $prayerDbMap[$rec->student_id][$rec->date][$pName] = $rec;
        }

        $prayerMap = [];
        $prayerSummary = [];

        foreach ($students as $student) {
            $prayerSummary[$student->id] = ['hadir' => 0, 'alpa' => 0];

            foreach ($weekDates as $dayName => $dateFormatted) {
                $rawDate = Carbon::createFromFormat('d/m/Y', $dateFormatted)->format('Y-m-d');

                foreach ($prayersList as $prayer) {
                    $rec = $prayerDbMap[$student->id][$rawDate][$prayer] ?? null;

                    if ($rec) {
                        $checkIn = $rec->check_in_time ? Carbon::parse($rec->check_in_time)->format('H:i') : 'v';
                        $st = strtolower(trim($rec->status ?? 'hadir'));

                        $prayerMap[$student->id][$rawDate][$prayer] = ['time' => $checkIn, 'status' => $st];
                        if ($st === 'hadir' || $st === 's') $prayerSummary[$student->id]['hadir']++;
                        else $prayerSummary[$student->id]['alpa']++;

                    } else {
                        if ($rawDate <= $todayDate) {
                            $prayerMap[$student->id][$rawDate][$prayer] = ['time' => 'X', 'status' => 'alpa'];
                            $prayerSummary[$student->id]['alpa']++;
                        } else {
                            $prayerMap[$student->id][$rawDate][$prayer] = ['time' => '-', 'status' => '-'];
                        }
                    }
                }
            }
        }

        return compact(
            'students', 'classroom', 'weekDates', 'startOfWeek', 'endOfWeek', 'settings', 'officers',
            'daySubjectMap', 'learningRecapMap', 'learningSummary',
            'gateMap', 'gateSummary',
            'prayerMap', 'prayerSummary',
            'generatedAt' // Passing variabel timestamp generate ke Blade
        );
    }

    // 3. PREVIEW BROWSER
    public function preview(Request $request)
    {
        $data = $this->prepareCombinedData($request);
        return view('report.combined_recap_preview', $data);
    }

    // 4. DOWNLOAD PDF (DOMPDF 3 HALAMAN)
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $data = $this->prepareCombinedData($request);

        $pdf = Pdf::loadView('report.combined_recap_pdf', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
                  ->setWarnings(false);

        return $pdf->download('Laporan_Mingguan_Terpadu_' . str_replace(' ', '_', $data['classroom']->name ?? 'Kelas') . '.pdf');
    }
}