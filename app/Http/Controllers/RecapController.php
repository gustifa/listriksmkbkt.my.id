<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecapController extends Controller
{
    /**
     * Halaman Dashboard Rekapitulasi (Statistik Utama + Grafik)
     */
    public function index()
    {
        $today = Carbon::today();

        // 1. Statistik Ringkas Hari Ini
        $totalStudents = Student::count();
        
        $dailyStats = [
            'hadir' => DailyAttendance::whereDate('date', $today)->where('status', 'hadir')->count(),
            'terlambat' => DailyAttendance::whereDate('date', $today)->where('status', 'terlambat')->count(),
            'alpa' => DailyAttendance::whereDate('date', $today)->where('status', 'alpa')->count(),
            'pulang' => DailyAttendance::whereDate('date', $today)->whereNotNull('departure_time')->count(),
        ];

        $attendanceRate = $totalStudents > 0 ? round(($dailyStats['hadir'] + $dailyStats['terlambat']) / $totalStudents * 100, 1) : 0;

        // 2. Data Grafik (7 Hari Terakhir)
        $chartLabels = [];
        $dataHadir = [];
        $dataTerlambat = [];
        $dataAlpa = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            
            // Label Hari (Senin, 12 Des)
            $chartLabels[] = $date->translatedFormat('D, d M');

            // Hitung data per tanggal tersebut
            $dataHadir[] = DailyAttendance::whereDate('date', $formattedDate)->where('status', 'hadir')->count();
            $dataTerlambat[] = DailyAttendance::whereDate('date', $formattedDate)->where('status', 'terlambat')->count();
            $dataAlpa[] = DailyAttendance::whereDate('date', $formattedDate)->where('status', 'alpa')->count();
        }

        return view('recap.index', compact(
            'totalStudents', 
            'dailyStats', 
            'attendanceRate',
            'chartLabels',
            'dataHadir',
            'dataTerlambat',
            'dataAlpa'
        ));
    }

    /**
     * Halaman Data Lengkap Absensi Harian (Gerbang)
     */
    public function dailyLog(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();

        $query = DailyAttendance::with(['student.classroom']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } else {
            $query->whereDate('date', Carbon::today());
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('recap.daily_list', compact('logs', 'classrooms'));
    }

    /**
     * Halaman Data Lengkap Absensi Pembelajaran (Mapel)
     */
    public function learningLog(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $query = Attendance::with(['student.classroom', 'subject']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('recap.learning_list', compact('logs', 'classrooms', 'subjects'));
    }

    /**
     * Daftar Semua Siswa untuk Dipilih Detailnya
     */
    public function studentsList(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        
        $query = Student::with(['classroom']);

        // Filter Kelas
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Pencarian Nama/NIS
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('recap.student_list', compact('students', 'classrooms'));
    }

    /**
     * Detail Lengkap Satu Siswa (Gerbang + Mapel)
     */
    public function studentDetail($id)
    {
        $student = Student::with('classroom')->findOrFail($id);

        // 1. Data Absensi Harian (Gerbang)
        $dailyLogs = DailyAttendance::where('student_id', $id)
            ->orderBy('date', 'desc')
            ->paginate(10, ['*'], 'daily_page');

        // 2. Data Absensi Pembelajaran (Mapel)
        $learningLogs = Attendance::with('subject')
            ->where('student_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'learning_page');

        // 3. Statistik Global Siswa Ini
        $stats = [
            'hadir' => DailyAttendance::where('student_id', $id)->where('status', 'hadir')->count(),
            'terlambat' => DailyAttendance::where('student_id', $id)->where('status', 'terlambat')->count(),
            'alpa' => DailyAttendance::where('student_id', $id)->where('status', 'alpa')->count(),
            'mapel_hadir' => Attendance::where('student_id', $id)->where('status', 'present')->count(),
        ];

        return view('recap.student_detail', compact('student', 'dailyLogs', 'learningLogs', 'stats'));
    }
}