<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use Carbon\Carbon;
use App\Jobs\SendWhatsappJob;

class DailyAttendanceController extends Controller
{
    /**
     * Halaman Scanner Gerbang (Tanpa Jadwal)
     */
    public function index()
    {
        return view('daily_attendance.scan');
    }

    /**
     * Proses Scan Datang / Pulang
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required'
        ]);

        // 1. Cari Siswa
        $student = Student::where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');

        // 2. Cek Data Hari Ini
        $attendance = DailyAttendance::where('student_id', $student->id)
                        ->where('date', $date)
                        ->first();

        // ==========================================================
        // SKENARIO PULANG (DATA SUDAH ADA)
        // ==========================================================
        if ($attendance) {
            if ($attendance->departure_time) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Siswa {$student->name} sudah absen pulang sebelumnya!"
                ]);
            }

            // Update Jam Pulang
            $attendance->update(['departure_time' => $time]);

            // Kirim WA Pulang
            $this->sendNotification($student, 'pulang', $time);

            return response()->json([
                'status' => 'success',
                'type' => 'out',
                'message' => 'Hati-hati di jalan! (Absen Pulang)',
                'student' => $student->name
            ]);
        }

        // ==========================================================
        // SKENARIO DATANG (DATA BARU)
        // ==========================================================

        // Logika Terlambat (Misal masuk jam 07:00)
        $jamMasukSekolah = Carbon::createFromTime(7, 0, 0); // Jam 07:00
        $status = Carbon::now()->greaterThan($jamMasukSekolah) ? 'terlambat' : 'hadir';

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => $date,
            'arrival_time' => $time,
            'status' => $status
        ]);

        // Kirim WA Datang
        $this->sendNotification($student, 'datang', $time, $status);

        return response()->json([
            'status' => 'success',
            'type' => 'in',
            'message' => 'Selamat Datang! (Absen Masuk)',
            'student' => $student->name . ' (' . strtoupper($status) . ')'
        ]);
    }

    private function sendNotification($student, $type, $time, $status = '')
    {
        if (empty($student->phone)) return;

        $tgl = date('d-m-Y');
        $kelas = $student->classroom->name ?? '-';

        if ($type == 'datang') {
            $msg = "*LAPORAN KEDATANGAN (GERBANG)*\n\n" .
                   "Putra/i: *{$student->name}*\n" .
                   "Kelas: {$kelas}\n" .
                   "Waktu: {$time} WIB\n" .
                   "Status: *" . strtoupper($status) . "*\n\n" .
                   "_Sistem Absensi Sekolah_";
        } else {
            $msg = "*LAPORAN KEPULANGAN (GERBANG)*\n\n" .
                   "Putra/i: *{$student->name}*\n" .
                   "Kelas: {$kelas}\n" .
                   "Waktu: {$time} WIB\n" .
                   "Status: *PULANG SEKOLAH*\n\n" .
                   "_Sistem Absensi Sekolah_";
        }

        SendWhatsappJob::dispatch($student->phone, $msg);
    }
}
