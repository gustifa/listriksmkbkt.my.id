<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\AttendanceSetting;
use App\Models\WhatsappGateway;
use App\Models\TeachingJournal;
use App\Models\TahfizRecord;
use App\Models\InternshipTimeline;
use App\Models\Schedule;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $todayDate   = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->format('H:i:s');

        // Ambil nama hari berjalan
        $todayIndo = Carbon::now()->locale('id')->isoFormat('dddd'); // contoh: 'Minggu'
        $todayEng  = Carbon::now()->format('l');                      

        // List Hari Aktif Sekolah untuk Tab Filter Navigasi Publik
        $daysList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        // Ambil hari yang dipilih dari request URL, jika tidak ada/hari minggu, default ke hari berjalan atau 'senin'
        $selectedDay = strtolower($request->input('day', $todayIndo));
        if ($selectedDay === 'minggu' || !in_array($selectedDay, $daysList)) {
            // Jika diakses hari Minggu/tidak valid, default tampilkan hari Senin agar jadwal tidak kosong
            $selectedDay = 'senin';
        }

        // Closure kondisi status aktif siswa
        $statusCondition = function($q) {
            $q->where('status', '1')
              ->orWhere('status', 1)
              ->orWhere('status', 'aktif')
              ->orWhere('status', 'active');
        };

        // 1. Pengaturan Waktu Absensi
        $attendanceSetting = AttendanceSetting::first();

        // 2. Query Jadwal Pelajaran Berdasarkan Hari yang Dipilih (Public View)
        $todaySchedules = Schedule::with(['subject', 'teacher', 'classroom', 'room'])
            ->whereRaw('LOWER(day) = ?', [$selectedDay])
            ->orderBy('start_time', 'asc')
            ->get();

        // 3. Statistik Kehadiran Hari Ini
        $attendances = DailyAttendance::whereDate('created_at', $todayDate)->get();
        $stats = [
            'hadir'     => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'sakit'     => $attendances->where('status', 'sakit')->count(),
            'alpa'      => $attendances->where('status', 'alpa')->count(),
            'total'     => $attendances->count(),
        ];

        // 4. HITUNG MONITORING REAL-TIME
        $noArrivalCount = Student::whereNotNull('classroom_id')
            ->where($statusCondition)
            ->whereDoesntHave('dailyAttendances', function ($q) use ($todayDate) {
                $q->whereDate('date', $todayDate)
                  ->orWhereDate('created_at', $todayDate);
            })
            ->count();

        $noDepartureCount = Student::whereNotNull('classroom_id')
            ->where($statusCondition)
            ->whereHas('dailyAttendances', function ($q) use ($todayDate) {
                $q->where(function($sub) use ($todayDate) {
                    $sub->whereDate('date', $todayDate)
                        ->orWhereDate('created_at', $todayDate);
                })
                ->whereNotNull('arrival_time')
                ->whereNull('departure_time');
            })
            ->count();

        $troubledSchedulesCount = Schedule::where(function($q) use ($todayIndo, $todayEng) {
                $q->whereRaw('LOWER(day) = ?', [strtolower($todayIndo)])
                  ->orWhereRaw('LOWER(day) = ?', [strtolower($todayEng)]);
            })
            ->where('start_time', '<=', $currentTime)
            ->whereHas('classroom.students', function($q) use ($statusCondition) {
                $q->whereNotNull('classroom_id')->where($statusCondition);
            })
            ->count();

        // 5. Status WhatsApp Gateway
        $waGateway = WhatsappGateway::latest()->first();
        $isWaActive = $waGateway ? ($waGateway->status === 'connected') : false;

        // 6. Timeline PKL / Magang
        $internshipTimeline = InternshipTimeline::where(function($query) {
            $query->whereRaw('LOWER(status) = ?', ['aktif'])
                  ->orWhereRaw('LOWER(status) = ?', ['active'])
                  ->orWhere('status', '1');
        })
        ->latest()
        ->get();

        // 7. Jurnal & Tahfiz
        $latestJournal = TeachingJournal::with('schedule')->latest()->first();
        $latestTahfiz = TahfizRecord::with('student')->latest()->first();

        // --- PERBAIKAN: HITUNG TOTAL POPULASI SISWA AKTIF ---
        $totalActiveStudents = Student::whereNotNull('classroom_id')
            ->where($statusCondition)
            ->count();

        // Total siswa yang secara fisik hadir di sekolah (Tepat Waktu + Terlambat)
        $totalHadirFisik = $stats['hadir'] + $stats['terlambat'];

        // Perhitungan Persentase Kehadiran yang Akurat
        $percentage = ($totalActiveStudents > 0) 
            ? round(($totalHadirFisik / $totalActiveStudents) * 100) 
            : 0;

        // Tambahkan 'percentage' ke compact()

        return view('welcome', compact(
            'attendanceSetting',
            'stats',
            'noArrivalCount',
            'noDepartureCount',
            'troubledSchedulesCount',
            'isWaActive',
            'internshipTimeline',
            'latestJournal',
            'todaySchedules',
            'todayIndo',
            'selectedDay',
            'daysList',
            'latestTahfiz',
            'percentage'
        ));
    }
}