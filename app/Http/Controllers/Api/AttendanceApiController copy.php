<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\DailyAttendance; // Asumsi model absensi harian
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    // 1. Ambil Data Siswa
    public function getStudents()
    {
        // Mengambil siswa beserta status absensi hari ini
        $students = Student::all()->map(function($student) {
            // Cek apakah hari ini sudah absen
            // Pastikan tabel daily_attendances ada
            $attendance = DailyAttendance::where('student_id', $student->id)
                            ->whereDate('created_at', Carbon::today())
                            ->first();

            return [
                'id' => (string) $student->id, // Cast ke string agar sama dengan tipe di React
                'name' => $student->name,
                'nisn' => $student->nis, // atau $student->nomor_induk
                'role' => 'siswa',
                'status' => $attendance ? 'hadir' : 'pending',
                'time_in' => $attendance ? Carbon::parse($attendance->time_in)->format('H:i') : null,
                'method' => $attendance ? $attendance->method : null, // qr_scan / face_scan
            ];
        });

        return response()->json($students);
    }

    // 2. Ambil Data Guru/Staff
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

    // 3. Simpan Absensi (Dari React ke MySQL)
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'method' => 'required', // qr_scan / face_scan
            'time_in' => 'required'
        ]);

        $student = Student::find($request->student_id);
        
        if (!$student) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }

        // Cek duplikasi hari ini
        $exists = DailyAttendance::where('student_id', $student->id)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

        if ($exists) {
            return response()->json(['message' => 'Sudah absen hari ini'], 200);
        }

        // Simpan ke MySQL
        DailyAttendance::create([
            'student_id' => $student->id,
            'status' => 'hadir',
            'time_in' => Carbon::now(), // atau $request->time_in
            'method' => $request->method,
            // Field lain sesuaikan dengan tabel Anda
        ]);

        return response()->json(['message' => 'Berhasil'], 201);
    }
}
