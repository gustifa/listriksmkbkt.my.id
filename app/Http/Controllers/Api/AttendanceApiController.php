<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    // 1. Ambil Data Siswa
    public function getStudents()
    {
        // Ambil tanggal hari ini (Y-m-d)
        $today = Carbon::today()->toDateString();

        $students = Student::all()->map(function($student) use ($today) {
            // Cek absensi berdasarkan student_id dan kolom 'date'
            $attendance = DailyAttendance::where('student_id', $student->id)
                                         ->where('date', $today)
                                         ->first();

            return [
                'id' => (string) $student->id,
                'name' => $student->name,
                'nisn' => $student->nis, 
                'role' => 'siswa',
                // Logika status: Jika ada data berarti hadir/terlambat, jika tidak pending
                'status' => $attendance ? $attendance->status : 'pending',
                // Mapping arrival_time dari database
                'time_in' => $attendance ? Carbon::parse($attendance->arrival_time)->format('H:i') : null,
                // Catatan: Kolom 'method' tidak ada di tabel daily_attendances Anda. 
                // Jika ingin menampilkan, harus ditambahkan ke migrasi dulu.
                'method' => null, 
            ];
        });

        return response()->json($students);
    }

    // 2. Ambil Data Guru/Staff (Tidak ada perubahan signifikan, hanya formatting)
    public function getStaff()
    {
        $staff = Teacher::all()->map(function($t) {
            return [
                'id' => (string) $t->id,
                'name' => $t->name,
                'nip' => $t->nip,
                'role' => $t->role_type,
            ];
        });

        return response()->json($staff);
    }

    // 3. Simpan Absensi
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'method' => 'required', // Validasi tetap ada untuk data dari Frontend
            // 'time_in' tidak wajib validasi jika kita menggunakan jam server (Carbon::now)
        ]);

        $student = Student::find($request->student_id);
        
        if (!$student) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }

        $today = Carbon::today()->toDateString();

        // Cek duplikasi menggunakan kolom 'date'
        $exists = DailyAttendance::where('student_id', $student->id)
                                 ->where('date', $today)
                                 ->exists();

        if ($exists) {
            return response()->json(['message' => 'Sudah absen hari ini'], 200);
        }

        // Tentukan status (Contoh sederhana: Lewat jam 07:30 dianggap terlambat)
        $jamSekarang = Carbon::now();
        $jamMasuk = Carbon::createFromTime(7, 30, 0); // Atur jam masuk sesuai aturan sekolah
        $statusKehadiran = $jamSekarang->greaterThan($jamMasuk) ? 'terlambat' : 'hadir';

        // Simpan ke MySQL sesuai kolom Schema
        DailyAttendance::create([
            // ID biasanya otomatis jika Model menggunakan HasUuids (Laravel 10+), 
            // jika tidak, tambahkan 'id' => \Illuminate\Support\Str::uuid()
            'student_id' => $student->id,
            'date' => $today, // Mengisi kolom date
            'arrival_time' => $jamSekarang->toTimeString(), // Mengisi kolom arrival_time
            'status' => $statusKehadiran, // Mengisi enum status
            // 'method' => $request->method, // DIHAPUS karena tidak ada kolom method di tabel migrasi
        ]);

        return response()->json([
            'message' => 'Berhasil absen',
            'status' => $statusKehadiran,
            'time' => $jamSekarang->format('H:i')
        ], 201);
    }
}