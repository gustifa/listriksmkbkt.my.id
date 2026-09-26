<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// App/Http/Controllers/DashboardController.php
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{

    /**
     * Handle redirection based on user role (jenis_user).
     */
    public function index(Request $request)
    {
        $url = '/dashboard'; // Default URL jika tidak ada yang cocok

        if ($request->user()->jenis_user === 'admin') {
            $url = 'admin/dashboard';
        } elseif ($request->user()->jenis_user === 'wakil') {
            $url = 'staff/dashboard';
        } elseif ($request->user()->jenis_user === 'guru') {
            // Mengarah ke fitur Dashboard Guru yang sudah kita buat sebelumnya
            // Jika ingin menggunakan URL murni sesuai request: $url = 'guru/dashboard';
            return redirect()->route('teacher.dashboard');
        } elseif ($request->user()->jenis_user === 'piket') {
            $url = 'piket/dashboard';
        } elseif ($request->user()->jenis_user === 'siswa') {
            $url = 'student/dashboard';
        }

        return redirect($url);
    }

    public function getRealtimeStats()
    {
    $today = now()->format('Y-m-d');

    $data = [
        // Hitung total kehadiran hari ini
        'present_count' => Attendance::where('date', $today)
                            ->where('status', 'hadir')
                            ->count(),

        // Hitung total terlambat hari ini
        'late_count'    => Attendance::where('date', $today)
                            ->where('status', 'terlambat')
                            ->count(),

        // Total siswa terdaftar
        'total_students'=> User::count(),

        'user' => User::where('status', 1)->count(),
        'student' => Student::count(),
        'attendance' => Attendance::count(),
    ];

    return response()->json($data);
    }
}
