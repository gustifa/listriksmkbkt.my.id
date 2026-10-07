<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Schedule;
use App\Models\Exam;
use App\Models\InternshipTimeline;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardStudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAktif = $user->status;

        // 1. Cari Data Siswa berdasarkan User
        $student = Student::where('user_id', $user->id)->first();

        if (!$student || $userAktif != 1) {
            return view('siswa.siswa_dashboard.empty');
        }

        $today = Carbon::today()->format('Y-m-d');
        $dayOfWeek = Carbon::now()->locale('id')->dayName;

        // 2. Kehadiran Hari Ini (Absensi Gerbang/Harian)
        $todayAttendance = DailyAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        // 3. Jadwal Pelajaran Hari Ini berdasarkan Kelas Siswa
        $todaySchedules = [];
        if ($student->classroom_id) {
            $todaySchedules = Schedule::with(['subject', 'teacher'])
                ->where('classroom_id', $student->classroom_id)
                ->where('day', $dayOfWeek)
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // 4. Statistik Kehadiran Bulan Ini
        $attendanceStats = [
            'hadir' => DailyAttendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'hadir')->count(),
            'izin'  => DailyAttendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'izin')->count(),
            'sakit' => DailyAttendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'sakit')->count(),
            'alpa'  => DailyAttendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'alpa')->count(),
        ];

        // 5. Ujian/Evaluasi Aktif Hari Ini (Hanya sesuai Kelas Siswa)
        $activeExams = [];
        if ($student->classroom_id) {
            $activeExams = Exam::where('is_active', 1)
                ->whereHas('classrooms', function ($query) use ($student) {
                    $query->where('classroom_id', $student->classroom_id);
                })
                ->get();
        }

        // 6. Timeline PKL / Kegiatan
        $timelines = InternshipTimeline::orderBy('start_date', 'asc')->get();

        return view('siswa.siswa_dashboard.index', compact(
            'student',
            'todayAttendance',
            'todaySchedules',
            'attendanceStats',
            'activeExams',
            'timelines'
        ));
    }
}