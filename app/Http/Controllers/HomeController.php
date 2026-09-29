<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyAttendance;
use App\Models\AttendanceSetting;
use App\Models\WhatsappGateway;
use App\Models\TeachingJournal;
use App\Models\TahfizRecord;
use App\Models\InternshipTimeline;
use App\Models\Schedule; // Sesuaikan dengan nama Model Jadwal Anda
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Pengaturan Waktu Absensi
        $attendanceSetting = AttendanceSetting::first();

        // Ambil jadwal pelajaran berdasarkan hari aktif
        // Sesuaikan nama tabel/relasi jika nama model & relasinya berbeda
        $todaySchedules = Schedule::with(['subject', 'teacher', 'classroom'])
            ->where('day', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        // 2. Statistik Kehadiran Hari Ini
        $attendances = DailyAttendance::whereDate('created_at', $today)->get();
        $stats = [
            'hadir'     => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'sakit'     => $attendances->where('status', 'sakit')->count(),
            'alpa'      => $attendances->where('status', 'alpa')->count(),
            'total'     => $attendances->count(),
        ];

        // 3. Status WhatsApp Gateway
        $waGateway = WhatsappGateway::latest()->first();
        $isWaActive = $waGateway ? ($waGateway->status === 'connected') : false;

        // 4. Timeline PKL / Magang Terbaru
        $internshipTimeline = InternshipTimeline::latest()->first();

        // 5. Jurnal Mengajar Terbaru
        $latestJournal = TeachingJournal::with(['subject', 'teacher'])
            ->latest()
            ->first();

        // 6. Catatan Tahfiz Terbaru
        $latestTahfiz = TahfizRecord::with('student')
            ->latest()
            ->first();

        return view('welcome', compact(
            'attendanceSetting',
            'stats',
            'isWaActive',
            'internshipTimeline',
            'latestJournal',
            'todaySchedules', // Tambahkan variabel ini ke view
            'latestTahfiz'
        ));
    }
}
