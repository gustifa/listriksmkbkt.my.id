<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Classroom;
use Carbon\Carbon;
use App\Jobs\SendWhatsappJob; // Queue Job untuk WA
use App\Models\AttendanceSetting; // Jangan lupa import model ini
use Illuminate\Support\Facades\DB; // Tambahkan import DB
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Penting untuk simpan foto
use Illuminate\Support\Facades\Log;

class DailyAttendanceController extends Controller
{
    /**
     * Halaman Scanner Gerbang (View)
     * Route: GET /daily-attendance
     */
    public function index()
    {
        return view('daily_attendance.scan');
    }

    /**
     * Proses Scan QR Otomatis (AJAX)
     * Logika: Cek Datang -> Cek Pulang -> Kirim WA
     * Route: POST /daily-attendance
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required'
    //     ]);

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!']);
    //     }

    //     $date = date('Y-m-d');
    //     $time = date('H:i:s');

    //     // 2. Cek Data Absensi Hari Ini
    //     $attendance = DailyAttendance::where('student_id', $student->id)
    //                     ->where('date', $date)
    //                     ->first();


    //     // AMBIL PENGATURAN DARI DATABASE
    //     // Kita ambil data pertama (karena setting biasanya cuma 1 baris)
    //     $setting = AttendanceSetting::first();

    //     // Fallback value jika database setting kosong (safety code)
    //     $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
    //     $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

    //     // ==========================================================
    //     // SKENARIO PULANG (DATA SUDAH ADA)
    //     // ==========================================================
    //     if ($attendance) {
    //         // Jika jam pulang sudah terisi, tolak scan
    //         if ($attendance->departure_time) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
    //             ]);
    //         }

    //         // --- TAMBAHAN FITUR: Validasi Jam Pulang (10:00) ---
    //         // Membuat objek waktu jam 10:00 hari ini
    //         //$jamBatasPulang = Carbon::createFromTime(18, 0, 0);

    //         // Cek apakah waktu sekarang kurang dari jam 10:00
    //         if (Carbon::now()->lessThan($batasBolehPulang)) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Belum waktunya pulang! Absen pulang baru dibuka pukul " .$batasBolehPulang." "
    //             ]);
    //         }
    //         // ------

    //         // Update Jam Pulang
    //         $attendance->update(['departure_time' => $time]);

    //         // Kirim WA Pulang (Antrian)
    //         $this->sendNotification($student, 'pulang', $time);

    //         return response()->json([
    //             'status' => 'success',
    //             'type' => 'out', // Sinyal ke JS untuk warna Biru
    //             'message' => 'Hati-hati di jalan! (Absen Pulang)',
    //             'student' => $student->name
    //         ]);
    //     }

    //     // ==========================================================
    //     // SKENARIO DATANG (DATA BARU)
    //     // ==========================================================

    //     // Logika Terlambat (Batas jam 07:00)
    //     // $jamMasukSekolah = Carbon::createFromTime(7, 0, 0); Lama
    //     $jamMasukSekolah = Carbon::createFromTimeString($batasTerlambat); // Ambil dari DB
    //     $status = Carbon::now()->greaterThan($jamMasukSekolah) ? 'terlambat' : 'hadir';

    //     DailyAttendance::create([
    //         'student_id' => $student->id,
    //         'date' => $date,
    //         'arrival_time' => $time,
    //         'status' => $status
    //     ]);

    //     // Kirim WA Datang (Antrian)
    //     $this->sendNotification($student, 'datang', $time, $status);

    //     return response()->json([
    //         'status' => 'success',
    //         'type' => 'in', // Sinyal ke JS untuk warna Hijau
    //         'message' => 'Selamat Datang! (Absen Masuk)',
    //         'student' => $student->name . ' (' . strtoupper($status) . ')'
    //     ]);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required'
    //     ]);

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
    //     }

    //     $date = date('Y-m-d');
    //     $time = date('H:i:s'); // Format waktu standar H:i:s

    //     // 2. Cek Data Absensi Hari Ini
    //     $attendance = DailyAttendance::where('student_id', $student->id)
    //                     ->where('date', $date)
    //                     ->first();

    //     // 3. AMBIL PENGATURAN DARI DATABASE
    //     $setting = AttendanceSetting::first();

    //     // Fallback value jika database setting kosong
    //     $jamMulaiScan = $setting ? $setting->start_check_in_time : '06:00:00';
    //     $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
    //     $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

    //     // --- VALIDASI AWAL: JAM BUKA SCAN ---
    //     // Cegah siswa absen terlalu pagi (misal jam 3 pagi)
    //     if ($time < $jamMulaiScan) {
    //          return response()->json([
    //             'status' => 'error',
    //             'message' => "Absensi belum dibuka. Dimulai pukul " . substr($jamMulaiScan, 0, 5)
    //         ], 400);
    //     }

    //     // ==========================================================
    //     // SKENARIO PULANG (DATA SUDAH ADA)
    //     // ==========================================================
    //     if ($attendance) {
    //         // Jika jam pulang sudah terisi, tolak scan (Double Check-out)
    //         if ($attendance->departure_time) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
    //             ], 400);
    //         }

    //         // --- VALIDASI JAM PULANG ---
    //         // Cek apakah waktu sekarang kurang dari batas boleh pulang
    //         // Menggunakan perbandingan string waktu H:i:s yang aman
    //         if ($time < $batasBolehPulang) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Belum waktunya pulang! Absen pulang baru dibuka pukul " . substr($batasBolehPulang, 0, 5)
    //             ], 400);
    //         }

    //         // Update Jam Pulang
    //         $attendance->update([
    //             'departure_time' => $time,
    //             'updated_at' => now()
    //         ]);

    //         // Kirim WA Pulang (Antrian)
    //         $this->sendNotification($student, 'pulang', $time);

    //         return response()->json([
    //             'status' => 'success',
    //             'type' => 'CHECK_OUT', // Sinyal ke Frontend (Warna Biru)
    //             'message' => 'Hati-hati di jalan! (Absen Pulang)',
    //             'student' => $student,
    //             'time' => $time
    //         ]);
    //     }

    //     // ==========================================================
    //     // SKENARIO DATANG (DATA BARU)
    //     // ==========================================================

    //     // Logika Terlambat
    //     // Jika waktu scan lebih besar dari batas terlambat -> late
    //     $status = ($time > $batasTerlambat) ? 'late' : 'present';

    //     DailyAttendance::create([
    //         'id' => (string) Str::uuid(),
    //         'student_id' => $student->id,
    //         'date' => $date,
    //         'arrival_time' => $time,
    //         'status' => $status,
    //         'recorded_by' => 'Scanner Gate'
    //     ]);

    //     // Kirim WA Datang (Antrian)
    //     $this->sendNotification($student, 'datang', $time, $status);

    //     $statusLabel = ($status == 'late') ? 'TERLAMBAT' : 'HADIR';
    //     $message = ($status == 'late')
    //         ? "Anda Terlambat! Batas jam " . substr($batasTerlambat, 0, 5)
    //         : "Selamat Datang! (Absen Masuk)";

    //     return response()->json([
    //         'status' => 'success',
    //         'type' => 'CHECK_IN', // Sinyal ke Frontend (Warna Hijau/Merah)
    //         'attendance_status' => $status, // late/present
    //         'message' => $message,
    //         'student' => $student,
    //         'time' => $time
    //     ]);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required'
    //     ]);

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
    //     }

    //     $date = date('Y-m-d');
    //     $time = date('H:i:s'); // Format waktu standar H:i:s

    //     // 2. Cek Data Absensi Hari Ini
    //     $attendance = DailyAttendance::where('student_id', $student->id)
    //                     ->where('date', $date)
    //                     ->first();

    //     // 3. AMBIL PENGATURAN DARI DATABASE
    //     $setting = AttendanceSetting::first();

    //     // Fallback value jika database setting kosong
    //     $jamMulaiScan = $setting ? $setting->start_check_in_time : '06:00:00';
    //     $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
    //     $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

    //     // --- VALIDASI AWAL: JAM BUKA SCAN ---
    //     if ($time < $jamMulaiScan) {
    //          return response()->json([
    //             'status' => 'error',
    //             'message' => "Absensi belum dibuka. Dimulai pukul " . substr($jamMulaiScan, 0, 5)
    //         ], 400);
    //     }

    //     // ==========================================================
    //     // SKENARIO PULANG (DATA SUDAH ADA)
    //     // ==========================================================
    //     if ($attendance) {
    //         // Jika jam pulang sudah terisi, tolak scan
    //         if ($attendance->departure_time) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
    //             ], 400);
    //         }

    //         // --- VALIDASI JAM PULANG ---
    //         if ($time < $batasBolehPulang) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Belum waktunya pulang! Absen pulang baru dibuka pukul " . substr($batasBolehPulang, 0, 5)
    //             ], 400);
    //         }

    //         // Update Jam Pulang
    //         $attendance->update([
    //             'departure_time' => $time,
    //             'updated_at' => now()
    //         ]);

    //         // Kirim WA Pulang (Antrian)
    //         $this->sendNotification($student, 'pulang', $time);

    //         return response()->json([
    //             'status' => 'success',
    //             'type' => 'CHECK_OUT', // Sinyal ke Frontend (Warna Biru)
    //             'message' => 'Hati-hati di jalan! (Absen Pulang)',
    //             'student' => $student,
    //             'time' => $time
    //         ]);
    //     }

    //     // ==========================================================
    //     // SKENARIO DATANG (DATA BARU)
    //     // ==========================================================

    //     // Logika Terlambat & Penentuan Status Database
    //     // Cek Migration Anda: Enum statusnya apa? ('hadir', 'terlambat') atau ('present', 'late')?
    //     // Berdasarkan error log, sepertinya DB menolak 'late'. Jadi kita coba pakai 'terlambat'.

    //     $isLate = ($time > $batasTerlambat);

    //     // GANTI BAGIAN INI SESUAI ENUM MIGRATION DATABASE ANDA
    //     $statusDB = $isLate ? 'terlambat' : 'hadir';

    //     DailyAttendance::create([
    //         'id' => (string) Str::uuid(),
    //         'student_id' => $student->id,
    //         'date' => $date,
    //         'arrival_time' => $time,
    //         'status' => $statusDB, // Gunakan variabel yang sudah disesuaikan
    //         'recorded_by' => 'Scanner Gate'
    //     ]);

    //     // Kirim WA Datang (Antrian)
    //     $this->sendNotification($student, 'datang', $time, $statusDB);

    //     $statusLabel = $isLate ? 'TERLAMBAT' : 'HADIR';
    //     $message = $isLate
    //         ? "Anda Terlambat! Batas jam " . substr($batasTerlambat, 0, 5)
    //         : "Selamat Datang! (Absen Masuk)";

    //     return response()->json([
    //         'status' => 'success',
    //         'type' => 'CHECK_IN',
    //         'attendance_status' => $isLate ? 'late' : 'present', // Untuk frontend JS tetap pakai late/present biar warna bener
    //         'message' => $message,
    //         'student' => $student,
    //         'time' => $time
    //     ]);
    // }

    /**
     * Handle Absensi Gerbang (Datang & Pulang) via Scan
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required',
    //         'image' => 'nullable|string' // Validasi input gambar base64
    //     ]);

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
    //     }

    //     $date = date('Y-m-d');
    //     $time = date('H:i:s'); // Format waktu standar H:i:s

    //     // 2. Cek Data Absensi Hari Ini
    //     $attendance = DailyAttendance::where('student_id', $student->id)
    //                     ->where('date', $date)
    //                     ->first();

    //     // 3. AMBIL PENGATURAN DARI DATABASE
    //     $setting = AttendanceSetting::first();

    //     // Fallback value jika database setting kosong
    //     $jamMulaiScan = $setting ? $setting->start_check_in_time : '06:00:00';
    //     $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
    //     $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

    //     // --- VALIDASI AWAL: JAM BUKA SCAN ---
    //     // Cegah siswa absen terlalu pagi (misal jam 3 pagi)
    //     if ($time < $jamMulaiScan) {
    //          return response()->json([
    //             'status' => 'error',
    //             'message' => "Absensi belum dibuka. Dimulai pukul " . substr($jamMulaiScan, 0, 5)
    //         ], 400);
    //     }

    //     // ==========================================================
    //     // SKENARIO PULANG (DATA MASUK SUDAH ADA)
    //     // ==========================================================
    //     if ($attendance) {
    //         // Jika jam pulang sudah terisi, tolak scan (Prevent Double Check-out)
    //         if ($attendance->departure_time) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Siswa {$student->name} sudah absen pulang hari ini!"
    //             ], 400);
    //         }

    //         // --- VALIDASI JAM PULANG ---
    //         // Cek apakah waktu sekarang kurang dari batas boleh pulang
    //         if ($time < $batasBolehPulang) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "Belum waktunya pulang! Absen pulang baru dibuka pukul " . substr($batasBolehPulang, 0, 5)
    //             ], 400);
    //         }

    //         // SIMPAN FOTO PULANG
    //         $photoPath = $this->saveImage($request->image, $student->nis, 'out', $date);

    //         // Update Jam Pulang
    //         $attendance->update([
    //             'departure_time' => $time,
    //             'photo_out' => $photoPath, // Simpan path foto
    //             'updated_at' => now()
    //         ]);

    //         // Kirim Notifikasi (Antrian)
    //         $this->sendNotification($student, 'pulang', $time);

    //         return response()->json([
    //             'status' => 'success',
    //             'type' => 'CHECK_OUT', // Sinyal ke Frontend (Warna Biru)
    //             'message' => 'Hati-hati di jalan! (Absen Pulang)',
    //             'student' => $student,
    //             'time' => $time
    //         ]);
    //     }

    //     // ==========================================================
    //     // SKENARIO DATANG (DATA BARU)
    //     // ==========================================================

    //     // Logika Terlambat & Penentuan Status Database
    //     $isLate = ($time > $batasTerlambat);
    //     $statusDB = $isLate ? 'terlambat' : 'hadir';

    //     // SIMPAN FOTO DATANG
    //     $photoPath = $this->saveImage($request->image, $student->nis, 'in', $date);

    //     DailyAttendance::create([
    //         'id' => (string) Str::uuid(),
    //         'student_id' => $student->id,
    //         'date' => $date,
    //         'arrival_time' => $time,
    //         'status' => $statusDB,
    //         'photo_in' => $photoPath, // Simpan path foto
    //         'recorded_by' => 'Scanner Gate'
    //     ]);

    //     // Kirim Notifikasi (Antrian)
    //     $this->sendNotification($student, 'datang', $time, $statusDB);

    //     $statusLabel = $isLate ? 'TERLAMBAT' : 'HADIR';
    //     $message = $isLate
    //         ? "Anda Terlambat! Batas jam " . substr($batasTerlambat, 0, 5)
    //         : "Selamat Datang! (Absen Masuk)";

    //     return response()->json([
    //         'status' => 'success',
    //         'type' => 'CHECK_IN',
    //         'attendance_status' => $isLate ? 'late' : 'present',
    //         'message' => $message,
    //         'student' => $student,
    //         'time' => $time
    //     ]);
    // }

    /**
     * Helper untuk menyimpan gambar Base64 ke Storage
     */
    // private function saveImage($base64Image, $nis, $type, $date)
    // {
    //     // Jika tidak ada gambar dikirim (misal scan manual tanpa kamera), return null
    //     if (!$base64Image) return null;

