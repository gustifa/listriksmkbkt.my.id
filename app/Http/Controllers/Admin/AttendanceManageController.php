<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceManageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil daftar ID kelas yang berhak diakses oleh user/guru via scopeAccessibleBy
        $accessibleClassroomIds = Classroom::accessibleBy($user)->pluck('id');

        // 1. Endpoint AJAX untuk mengambil daftar siswa berdasarkan kelas (dibatasi scope)
        if ($request->ajax() && $request->has('get_students')) {
            $classroomId = $request->query('classroom_id');

            // Keamanan: Pastikan classroom_id terdaftar di kelas yang boleh diakses
            if ($accessibleClassroomIds->contains($classroomId)) {
                $students = Student::where('classroom_id', $classroomId)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name', 'nis']);
            } else {
                $students = collect();
            }

            return response()->json($students);
        }

        // 2. Tampilkan dropdown kelas yang HANYA boleh diakses oleh user yang sedang login
        $classrooms = Classroom::accessibleBy($user)->orderBy('name', 'asc')->get();

        $date = $request->query('date', Carbon::today()->toDateString());
        $classroomId = $request->query('classroom_id');
        $studentId = $request->query('student_id');

        $students = collect();
        if ($classroomId) {
            // Validasi hak akses kelas
            if ($accessibleClassroomIds->contains($classroomId)) {
                $students = Student::where('classroom_id', $classroomId)->orderBy('name', 'asc')->get();
            }
        }

        $gateAttendances = collect();
        $subjectAttendances = collect();
        $prayerAttendances = collect();

        if ($studentId || $classroomId) {
            // Jika memilih siswa spesifik, pastikan siswa tersebut berada di kelas otoritas user
            if ($studentId) {
                $validStudent = Student::whereIn('classroom_id', $accessibleClassroomIds)->find($studentId);
                $studentIds = $validStudent ? [$validStudent->id] : [];
            } else {
                $studentIds = $students->pluck('id')->toArray();
            }

            if (!empty($studentIds)) {
                $gateAttendances = DailyAttendance::with('student')
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get();

                $subjectAttendances = Attendance::with(['student', 'subject'])
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get();

                $prayerAttendances = PrayerAttendance::with('student')
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get();
            }
        }

        return view('admin.attendance.manage', compact(
            'classrooms', 'students', 'date', 'classroomId', 'studentId',
            'gateAttendances', 'subjectAttendances', 'prayerAttendances'
        ));
    }

    // Endpoint DELETE Gate
    public function deleteGate($id)
    {
        $attendance = DailyAttendance::with('student')->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Data presensi tidak ditemukan atau sudah dihapus.'
            ], 404);
        }

        // Hapus bukti foto dari storage jika ada
        if ($attendance->photo_in) Storage::disk('public')->delete($attendance->photo_in);
        if ($attendance->photo_out) Storage::disk('public')->delete($attendance->photo_out);

        // Hapus presensi
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Presensi gerbang berhasil dibatalkan.'
        ]);
    }

    // Endpoint DELETE Subject
    public function deleteSubject($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Presensi mata pelajaran berhasil dibatalkan.']);
    }

    // Endpoint DELETE Prayer
    public function deletePrayer($id)
    {
        $prayer = PrayerAttendance::findOrFail($id);
        if ($prayer->photo_evidence) Storage::disk('public')->delete($prayer->photo_evidence);
        $prayer->delete();

        return response()->json(['success' => true, 'message' => 'Presensi sholat berhasil dibatalkan.']);
    }
}