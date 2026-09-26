<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <--- WAJIB IMPORT HTTP CLIENT

class AttendanceController extends Controller
{
    /**
     * Menampilkan Halaman Scanner
     * URL: /scan/{schedule_id}
     */
    public function index($schedule_id)
    {
        // 1. Cari Jadwal
        // - with('classroom'): Kita butuh nama kelas untuk ditampilkan di judul halaman
        // - where('teacher_id', Auth::id()): Keamanan, hanya pemilik jadwal yang bisa buka
        $schedule = Schedule::with('classroom')
                    ->where('id', $schedule_id)
                    ->where('teacher_id', Auth::id())
                    ->firstOrFail(); // Jika tidak ketemu/bukan pemiliknya, muncul 404

        return view('scan', compact('schedule'));
    }

    /**
     * Proses Simpan Data Absensi (Dipanggil via AJAX)
     * URL: /scan/store
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'schedule_id' => 'required|exists:schedules,id'
        ]);

        // 1. Cari Siswa berdasarkan NIS
        // Load 'classroom' agar kita tahu nama kelas siswa tersebut (untuk pesan error)
        $student = Student::with('classroom')->where('nis', $request->nis)->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Siswa tidak ditemukan!'
            ]);
        }

        // 2. Cari Jadwal
        // Load 'classroom' untuk validasi kesesuaian kelas
        $schedule = Schedule::with('classroom')->find($request->schedule_id);

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak valid!'
            ]);
        }

        // ---------------------------------------------------------
        // LOGIKA VALIDASI 1: CEK KESESUAIAN KELAS (UUID)
        // ---------------------------------------------------------
        if ($student->classroom_id !== $schedule->classroom_id) {

            $kelasSiswa = $student->classroom->name ?? 'Tanpa Kelas';
            $kelasJadwal = $schedule->classroom->name ?? 'Tanpa Kelas';

            return response()->json([
                'status' => 'error',
                'message' => "SALAH KELAS! Siswa {$student->name} terdaftar di {$kelasSiswa}, tidak bisa absen di jadwal {$kelasJadwal}."
            ]);
        }

        // ---------------------------------------------------------
        // LOGIKA VALIDASI 2: KEAMANAN KEPEMILIKAN JADWAL
        // ---------------------------------------------------------
        if ($schedule->teacher_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Security Alert: Anda tidak berhak mengabsen di jadwal guru lain!'
            ]);
        }

        // ---------------------------------------------------------
        // LOGIKA VALIDASI 3: CEK DUPLIKASI (SUDAH ABSEN HARI INI?)
        // ---------------------------------------------------------
        $existing = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('date', date('Y-m-d'))
                    ->first();

        if ($existing) {
            // Pesan berbeda tergantung status sebelumnya
            $statusLama = strtoupper($existing->status);
            return response()->json([
                'status' => 'error',
                'message' => "Siswa {$student->name} SUDAH ABSEN sebelumnya! (Status: {$statusLama})"
            ]);
        }

        // ---------------------------------------------------------
        // LOGIKA 4: HITUNG KETERLAMBATAN
        // ---------------------------------------------------------
        $status = 'hadir';

        // Pastikan Timezone di config/app.php sudah 'Asia/Jakarta'
        $jamMasuk = Carbon::parse($schedule->start_time);
        $jamSekarang = Carbon::now();

        // Toleransi 15 menit
        if ($jamSekarang->greaterThan($jamMasuk->addMinutes(15))) {
            $status = 'terlambat';
        }

        // ---------------------------------------------------------
        // 5. SIMPAN KE DATABASE
        // ---------------------------------------------------------
        Attendance::create([
            'student_id' => $student->id,
            'schedule_id' => $schedule->id,
            'date' => date('Y-m-d'),
            'check_in_time' => date('H:i:s'),
            'status' => $status
        ]);

        // ======================================================
        // LOGIKA TAMBAHAN: KIRIM NOTIFIKASI WHATSAPP OTOMATIS
        // ======================================================
        // Cek apakah siswa punya nomor HP orang tua
        if (!empty($student->phone)) {
            try {
                // 1. Susun Variabel Pesan
                $namaSiswa = $student->name;
                $kelas     = $student->classroom->name ?? '-';

                // Ambil nama mapel dari relasi subject
                // Jika relasi null (legacy data), coba ambil kolom lama atau dash
                $mapel     = $schedule->subject->name ?? $schedule->subject_name ?? '-';

                $waktu     = date('H:i');
                $tgl       = date('d-m-Y');

                // Variasi Emoji berdasarkan status
                $statEmoji = $status == 'hadir' ? '✅' : '⚠️';
                $statText  = strtoupper($status);

                // 2. Format Pesan WhatsApp
                $message = "*LAPORAN KEHADIRAN SISWA*\n\n" .
                           "Yth. Orang Tua/Wali,\n" .
                           "Putra/Putri Anda:\n\n" .
                           "👤 Nama : *$namaSiswa*\n" .
                           "🏫 Kelas : $kelas\n" .
                           "📚 Mapel : $mapel\n" .
                           "📅 Tanggal : $tgl\n" .
                           "⏰ Pukul : $waktu WIB\n" .
                           "📝 Status : *$statText* $statEmoji\n\n" .
                           "_Pesan ini dikirim otomatis oleh Sistem Presensi Sekolah._";

                // 3. Kirim ke Service Node.js (Port 3000)
                // Gunakan timeout singkat (2 detik) agar proses scan tidak terasa lambat jika bot mati
                Http::timeout(2)->post('http://localhost:3000/send-message', [
                    'number'  => $student->phone, // No HP Ortu
                    'message' => $message,
                ]);

            } catch (\Exception $e) {
                // Jika Bot mati/error, catat di log saja (storage/logs/laravel.log).
                // JANGAN gagalkan proses absensi hanya karena WA gagal.
                \Illuminate\Support\Facades\Log::error("Gagal Kirim WA Auto: " . $e->getMessage());
            }
        }
        // ======================================================

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi Berhasil',
            'student' => $student->name . " (" . strtoupper($status) . ")"
        ]);
    }

    // =============================================================
    // FITUR BARU: ABSENSI MANUAL GURU (SAKIT/IZIN/ALPA)
    // =============================================================

    /**
     * Halaman Form Manual (Menampilkan Daftar Siswa)
     * Route: GET /schedule/{id}/manual
     */
    public function createManual($schedule_id)
    {
        // 1. Cari Jadwal & Validasi Pemilik
        $schedule = Schedule::with(['classroom', 'subject'])
                    ->where('id', $schedule_id)
                    ->where('teacher_id', Auth::id())
                    ->firstOrFail();

        // 2. Ambil semua siswa di kelas tersebut
        $students = Student::where('classroom_id', $schedule->classroom_id)
                    ->orderBy('name')
                    ->get();

        // 3. Ambil data absensi yang SUDAH ada hari ini (untuk mengisi status radio button)
        // Format Array: [student_id => status] contoh: [1 => 'hadir', 2 => 'sakit']
        $existingAttendances = Attendance::where('schedule_id', $schedule_id)
                                ->where('date', date('Y-m-d'))
                                ->pluck('status', 'student_id')
                                ->toArray();

        return view('attendance.manual', compact('schedule', 'students', 'existingAttendances'));
    }

