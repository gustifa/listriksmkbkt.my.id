<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Schedule;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function checkMissingAttendance(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->format('H:i:s');
        $dayOfWeek = Carbon::now()->locale('id')->dayName;

        // 1. SISWA YANG TIDAK ABSEN GERBANG HARI INI
        $absentGateStudents = Student::where('status', 1)
            ->whereDoesntHave('dailyAttendances', function ($query) use ($today) {
                $query->whereDate('date', $today);
            })
            ->with('classroom')
            ->get();

        // 2. SISWA YANG TIDAK ABSEN MAPEL (Sesuai Jadwal Hari Ini & Jam Terlewat/Aktif)
        $schedulesToday = Schedule::where('day', $dayOfWeek)
            ->where('start_time', '<=', $currentTime) // Jam pelajaran sudah dimulai/selesai
            ->with(['classroom', 'subject', 'teacher'])
            ->get();

        $missingSubjectStudents = [];

        foreach ($schedulesToday as $schedule) {
            // Cari siswa di kelas tersebut yang belum absen mapel ini
            $studentsInClass = Student::where('classroom_id', $schedule->classroom_id)
                ->where('status', 1)
                ->whereDoesntHave('attendances', function ($query) use ($today, $schedule) {
                    $query->whereDate('date', $today)
                        ->where('schedule_id', $schedule->id);
                })
                ->get();

            if ($studentsInClass->isNotEmpty()) {
                $missingSubjectStudents[] = [
                    'schedule' => $schedule,
                    'students' => $studentsInClass
                ];
            }
        }

        return view('attendance.missing_report', compact('absentGateStudents', 'missingSubjectStudents', 'today'));
    }
}