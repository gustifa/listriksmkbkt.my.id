<?php

namespace App\Services\Attendance;

use App\Models\Student;
use App\Models\AttendanceSetting;
use App\Services\Telegram\GeminiAiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentAttendanceService
{
    protected GeminiAiService $aiService;

    public function __construct(GeminiAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Perhitungan Jarak GPS (Rumus Haversine) dalam Meter
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Eksekusi Absensi Mandiri Siswa
     */
    public function processSelfAttendance(Student $student, float $userLat, float $userLng, ?string $photoPath = null): array
    {
        $setting = AttendanceSetting::first();
        
        // Koordinat Default SMKN 1 Bukittinggi (Bisa disesuaikan di AttendanceSetting)
        $schoolLat = $setting?->school_latitude ?? -0.30521;
        $schoolLng = $setting?->school_longitude ?? 100.36912;
        $maxRadius = $setting?->max_radius_meters ?? 100; // Maksimal 100 meter

        // 1. Cek Radius Lokasi GPS
        $distance = $this->calculateDistance($userLat, $userLng, $schoolLat, $schoolLng);

        if ($distance > $maxRadius) {
            return [
                'status'  => false,
                'message' => "❌ *Absensi Gagal!*\n\n" .
                             "Lokasi Anda terdeteksi *" . round($distance) . " meter* dari sekolah.\n" .
                             "Batas maksimal radius adalah *{$maxRadius} meter*. Silakan absen di dalam area sekolah."
            ];
        }

        // 2. Simpan Data Kehadiran ke PostgreSQL
        $today = date('Y-m-d');
        $timeNow = date('H:i:s');

        DB::table('daily_attendances')->updateOrInsert(
            [
                'student_id' => $student->id,
                'date'       => $today,
            ],
            [
                'status'      => 'hadir',
                'recorded_by' => 'Absen Mandiri Bot (GPS & Selfie)',
                'updated_at'  => now(),
                'created_at'  => now(),
                'is_synced'   => true,
            ]
        );

        return [
            'status'  => true,
            'message' => "✅ *Absensi Masuk Berhasil!*\n\n" .
                         "👤 Nama: *{$student->name}*\n" .
                         "📅 Tanggal: *{$today}*\n" .
                         "⏰ Waktu: *{$timeNow} WIB*\n" .
                         "📍 Jarak dari Sekolah: *" . round($distance) . " meter*\n" .
                         "🟢 Status: *Hadir (Terverifikasi GPS)*"
        ];
    }
}