    //     try {
    //         // Bersihkan header data:image jika ada
    //         if (strpos($base64Image, 'data:image') !== false) {
    //             $image = str_replace('data:image/jpeg;base64,', '', $base64Image);
    //             $image = str_replace(' ', '+', $image);

    //             // Struktur Folder: public/attendance/gate/{in/out}/{tanggal}/
    //             $folder = "attendance/gate/{$type}/{$date}";

    //             // Nama File: NIS_TIMESTAMP.jpg
    //             $filename = "{$nis}_" . time() . ".jpg";
    //             $fullPath = "{$folder}/{$filename}";

    //             // Buat folder jika belum ada
    //             if (!Storage::disk('public')->exists($folder)) {
    //                 Storage::disk('public')->makeDirectory($folder);
    //             }

    //             // Simpan file
    //             Storage::disk('public')->put($fullPath, base64_decode($image));

    //             return $fullPath;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Gagal menyimpan foto absensi gerbang ({$type}) untuk NIS {$nis}: " . $e->getMessage());
    //     }

    //     return null;
    // }

    // =============================================================
    // FITUR INPUT MANUAL (ADMIN/GURU PIKET)
    // =============================================================

    /**
     * Halaman Form Manual
     * Route: GET /daily-attendance/manual
     */
    public function create()
    {
        $students = Student::with('classroom')->orderBy('name')->get();

        // --- FITUR BARU: CEK PERSENTASE KEHADIRAN GERBANG (BANTUAN GURU) ---
        $today = Carbon::today()->format('Y-m-d');

        //  Ambil Status Absensi Mapel Hari Ini
        $existingAttendances = DailyAttendance::whereDate('created_at', Carbon::today())
                                ->pluck('status', 'student_id')
                                ->toArray();

        // Ambil ID siswa yang SUDAH absen gerbang hari ini
        $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
                            ->whereIn('student_id', $students->pluck('id'))
                            ->whereNotNull('arrival_time')
                            ->pluck('student_id')
                            ->toArray();

        $totalSiswa = $students->count();
        $totalHadirGerbang = count($idsHadirGerbang);

        // Hitung Persentase
        $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

        // Ambil data siswa yang BELUM absen gerbang (untuk ditampilkan di modal bantuan)
        $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);
        return view('daily_attendance.create', compact(
            'students',
            'gatePercentage',
            'studentsMissingGate',
            'existingAttendances'
        ));
    }

    /**
     * Proses Simpan Manual
     * Route: POST /daily-attendance/manual
     */
    // public function storeManual(Request $request)
    // {
    //     // $request->validate([
    //     //     'student_id' => 'required|exists:students,id',
    //     //     'date' => 'required|date',
    //     //     'status' => 'required'
    //     // ]);

    //     // Simpan atau Update data
    //     DailyAttendance::updateOrCreate(
    //         [
    //             'student_id' => $request->student_id,
    //             'date' => $request->date
    //         ],
    //         [
    //             'arrival_time' => $request->arrival_time,
    //             'departure_time' => $request->departure_time,
    //             'status' => $request->status
    //         ]
    //     );

    //     // Kirim notifikasi manual jika diperlukan (Uncomment jika ingin aktif)
    //     /*
    //     $student = Student::with('classroom')->find($request->student_id);
    //     if ($student) {
    //         $this->sendNotification($student, 'manual', $request->arrival_time ?? '-', $request->status);
    //     }
    //     */

    //     return redirect()->route('daily.index')->with('success', 'Data absensi harian berhasil disimpan secara manual.');
    // }



    /**
     * Helper Private: Kirim Pesan WA via Queue
     * PERBAIKAN: Menangani Relasi Null & Inisialisasi Variabel Pesan
     */
    private function sendNotification($student, $type, $time, $status = '')
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $inf_app = $settings['inf_app'] ?? 'Sistem Sekolah'; // Pakai default jika null
        if (empty($student->phone)) return;

        $tgl = date('d-m-Y');

        // Cek apakah relasi classroom ada, jika tidak pakai '-' agar tidak error
        $kelas = $student->classroom ? $student->classroom->name : '-';

        $msg = ''; // Default string kosong

        if ($type == 'datang') {
            $msg = "*LAPORAN KEDATANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua/Wali,\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📅 Waktu: {$time} WIB\n" .
                   "📝 Status: *" . strtoupper($status) . "*\n\n" .
                   "_Absensi_Sekolah_";
        } elseif ($type == 'pulang') {
            $msg = "*LAPORAN KEPULANGAN (GERBANG)*\n\n" .
                   "Yth. Orang Tua/Wali,\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📅 Waktu: {$time} WIB\n" .
                   "📝 Status: *PULANG SEKOLAH*\n\n" .
                   "_Absensi_Sekolah_";
        } elseif ($type == 'manual') {
            // Format pesan untuk input manual
            $msg = "*LAPORAN PRESENSI (MANUAL)*\n\n" .
                   "👤 Nama: *{$student->name}*\n" .
                   "🏫 Kelas: {$kelas}\n" .
                   "📝 Status: *" . strtoupper($status) . "*\n" .
                   "Ket: Data diinput manual oleh petugas.\n\n" .
                   "_Absensi_Sekolah_";
        }

        // Hanya dispatch job jika pesan berhasil dibuat
        if (!empty($msg)) {
            SendWhatsappJob::dispatch($student->phone, $msg);
        }
    }

    /**
     * Halaman Dashboard Realtime (Monitor)
     * Route: GET /daily-attendance/monitor
     */
    public function monitor()
    {
        return view('daily_attendance.monitor');
    }

     /**
     * Halaman Dashboard Realtime (Monitor)
     * Route: GET /daily-attendance/monitor
     */
    public function monitorKelas()
    {
        // Ambil daftar kelas untuk dropdown filter
        $classrooms = Classroom::orderBy('name')->get();
        return view('daily_attendance.monitor', compact('classrooms'));
    }


    /**
     * API JSON untuk Data Realtime
     * Route: GET /daily-attendance/api/latest
     */
    /**
     * API JSON untuk Data Realtime (Updated)
     */
    // public function getRealtimeData(Request $request)
    // {
    //     $today = date('Y-m-d');

    //     // 1. Query Data Live (Tabel)
    //     $query = DailyAttendance::with(['student.classroom'])
    //                     ->where('date', $today);

    //     if ($request->filled('classroom_id')) {
    //         $query->whereHas('student', function($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //     }

    //     $attendances = $query->orderBy('updated_at', 'desc')->take(20)->get();

    //     $data = $attendances->map(function($item) {
    //         $jam = $item->departure_time ? $item->departure_time : $item->arrival_time;
    //         $statusLabel = 'DATANG';
    //         $badgeColor = 'success';

    //         if ($item->departure_time) {
    //             $statusLabel = 'PULANG';
    //             $badgeColor = 'primary';
    //         } elseif ($item->status == 'terlambat') {
    //             $statusLabel = 'TERLAMBAT';
    //             $badgeColor = 'warning';
    //         }

    //         return [
    //             'nis' => $item->student->nis,
    //             'name' => $item->student->name,
    //             'class' => $item->student->classroom->name ?? '-',
    //             'time' => Carbon::parse($jam)->format('H:i:s'),
    //             'status_label' => $statusLabel,
    //             'badge_color' => $badgeColor,
    //         ];
    //     });

    //     // 2. Hitung Summary Global
    //     $summaryQuery = DailyAttendance::where('date', $today);
    //     if ($request->filled('classroom_id')) {
    //         $summaryQuery->whereHas('student', function($q) use ($request) {
    //             $q->where('classroom_id', $request->classroom_id);
    //         });
    //     }
    //     $hadirCount = (clone $summaryQuery)->count();
    //     $pulangCount = (clone $summaryQuery)->whereNotNull('departure_time')->count();


    //     // 3. [BARU] Hitung Rekapitulasi Per Kelas
    //     // Kita join tabel agar bisa group by nama kelas
    //     $rekapKelas = DailyAttendance::join('students', 'daily_attendances.student_id', '=', 'students.id')
    //         ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //         ->where('daily_attendances.date', $today)
    //         ->select(
    //             'classrooms.name as nama_kelas',
    //             DB::raw('count(daily_attendances.arrival_time) as total_datang'),
    //             DB::raw('count(daily_attendances.departure_time) as total_pulang')
    //         )
    //         ->groupBy('classrooms.id', 'classrooms.name')
    //         ->orderBy('classrooms.name', 'asc')
    //         ->get();

    //     return response()->json([
    //         'data' => $data,
    //         'summary' => [
    //             'hadir' => $hadirCount,
    //             'pulang' => $pulangCount
    //         ],
    //         'rekap_kelas' => $rekapKelas // Kirim data rekap ke view
    //     ]);
    // }

    public function getRealtimeData(Request $request)
{
    $today = date('Y-m-d');

    // 1. Query Data Live (Tabel)
    $query = DailyAttendance::with(['student.classroom'])
                            ->where('date', $today);

    if ($request->filled('classroom_id')) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('classroom_id', $request->classroom_id);
        });
    }

    $attendances = $query->orderBy('updated_at', 'desc')->take(20)->get();

    $data = $attendances->map(function($item) {
        // Tentukan jam terakhir aktivitas
        $jam = $item->departure_time ? $item->departure_time : $item->arrival_time;

        // Logika Status & Warna
        $statusLabel = 'DATANG';
        $badgeColor = 'success';

        // Tentukan foto mana yang ditampilkan (Foto Pulang jika sudah pulang, jika tidak Foto Masuk)
        $fotoPath = $item->photo_in;

        if ($item->departure_time) {
            $statusLabel = 'PULANG';
            $badgeColor = 'primary';
            $fotoPath = $item->photo_out; // Gunakan foto capture saat pulang
        } elseif ($item->status == 'terlambat') {
            $statusLabel = 'TERLAMBAT';
            $badgeColor = 'warning';
        }

        return [
            'nis' => $item->student->nis,
            'name' => $item->student->name,
            'class' => $item->student->classroom->name ?? '-',
            'time' => Carbon::parse($jam)->format('H:i:s'),
            'status_label' => $statusLabel,
            'badge_color' => $badgeColor,
            // Generate URL Asset untuk foto wajah
            'photo_url' => $fotoPath ? asset('storage/' . $fotoPath) : asset('images/default-user.png'),
        ];
    });

    // 2. Hitung Summary Global
    $summaryQuery = DailyAttendance::where('date', $today);
    if ($request->filled('classroom_id')) {
        $summaryQuery->whereHas('student', function($q) use ($request) {
            $q->where('classroom_id', $request->classroom_id);
        });
    }
    $hadirCount = (clone $summaryQuery)->count();
    $pulangCount = (clone $summaryQuery)->whereNotNull('departure_time')->count();


    // 3. Hitung Rekapitulasi Per Kelas
    $rekapKelas = DailyAttendance::join('students', 'daily_attendances.student_id', '=', 'students.id')
        ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
        ->where('daily_attendances.date', $today)
        ->select(
            'classrooms.name as nama_kelas',
            DB::raw('count(daily_attendances.arrival_time) as total_datang'),
            DB::raw('count(daily_attendances.departure_time) as total_pulang')
        )
        ->groupBy('classrooms.id', 'classrooms.name')
        ->orderBy('classrooms.name', 'asc')
        ->get();

    return response()->json([
        'data' => $data,
        'summary' => [
            'hadir' => $hadirCount,
            'pulang' => $pulangCount
        ],
        'rekap_kelas' => $rekapKelas
    ]);
}

    /**
     * API JSON untuk Data Realtime
     * Route: GET /daily-attendance/api/latest
     */
    public function getRealtimeDataKelas(Request $request)
    {
        // 1. Query Dasar
        $query = DailyAttendance::with(['student.classroom'])
                        ->where('date', date('Y-m-d'));

        // 2. Filter Berdasarkan Kelas (Jika ada input)
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // 3. Ambil Data Tabel (20 Terakhir)
        $attendances = $query->orderBy('updated_at', 'desc')
                        ->take(20)
                        ->get();

        // Format data agar mudah dibaca JS
        $data = $attendances->map(function($item) {
            $jam = $item->departure_time ? $item->departure_time : $item->arrival_time;
            $statusLabel = 'DATANG';
            $badgeColor = 'success'; // Hijau

            if ($item->departure_time) {
                $statusLabel = 'PULANG';
                $badgeColor = 'primary'; // Biru
            } elseif ($item->status == 'terlambat') {
                $statusLabel = 'TERLAMBAT';
                $badgeColor = 'warning'; // Kuning
            }

            return [
                'nis' => $item->student->nis,
                'name' => $item->student->name,
                'class' => $item->student->classroom->name ?? '-',
                'time' => Carbon::parse($jam)->format('H:i:s'),
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor,
            ];
        });

        // 4. Hitung Summary (Total Hadir/Pulang) - Juga difilter
        $summaryQuery = DailyAttendance::where('date', date('Y-m-d'));

        if ($request->filled('classroom_id')) {
            $summaryQuery->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // Clone query agar tidak tumpang tindih
        $hadirCount = (clone $summaryQuery)->count();
        $pulangCount = (clone $summaryQuery)->whereNotNull('departure_time')->count();

        return response()->json([
            'data' => $data,
            'summary' => [
                'hadir' => $hadirCount,
                'pulang' => $pulangCount
            ]
        ]);
    }

    /**
     * Menampilkan laporan absensi dengan filter lengkap.
     */


    public function laporan(Request $request)
    {
        // 1. Ambil data siswa untuk dropdown filter
        $students = Student::orderBy('name', 'asc')->get();

        // 2. Query Builder Dasar
        // Load relasi 'student' dan 'classroom' agar tidak N+1 Query
        $query = DailyAttendance::with(['student', 'student.classroom']);

        // --- FILTER 1: SPESIFIK SISWA ---
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // --- FILTER 2: PERIODE WAKTU ---
        // Jika tidak ada filter, default ke hari ini
        $filterType = $request->filter_type ?? 'harian';

        switch ($filterType) {
            case 'harian':
                $date = $request->date ?? now()->format('Y-m-d');
                $query->whereDate('created_at', $date);
                break;

            case 'mingguan':
                // Menggunakan rentang tanggal manual (Start Date s/d End Date)
                $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfWeek();
                $endDate   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfWeek();

                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;

            case 'bulanan':
                $month = $request->month ?? now()->month;
                $year  = $request->year ?? now()->year;

                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $year);
                break;

            case 'semester':
                $year = $request->year ?? now()->year;
                $semester = $request->semester; // 'ganjil' atau 'genap'

                if ($semester == 'ganjil') {
                    // Semester Ganjil: 1 Juli - 31 Desember
                    $start = Carbon::create($year, 7, 1)->startOfDay();
                    $end   = Carbon::create($year, 12, 31)->endOfDay();
                } else {
                    // Semester Genap: 1 Januari - 30 Juni
                    $start = Carbon::create($year, 1, 1)->startOfDay();
                    $end   = Carbon::create($year, 6, 30)->endOfDay();
                }

                $query->whereBetween('created_at', [$start, $end]);
                break;
        }

        // 3. Urutkan data: Terbaru di atas
        $attendances = $query->latest()->get();

        // 4. Hitung Ringkasan (Opsional, untuk header laporan)
        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
        ];

        return view('daily_attendance.reports.laporan', compact('attendances', 'students', 'summary'));
    }


     // SOLUSI: PERBAIKAN PADA CONTROLLER ANDA
    // public function storeManual(Request $request)
    // {
    //     // 1. Ambil array 'attendances' yang berisi ['student_id' => 'status']
    //     $attendancesData = $request->input('attendances');
    //     $user = Auth::user();

    //     if (empty($attendancesData)) {
    //         // Handle jika tidak ada data yang dicentang
    //         return back()->with('error', 'Tidak ada siswa yang diabsen.');
    //     }

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     $date = Carbon::now()->format('Y-m-d');
    //     $time = Carbon::now()->format('H:i:s');
    //     $count = 0;

    //     foreach ($attendancesData as $studentId => $status) {
    //         // Cek apakah sudah ada data (untuk menghindari duplikat)
    //         $exists = DailyAttendance::where('student_id', $studentId)
    //                     ->where('date', $date)
    //                     ->exists();

    //         if (!$exists) {
    //             DailyAttendance::create([
    //                 'student_id'   => $studentId,
    //                 'date'         => $date,
    //                 'arrival_time' => $time, // Set jam sekarang sebagai jam datang
    //                 'status'       => $request->status,
    //                 // RECORDED BY: Nama Guru (Bantuan Manual)
    //                 'recorded_by'  => $user->name . ' (Bantuan Manual)'
    //             ]);
    //             $count++;
    //         }
    //     }

    //     // // 2. Lakukan loop, gunakan $studentId sebagai key dan $status sebagai value
    //     // foreach ($attendancesData as $studentId => $status) {
    //     //     // PENTING: $studentId yang kita ambil dari key array adalah nilai non-null yang dicari

    //     //     // Asumsi data ini adalah data absensi mapel (bukan gerbang)
    //     //     $recordsToInsert[] = [
    //     //         'id' => \Illuminate\Support\Str::uuid(), // Jika Anda menggunakan UUID
    //     //         'student_id' => $studentId, // <<< INI SOLUSINYA
    //     //         'date' => $currentTimestamp->toDateString(),
    //     //         'status' => $status,
    //     //         'arrival_time' => null, // Mungkin null jika absensi mapel
    //     //         'departure_time' => null, // Mungkin null
    //     //         'created_at' => $currentTimestamp,
    //     //         'updated_at' => $currentTimestamp,
    //     //         // ... pastikan semua kolom NOT NULL lainnya (kecuali ID) juga terisi.
    //     //     ];
    //     // }

    //     // // 3. Lakukan Mass Insert (lebih efisien) atau loop dan save
    //     // \Illuminate\Support\Facades\DB::table('daily_attendances')->insert($recordsToInsert);

    //     return back()->with('success', 'Absensi berhasil disimpan!');
    // }

    public function storeManual(Request $request)
    {
        // 1. Validasi Data (PENTING, agar tidak terjadi error SQL lagi)
        $request->validate([
            'attendances' => 'required|array|min:1',
            'attendances.*' => 'required|string|in:hadir,terlambat,sakit,izin,alpa',
            // Tambahkan validasi untuk schedule_id jika ini Absensi Mapel
            // 'schedule_id' => 'required|uuid|exists:schedules,id',
        ], [
            'attendances.required' => 'Minimal satu siswa harus dipilih status kehadirannya.',
            'attendances.*.required' => 'Status kehadiran untuk setiap siswa wajib diisi.',
            // 'schedule_id.required' => 'ID Jadwal harus disediakan untuk absensi mapel.'
        ]);

        // 2. Persiapan Variabel
        $attendancesData = $request->input('attendances');
        $user = Auth::user();
        $todayDate = Carbon::now()->toDateString();
        $currentTimestamp = Carbon::now();
        $scheduleId = $request->schedule_id; // Ambil schedule_id dari request

        $insertedCount = 0;
        $updatedCount = 0;

        // Asumsi: Semua absensi manual dicatat oleh user yang sedang login
        $recordedBy = $user->name . ' (Manual)';

        // 3. Loop dan Lakukan Update/Insert
        foreach ($attendancesData as $studentId => $newStatus) {

            // --- KRITERIA PENCARIAN (MATCHING) ---
            // Cari baris berdasarkan ID Siswa, Tanggal, dan Jadwal (jika ini absensi mapel)
            $matchConditions = [
                'student_id' => $studentId,
                'date' => $todayDate,
                // Kunci unik untuk membedakan absensi mapel satu dengan yang lain
                // 'schedule_id' => $scheduleId,
            ];

            // --- DATA YANG AKAN DI-UPDATE/INSERT ---
            $updateData = [
                'status' => $newStatus,
                'arrival_time' => $currentTimestamp->toTimeString(), // Asumsi set waktu datang saat absen manual
                'recorded_by' => $recordedBy,
            ];

            // Cari data yang sudah ada
            $attendance = DailyAttendance::where($matchConditions)->first();

            if ($attendance) {
                // DATA SUDAH ADA (UPDATE jika ada perubahan status)
                if ($attendance->status !== $newStatus) {
                    $attendance->update($updateData);
                    $updatedCount++;
                }
                // Jika status sama, diabaikan (skip)
            } else {
                // DATA BELUM ADA (INSERT)

                // Gabungkan kriteria pencarian dan data update, tambahkan ID
                $createData = array_merge($matchConditions, $updateData, [
                    'id' => Str::uuid(),
                    // created_at dan updated_at akan otomatis diisi jika model menggunakan timestamps
                ]);

                DailyAttendance::create($createData);
                $insertedCount++;
            }
        }

        // 4. Return Hasil
        return redirect()->route('daily.create')->with('success', "Absensi Mapel berhasil disimpan! ($insertedCount Ditambahkan, $updatedCount Diperbarui).");
    }



    /**
     * FITUR BARU: Simpan Absensi Gerbang Massal (Bantuan Guru)
     * Digunakan jika scanner rusak atau antrian panjang, guru bisa mem-bypass.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
        ]);

        $user = Auth::user();
        $setting = AttendanceSetting::first();

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        $date = Carbon::now()->format('Y-m-d');
        $time = Carbon::now()->format('H:i:s');
        $count = 0;

        foreach ($request->student_ids as $studentId) {
            // Cek apakah sudah ada data (untuk menghindari duplikat)
            $exists = DailyAttendance::where('student_id', $studentId)
                        ->where('date', $date)
                        ->exists();

            if (!$exists) {
                DailyAttendance::create([
                    'student_id'   => $studentId,
                    'date'         => $date,
                    'arrival_time' => $time, // Set jam sekarang sebagai jam datang
                    'status'       => $request->status,
                    // RECORDED BY: Nama Guru (Bantuan Manual)
                    'recorded_by'  => $user->name . ' (Bantuan Manual)'
                ]);
                $count++;
            }
        }



        return redirect()->back()->with('success', "Berhasil menambahkan absensi gerbang manual untuk $count siswa.");
    }

    public function createManual($schedule_id)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($user->jenis_user !== 'admin' && !$teacher) {
            abort(403, 'Akses Ditolak');
        }

        // 1. Cari Jadwal
        $query = Schedule::with(['classroom', 'subject'])->where('id', $schedule_id);

        if ($user->jenis_user !== 'admin') {
            $query->where('teacher_id', $teacher->id);
        }

        $schedule = $query->firstOrFail();

        // 2. Ambil Semua Siswa di Kelas Ini
        $students = Student::where('classroom_id', $schedule->classroom_id)
                    ->orderBy('name')
                    ->get();

        // 3. Ambil Status Absensi Mapel Hari Ini
        $existingAttendances = Attendance::where('schedule_id', $schedule_id)
                                ->whereDate('created_at', Carbon::today())
                                ->pluck('status', 'student_id')
                                ->toArray();

        // --- FITUR BARU: CEK PERSENTASE KEHADIRAN GERBANG (BANTUAN GURU) ---
        $today = Carbon::today()->format('Y-m-d');

        // Ambil ID siswa yang SUDAH absen gerbang hari ini
        $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
                            ->whereIn('student_id', $students->pluck('id'))
                            ->whereNotNull('arrival_time')
                            ->pluck('student_id')
                            ->toArray();

        $totalSiswa = $students->count();
        $totalHadirGerbang = count($idsHadirGerbang);

        // Hitung Persentase
        $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

        // Ambil data siswa yang BELUM absen gerbang (untuk ditampilkan di modal bantuan)
        $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);

        return view('attendance.manual', compact(
            'schedule',
            'students',
            'existingAttendances',
            'gatePercentage',
            'studentsMissingGate'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'image' => 'nullable|string', // Validasi input gambar base64
        ]);

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan!'], 404);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s'); // Format waktu standar H:i:s

        // 2. Cek Data Absensi Hari Ini
        $attendance = DailyAttendance::where('student_id', $student->id)
                        ->where('date', $date)
                        ->first();

        // 3. AMBIL PENGATURAN DARI DATABASE
        $setting = AttendanceSetting::first();

        // Fallback value jika database setting kosong
        $jamMulaiScan = $setting ? $setting->start_check_in_time : '06:00:00';
        $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
        $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';

        // --- VALIDASI AWAL: JAM BUKA SCAN ---
        if ($time < $jamMulaiScan) {
             return response()->json([
                'status' => 'error',
                'message' => "Absensi belum dibuka. Dimulai pukul " . substr($jamMulaiScan, 0, 5)
            ], 400);
        }

        // ==========================================================
        // SKENARIO PULANG (CHECK-OUT)
        // ==========================================================
        if ($attendance) {
            // Jika jam pulang sudah terisi, tolak scan
            if ($attendance->departure_time) {
                return response()->json(['status' => 'error', 'message' => "Siswa {$student->name} sudah absen pulang hari ini!"], 400);
            }

            // Validasi Jam Pulang
            if ($time < $batasBolehPulang) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Belum waktunya pulang! Dibuka pukul " . substr($batasBolehPulang, 0, 5)
                ], 400);
            }

            // SIMPAN FOTO PULANG
            $photoPath = $this->saveImage($request->image, $student->nis, 'out', $date);

            // Update Data
            $attendance->update([
                'departure_time' => $time,
                'photo_out' => $photoPath, // Simpan path foto
                'updated_at' => now()
            ]);

            $this->sendNotification($student, 'pulang', $time);

            return response()->json([
                'status' => 'success',
                'type' => 'CHECK_OUT',
                'message' => 'Hati-hati di jalan! (Absen Pulang)',
                'student' => $student,
                'time' => $time
            ]);
        }

        // ==========================================================
        // SKENARIO DATANG (CHECK-IN)
        // ==========================================================

        $isLate = ($time > $batasTerlambat);
        $statusDB = $isLate ? 'terlambat' : 'hadir';

        // SIMPAN FOTO DATANG
        $photoPath = $this->saveImage($request->image, $student->nis, 'in', $date);

        DailyAttendance::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'date' => $date,
            'arrival_time' => $time,
            'status' => $statusDB,
            'photo_in' => $photoPath, // Simpan path foto
            'recorded_by' => 'Scanner Gate'
        ]);

        $this->sendNotification($student, 'datang', $time, $statusDB);

        $message = $isLate
            ? "Anda Terlambat! Batas jam " . substr($batasTerlambat, 0, 5)
            : "Selamat Datang! (Absen Masuk)";

        return response()->json([
            'status' => 'success',
            'type' => 'CHECK_IN',
            'attendance_status' => $isLate ? 'late' : 'present',
            'message' => $message,
            'student' => $student,
            'time' => $time
        ]);
    }

    /**
     * Helper untuk menyimpan gambar Base64 ke Storage
     */
    private function saveImage($base64Image, $nis, $type, $date)
    {
        // Jika tidak ada gambar dikirim (misal scan manual tanpa kamera), return null
        if (!$base64Image) return null;

        try {
            // Bersihkan header data:image jika ada
            if (strpos($base64Image, 'data:image') !== false) {
                $image = str_replace('data:image/jpeg;base64,', '', $base64Image);
                $image = str_replace(' ', '+', $image);

                // Struktur Folder: public/attendance/gate/{in/out}/{tanggal}/
                $folder = "attendance/gate/{$type}/{$date}";

                // Nama File: NIS_TIMESTAMP.jpg
                $filename = "{$nis}_" . time() . ".jpg";
                $fullPath = "{$folder}/{$filename}";

                // Buat folder jika belum ada
                if (!Storage::disk('public')->exists($folder)) {
                    Storage::disk('public')->makeDirectory($folder);
                }

                // Simpan file
                Storage::disk('public')->put($fullPath, base64_decode($image));

                return $fullPath;
            }
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan foto absensi gerbang ({$type}) untuk NIS {$nis}: " . $e->getMessage());
        }

        return null;
    }

}
