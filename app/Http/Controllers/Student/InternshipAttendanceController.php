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
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

class InternshipAttendanceController extends Controller
{
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
        // 1. Validasi Tipe Absensi Awal
        $request->validate([
            'type' => 'required|in:check_in,check_out',
        ]);

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $internship = Internship::where('student_id', $student->id)
            ->where('status', 'active')
            ->firstOrFail();

        $type = $request->input('type');

        // ==========================================================
        // LOGIKA ABSEN DATANG (CHECK IN)
        // ==========================================================
        if ($type === 'check_in') {

            $request->validate([
                'status'    => 'required|in:present,sick,permit',
                'photo'     => 'required_if:status,present|nullable|image|max:10240',
                'latitude'  => 'required_if:status,present',
                'longitude' => 'required_if:status,present',
            ], [
                'status.required'      => 'Pilih status kehadiran terlebih dahulu.',
                'photo.required_if'    => 'Foto selfie wajib diunggah jika status Hadir.',
                'photo.max'            => 'Ukuran foto terlalu besar. Maksimal 10MB.',
                'latitude.required_if' => 'Lokasi GPS Anda tidak terdeteksi. Aktifkan GPS pada perangkat Anda.',
            ]);

            // Cek Double Input Check-In
            $exists = InternshipAttendance::where('internship_id', $internship->id)
                ->where('date', Carbon::today())
                ->exists();

            if ($exists) {
                return back()->with('error', 'Anda sudah melakukan absen datang hari ini.');
            }

            // Validasi Geolokasi (Hanya untuk Status Hadir/Present)
            if ($request->status === 'present') {
                $industry = $internship->industry;

                if ($industry && $industry->latitude && $industry->longitude) {
                    $distance = $this->calculateDistance(
                        (float) $request->latitude,
                        (float) $request->longitude,
                        (float) $industry->latitude,
                        (float) $industry->longitude
                    );

                    $maxRadius = $industry->radius ?? 100; // Default 100 meter

                    if ($distance > $maxRadius) {
                        return back()->with('error', "Anda berada di luar lokasi PKL! Jarak Anda: " . round($distance) . " m. (Batas Maksimal: {$maxRadius} m)");
                    }
                } else {
                    return back()->with('error', 'Titik lokasi industri belum dikonfigurasi oleh Admin/Pembimbing.');
                }
            }

            // Upload Foto Masuk
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('pkl_attendances', 'public');
            }

            // Simpan Data Check-In
            InternshipAttendance::create([
                'internship_id'  => $internship->id,
                'student_id'     => $student->id,
                'date'           => Carbon::today(),
                'time'           => Carbon::now()->format('H:i:s'),
                'status'         => $request->status,
                'photo_path'     => $photoPath,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'activity_log'   => null,
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
                'activity_log'  => 'required|string|min:10',
                'photo_out'     => 'nullable|image|max:10240',
            ], [
                'activity_log.required' => 'Jurnal kegiatan wajib diisi sebelum pulang.',
                'activity_log.min'      => 'Jurnal kegiatan minimal 10 karakter.',
                'photo_out.max'         => 'Ukuran foto terlalu besar. Maksimal 10MB.',
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

            // Update Data Check-Out
            $updateData = [
                'check_out_time' => Carbon::now()->format('H:i:s'),
                'activity_log'   => $request->activity_log,
            ];

            if ($photoOutPath) {
                $updateData['photo_out_path'] = $photoOutPath;
            }

            $attendance->update($updateData);

            return back()->with('success', 'Absen Pulang & Jurnal berhasil disimpan! Hati-hati di jalan.');
        }

        return back()->with('error', 'Tipe absensi tidak valid.');
    }

    /**
     * Menghitung jarak antara dua koordinat (dalam meter) menggunakan Rumus Haversine
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
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

    public function importView()
    {
        return view('admin.internships.import');
    }

    /**
     * Memproses File Import yang Diunggah
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal adalah 2MB.',
        ]);

        try {
            Excel::import(new AttendanceImport, $request->file('file'));

            return redirect()->route('admin.internships.attendance.import')
                ->with('success', 'Data absensi berhasil di-import!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses import data: ' . $e->getMessage());
        }
    }

    /**
     * Mengunduh Template File Excel Import
     */
    public function downloadTemplate()
    {
        $filePath = public_path('templates/template_import_absensi.xlsx');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template belum tersedia di server.');
        }

        return response()->download($filePath, 'Template_Import_Absensi_PKL.xlsx');
    }
}
