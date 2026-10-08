<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentFaceDescriptor;
use App\Models\AttendanceSetting; // Import Model Setting Absensi
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\DailyAttendance;

class StudentSelfServiceController extends Controller
{
    // READ: Menampilkan daftar sampel wajah milik siswa yang sedang login
    public function index()
    {
        $student = Auth::user()->student; // Sesuaikan dengan relasi User -> Student
        $descriptors = StudentFaceDescriptor::where('student_id', $student->id)->latest()->get();

        return view('students.face_index', compact('student', 'descriptors'));
    }


    // DELETE: Menghapus sampel wajah milik siswa sendiri
    public function destroy($id)
    {
        $student = Auth::user()->student;

        // Pastikan hanya bisa menghapus sampel miliknya sendiri
        $descriptor = StudentFaceDescriptor::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $descriptor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sampel wajah berhasil dihapus.'
        ]);
    }


    // Halaman Pendaftaran Wajah Sendiri
    public function registerFaceView()
    {
        $student = Auth::user()->student; // Sesuaikan relasi User -> Student
        return view('students.register_face', compact('student'));
    }

    public function storeFace(Request $request)
    {
        // Validasi minimal 3 sampel descriptor
        $request->validate([
            'descriptors' => 'required|array|min:3',
            'labels'      => 'nullable|array',
        ], [
            'descriptors.min' => 'Anda wajib mengambil minimal 3 sampel foto wajah!',
            'descriptors.required' => 'Belum ada sampel wajah yang diambil.'
        ]);

        $student = Auth::user()->student;

        foreach ($request->descriptors as $index => $descriptorJson) {
            StudentFaceDescriptor::create([
                'student_id' => $student->id,
                'descriptor' => json_decode($descriptorJson, true),
                'label'      => $request->labels[$index] ?? 'Mandiri ' . ($index + 1),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menyimpan 3 sampel wajah!'
        ]);
    }

    // Halaman Scan Mandiri dengan GPS
    public function scanView()
    {
        $student = Auth::user()->student->load('classroom');
        return view('students._scan', compact('student'));
    }

    // API Fetch Descriptor Khusus Siswa Yang Sedang Login
    public function getMyDescriptors()
    {
        $student = Auth::user()->student;
        $descriptors = StudentFaceDescriptor::where('student_id', $student->id)->pluck('descriptor')->toArray();

        return response()->json([
            'label' => $student->nis . ' - ' . $student->name,
            'descriptors' => $descriptors
        ]);
    }

    // public function processScan(Request $request)
    // {
    //     $request->validate([
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //         'image' => 'required',
    //     ]);

    //     // Ambil Pengaturan Absensi dari tabel attendance_settings
    //     $setting = AttendanceSetting::first();

    //     if (!$setting || !$setting->latitude || !$setting->longitude) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Pengaturan koordinat lokasi sekolah belum diset oleh Admin!'
    //         ], 422);
    //     }

    //     $schoolLat = $setting->latitude;
    //     $schoolLng = $setting->longitude;
    //     $maxRadius = $setting->radius_meters ?? 100; // Default 100 meter jika radius_meters null

    //     // Hitung Jarak GPS Menggunakan Rumus Haversine
    //     $distance = $this->calculateDistance($request->latitude, $request->longitude, $schoolLat, $schoolLng);

    //     if ($distance > $maxRadius) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal Absen! Anda di luar area sekolah. Jarak Anda: ' . round($distance) . ' meter dari sekolah.'
    //         ], 422);
    //     }

    //     // =========================================================
    //     // SIMPAN PRESENSI KE DATABASE DI SINI
    //     // =========================================================

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Presensi Berhasil! Jarak Anda: ' . round($distance) . 'm dari lokasi sekolah.'
    //     ]);
    // }

public function processScan(Request $request)
{
    $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'image' => 'required',
    ]);

    // 1. Ambil Pengaturan Lokasi & Jam dari attendance_settings
    $setting = AttendanceSetting::first();

    if (!$setting || !$setting->latitude || !$setting->longitude) {
        return response()->json([
            'status' => 'error',
            'message' => 'Pengaturan lokasi presensi sekolah belum dikonfigurasi oleh Admin!'
        ], 422);
    }

    // 2. Validasi Jarak GPS Menggunakan Rumus Haversine
    $distance = $this->calculateDistance($request->latitude, $request->longitude, $setting->latitude, $setting->longitude);
    $maxRadius = $setting->radius_meters ?? 100;

    if ($distance > $maxRadius) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal Presensi! Anda berada di luar area sekolah. Jarak Anda: ' . round($distance) . ' meter dari sekolah.'
        ], 422);
    }

    $student = Auth::user()->student;
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

    // 3. Simpan Screenshot Bukti Foto Kamera ke Public Storage
    $imagePath = null;
    if ($request->image) {
        $imageParts = explode(";base64,", $request->image);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'jpg';
        $imageBase64 = base64_decode($imageParts[1] ?? '');

        $fileName = 'attendance_' . $student->id . '_' . time() . '.' . $imageType;
        $imagePath = 'attendances/' . $fileName;

        Storage::disk('public')->put($imagePath, $imageBase64);
    }

    // 4. Cek Data Presensi Hari Ini di Tabel daily_attendances
    $attendance = DailyAttendance::where('student_id', $student->id)
        ->where('date', $today)
        ->first();

    // =========================================================
    // A. SKENARIO SCAN MASUK
    // =========================================================
    if (!$attendance) {
        if ($currentTime < $setting->start_check_in_time) {
            return response()->json([
                'status' => 'error',
                'message' => 'Presensi belum dibuka. Jam masuk dimulai pukul ' . $setting->start_check_in_time
            ], 422);
        }

        // Tentukan Status Masuk (Gunakan 'present' / 'late' atau 'Hadir' / 'Terlambat' sesuai constraint PGSQL)
        $status = ($currentTime <= $setting->late_limit_time) ? 'present' : 'late';
        // Ubah ke 'Hadir' / 'Terlambat' jika constraint di database Anda menggunakan Bahasa Indonesia:
        // $status = ($currentTime <= $setting->late_limit_time) ? 'Hadir' : 'Terlambat';

        DailyAttendance::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'date' => $today,
            'arrival_time' => $currentTime,
            'status' => $status,
            'recorded_by' => 'HP Siswa (Mandiri)',
            'photo_in' => $imagePath,
            'academic_year_id' => $student->academic_year_id ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Presensi MASUK Berhasil! Status: {$status} ({$currentTime})"
        ]);
    }

    // =========================================================
    // B. SKENARIO SCAN PULANG
    // =========================================================
    if ($attendance->arrival_time && !$attendance->departure_time) {
        if (isset($setting->early_departure_time) && $currentTime < $setting->early_departure_time) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum waktunya pulang. Jam pulang dimulai pukul ' . $setting->early_departure_time
            ], 422);
        }

        $attendance->update([
            'departure_time' => $currentTime,
            'photo_out' => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Presensi PULANG Berhasil! Jam Pulang: {$currentTime}"
        ]);
    }

    // =========================================================
    // C. SUDAH ABSEN MASUK DAN PULANG
    // =========================================================
    return response()->json([
        'status' => 'error',
        'message' => 'Anda telah menyelesaikan presensi masuk dan pulang untuk hari ini!'
    ], 422);
}

    

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}