<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyAttendanceRecapController extends Controller
{
    // 1. TAMPILAN FILTER (HANYA KELAS AKTIF)
    public function index()
    {
        $classrooms = Classroom::where('is_active', true)
                               ->orderBy('name', 'asc')
                               ->get();

        return view('report.daily_recap_filter', compact('classrooms'));
    }

    // 2. HELPER PREPARE DATA & CONVERT LOGO TO BASE64
    private function prepareData(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::FRIDAY);

        $weekDates = [
            'Senin'  => $startOfWeek->copy()->addDays(0)->format('Y-m-d'),
            'Selasa' => $startOfWeek->copy()->addDays(1)->format('Y-m-d'),
            'Rabu'   => $startOfWeek->copy()->addDays(2)->format('Y-m-d'),
            'Kamis'  => $startOfWeek->copy()->addDays(3)->format('Y-m-d'),
            'Jumat'  => $startOfWeek->copy()->addDays(4)->format('Y-m-d'),
        ];

        $classroomId = trim($request->input('classroom_id'));
        $classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader', 'major'])
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

        // Query Absensi Gerbang dari tabel daily_attendances
        $studentIds = $students->pluck('id')->toArray();
        $dailyRecords = DB::table('daily_attendances')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->get();

        $attendanceMap = [];
        $summary = [];

        foreach ($students as $student) {
            $summary[$student->id] = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
        }

        foreach ($dailyRecords as $record) {
            $date = $record->date;
            $sId  = $record->student_id;

            $arrivalTime   = $record->arrival_time ? Carbon::parse($record->arrival_time)->format('H:i') : '-';
            $departureTime = $record->departure_time ? Carbon::parse($record->departure_time)->format('H:i') : '-';
            $status        = strtolower(trim($record->status));

            $attendanceMap[$sId][$date] = [
                'arrival'   => $arrivalTime,
                'departure' => $departureTime,
                'status'    => $status
            ];

            if (isset($summary[$sId])) {
                if ($status === 'hadir') $summary[$sId]['hadir']++;
                elseif ($status === 'terlambat') $summary[$sId]['terlambat']++;
                elseif ($status === 'sakit') $summary[$sId]['sakit']++;
                elseif ($status === 'izin') $summary[$sId]['izin']++;
                elseif ($status === 'alpa' || $status === 'x') $summary[$sId]['alpa']++;
            }
        }

        // Ambil Data Settings
        $settingsRaw = DB::table('settings')->get()->pluck('value', 'key');

        // Helper Konversi Logo File/Path DB ke Base64 untuk Render DomPDF & Browser
        $getLogoBase64 = function($key) use ($settingsRaw) {
            $val = $settingsRaw[$key] ?? null;
            if (!$val) return null;

            $possiblePaths = [
                public_path('storage/' . ltrim($val, '/')),
                storage_path('app/public/' . ltrim($val, '/')),
                public_path(ltrim($val, '/')),
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
        };

        $settings = [
            'provinsi_name'  => $settingsRaw['provinsi_name'] ?? 'PEMERINTAH PROVINSI SUMATERA BARAT',
            'school_name'    => $settingsRaw['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI',
            'school_address' => $settingsRaw['school_address'] ?? 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kota Bukittinggi',
            'school_email'   => $settingsRaw['school_email'] ?? 'info@smkn1bukittinggi.sch.id',
            'school_web'     => $settingsRaw['school_web'] ?? 'www.smkn1bukittinggi.sch.id',
            'school_phone'   => $settingsRaw['school_phone'] ?? '',
            'kepsek_nama'    => $settingsRaw['ttd_surat'] ?? 'GUSTIAN BUDIARTO, M.Pd.',
            'kepsek_nip'     => $settingsRaw['signature_nip'] ?? '19740731 200801 1 002',
            'logo_left'      => $getLogoBase64('logo_left'),
            'logo_right'     => $getLogoBase64('logo_right'),
        ];

        $officers = [
            'walas_nama' => optional(optional($classroom)->homeroomTeacher)->name ?? 'SATRIAL FITRI',
            'walas_nip'  => optional(optional($classroom)->homeroomTeacher)->nip ?? '197510042006041000',
            'bk_nama'    => optional(optional($classroom)->counselingTeacher)->name ?? 'BENI OKTA',
            'bk_nip'     => optional(optional($classroom)->counselingTeacher)->nip ?? '198010042010011015',
        ];

        return compact('students', 'classroom', 'weekDates', 'startOfWeek', 'endOfWeek', 'attendanceMap', 'summary', 'settings', 'officers');
    }

    // HELPER MENCATAT LOG AKTIVITAS
    private function logActivity($action, $classroom, $startOfWeek, $endOfWeek)
    {
        try {
            $className = $classroom->name ?? 'Semua Kelas';
            $period = $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y');
            $user = Auth::user();
            $userName = $user ? $user->name : 'Sistem/Guest';

            $description = "{$userName} {$action} Rekap Presensi Gerbang Kelas {$className} Periode {$period}";

            DB::table('activity_log')->insert([
                'log_name'     => 'daily_attendance_recap',
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
        } catch (\Exception $e) {
            // Silence exception so log failure doesn't block response
        }
    }

    // 3. PREVIEW WEB
    public function preview(Request $request)
    {
        $data = $this->prepareData($request);
        $this->logActivity('melihat pratinjau', $data['classroom'], $data['startOfWeek'], $data['endOfWeek']);

        return view('report.daily_recap_preview', $data);
    }

    // 4. DOWNLOAD PDF (DOMPDF)
    public function downloadPdf(Request $request)
    {
        $data = $this->prepareData($request);
        $this->logActivity('mengunduh PDF', $data['classroom'], $data['startOfWeek'], $data['endOfWeek']);

        $pdf = Pdf::loadView('report.daily_recap_pdf', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
                  ->setWarnings(false);

        return $pdf->download('Rekap_Absensi_Gerbang_' . str_replace(' ', '_', $data['classroom']->name ?? 'Kelas') . '.pdf');
    }
}