<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\InternshipAttendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InternshipAttendanceController extends Controller
{
    // /**
    //  * Halaman Utama Absensi PKL (Form & Riwayat)
    //  */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();

    //     // 1. Cek Data PKL Aktif
    //     $internship = Internship::with('industry')
    //         ->where('student_id', $student->id)
    //         ->where('status', 'active') // Hanya yang statusnya AKTIF
    //         ->first();

    //     if (!$internship) {
    //         return redirect()->route('student.internships.index')
    //             ->with('error', 'Anda belum memiliki status PKL Aktif. Silakan ajukan atau tunggu persetujuan.');
    //     }

    //     // 2. Cek Apakah Sudah Absen Hari Ini
    //     $todayAttendance = InternshipAttendance::where('internship_id', $internship->id)
    //         ->where('date', Carbon::today())
    //         ->first();

    //     // 3. Ambil Riwayat Absensi (7 Hari Terakhir)
    //     $history = InternshipAttendance::where('internship_id', $internship->id)
    //         ->orderBy('date', 'desc')
    //         ->limit(10)
    //         ->get();

    //     return view('siswa.internships.attendance', compact('internship', 'todayAttendance', 'history'));
    // }

    // /**
    //  * Proses Simpan Absensi
    //  */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'status' => 'required|in:present,sick,permit',
    //         'activity_log' => 'required|string|min:10', // Wajib isi jurnal
    //         'photo' => 'required_if:status,present|image|max:3072', // Wajib foto jika hadir
    //         'latitude' => 'nullable',
    //         'longitude' => 'nullable',
    //     ], [
    //         'activity_log.min' => 'Jurnal kegiatan minimal 10 karakter.',
    //         'photo.required_if' => 'Foto selfie wajib diupload jika status Hadir.'
    //     ]);

    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();

    //     $internship = Internship::where('student_id', $student->id)
    //         ->where('status', 'active')
    //         ->firstOrFail();

    //     // Cek Double Input
    //     $exists = InternshipAttendance::where('internship_id', $internship->id)
    //         ->where('date', Carbon::today())
    //         ->exists();

    //     if ($exists) {
    //         return back()->with('error', 'Anda sudah mengisi absensi hari ini.');
    //     }

    //     // Upload Foto
    //     $photoPath = null;
    //     if ($request->hasFile('photo')) {
    //         $photoPath = $request->file('photo')->store('pkl_attendances', 'public');
    //     }

    //     InternshipAttendance::create([
    //         'internship_id' => $internship->id,
    //         'student_id' => $student->id,
    //         'date' => Carbon::today(),
    //         'time' => Carbon::now()->format('H:i:s'),
    //         'status' => $request->status,
    //         'activity_log' => $request->activity_log,
    //         'photo_path' => $photoPath,
    //         'latitude' => $request->latitude,
    //         'longitude' => $request->longitude,
    //     ]);

    //     return back()->with('success', 'Absensi dan Jurnal Kegiatan berhasil disimpan!');
    // }

    // /**
    //  * Halaman Utama Absensi PKL (Form & Riwayat)
    //  */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();

    //     // 1. Cek Data PKL Aktif
    //     $internship = Internship::with('industry')
    //         ->where('student_id', $student->id)
    //         ->where('status', 'active')
    //         ->first();

    //     if (!$internship) {
    //         return redirect()->route('student.internships.index')
    //             ->with('error', 'Anda belum memiliki status PKL Aktif. Silakan ajukan atau tunggu persetujuan.');
    //     }

    //     // 2. Cek Apakah Sudah Absen Hari Ini
    //     $todayAttendance = InternshipAttendance::where('internship_id', $internship->id)
    //         ->where('date', Carbon::today())
    //         ->first();

    //     // 3. Ambil Riwayat Absensi (10 Hari Terakhir)
    //     $history = InternshipAttendance::where('internship_id', $internship->id)
    //         ->orderBy('date', 'desc')
    //         ->limit(10)
    //         ->get();

    //     return view('siswa.internships.attendance', compact('internship', 'todayAttendance', 'history'));
    // }

    // /**
    //  * Proses Simpan Absensi (Datang & Pulang)
    //  */
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();

    //     $internship = Internship::where('student_id', $student->id)
    //         ->where('status', 'active')
    //         ->firstOrFail();

    //     $type = $request->input('type'); // 'check_in' atau 'check_out'

    //     // ==========================================================
    //     // LOGIKA ABSEN DATANG (CHECK IN)
    //     // ==========================================================
    //     if ($type === 'check_in') {

    //         $request->validate([
    //             'status' => 'required|in:present,sick,permit',
    //             'photo' => 'required_if:status,present|image|max:3072', // Wajib foto jika hadir
    //             'latitude' => 'nullable',
    //             'longitude' => 'nullable',
    //         ], [
    //             'photo.required_if' => 'Foto selfie wajib diupload jika status Hadir.'
    //         ]);

    //         // Cek Double Input
    //         $exists = InternshipAttendance::where('internship_id', $internship->id)
    //             ->where('date', Carbon::today())
    //             ->exists();

    //         if ($exists) {
    //             return back()->with('error', 'Anda sudah melakukan absen datang hari ini.');
    //         }

    //         // Upload Foto Masuk
    //         $photoPath = null;
    //         if ($request->hasFile('photo')) {
    //             $photoPath = $request->file('photo')->store('pkl_attendances', 'public');
    //         }

    //         // Simpan Data
    //         InternshipAttendance::create([
    //             'internship_id' => $internship->id,
    //             'student_id' => $student->id,
    //             'date' => Carbon::today(),
    //             'time' => Carbon::now()->format('H:i:s'), // Jam Datang
    //             'status' => $request->status,
    //             'photo_path' => $photoPath,
    //             'latitude' => $request->latitude,
    //             'longitude' => $request->longitude,
    //             // Jurnal & Jam Pulang masih kosong saat Check In
    //             'activity_log' => null,
    //             'check_out_time' => null,
    //         ]);

    //         return back()->with('success', 'Absen Datang berhasil disimpan! Selamat beraktivitas.');
    //     }

    //     // ==========================================================
    //     // LOGIKA ABSEN PULANG (CHECK OUT)
    //     // ==========================================================
    //     elseif ($type === 'check_out') {

    //         $request->validate([
    //             'attendance_id' => 'required|exists:internship_attendances,id',
    //             'activity_log' => 'required|string|min:10', // Jurnal wajib saat pulang
    //             'photo_out' => 'nullable|image|max:3072',
    //         ], [
    //             'activity_log.required' => 'Jurnal kegiatan wajib diisi sebelum pulang.',
    //             'activity_log.min' => 'Jurnal kegiatan minimal 10 karakter.',
    //         ]);

    //         $attendance = InternshipAttendance::where('id', $request->attendance_id)
    //             ->where('student_id', $student->id)
    //             ->firstOrFail();

    //         if ($attendance->check_out_time) {
    //             return back()->with('error', 'Anda sudah melakukan absen pulang hari ini.');
    //         }

    //         // Upload Foto Pulang (Opsional)
    //         $photoOutPath = null;
    //         if ($request->hasFile('photo_out')) {
    //             $photoOutPath = $request->file('photo_out')->store('pkl_attendances_out', 'public');
    //         }

    //         // Update Data Pulang
    //         $updateData = [
    //             'check_out_time' => Carbon::now()->format('H:i:s'),
    //             'activity_log' => $request->activity_log,
    //         ];

    //         if ($photoOutPath) {
    //             $updateData['photo_out_path'] = $photoOutPath;
    //         }

    //         $attendance->update($updateData);

    //         return back()->with('success', 'Absen Pulang & Jurnal berhasil disimpan! Hati-hati di jalan.');
    //     }

    //     return back()->with('error', 'Tipe absensi tidak valid.');
    // }

    /**
     * Halaman Utama Absensi PKL (Form & Riwayat)
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // 1. Cek Data PKL Aktif
        $internship = Internship::with('industry')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$internship) {
            return redirect()->route('student.internships.index')
                ->with('error', 'Anda belum memiliki status PKL Aktif. Silakan ajukan atau tunggu persetujuan.');
        }

        // 2. Cek Apakah Sudah Absen Hari Ini
        $todayAttendance = InternshipAttendance::where('internship_id', $internship->id)
            ->where('date', Carbon::today())
            ->first();

        // 3. Ambil Riwayat Absensi (10 Hari Terakhir)
        $history = InternshipAttendance::where('internship_id', $internship->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('siswa.internships.attendance', compact('internship', 'todayAttendance', 'history'));
    }

    /**
     * Proses Simpan Absensi (Datang & Pulang)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $internship = Internship::where('student_id', $student->id)
            ->where('status', 'active')
            ->firstOrFail();


        $industry = $internship->industry;

        // VALIDASI GEOLOKASI DINAMIS
        if ($industry->latitude && $industry->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude, 
                $request->longitude, 
                $industry->latitude, 
                $industry->longitude
            );

            $maxRadius = $industry->radius ?? 100; // Default 100m jika null

            if ($distance > $maxRadius) {
                return back()->with('error', "Anda berada di luar lokasi PKL! Jarak: " . round($distance) . "m. (Max: $maxRadius m)");
            }
        } else {
            // Opsional: Tolak jika industri belum disetting lokasinya oleh admin
            return back()->with('error', 'Lokasi kantor belum disetting oleh Admin. Hubungi Guru Pembimbing.');
        }

        $type = $request->input('type'); // 'check_in' atau 'check_out'

        // ==========================================================
        // LOGIKA ABSEN DATANG (CHECK IN)
        // ==========================================================
        if ($type === 'check_in') {

            $request->validate([
                'status' => 'required|in:present,sick,permit',
                'photo' => 'required_if:status,present|image|max:10240', // Update ke 10MB
                'latitude' => 'nullable',
                'longitude' => 'nullable',
            ], [
                'photo.required_if' => 'Foto selfie wajib diupload jika status Hadir.',
                'photo.max' => 'Ukuran foto terlalu besar. Maksimal 10MB.',
            ]);

            // Cek Double Input
            $exists = InternshipAttendance::where('internship_id', $internship->id)
                ->where('date', Carbon::today())
                ->exists();

            if ($exists) {
                return back()->with('error', 'Anda sudah melakukan absen datang hari ini.');
            }

            // Upload Foto Masuk
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('pkl_attendances', 'public');
            }

            // Simpan Data
            InternshipAttendance::create([
                'internship_id' => $internship->id,
                'student_id' => $student->id,
                'date' => Carbon::today(),
                'time' => Carbon::now()->format('H:i:s'), // Jam Datang
                'status' => $request->status,
                'photo_path' => $photoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                // Jurnal & Jam Pulang masih kosong saat Check In
                'activity_log' => null,
                'check_out_time' => null,
            ]);

            return back()->with('success', 'Absen Datang berhasil disimpan! Selamat beraktivitas.');
        }

        // ==========================================================
        // LOGIKA ABSEN PULANG (CHECK OUT)
        // ==========================================================
        elseif ($type === 'check_out') {

            $request->validate([
                'attendance_id' => 'required|exists:internship_attendances,id',
                'activity_log' => 'required|string|min:10', // Jurnal wajib saat pulang
                'photo_out' => 'nullable|image|max:10240', // Update ke 10MB
            ], [
                'activity_log.required' => 'Jurnal kegiatan wajib diisi sebelum pulang.',
                'activity_log.min' => 'Jurnal kegiatan minimal 10 karakter.',
                'photo_out.max' => 'Ukuran foto terlalu besar. Maksimal 10MB.',
            ]);

            $attendance = InternshipAttendance::where('id', $request->attendance_id)
                ->where('student_id', $student->id)
                ->firstOrFail();

            if ($attendance->check_out_time) {
                return back()->with('error', 'Anda sudah melakukan absen pulang hari ini.');
            }

            // Upload Foto Pulang (Opsional)
            $photoOutPath = null;
            if ($request->hasFile('photo_out')) {
                $photoOutPath = $request->file('photo_out')->store('pkl_attendances_out', 'public');
            }

            // Update Data Pulang
            $updateData = [
                'check_out_time' => Carbon::now()->format('H:i:s'),
                'activity_log' => $request->activity_log,
            ];

            if ($photoOutPath) {
                $updateData['photo_out_path'] = $photoOutPath;
            }

            $attendance->update($updateData);

            return back()->with('success', 'Absen Pulang & Jurnal berhasil disimpan! Hati-hati di jalan.');
        }

        return back()->with('error', 'Tipe absensi tidak valid.');
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Rumus Haversine (Sama seperti sebelumnya)
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
