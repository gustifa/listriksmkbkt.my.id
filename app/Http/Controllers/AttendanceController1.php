<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        return response()->json([
            'status' => 'success', 
            'message' => 'Absensi Berhasil', 
            'student' => $student->name . " (" . strtoupper($status) . ")"
        ]);
    }
}