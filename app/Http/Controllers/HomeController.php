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
    public function index()
    {
        $todayDate   = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->format('H:i:s');

        // Ambil nama hari dalam Bahasa Indonesia dan Inggris
        $todayIndo = Carbon::now()->locale('id')->isoFormat('dddd'); // contoh: 'Senin'
        $todayEng  = Carbon::now()->format('l');                      // contoh: 'Monday'

        // Closure kondisi status aktif siswa
        $statusCondition = function($q) {
            $q->where('status', '1')
              ->orWhere('status', 1)
              ->orWhere('status', 'aktif')
              ->orWhere('status', 'active');
        };

        // 1. Pengaturan Waktu Absensi
        $attendanceSetting = AttendanceSetting::first();

        // 2. Query Jadwal Pelajaran Hari Ini (Case-Insensitive & Multi-Language)
        $todaySchedules = Schedule::with(['subject', 'teacher', 'classroom', 'room'])
            ->where(function($query) use ($todayIndo, $todayEng) {
                $query->whereRaw('LOWER(day) = ?', [strtolower($todayIndo)])
                      ->orWhereRaw('LOWER(day) = ?', [strtolower($todayEng)]);
            })
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

        // 4. HITUNG MONITORING REAL-TIME (BELUM ABSEN MASUK, PULANG & MAPEL)
        // A. Belum Absen Masuk Gerbang (Hanya siswa berkelas)
        $noArrivalCount = Student::whereNotNull('classroom_id')
            ->where($statusCondition)
            ->whereDoesntHave('dailyAttendances', function ($q) use ($todayDate) {
                $q->whereDate('date', $todayDate)
                  ->orWhereDate('created_at', $todayDate);
            })
            ->count();

        // B. Belum Absen Pulang Gerbang (Hanya siswa berkelas)
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

        // C. Jadwal Mapel Bermasalah Hari Ini
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

        // 6. Timeline PKL / Magang yang STATUSNYA AKTIF
        $internshipTimeline = InternshipTimeline::where(function($query) {
            $query->whereRaw('LOWER(status) = ?', ['aktif'])
                  ->orWhereRaw('LOWER(status) = ?', ['active'])
                  ->orWhere('status', '1');
        })
        ->latest()
        ->get();

        // 7. Jurnal Mengajar Terbaru
        $latestJournal = TeachingJournal::with('schedule')->latest()->first();

        // 8. Catatan Tahfiz Terbaru
        $latestTahfiz = TahfizRecord::with('student')->latest()->first();

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
            'latestTahfiz'
        ));
    }
}