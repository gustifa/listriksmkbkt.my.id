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
        // 1. Endpoint AJAX untuk mengambil daftar siswa berdasarkan kelas
        if ($request->ajax() && $request->has('get_students')) {
            $classroomId = $request->query('classroom_id');
            $students = Student::where('classroom_id', $classroomId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'nis']);

            return response()->json($students);
        }

        $classrooms = Classroom::orderBy('name', 'asc')->get();
        $date = $request->query('date', Carbon::today()->toDateString());
        $classroomId = $request->query('classroom_id');
        $studentId = $request->query('student_id');

        $students = collect();
        if ($classroomId) {
            $students = Student::where('classroom_id', $classroomId)->orderBy('name', 'asc')->get();
        }

        $gateAttendances = collect();
        $subjectAttendances = collect();
        $prayerAttendances = collect();

        if ($studentId || $classroomId) {
            $studentIds = $studentId ? [$studentId] : $students->pluck('id')->toArray();

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

    // Endpoint DELETE Gate
    // public function deleteGate($id)
    // {
    //     $attendance = DailyAttendance::findOrFail($id);

    //     // Catat ke Log sebelum dihapus
    //     activity('Presensi Gerbang')
    //         ->performedOn($attendance)
    //         ->causedBy(auth()->user())
    //         ->withProperties([
    //             'student_name' => $attendance->student->name ?? '-',
    //             'date' => $attendance->date,
    //             'status' => $attendance->status,
    //         ])
    //     ->log("Membatalkan/menghapus presensi gerbang siswa {$attendance->student->name}");
    //     if ($attendance->photo_in) Storage::disk('public')->delete($attendance->photo_in);
    //     if ($attendance->photo_out) Storage::disk('public')->delete($attendance->photo_out);
    //     $attendance->delete();

    //     return response()->json(['success' => true, 'message' => 'Presensi gerbang berhasil dibatalkan.']);
    // }

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

    // Proses hapus ini akan otomatis memicu Trait LogsActivity di Model secara tunggal
    $attendance->delete();

    return response()->json([
        'success' => true,
        'message' => 'Presensi gerbang berhasil dibatalkan.'
    ]);
}

//     public function deleteGate($id)
// {
//     $attendance = DailyAttendance::with('student')->find($id);

//     if (!$attendance) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Data presensi sudah dihapus atau tidak ditemukan.'
//         ], 404);
//     }

//     $studentName = $attendance->student->name ?? 'Siswa';

//     try {
//         if (function_exists('activity')) {
//             activity('Presensi Gerbang')
//                 ->performedOn($attendance)
//                 ->causedBy(auth()->user())
//                 ->withProperties([
//                     'student_name' => $studentName,
//                     'date'         => $attendance->date,
//                     'status'       => $attendance->status,
//                 ])
//                 ->log("Membatalkan/menghapus presensi gerbang siswa {$studentName}");
//         }
//     } catch (\Throwable $e) {
//         \Log::error('Spatie Activitylog Error: ' . $e->getMessage());
//     }

//     if ($attendance->photo_in) \Storage::disk('public')->delete($attendance->photo_in);
//     if ($attendance->photo_out) \Storage::disk('public')->delete($attendance->photo_out);

//     $attendance->delete();

//     return response()->json([
//         'success' => true,
//         'message' => 'Presensi gerbang berhasil dibatalkan.'
//     ], 200);
// }

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