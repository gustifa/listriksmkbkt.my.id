<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $todayDate = Carbon::today()->toDateString();

        // Ambil nama hari dalam Bahasa Indonesia dan Inggris
        $todayIndo = Carbon::now()->locale('id')->isoFormat('dddd'); // contoh: 'Senin'
        $todayEng  = Carbon::now()->format('l');                      // contoh: 'Monday'

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

        // 4. Status WhatsApp Gateway
        $waGateway = WhatsappGateway::latest()->first();
        $isWaActive = $waGateway ? ($waGateway->status === 'connected') : false;

        // 4. Timeline PKL / Magang yang STATUSNYA AKTIF
        $internshipTimeline = InternshipTimeline::where(function($query) {
        $query->whereRaw('LOWER(status) = ?', ['aktif'])
              ->orWhereRaw('LOWER(status) = ?', ['active'])
              ->orWhere('status', '1');
        })
        ->latest()
        ->get();

        // Juga sertakan variabel single untuk kompatibilitas
        // $internshipTimeline = $internshipTimelines->get();

        // 6. Jurnal Mengajar Terbaru

        $latestJournal = TeachingJournal::with(['schedule.subject', 'schedule.teacher', 'schedule.user'])
        ->latest()
        ->first();

        // 7. Catatan Tahfiz Terbaru
        $latestTahfiz = TahfizRecord::with('student')
            ->latest()
            ->first();

        return view('welcome', compact(
            'attendanceSetting',
            'stats',
            'isWaActive',
            'internshipTimeline',
            'latestJournal',
            'todaySchedules',
            'todayIndo',
            'latestTahfiz'
        ));
    }
}
