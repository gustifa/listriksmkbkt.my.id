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
use Carbon\Carbon;

class AttendanceManageController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        $date = $request->query('date', Carbon::today()->toDateString());
        $classroomId = $request->query('classroom_id');
        $studentId = $request->query('student_id');

        $students = collect();
        if ($classroomId) {
            $students = Student::where('classroom_id', $classroomId)->orderBy('name', 'asc')->get();
        }

        // Query data absensi jika filter diterapkan
        $gateAttendances = collect();
        $subjectAttendances = collect();
        $prayerAttendances = collect();

        if ($studentId || $classroomId) {
            $studentIds = $studentId 
                ? [$studentId] 
                : $students->pluck('id')->toArray();

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

        return view('admin.attendance.manage', compact(
            'classrooms', 'students', 'date', 'classroomId', 'studentId',
            'gateAttendances', 'subjectAttendances', 'prayerAttendances'
        ));
    }

    // Endpoint Hapus Gerbang
    public function deleteGate($id)
    {
        $attendance = DailyAttendance::findOrFail($id);
        if ($attendance->photo_in) Storage::disk('public')->delete($attendance->photo_in);
        if ($attendance->photo_out) Storage::disk('public')->delete($attendance->photo_out);
        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Presensi gerbang berhasil dibatalkan.']);
    }

    // Endpoint Hapus Mapel
    public function deleteSubject($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Presensi mata pelajaran berhasil dibatalkan.']);
    }

    // Endpoint Hapus Sholat
    public function deletePrayer($id)
    {
        $prayer = PrayerAttendance::findOrFail($id);
        if ($prayer->photo_evidence) Storage::disk('public')->delete($prayer->photo_evidence);
        $prayer->delete();

        return response()->json(['success' => true, 'message' => 'Presensi sholat berhasil dibatalkan.']);
    }
}