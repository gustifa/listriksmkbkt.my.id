<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\DailyAttendance; // Tambahkan Model DailyAttendance
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <--- WAJIB IMPORT HTTP CLIENT
use App\Jobs\SendWhatsappJob; // <--- WAJIB IMPORT JOB

class AttendanceController extends Controller
{
    /**
     * Menampilkan Halaman Scanner
     * URL: /scan/{schedule_id}
     */
    // public function index($schedule_id)
    // {
    //     // 1. Cari Jadwal
    //     // - with('classroom'): Kita butuh nama kelas untuk ditampilkan di judul halaman
    //     // - where('teacher_id', Auth::id()): Keamanan, hanya pemilik jadwal yang bisa buka
    //     $schedule = Schedule::with('classroom')
    //                 ->where('id', $schedule_id)
    //                 ->where('teacher_id', Auth::id())
    //                 ->firstOrFail(); // Jika tidak ketemu/bukan pemiliknya, muncul 404

    //     return view('scan', compact('schedule'));
    // }


    // public function index($schedule_id)
    // {
    //     // 1. Ambil data Guru berdasarkan User yang login
    //     $teacher = Teacher::where('user_id', Auth::id())->first();

    //     // Validasi jika akun tidak terhubung ke data guru
    //     if (!$teacher) {
    //         abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
    //     }

    //     // 2. Cari Jadwal menggunakan ID GURU ($teacher->id), bukan ID USER
    //     $schedule = Schedule::with('classroom')
    //                 ->where('id', $schedule_id)
    //                 ->where('teacher_id', $teacher->id) // PERBAIKAN DI SINI
    //                 ->firstOrFail();

    //     return view('attendance.scan_qr', compact('schedule'));
    // }

    /**
     * Menampilkan Halaman Scanner (QR Code)
     */
    public function index($schedule_id)
    {
        $user = Auth::user();

        // 1. Cek apakah User adalah Guru
        $teacher = Teacher::where('user_id', $user->id)->first();

        // JIKA BUKAN ADMIN dan TIDAK PUNYA DATA GURU -> Tolak
        // (Admin boleh masuk walau tidak punya data di tabel teachers)
        if ($user->jenis_user !== 'admin' && !$teacher) {
            abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
        }

        // 2. Cari Jadwal
        $query = Schedule::with(['classroom', 'subject'])->where('id', $schedule_id);

        // JIKA BUKAN ADMIN, batasi hanya jadwal miliknya
        if ($user->jenis_user !== 'admin') {
            $query->where('teacher_id', $teacher->id);
        }

        $schedule = $query->firstOrFail();

        return view('attendance.lesson_scan', compact('schedule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'schedule_id' => 'required|exists:schedules,id'
        ]);

        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        // 1. Cari Siswa
        $student = Student::with('classroom')->where('nis', $request->nis)->first();

        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Data Siswa tidak ditemukan!']);
        }

        // 2. Cari Jadwal
        $schedule = Schedule::with(['classroom', 'subject'])->find($request->schedule_id);

        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Jadwal tidak valid!']);
        }

        // =================================================================
        // VALIDASI 1: CEK KEHADIRAN GERBANG (DailyAttendance)
        // =================================================================
        $today = Carbon::today();

        $dailyLog = DailyAttendance::where('student_id', $student->id)
                    ->whereDate('date', $today)
                    ->first();

        // A. Cek apakah sudah absen masuk?
        if (!$dailyLog || empty($dailyLog->arrival_time)) {
            return response()->json([
                'status' => 'error',
                'message' => "Anda belum melakukan Scan Masuk di Gerbang Sekolah!"
            ]);
        }

        // B. Cek apakah sudah absen pulang?
        if (!empty($dailyLog->departure_time)) {
            return response()->json([
                'status' => 'error',
                'message' => "Anda tercatat sudah Pulang, tidak bisa masuk kelas!"
            ]);
        }

        // =========================================================
        // VALIDASI BARU: HARI DAN JAM (WAKTU)
        // =========================================================

        $now = Carbon::now();

        // A. Validasi Hari
        // Mapping hari Inggris ke Indonesia karena Carbon defaultnya Inggris
        $daysMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $currentDayEnglish = $now->format('l');
        $currentDayIndo = $daysMap[$currentDayEnglish];

        // Bandingkan hari jadwal dengan hari ini (Case Insensitive)
        if (strtolower($schedule->day) !== strtolower($currentDayIndo)) {
            return response()->json([
                'status' => 'error',
                'message' => "Jadwal Salah! Ini jadwal hari {$schedule->day}, sekarang hari {$currentDayIndo}."
            ]);
        }

        // B. Validasi Jam (Start & End)
        // Parse jam jadwal ke tanggal hari ini agar bisa dibandingkan
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);

        // Opsional: Tambahkan toleransi waktu masuk (misal boleh absen 15 menit sebelum mulai)
        // $startTime->subMinutes(15);

        if ($now->lessThan($startTime)) {
            return response()->json([
                'status' => 'error',
                'message' => "Belum Waktunya! Absen dibuka pukul " . $startTime->format('H:i')
            ]);
        }

        if ($now->greaterThan($endTime)) {
            return response()->json([
                'status' => 'error',
                'message' => "Waktu Habis! Jam pelajaran berakhir pukul " . $endTime->format('H:i')
            ]);
        }
        // =========================================================


        // VALIDASI 1: Kesesuaian Kelas
        if ($student->classroom_id !== $schedule->classroom_id) {
            $kelasSiswa = $student->classroom->name ?? 'Tanpa Kelas';
            $kelasJadwal = $schedule->classroom->name ?? 'Tanpa Kelas';
            return response()->json([
                'status' => 'error',
                'message' => "SALAH KELAS! Siswa {$student->name} ({$kelasSiswa}) tidak ada di jadwal ini."
            ]);
        }

        // VALIDASI 2: KEAMANAN KEPEMILIKAN JADWAL (Admin Bypass)
        if ($user->jenis_user !== 'admin') {
            if (!$teacher || $schedule->teacher_id !== $teacher->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Security Alert: Anda tidak berhak mengabsen di jadwal guru lain!'
                ]);
            }
        }

        // VALIDASI 3: DUPLIKASI
        $today = Carbon::today();

        $existing = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('date', $today)
                    ->first();

        if ($existing) {
            return response()->json([
                'status' => 'warning',
                'message' => "Siswa {$student->name} SUDAH ABSEN sebelumnya!"
            ]);
        }

        // LOGIKA 4: STATUS KEHADIRAN
        $status = 'hadir'; // Default

        // Reset startTime ke jam asli untuk perhitungan keterlambatan (jika tadi dimodifikasi buffer)
        $jamMasuk = Carbon::parse($schedule->start_time);

        // Toleransi Terlambat 15 menit setelah jam masuk
        if ($now->greaterThan($jamMasuk->addMinutes(15))) {
            $status = 'terlambat';
        }

        // SIMPAN DATA
        Attendance::create([
            'student_id'   => $student->id,
            'schedule_id'  => $schedule->id,
            // Pastikan kolom subject_id ada di database. Jika belum migrasi, comment baris ini.
            'subject_id'   => $schedule->subject_id,
            'date'         => $today->format('Y-m-d'),
            'check_in_time'=> $now->format('H:i:s'),
            'status'       => $status,
        ]);

        // KIRIM WA (Jika nomor ada)
        if (!empty($student->phone)) {
            $mapel = $schedule->subject->name ?? '-';
            $waktu = $now->format('H:i');
            $statText = ($status == 'present') ? 'HADIR TEPAT WAKTU' : 'TERLAMBAT';

            $message = "*LAPORAN KEHADIRAN MAPEL*\n\n" .
                       "Siswa: *{$student->name}*\n" .
                       "Mapel: {$mapel}\n" .
                       "Waktu: {$waktu}\n" .
                       "Status: {$statText}\n\n" .
                       "_Sistem Absensi Sekolah_";

            try {
                SendWhatsappJob::dispatch($student->phone, $message);
            } catch (\Exception $e) {
                // Log error diam-diam
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi Pembelajaran'.$schedule->subject->name.' Berhasil Dicatat',
            'student' => $student->name,
            'nis' => $student->nis
        ]);
    }