    /**
     * Proses Simpan Absensi Manual (Massal)
     * Route: POST /schedule/{id}/manual
     */
    public function storeManual(Request $request, $schedule_id)
    {
        // Validasi Input array
        $request->validate([
            'attendances' => 'required|array', // Key: student_id, Value: status
        ]);

        $schedule = Schedule::findOrFail($schedule_id);

        // Pastikan hanya pemilik jadwal yang bisa simpan
        if ($schedule->teacher_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $date = date('Y-m-d');
        $now = date('H:i:s');

        // Loop setiap siswa yang diinput
        foreach ($request->attendances as $studentId => $status) {
            // Jika status kosong/tidak dipilih, skip
            if (!$status) continue;

            // Gunakan updateOrCreate agar data lama terupdate, data baru terbuat
            Attendance::updateOrCreate(
                [
                    'student_id'  => $studentId,
                    'schedule_id' => $schedule_id,
                    'date'        => $date
                ],
                [
                    'check_in_time' => $now, // Set waktu saat guru klik simpan
                    'status'        => $status
                ]
            );

            // Catatan: Kita TIDAK mengirim Notifikasi WA di sini untuk menghindari
            // spam massal atau timeout server jika mengabsen 30 siswa sekaligus.
        }

        return redirect()->route('schedule.index')->with('success', 'Data absensi manual berhasil disimpan!');
    }
}
