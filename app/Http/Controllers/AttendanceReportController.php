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
        
        // Mengambil nama hari dalam bahasa Indonesia (misal: 'Rabu')
        $dayName = Carbon::now()->locale('id')->isoFormat('dddd');

        // 1. SISWA YANG BELUM ABSEN GERBANG (HARIAN)
        // A. Belum Absen Masuk Sama Sekali (Tidak Ada Record Hari Ini)
        $noArrivalStudents = Student::where('status', 1)
            ->whereDoesntHave('dailyAttendances', function ($query) use ($today) {
                $query->whereDate('date', $today);
            })
            ->with('classroom')
            ->get();

        // B. Sudah Absen Masuk Tapi Belum Absen Pulang (arrival_time terisi, departure_time NULL)
        $noDepartureStudents = Student::where('status', 1)
            ->whereHas('dailyAttendances', function ($query) use ($today) {
                $query->whereDate('date', $today)
                      ->whereNotNull('arrival_time')
                      ->whereNull('departure_time');
            })
            ->with('classroom')
            ->get();

        // 2. SISWA YANG BELUM PRESENSI MAPEL (MATA PELAJARAN)
        // Cari jadwal hari ini yang jam mulainya SUDAH lewat atau sedang berlangsung
        $schedulesToday = Schedule::whereRaw('LOWER(day) = ?', [strtolower($dayName)])
            ->where('start_time', '<=', $currentTime)
            ->with(['classroom', 'subject', 'teacher'])
            ->get();

        $missingSubjectStudents = [];

        foreach ($schedulesToday as $schedule) {
            // Filter siswa di kelas terkait yang belum mengisi check_in_time pada mapel ini
            $studentsInClass = Student::where('classroom_id', $schedule->classroom_id)
                ->where('status', 1)
                ->whereDoesntHave('attendances', function ($query) use ($today, $schedule) {
                    $query->whereDate('date', $today)
                          ->where('schedule_id', $schedule->id)
                          ->whereNotNull('check_in_time');
                })
                ->get();

            if ($studentsInClass->isNotEmpty()) {
                $missingSubjectStudents[] = [
                    'schedule' => $schedule,
                    'students' => $studentsInClass
                ];
            }
        }

        return view('attendance.missing_report', compact(
            'noArrivalStudents',
            'noDepartureStudents',
            'missingSubjectStudents',
            'today'
        ));
    }
}