//    public function store(Request $request)
//     {
//         $request->validate([
//             'nis' => 'required',
//             'schedule_id' => 'required|exists:schedules,id'
//         ]);

//         $user = Auth::user();

//         // Cek data guru (jika user adalah guru)
//         $teacher = Teacher::where('user_id', $user->id)->first();

//         // 1. Cari Siswa
//         $student = Student::with('classroom')->where('nis', $request->nis)->first();

//         if (!$student) {
//             return response()->json(['status' => 'error', 'message' => 'Data Siswa tidak ditemukan!']);
//         }

//         // 2. Cari Jadwal
//         // Load 'classroom' untuk validasi kesesuaian kelas
//         $schedule = Schedule::with('classroom')->find($request->schedule_id);

//         if (!$schedule) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Jadwal tidak valid!'
//             ]);
//         }

//         // VALIDASI 1: Kesesuaian Kelas
//         if ($student->classroom_id !== $schedule->classroom_id) {
//             $kelasSiswa = $student->classroom->name ?? 'Tanpa Kelas';
//             $kelasJadwal = $schedule->classroom->name ?? 'Tanpa Kelas';
//             return response()->json([
//                 'status' => 'error',
//                 'message' => "SALAH KELAS! Siswa {$student->name} ({$kelasSiswa}) tidak ada di jadwal ini."
//             ]);
//         }

//         // VALIDASI 2: KEAMANAN KEPEMILIKAN JADWAL (Admin Bypass)
//         if ($user->jenis_user !== 'admin') {
//             if (!$teacher || $schedule->teacher_id !== $teacher->id) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => 'Security Alert: Anda tidak berhak mengabsen di jadwal guru lain!'
//                 ]);
//             }
//         }

//         // VALIDASI 3: DUPLIKASI
//         $today = Carbon::today();

//         $existing = Attendance::where('student_id', $student->id)
//                     ->where('schedule_id', $schedule->id)
//                     ->whereDate('date', $today) // Gunakan kolom 'date'
//                     ->first();

//         if ($existing) {
//             return response()->json([
//                 'status' => 'warning',
//                 'message' => "Siswa {$student->name} SUDAH ABSEN sebelumnya!"
//             ]);
//         }

//         // LOGIKA 4: STATUS KEHADIRAN
//         $status = 'hadir'; // Default

//         $jamMasuk = Carbon::parse($schedule->start_time);
//         $jamSekarang = Carbon::now();

//         // Toleransi 15 menit
//         if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
//             $status = 'terlambat';
//         }

//         // SIMPAN DATA (PERBAIKAN: Menambahkan 'date' dan 'check_in_time')
//         Attendance::create([
//             'student_id'   => $student->id,
//             'schedule_id'  => $schedule->id,
//             'subject_id'   => $schedule->subject_id, // Pastikan ada di migration attendances
//             'date'         => $today->format('Y-m-d'), // SOLUSI UTAMA ERROR NOT NULL
//             'check_in_time'=> $jamSekarang->format('H:i:s'),
//             'status'       => $status,
//         ]);

//         // KIRIM WA (Jika nomor ada)
//         if (!empty($student->phone)) {
//             $mapel = $schedule->subject->name ?? '-';
//             $waktu = $jamSekarang->format('H:i');
//             $statText = ($status == 'present') ? 'HADIR TEPAT WAKTU' : 'TERLAMBAT';

//             $message = "*LAPORAN KEHADIRAN MAPEL*\n\n" .
//                        "Siswa: *{$student->name}*\n" .
//                        "Mapel: {$mapel}\n" .
//                        "Waktu: {$waktu}\n" .
//                        "Status: {$statText}\n\n" .
//                        "_Sistem Absensi Sekolah_";

