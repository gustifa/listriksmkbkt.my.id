<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScannerDevice; // Pastikan buat Model ini
use App\Models\Student;
use App\Models\DailyAttendance;
use Carbon\Carbon;

class ScannerController extends Controller
{
    // 1. Registrasi Perangkat Baru
    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_name' => 'required',
            'device_token' => 'required'
        ]);

        $device = ScannerDevice::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'device_name' => $request->device_name,
                'status' => 'active',
                'last_active_at' => now()
            ]
        );

        return response()->json(['message' => 'Perangkat Terdaftar', 'device' => $device]);
    }

    // 2. Scan Standby (Cepat & Ringan)
    public function deviceScan(Request $request)
    {
        // Validasi Token Perangkat
        $device = ScannerDevice::where('device_token', $request->device_token)
                    ->where('status', 'active')->first();

        if (!$device) {
            return response()->json(['message' => 'Perangkat tidak dikenali / Diblokir'], 403);
        }

        // Update Last Active
        $device->update(['last_active_at' => now()]);

        // Cari Siswa
        $student = Student::where('nisn', $request->qr_content)
                    ->orWhere('nis', $request->qr_content) // Asumsi QR berisi NISN/NIS
                    ->first();

        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan'], 404);
        }

        // Logika Absen Masuk/Pulang
        $today = Carbon::today();
        $attendance = DailyAttendance::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->first();

        $timeNow = now()->format('H:i:s');
        $type = 'Masuk';

        if ($attendance) {
            // Jika sudah masuk, cek apakah sudah pulang
            if ($attendance->departure_time) {
                return response()->json(['status' => 'warning', 'message' => 'Sudah absen pulang hari ini', 'student' => $student]);
            }
            // Lakukan Absen Pulang
            $attendance->update(['departure_time' => $timeNow]);
            $type = 'Pulang';
        } else {
            // Absen Masuk Baru
            DailyAttendance::create([
                'student_id' => $student->id,
                'date' => $today,
                'arrival_time' => $timeNow,
                'status' => 'hadir' // Sesuaikan logika terlambat disini jika perlu
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Absen $type Berhasil",
            'type' => $type,
            'student' => $student,
            'time' => $timeNow
        ]);
    }
}
