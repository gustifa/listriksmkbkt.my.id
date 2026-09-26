<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PrayerAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Student;

class PrayerMonitoringController extends Controller
{
    /**
     * Menampilkan daftar absensi sholat seluruh siswa
     */
    // public function index(Request $request)
    // {
    //     // Default filter ke hari ini jika tidak ada input tanggal
    //     $date = $request->input('date', Carbon::now()->format('Y-m-d'));

    //     // Mengambil data absensi dengan relasi siswa
    //     // Catatan: Mengambil semua data tanpa limit (sesuai aturan Rule 2 untuk pemrosesan memori)
    //     $attendances = PrayerAttendance::with('student.user')
    //         ->whereDate('date', $date)
    //         ->get()
    //         ->sortByDesc('check_in_time');

    //     return view('admin.prayer.monitoring', compact('attendances', 'date'));
    // }

    /**
     * Menampilkan monitoring sholat yang dikelompokkan per siswa.
     */
    public function index(Request $request)
    {
        // 1. Inisialisasi parameter tanggal (default hari ini)
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        // 2. Daftar waktu sholat sebagai referensi mapping
        $prayerTypes = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];

        // 3. Query Siswa dengan Eager Loading absensi berdasarkan tanggal tertentu
        // Menggunakan Student sebagai primary model agar siswa yang belum absen tetap muncul di list
        $students = Student::with(['user', 'prayer_attendance' => function($query) use ($date) {
            $query->whereDate('date', $date);
        }])
        ->get();

        // 4. Transformasi data: Mengelompokkan status sholat ke dalam satu baris per siswa
        $monitoringData = $students->map(function ($student) use ($prayerTypes) {
            // Mengambil semua absensi siswa dan diubah menjadi key-value (prayer_type => status)
            $attendanceStatus = $student->prayer_attendance->pluck('status', 'prayer_type');

            return (object) [
                'name' => $student->user->name ?? 'Tanpa Nama',
                'nis'  => $student->nis,
                // Kita buat status dinamis untuk setiap waktu sholat
                'statuses' => collect($prayerTypes)->mapWithKeys(function ($type) use ($attendanceStatus) {
                    return [
                        strtolower($type) => $attendanceStatus->get($type, 'Alpha') // Default 'Alpha' jika data kosong
                    ];
                })
            ];
        });

        return view('admin.prayer.monitoring', [
            'data' => $monitoringData,
            'date' => $date,
            'prayerTypes' => $prayerTypes
        ]);
    }

    /**
     * Menampilkan detail lokasi siswa di peta (Opsional jika ingin page khusus)
     */
    public function showLocation($id)
    {
        $attendance = PrayerAttendance::with('student.user')->findOrFail($id);

        return view('admin.prayer.location', compact('attendance'));
    }
}