//             try {
//                 SendWhatsappJob::dispatch($student->phone, $message);
//             } catch (\Exception $e) {
//                 // Log error diam-diam
//             }
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Absensi Berhasil Dicatat',
//             'student' => $student->name,
//             'nis' => $student->nis
//         ]);
//     }



    /**
     * Proses Simpan Data Absensi (Dipanggil via AJAX)
     * URL: /scan/store
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nis' => 'required',
    //         'schedule_id' => 'required|exists:schedules,id'
    //     ]);

    //     // 1. Cari Siswa berdasarkan NIS
    //     // Load 'classroom' agar kita tahu nama kelas siswa tersebut (untuk pesan error)
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();

    //     if (!$student) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Data Siswa tidak ditemukan!'
    //         ]);
    //     }

    //     // 2. Cari Jadwal
    //     // Load 'classroom' untuk validasi kesesuaian kelas
    //     $schedule = Schedule::with('classroom')->find($request->schedule_id);

    //     if (!$schedule) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Jadwal tidak valid!'
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 1: CEK KESESUAIAN KELAS (UUID)
    //     // ---------------------------------------------------------
    //     if ($student->classroom_id !== $schedule->classroom_id) {

    //         $kelasSiswa = $student->classroom->name ?? 'Tanpa Kelas';
    //         $kelasJadwal = $schedule->classroom->name ?? 'Tanpa Kelas';

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => "SALAH KELAS! Siswa {$student->name} terdaftar di {$kelasSiswa}, tidak bisa absen di jadwal {$kelasJadwal}."
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 2: KEAMANAN KEPEMILIKAN JADWAL
    //     // ---------------------------------------------------------
    //     if ($schedule->teacher_id !== $teacher->id) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Security Alert: Anda tidak berhak mengabsen di jadwal guru lain!'
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA VALIDASI 3: CEK DUPLIKASI (SUDAH ABSEN HARI INI?)
    //     // ---------------------------------------------------------
    //     $existing = Attendance::where('student_id', $student->id)
    //                 ->where('schedule_id', $schedule->id)
    //                 ->where('date', date('Y-m-d'))
    //                 ->first();

    //     if ($existing) {
    //         // Pesan berbeda tergantung status sebelumnya
    //         $statusLama = strtoupper($existing->status);
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => "Siswa {$student->name} SUDAH ABSEN sebelumnya! (Status: {$statusLama})"
    //         ]);
    //     }

    //     // ---------------------------------------------------------
    //     // LOGIKA 4: HITUNG KETERLAMBATAN
    //     // ---------------------------------------------------------
    //     $status = 'hadir';

    //     // Pastikan Timezone di config/app.php sudah 'Asia/Jakarta'
    //     $jamMasuk = Carbon::parse($schedule->start_time);
    //     $jamSekarang = Carbon::now();

    //     // Toleransi 15 menit
    //     if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
    //         $status = 'terlambat';
    //     }

    //     $settings = Setting::pluck('value', 'key')->toArray();

    //     // ---------------------------------------------------------
    //     // 5. SIMPAN KE DATABASE
    //     // ---------------------------------------------------------
    //     Attendance::create([
    //         'student_id' => $student->id,
    //         'schedule_id' => $schedule->id,
    //         'date' => date('Y-m-d'),
    //         'check_in_time' => date('H:i:s'),
    //         'status' => $status
    //     ]);

    //     // ======================================================
    //     // DISPATCH JOB ANTRIAN WHATSAPP (INSTANT RESPONSE)
    //     // ======================================================
    //     if (!empty($student->phone)) {
    //         // Susun Pesan
    //         $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    //         $waktu = date('H:i');
    //         $tgl = date('d-m-Y');
    //         $statText = strtoupper($status);
    //         $emoji = $status == 'hadir' ? '✅' : '⚠️';

    //         $message = "*LAPORAN KEHADIRAN SISWA*\n\n" .
    //                    "Yth. Orang Tua/Wali,\n" .
    //                    "👤 Putra/Putri Anda: *{$student->name}*\n" .
    //                    "🏫 Kelas: {$student->classroom->name}\n" .
    //                    "📚 Mapel: {$mapel}\n" .
    //                    "📅 Waktu: {$waktu} WIB ($tgl)\n" .
    //                    "📝 Status: *{$statText}* {$emoji}\n\n" .
    //                    "_Sistem Absensi Sekolah._";

    //         // Masukkan ke Antrian (Tidak menunggu WA terkirim)
    //         SendWhatsappJob::dispatch($student->phone, $message);
    //     }
    //     // ======================================================

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Absensi Berhasil (Notifikasi diproses)',
    //         'student' => $student->name . " (" . strtoupper($status) . ")"
    //     ]);
    // }

    // public function store(Request $request)
    // {
    //     // ... (Validasi input tetap sama)
    //     $request->validate([
    //         'nis' => 'required',
    //         'schedule_id' => 'required|exists:schedules,id'
    //     ]);

    //     // 1. Cari Siswa
    //     $student = Student::with('classroom')->where('nis', $request->nis)->first();
    //     if (!$student) {
    //         return response()->json(['status' => 'error', 'message' => 'Data Siswa tidak ditemukan!']);
    //     }

    //     // 2. Cari Jadwal
    //     $schedule = Schedule::with('classroom')->find($request->schedule_id);
    //     if (!$schedule) {
    //         return response()->json(['status' => 'error', 'message' => 'Jadwal tidak valid!']);
    //     }

    //     // ... (Validasi Kelas & Guru tetap sama)
    //     if ($student->classroom_id !== $schedule->classroom_id) {
    //         // ... (kode error kelas mismatch)
    //         return response()->json(['status' => 'error', 'message' => 'Salah Kelas!']);
    //     }

    //     if ($schedule->teacher_id !== Auth::id()) {
    //         return response()->json(['status' => 'error', 'message' => 'Security Alert!']);
    //     }

    //     // ---------------------------------------------------------
    //     // PERUBAHAN 1: HITUNG STATUS DULUAN
    //     // Kita harus tahu status 'baru' ini apa sebelum cek DB
    //     // ---------------------------------------------------------
    //     $jamMasuk = Carbon::parse($schedule->start_time);
    //     $jamSekarang = Carbon::now();

    //     // Default status
    //     $statusBaru = 'hadir';

    //     // Toleransi 15 menit
    //     if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
    //         $statusBaru = 'terlambat';
    //     }

    //     // ---------------------------------------------------------
    //     // PERUBAHAN 2: LOGIKA PENYIMPANAN CERDAS
    //     // ---------------------------------------------------------
    //     $existing = Attendance::where('student_id', $student->id)
    //                 ->where('schedule_id', $schedule->id)
    //                 ->where('date', date('Y-m-d'))
    //                 ->first();

    //     $isDataSaved = false; // Flag untuk trigger notifikasi

    //     if ($existing) {
    //         // SKENARIO A: DATA SUDAH ADA

    //         // Cek apakah statusnya sama persis?
    //         if ($existing->status === $statusBaru) {
    //             // JIKA SAMA: Jangan simpan, Jangan kirim notif, tapi beri respon sukses agar frontend tidak error
    //             return response()->json([
    //                 'status' => 'success', // Tetap success agar alat scan tidak bunyi error
    //                 'message' => 'Siswa sudah absen (Data Sama, Tidak Disimpan)',
    //                 'student' => $student->name . " (SUDAH ADA)"
    //             ]);
    //         } else {
    //             // JIKA BEDA: Update data (Misal dari 'alpha' jadi 'hadir' atau koreksi status)
    //             $existing->update([
    //                 'check_in_time' => date('H:i:s'),
    //                 'status' => $statusBaru
    //             ]);
    //             $isDataSaved = true; // Trigger notifikasi
    //         }

    //     } else {
    //         // SKENARIO B: DATA BELUM ADA (BARU)
    //         Attendance::create([
    //             'student_id' => $student->id,
    //             'schedule_id' => $schedule->id,
    //             'date' => date('Y-m-d'),
    //             'check_in_time' => date('H:i:s'),
    //             'status' => $statusBaru
    //         ]);
    //         $isDataSaved = true; // Trigger notifikasi
    //     }

    //     // ---------------------------------------------------------
    //     // KIRIM NOTIFIKASI HANYA JIKA ADA DATA BARU/UPDATE
    //     // ---------------------------------------------------------
    //     if ($isDataSaved && !empty($student->phone)) {
    //         // Susun Pesan
    //         $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    //         $waktu = date('H:i');
    //         $tgl = date('d-m-Y');
    //         $statText = strtoupper($statusBaru);
    //         $emoji = $statusBaru == 'hadir' ? '✅' : '⚠️';

    //         $message = "*LAPORAN KEHADIRAN SISWA*\n\n" .
    //                 "Yth. Orang Tua/Wali,\n" .
    //                 "👤 Putra/Putri Anda: *{$student->name}*\n" .
    //                 "🏫 Kelas: {$student->classroom->name}\n" .
    //                 "📚 Mapel: {$mapel}\n" .
    //                 "📅 Waktu: {$waktu} WIB ($tgl)\n" .
    //                 "📝 Status: *{$statText}* {$emoji}\n\n" .
    //                 "_Sistem Absensi Sekolah._";

    //         SendWhatsappJob::dispatch($student->phone, $message);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => $existing ? 'Data Absensi Diperbarui' : 'Absensi Berhasil Disimpan',
    //         'student' => $student->name . " (" . strtoupper($statusBaru) . ")"
    //     ]);
    // }

    // =============================================================
    // FITUR BARU: ABSENSI MANUAL GURU (SAKIT/IZIN/ALPA)
    // =============================================================

    /**
     * Halaman Form Manual (Menampilkan Daftar Siswa)
     * Route: GET /schedule/{id}/manual
     */
    // public function createManual($schedule_id)
    // {

    //      $teacher = Teacher::where('user_id', Auth::id())->first();

    //     // Validasi jika akun tidak terhubung ke data guru
    //     if (!$teacher) {
    //         abort(403, 'Akun Anda tidak terdaftar sebagai Guru.');
    //     }


    //     // 1. Cari Jadwal & Validasi Pemilik
    //     $schedule = Schedule::with(['classroom', 'subject'])
    //                 ->where('id', $schedule_id)
    //                 ->where('teacher_id', $teacher->id)
    //                 ->firstOrFail();

    //     // 2. Ambil semua siswa di kelas tersebut
    //     $students = Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // 3. Ambil data absensi yang SUDAH ada hari ini (untuk mengisi status radio button)
    //     // Format Array: [student_id => status] contoh: [1 => 'hadir', 2 => 'sakit']
    //     $existingAttendances = Attendance::where('schedule_id', $schedule_id)
    //                             ->where('date', date('Y-m-d'))
    //                             ->pluck('status', 'student_id')
    //                             ->toArray();

    //     return view('attendance.manual', compact('schedule', 'students', 'existingAttendances'));
    // }

    // /**
    //  * Proses Simpan Absensi Manual (Massal)
    //  * Route: POST /schedule/{id}/manual
    //  */

    // /**
    //  * Proses Simpan Absensi Manual (Massal) + Kirim WA Antrian
    //  */
    // // public function storeManual(Request $request, $schedule_id)
    // // {
    // //     $request->validate([
    // //         'attendances' => 'required|array', // Key: student_id, Value: status
    // //     ]);

    // //     $settings = Setting::pluck('value', 'key')->toArray();
    // //     $inf_app = $settings['inf_app'];
    // //     //dd($inf_app);

    // //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    // //     if ($schedule->teacher_id !== Auth::id()) {
    // //         abort(403, 'Unauthorized action.');
    // //     }

    // //     $date = date('Y-m-d');
    // //     $now = date('H:i:s');

    // //     // Loop setiap input dari guru
    // //     foreach ($request->attendances as $studentId => $status) {
    // //         // Jika status tidak dipilih (kosong), lewati
    // //         if (!$status) continue;

    // //         // 1. Simpan ke Database
    // //         Attendance::updateOrCreate(
    // //             [
    // //                 'student_id'  => $studentId,
    // //                 'schedule_id' => $schedule_id,
    // //                 'date'        => $date
    // //             ],
    // //             [
    // //                 'check_in_time' => $now,
    // //                 'status'        => $status
    // //             ]
    // //         );

    // //         // ==========================================================
    // //         // 2. LOGIKA KIRIM WA (ANTRIAN / QUEUE)
    // //         // ==========================================================
    // //         // Ambil data siswa untuk dapat nomor HP & Nama
    // //         $student = Student::find($studentId);

    // //         if ($student && !empty($student->phone)) {

    // //             // Tentukan Emoji & Pesan berdasarkan status
    // //             $emoji = '✅';
    // //             $keterangan = 'Hadir di sekolah';

    // //             switch ($status) {
    // //                 case 'izin':
    // //                     $emoji = '📩';
    // //                     $keterangan = 'Izin (Diketahui Guru)';
    // //                     break;
    // //                 case 'sakit':
    // //                     $emoji = '💊';
    // //                     $keterangan = 'Sakit (Diketahui Guru)';
    // //                     break;
    // //                 case 'alpa':
    // //                     $emoji = '❌';
    // //                     $keterangan = 'ALPA / Tanpa Keterangan';
    // //                     break;
    // //                 case 'terlambat':
    // //                     $emoji = '⚠️';
    // //                     $keterangan = 'Hadir (Terlambat)';
    // //                     break;
    // //             }

    // //             // Susun Pesan
    // //             $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    // //             $tgl = date('d-m-Y');

    // //             $message = "*LAPORAN PRESENSI MANUAL*\n\n" .
    // //                        "Yth. Orang Tua/Wali,\n" .
    // //                        "Informasi kehadiran putra/putri Anda:\n\n" .
    // //                        "👤 Nama : *$student->name*\n" .
    // //                        "🏫 Kelas : {$schedule->classroom->name}\n" .
    // //                        "📚 Mapel : $mapel\n" .
    // //                        "📅 Tanggal : $tgl\n" .
    // //                        "📝 Status : *" . strtoupper($status) . "* $emoji\n" .
    // //                        "ℹ️ Ket : $keterangan\n\n" .
    // //                        "_$inf_app, Data ini diinput manual oleh Guru Pengampu._";

    // //             // Masukkan ke Antrian (Background Job)
    // //             // Ini penting agar proses simpan tidak lemot
    // //             SendWhatsappJob::dispatch($student->phone, $message);
    // //         }
    // //         // ==========================================================
    // //     }

    // //     return redirect()->route('schedule.index')->with('success', 'Absensi manual disimpan & notifikasi WA sedang dikirim!');
    // // }

    // public function storeManual(Request $request, $schedule_id)
    // {
    //     $request->validate([
    //         'attendances' => 'required|array', // Key: student_id, Value: status
    //     ]);

    //     $settings = Setting::pluck('value', 'key')->toArray();
    //     $inf_app = $settings['inf_app'] ?? 'Sistem Sekolah'; // Pakai default jika null

    //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);


    //     $teacher = Teacher::where('user_id', Auth::id())->first();

    //     if ($schedule->teacher_id !== $teacher->id) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $date = date('Y-m-d');
    //     $now = date('H:i:s');

    //     $updatedCount = 0; // Opsional: untuk menghitung berapa siswa yang diupdate

    //     // Loop setiap input dari guru
    //     foreach ($request->attendances as $studentId => $status) {
    //         // Jika status tidak dipilih (kosong), lewati
    //         if (!$status) continue;

    //         // ---------------------------------------------------------
    //         // 1. CEK DATA LAMA (EXISTING)
    //         // ---------------------------------------------------------
    //         $attendance = Attendance::where('student_id', $studentId)
    //             ->where('schedule_id', $schedule_id)
    //             ->where('date', $date)
    //             ->first();

    //         $shouldSendNotif = false; // Flag penanda kirim WA

    //         if ($attendance) {
    //             // SKENARIO A: DATA SUDAH ADA

    //             // Cek apakah statusnya sama persis?
    //             if ($attendance->status === $status) {
    //                 // JIKA SAMA: Lewati loop ini. Jangan simpan, jangan kirim WA.
    //                 continue;
    //             }

    //             // JIKA BEDA: Update datanya
    //             $attendance->update([
    //                 'check_in_time' => $now,
    //                 'status'        => $status
    //             ]);
    //             $shouldSendNotif = true; // Status berubah, perlu kirim WA

    //         } else {
    //             // SKENARIO B: DATA BELUM ADA (BARU)
    //             Attendance::create([
    //                 'student_id'  => $studentId,
    //                 'schedule_id' => $schedule_id,
    //                 'subject_id'   => $schedule->subject_id,
    //                 'date'        => $date,
    //                 'check_in_time' => $now,
    //                 'status'      => $status
    //             ]);
    //             $shouldSendNotif = true; // Data baru, perlu kirim WA
    //         }

    //         // ==========================================================
    //         // 2. LOGIKA KIRIM WA (HANYA JIKA ADA PERUBAHAN)
    //         // ==========================================================
    //         if ($shouldSendNotif) {
    //             $updatedCount++; // Counter update bertambah

    //             // Ambil data siswa HANYA jika notif akan dikirim (Hemat Query)
    //             $student = Student::find($studentId);

    //             if ($student && !empty($student->phone)) {

    //                 // Tentukan Emoji & Pesan berdasarkan status
    //                 $emoji = '✅';
    //                 $keterangan = 'Hadir di sekolah';

    //                 switch ($status) {
    //                     case 'izin':
    //                         $emoji = '📩';
    //                         $keterangan = 'Izin (Diketahui Guru)';
    //                         break;
    //                     case 'sakit':
    //                         $emoji = '💊';
    //                         $keterangan = 'Sakit (Diketahui Guru)';
    //                         break;
    //                     case 'alpa':
    //                         $emoji = '❌';
    //                         $keterangan = 'ALPA / Tanpa Keterangan';
    //                         break;
    //                     case 'terlambat':
    //                         $emoji = '⚠️';
    //                         $keterangan = 'Hadir (Terlambat)';
    //                         break;
    //                 }

    //                 // Susun Pesan
    //                 $mapel = $schedule->subject->name ?? $schedule->subject_name ?? '-';
    //                 $tgl = date('d-m-Y');

    //                 $message = "*LAPORAN PRESENSI MANUAL*\n\n" .
    //                             "Yth. Orang Tua/Wali,\n" .
    //                             "Informasi kehadiran putra/putri Anda:\n\n" .
    //                             "👤 Nama : *$student->name*\n" .
    //                             "🏫 Kelas : {$schedule->classroom->name}\n" .
    //                             "📚 Mapel : $mapel\n" .
    //                             "📅 Tanggal : $tgl\n" .
    //                             "📝 Status : *" . strtoupper($status) . "* $emoji\n" .
    //                             "ℹ️ Ket : $keterangan\n\n" .
    //                             "_$inf_app, Data ini diinput manual oleh Guru Pengampu._";

    //                 // Masukkan ke Antrian (Background Job)
    //                 SendWhatsappJob::dispatch($student->phone, $message);
    //             }
    //         }
    //         // ==========================================================
    //     }

    //     // Pesan feedback disesuaikan
    //     if ($updatedCount > 0) {
    //         return redirect()->route('schedule.index')->with('success', "Berhasil menyimpan $updatedCount data siswa & notifikasi diproses.");
    //     } else {
    //         return redirect()->route('schedule.index')->with('warning', 'Tidak ada perubahan data absensi.');
    //     }
    // }

    // =============================================================
    // FITUR ABSENSI MANUAL GURU
    // =============================================================

    /**
     * Halaman Form Manual
     */
    // public function createManual($schedule_id)
    // {
    //     $user = Auth::user();
    //     $teacher = Teacher::where('user_id', $user->id)->first();

    //     if ($user->jenis_user !== 'admin' && !$teacher) {
    //         abort(403, 'Akses Ditolak');
    //     }

    //     // 1. Cari Jadwal
    //     $query = Schedule::with(['classroom', 'subject'])->where('id', $schedule_id);

    //     if ($user->jenis_user !== 'admin') {
    //         $query->where('teacher_id', $teacher->id);
    //     }

    //     $schedule = $query->firstOrFail();

    //     // 2. Ambil Siswa
    //     $students = Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // 3. Ambil Status Absensi Hari Ini
    //     $existingAttendances = Attendance::where('schedule_id', $schedule_id)
    //                             ->whereDate('created_at', Carbon::today())
    //                             ->pluck('status', 'student_id')
    //                             ->toArray();

    //     return view('attendance.manual', compact('schedule', 'students', 'existingAttendances'));
    // }

    // public function createManual($schedule_id)
    // {
    //     $user = Auth::user();
    //     $teacher = Teacher::where('user_id', $user->id)->first();

    //     if ($user->jenis_user !== 'admin' && !$teacher) {
    //         abort(403, 'Akses Ditolak');
    //     }

    //     // 1. Cari Jadwal
    //     $query = Schedule::with(['classroom', 'subject'])->where('id', $schedule_id);

    //     if ($user->jenis_user !== 'admin') {
    //         $query->where('teacher_id', $teacher->id);
    //     }

    //     $schedule = $query->firstOrFail();

    //     // 2. Ambil Semua Siswa di Kelas Ini
    //     $students = Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // 3. Ambil Status Absensi Mapel Hari Ini
    //     $existingAttendances = Attendance::where('schedule_id', $schedule_id)
    //                             ->whereDate('created_at', Carbon::today())
    //                             ->pluck('status', 'student_id')
    //                             ->toArray();

    //     // --- FITUR BARU: CEK PERSENTASE KEHADIRAN GERBANG (BANTUAN GURU) ---
    //     $today = Carbon::today()->format('Y-m-d');

    //     // Ambil ID siswa yang SUDAH absen gerbang hari ini
    //     $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
    //                         ->whereIn('student_id', $students->pluck('id'))
    //                         ->whereNotNull('arrival_time')
    //                         ->pluck('student_id')
    //                         ->toArray();

    //     $totalSiswa = $students->count();
    //     $totalHadirGerbang = count($idsHadirGerbang);

    //     // Hitung Persentase
    //     $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

    //     // Ambil data siswa yang BELUM absen gerbang (untuk ditampilkan di modal bantuan)
    //     $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);

    //     return view('attendance.manual', compact(
    //         'schedule',
    //         'students',
    //         'existingAttendances',
    //         'gatePercentage',
    //         'studentsMissingGate'
    //     ));
    // }

    // public function createManual($schedule_id)
    // {
    //     $user = Auth::user();
    //     $teacher = Teacher::where('user_id', $user->id)->first();

    //     // Validasi Akses: Admin boleh akses semua, Guru hanya jadwalnya sendiri
    //     if ($user->jenis_user !== 'admin') {
    //         if (!$teacher) {
    //             abort(403, 'Akses Ditolak: Akun Anda bukan Guru.');
    //         }
    //         // Pastikan jadwal ini milik guru tersebut
    //         $isOwner = Schedule::where('id', $schedule_id)->where('teacher_id', $teacher->id)->exists();
    //         if (!$isOwner) {
    //             abort(403, 'Akses Ditolak: Ini bukan jadwal mengajar Anda.');
    //         }
    //     }

    //     // 1. Cari Jadwal
    //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    //     // 2. Ambil Semua Siswa di Kelas Ini
    //     $students = Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // 3. Ambil Status Absensi Mapel Hari Ini (agar form terisi jika sudah pernah simpan)
    //     $existingAttendances = Attendance::where('schedule_id', $schedule_id)
    //                             ->whereDate('date', Carbon::today())
    //                             ->pluck('status', 'student_id')
    //                             ->toArray();

    //     // 4. [OPTIMASI] Ambil History Absensi Minggu-minggu Sebelumnya
    //     // Kita ambil sekaligus (Eager Loading style) untuk mencegah query berulang di View
    //     $allHistories = Attendance::where('schedule_id', $schedule_id)
    //                         ->whereDate('date', '<', Carbon::today())
    //                         ->orderBy('date', 'desc')
    //                         ->get()
    //                         ->groupBy('student_id');

    //     // --- FITUR BARU: CEK PERSENTASE KEHADIRAN GERBANG (BANTUAN GURU) ---
    //     $today = Carbon::today()->format('Y-m-d');

    //     // Ambil ID siswa yang SUDAH absen gerbang hari ini
    //     $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
    //                         ->whereIn('student_id', $students->pluck('id'))
    //                         ->whereNotNull('arrival_time')
    //                         ->pluck('student_id')
    //                         ->toArray();

    //     $totalSiswa = $students->count();
    //     $totalHadirGerbang = count($idsHadirGerbang);

    //     // Hitung Persentase
    //     $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

    //     // Ambil data siswa yang BELUM absen gerbang (untuk ditampilkan di modal bantuan)
    //     $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);

    //     return view('attendance.manual', compact(
    //         'schedule',
    //         'students',
    //         'existingAttendances',
    //         'allHistories', // Dikirim ke view untuk kolom History
    //         'gatePercentage',
    //         'studentsMissingGate'
    //     ));
    // }

    //  public function createManual($schedule_id)
    // {
    //     $user = Auth::user();
    //     $teacher = Teacher::where('user_id', $user->id)->first();

    //     // Validasi Akses: Admin boleh akses semua, Guru hanya jadwalnya sendiri
    //     if ($user->jenis_user !== 'admin') {
    //         if (!$teacher) {
    //             abort(403, 'Akses Ditolak: Akun Anda bukan Guru.');
    //         }
    //         // Pastikan jadwal ini milik guru tersebut
    //         $isOwner = Schedule::where('id', $schedule_id)->where('teacher_id', $teacher->id)->exists();
    //         if (!$isOwner) {
    //             abort(403, 'Akses Ditolak: Ini bukan jadwal mengajar Anda.');
    //         }
    //     }

    //     // 1. Cari Jadwal
    //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    //     // 2. Ambil Semua Siswa di Kelas Ini
    //     $students = Student::where('classroom_id', $schedule->classroom_id)
    //                 ->orderBy('name')
    //                 ->get();

    //     // 3. Ambil Status Absensi Mapel Hari Ini (agar form terisi jika sudah pernah simpan)
    //     // Kita menggunakan keyBy untuk memudahkan akses array di view
    //     $existingAttendances = Attendance::where('schedule_id', $schedule_id)
    //                             ->whereDate('date', Carbon::today())
    //                             ->get()
    //                             ->mapWithKeys(function ($item) {
    //                                 // Mengembalikan array lengkap agar recorded_by bisa diakses di view
    //                                 return [$item->student_id => [
    //                                     'status' => $item->status,
    //                                     'recorded_by' => $item->recorded_by
    //                                 ]];
    //                             })
    //                             ->toArray();

    //     // 4. [OPTIMASI] Ambil History Absensi Minggu-minggu Sebelumnya
    //     // Kita ambil sekaligus (Eager Loading style) untuk mencegah query berulang di View
    //     $allHistories = Attendance::where('schedule_id', $schedule_id)
    //                         ->whereDate('date', '<', Carbon::today())
    //                         ->orderBy('date', 'desc')
    //                         ->get()
    //                         ->groupBy('student_id');

    //     //dd($schedule);

    //     // --- FITUR BARU: CEK PERSENTASE KEHADIRAN GERBANG (BANTUAN GURU) ---
    //     $today = Carbon::today()->format('Y-m-d');

    //     // Ambil ID siswa yang SUDAH absen gerbang hari ini
    //     $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
    //                         ->whereIn('student_id', $students->pluck('id'))
    //                         ->whereNotNull('arrival_time')
    //                         ->pluck('student_id')
    //                         ->toArray();

    //     $totalSiswa = $students->count();
    //     $totalHadirGerbang = count($idsHadirGerbang);

    //     // Hitung Persentase
    //     $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

    //     // Ambil data siswa yang BELUM absen gerbang (untuk ditampilkan di modal bantuan)
    //     $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);

    //     return view('attendance.manual', compact(
    //         'schedule',
    //         'students',
    //         'existingAttendances',
    //         'allHistories', // Dikirim ke view untuk kolom History
    //         'gatePercentage',
    //         'studentsMissingGate'
    //     ));
    // }

     /**
     * Halaman Input Absensi Manual Guru
     */
    public function createManual($schedule_id)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        // Validasi Akses: Admin boleh akses semua, Guru hanya jadwalnya sendiri
        if ($user->jenis_user !== 'admin') {
            if (!$teacher) {
                abort(403, 'Akses Ditolak: Akun Anda bukan Guru.');
            }
            // Pastikan jadwal ini milik guru tersebut
            $isOwner = Schedule::where('id', $schedule_id)->where('teacher_id', $teacher->id)->exists();
            if (!$isOwner) {
                abort(403, 'Akses Ditolak: Ini bukan jadwal mengajar Anda.');
            }
        }

        // 1. Cari Jadwal
        $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

        // 2. Ambil Semua Siswa di Kelas Ini
        $students = Student::where('classroom_id', $schedule->classroom_id)
                    ->orderBy('name')
                    ->get();

        // 3. Ambil Status Absensi Mapel Hari Ini
        $existingAttendances = Attendance::where('schedule_id', $schedule_id)
                                ->whereDate('date', Carbon::today())
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->student_id => [
                                        'status' => $item->status,
                                        'recorded_by' => $item->recorded_by
                                    ]];
                                })
                                ->toArray();

        // 4. [FIXED] Ambil History Absensi Berdasarkan KELAS dan MAPEL (Bukan Schedule ID saja)
        // Ini memastikan pertemuan di hari lain (misal Senin & Kamis) tetap muncul dalam history
        $allHistories = Attendance::whereHas('schedule', function($q) use ($schedule) {
                                $q->where('classroom_id', $schedule->classroom_id)
                                  ->where('subject_id', $schedule->subject_id);
                            })
                            ->whereDate('date', '<', Carbon::today()) // Hanya ambil tanggal SEBELUM hari ini
                            ->orderBy('date', 'desc')
                            ->get()
                            ->groupBy('student_id');

        // --- FITUR BANTUAN GURU: CEK PERSENTASE KEHADIRAN GERBANG ---
        $today = Carbon::today()->format('Y-m-d');

        $idsHadirGerbang = DailyAttendance::whereDate('date', $today)
                            ->whereIn('student_id', $students->pluck('id'))
                            ->whereNotNull('arrival_time')
                            ->pluck('student_id')
                            ->toArray();

        $totalSiswa = $students->count();
        $totalHadirGerbang = count($idsHadirGerbang);
        $gatePercentage = $totalSiswa > 0 ? ($totalHadirGerbang / $totalSiswa) * 100 : 0;

        $studentsMissingGate = $students->whereNotIn('id', $idsHadirGerbang);

        return view('guru.pembelajaran.manual', compact(
            'schedule',
            'students',
            'existingAttendances',
            'allHistories',
            'gatePercentage',
            'studentsMissingGate'
        ));
    }

    /**
     * Proses Simpan Manual dengan VALIDASI KETAT
     */
    // public function storeManual(Request $request, $schedule_id)
    // {
    //     $request->validate([
    //         'attendances' => 'required|array', // Key: student_id, Value: status
    //     ]);

    //     $user = Auth::user();
    //     $teacher = Teacher::where('user_id', $user->id)->first();
    //     $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);

    //     // Validasi Kepemilikan (Kecuali Admin)
    //     if ($user->jenis_user !== 'admin') {
    //         if (!$teacher || $schedule->teacher_id !== $teacher->id) {
    //             abort(403, 'Unauthorized action.');
    //         }
    //     }

    //     $now = Carbon::now();
    //     $date = $now->format('Y-m-d');

    //     // -------------------------------------------------------------
    //     // 1. VALIDASI WAKTU (HARI & JAM)
    //     // -------------------------------------------------------------
    //     $daysMap = [
    //         'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    //         'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    //     ];
    //     $currentDayIndo = $daysMap[$now->format('l')];

    //     // Cek Hari
    //     if (strtolower($schedule->day) !== strtolower($currentDayIndo)) {
    //         return redirect()->back()->with('error', "Gagal! Jadwal ini untuk hari {$schedule->day}, sekarang hari {$currentDayIndo}.");
    //     }

    //     // Cek Jam
    //     $startTime = Carbon::parse($schedule->start_time)->setDate($now->year, $now->month, $now->day);
    //     $endTime = Carbon::parse($schedule->end_time)->setDate($now->year, $now->month, $now->day);

    //     // Validasi Jam Manual
    //     /*
    //     if ($now->lt($startTime) || $now->gt($endTime)) {
    //          return redirect()->back()->with('error', "Gagal! Absensi hanya dibuka pada pukul {$schedule->start_time} - {$schedule->end_time}.");
    //     }
    //     */

    //     // -------------------------------------------------------------
    //     // 2. PROSES ABSENSI PER SISWA
    //     // -------------------------------------------------------------
    //     $updatedCount = 0;
    //     $skippedCount = 0;

    //     foreach ($request->attendances as $studentId => $status) {
    //         if (!$status) continue;

    //         // --- VALIDASI GERBANG (DAILY ATTENDANCE) ---
    //         $dailyLog = DailyAttendance::where('student_id', $studentId)
    //                     ->whereDate('date', $date)
    //                     ->first();

    //         // Aturan A: Harus sudah Scan Masuk
    //         if (!$dailyLog || empty($dailyLog->arrival_time)) {
    //             $skippedCount++;
    //             continue; // Skip siswa ini (Belum datang ke sekolah)
    //         }

    //         // Aturan B: Tidak boleh sudah Scan Pulang
    //         if (!empty($dailyLog->departure_time)) {
    //             $skippedCount++;
    //             continue; // Skip siswa ini (Sudah pulang)
    //         }
    //         // -------------------------------------------

    //         // Cek data existing di mapel ini
    //         $attendance = Attendance::where('student_id', $studentId)
    //             ->where('schedule_id', $schedule_id)
    //             ->whereDate('created_at', $date)
    //             ->first();

    //         $shouldSendNotif = false;

    //         // PERBAIKAN: Gunakan nilai langsung dari Form (Bahasa Indonesia)
    //         // Form mengirim: 'hadir', 'terlambat', 'izin', 'sakit', 'alpa'
    //         // Database menerima nilai tersebut secara langsung.
    //         $dbStatus = $status;

    //         if ($attendance) {
    //             // Update jika status beda
    //             if ($attendance->status !== $dbStatus) {
    //                 $attendance->update([
    //                     'check_in_time' => $now->toTimeString(),
    //                     'status' => $dbStatus
    //                 ]);
    //                 $shouldSendNotif = true;
    //             }
    //         } else {
    //             // Create baru
    //             Attendance::create([
    //                 'student_id'   => $studentId,
    //                 'schedule_id'  => $schedule_id,
    //                 'subject_id'   => $schedule->subject_id,
    //                 'date'         => $date,
    //                 'check_in_time'=> $now->toTimeString(),
    //                 'status'       => $dbStatus
    //             ]);
    //             $shouldSendNotif = true;
    //         }

    //         if ($shouldSendNotif) {
    //             $updatedCount++;

    //             // Kirim WA
    //             $student = Student::find($studentId);
    //             if ($student && !empty($student->phone)) {
    //                 $mapel = $schedule->subject->name ?? '-';
    //                 $statText = strtoupper($status);
    //                 $message = "*LAPORAN KEHADIRAN MAPEL*\n\n" .
    //                            "Siswa: *{$student->name}*\n" .
    //                            "Mapel: {$mapel}\n" .
    //                            "Status: {$statText}\n\n" .
    //                            "_Sistem Absensi Sekolah_";

    //                 try {
    //                     SendWhatsappJob::dispatch($student->phone, $message);
    //                 } catch (\Exception $e) {}
    //             }
    //         }
    //     }

    //     $msg = "Berhasil memproses $updatedCount data.";
    //     if ($skippedCount > 0) {
    //         $msg .= " (Peringatan: $skippedCount siswa dilewati karena belum scan masuk gerbang atau sudah pulang)";
    //     }

    //     if ($updatedCount > 0) {
    //         return redirect()->route('schedule.index')->with('success', $msg);
    //     } else {
    //         return redirect()->route('schedule.index')->with('warning', $msg ?: 'Tidak ada perubahan data.');
    //     }
    // }

    // public function storeManual(Request $request, $schedule_id)
    // {
    //     $request->validate([
    //         'attendances' => 'array',
    //     ]);

    //     $date = Carbon::now()->format('Y-m-d');
    //     // Gunakan waktu sekarang atau default jam masuk mapel jika perlu
    //     $checkInTime = Carbon::now()->format('H:i:s');

    //     foreach ($request->attendances as $studentId => $status) {
    //         Attendance::updateOrCreate(
    //             [
    //                 'schedule_id' => $schedule_id,
    //                 'student_id' => $studentId,
    //                 'date' => $date
    //             ],
    //             [
    //                 'status' => $status,
    //                 'check_in_time' => $checkInTime
    //             ]
    //         );
    //     }

    //     return redirect()->route('schedule.index')->with('success', 'Data kehadiran berhasil disimpan.');
    // }

    /**
     * Proses Simpan Absensi Manual
     */
    // public function storeManual(Request $request, $schedule_id)
    // {
    //     $request->validate([
    //         'attendances' => 'array',
    //     ]);

    //     $date = Carbon::now()->format('Y-m-d');
    //     $checkInTime = Carbon::now()->format('H:i:s');

    //     $schedule = Schedule::findOrFail($schedule_id);

    //     if ($request->has('attendances')) {
    //         foreach ($request->attendances as $studentId => $status) {
    //             Attendance::updateOrCreate(
    //                 [
    //                     'schedule_id' => $schedule->id,
    //                     'student_id' => $studentId,
    //                     'date' => $date
    //                 ],
    //                 [
    //                     'status' => $status,
    //                     'check_in_time' => $checkInTime,
    //                     'recorded_by' => 'teacher_manual'
    //                 ]
    //             );
    //         }
    //     }

    //     return redirect()->route('schedule.index')->with('success', 'Data kehadiran berhasil disimpan.');
    // }

    /**
     * Proses Simpan Absensi Manual
     */
    public function storeManual(Request $request, $schedule_id)
    {
        $request->validate([
            'attendances' => 'array',
        ]);

        $date = Carbon::now()->format('Y-m-d');
        $checkInTime = Carbon::now()->format('H:i:s');

        $schedule = Schedule::findOrFail($schedule_id);

        if ($request->has('attendances')) {
            foreach ($request->attendances as $studentId => $status) {
                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $studentId,
                        'date' => $date
                    ],
                    [
                        'subject_id' => $schedule->subject_id, // FIX: Menambahkan subject_id agar tidak null
                        'status' => $status,
                        'check_in_time' => $checkInTime,
                        'recorded_by' => 'teacher_manual'
                    ]
                );
            }
        }

        return redirect()->route('schedule.index')->with('success', 'Data kehadiran berhasil disimpan.');
    }
}
