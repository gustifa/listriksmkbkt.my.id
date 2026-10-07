<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Internship;
use App\Models\TeachingJournal;
use Carbon\Carbon;

class DashboardGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAktif = $user->status ?? 1;

        // 1. Ambil Data Guru
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher || $userAktif != 1) {
            return view('guru.teacher_dashboard.empty', compact('teacher'));
        }

        // 2. Ambil Mapping Pengampuan (Teaching Assignments)
        $assignments = TeachingAssignment::with(['classroom.students', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->orderBy('academic_year', 'desc')
                        ->get();

        // 3. Ambil Jadwal Mengajar Hari Ini
        $dayOfWeek = Carbon::now()->locale('id')->dayName;
        $todaySchedules = Schedule::with(['classroom', 'subject'])
                            ->where('teacher_id', $teacher->id)
                            ->where('day', $dayOfWeek)
                            ->orderBy('start_time', 'asc')
                            ->get();

        // 4. Data Wali Kelas (Pengecekan Aman Struktur Kolom Database)
        $homeroomClass = null;
        if (Schema::hasColumn('classrooms', 'teacher_id')) {
            $homeroomClass = Classroom::where('teacher_id', $teacher->id)->withCount('students')->first();
        } elseif (Schema::hasColumn('classrooms', 'homeroom_teacher_id')) {
            $homeroomClass = Classroom::where('homeroom_teacher_id', $teacher->id)->withCount('students')->first();
        }

        // 5. Data Siswa Bimbingan PKL
        $internshipStudentsCount = 0;
        if (Schema::hasTable('internships') && Schema::hasColumn('internships', 'advisor_id')) {
            $internshipStudentsCount = Internship::where('advisor_id', $teacher->id)->count();
        }

        // 6. Jurnal Mengajar Terbaru Guru
        $recentJournals = collect();
        if (Schema::hasTable('teaching_journals')) {
            $recentJournals = TeachingJournal::with(['schedule.classroom', 'schedule.subject'])
                                ->whereHas('schedule', function ($q) use ($teacher) {
                                    $q->where('teacher_id', $teacher->id);
                                })
                                ->latest()
                                ->take(5)
                                ->get();
        }

        // --- STATISTIK RINGKAS ---
        $totalClasses = $assignments->pluck('classroom_id')->unique()->count();
        $totalSubjects = $assignments->pluck('subject_id')->unique()->count();
        
        // Hitung Total Siswa Unik yang Diajar
        $totalStudents = 0;
        $processedClasses = [];
        foreach ($assignments as $assign) {
            if ($assign->classroom && !in_array($assign->classroom_id, $processedClasses)) {
                $totalStudents += $assign->classroom->students->count();
                $processedClasses[] = $assign->classroom_id;
            }
        }

        // --- DATA GRAFIK KEHADIRAN SISWA ---
        $scheduleIds = Schedule::where('teacher_id', $teacher->id)->pluck('id');
        $now = Carbon::now();

        // Kehadiran Hari Ini
        $todayPresence = Attendance::whereIn('schedule_id', $scheduleIds)
                            ->whereDate('created_at', $now->toDateString())
                            ->whereIn('status', ['present', 'hadir'])
                            ->count();

        // Helper query statistik
        $getAttendanceStats = function ($startDate, $endDate) use ($scheduleIds) {
            return Attendance::select('status', DB::raw('count(*) as total'))
                ->whereIn('schedule_id', $scheduleIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        };

        $chartData = [
            'harian'   => $this->formatChartData($getAttendanceStats($now->copy()->startOfDay(), $now->copy()->endOfDay())),
            'mingguan' => $this->formatChartData($getAttendanceStats($now->copy()->startOfWeek(), $now->copy()->endOfWeek())),
            'bulanan'  => $this->formatChartData($getAttendanceStats($now->copy()->startOfMonth(), $now->copy()->endOfMonth())),
            'semester' => $this->formatChartData($this->getSemesterRange($now->year, $now->month, $getAttendanceStats)), 
            'tahunan'  => $this->formatChartData($getAttendanceStats($now->copy()->startOfYear(), $now->copy()->endOfYear())),
        ];

        return view('guru.teacher_dashboard.index', compact(
            'teacher', 
            'assignments', 
            'todaySchedules',
            'homeroomClass',
            'internshipStudentsCount',
            'recentJournals',
            'totalClasses', 
            'totalSubjects', 
            'totalStudents',
            'todayPresence', 
            'chartData'
        ));
    }

    private function getSemesterRange($year, $month, $callback) 
    {
        if ($month >= 7) {
            $start = Carbon::create($year, 7, 1);
            $end = Carbon::create($year, 12, 31);
        } else {
            $start = Carbon::create($year, 1, 1);
            $end = Carbon::create($year, 6, 30);
        }
        return $callback($start->startOfDay(), $end->endOfDay());
    }

    private function formatChartData($data)
    {
        return [
            ($data['present'] ?? 0) + ($data['hadir'] ?? 0),
            ($data['late'] ?? 0) + ($data['terlambat'] ?? 0),
            ($data['permission'] ?? 0) + ($data['izin'] ?? 0),
            ($data['sick'] ?? 0) + ($data['sakit'] ?? 0),
            ($data['alpha'] ?? 0) + ($data['alpa'] ?? 0),
        ];
    }
}