<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAktif = $user->status;
        // dd($userAktif);

        // 1. Cari Data Guru
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher || $userAktif != 1 ) {
            return view('guru.teacher_dashboard.empty', compact('teacher'));
        }

        // 2. Ambil Jadwal Mengajar (Mapping)
        $assignments = TeachingAssignment::with(['classroom.students', 'subject'])
                        ->where('teacher_id', $teacher->id)
                        ->orderBy('academic_year', 'desc')
                        ->get();


        // --- STATISTIK RINGKAS ---
        $totalClasses = $assignments->pluck('classroom_id')->unique()->count();
        $totalSubjects = $assignments->pluck('subject_id')->unique()->count();
        
        // Hitung total siswa unik
        $totalStudents = 0;
        $processedClasses = [];
        foreach($assignments as $assign) {
            if (!in_array($assign->classroom_id, $processedClasses)) {
                $totalStudents += $assign->classroom->students->count();
                $processedClasses[] = $assign->classroom_id;
            }
        }

        // --- DATA GRAFIK & KEHADIRAN HARI INI ---
        $scheduleIds = Schedule::where('teacher_id', $teacher->id)->pluck('id');
        $now = Carbon::now();

        // Hitung Kehadiran Hari Ini (Status: Present atau Hadir)
        $todayPresence = Attendance::whereIn('schedule_id', $scheduleIds)
                            ->whereDate('created_at', $now)
                            ->whereIn('status', ['present', 'hadir'])
                            ->count();

        // Helper query grafik (Closure)
        $getAttendanceStats = function($startDate, $endDate) use ($scheduleIds) {
            return Attendance::select('status', DB::raw('count(*) as total'))
                ->whereIn('schedule_id', $scheduleIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        };

        // Data Grafik (Format Data untuk Chart.js)
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
            'totalClasses', 
            'totalSubjects', 
            'totalStudents',
            'todayPresence', 
            'chartData'
        ));
    }

    /**
     * Helper untuk mendapatkan rentang tanggal semester
     */
    private function getSemesterRange($year, $month, $callback) {
        if ($month >= 7) {
            // Semester Ganjil (Juli - Desember)
            $start = Carbon::create($year, 7, 1);
            $end = Carbon::create($year, 12, 31);
        } else {
            // Semester Genap (Januari - Juni)
            $start = Carbon::create($year, 1, 1);
            $end = Carbon::create($year, 6, 30);
        }
        return $callback($start->startOfDay(), $end->endOfDay());
    }

    /**
     * Format data agar sesuai urutan Chart.js dan support dwi-bahasa (Inggris/Indo)
     */
    private function formatChartData($data)
    {
        return [
            ($data['present'] ?? 0) + ($data['hadir'] ?? 0),       // Index 0: Hadir
            ($data['late'] ?? 0) + ($data['terlambat'] ?? 0),      // Index 1: Terlambat
            ($data['permission'] ?? 0) + ($data['izin'] ?? 0),     // Index 2: Izin
            ($data['sick'] ?? 0) + ($data['sakit'] ?? 0),          // Index 3: Sakit
            ($data['alpha'] ?? 0) + ($data['alpa'] ?? 0),          // Index 4: Alpha
        ];
    }
}
