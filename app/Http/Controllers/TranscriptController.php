<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\DailyAttendance; // Model untuk Absensi Datang/Pulang
use App\Models\Attendance;      // Model untuk Absensi Pembelajaran/Mapel
use App\Models\Classroom;      // Model untuk Absensi Pembelajaran/Mapel
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TranscriptController extends Controller
{
    /**
     * Halaman pencarian siswa untuk dicetak transkripnya.
     */
    public function index()
    {
        // Ambil daftar kelas urut nama
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('report.transcript_index', compact('students', 'classrooms'));
    }

    public function show(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'month'      => 'nullable|numeric',
            'year'       => 'nullable|numeric',
        ]);

        $student = Student::with('classroom')->findOrFail($request->student_id);

        // Filter Waktu (Default: Bulan ini)
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;
        $dateObj = Carbon::create($year, $month, 1);

        // Panggil fungsi private untuk ambil data (supaya codingan tidak berulang)
        $data = $this->getStudentAttendanceData($student, $month, $year);

        return view('report.transcript_print', array_merge([
            'student' => $student,
            'dateObj' => $dateObj
        ], $data));
    }

    /**
     * FITUR BARU: Cetak Transkrip Satu Kelas Sekaligus
     */
    public function printByClass(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'month'        => 'nullable|numeric',
            'year'         => 'nullable|numeric',
        ]);

        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;
        $dateObj = Carbon::create($year, $month, 1);

        // 1. Ambil Kelas dan Siswanya
        $classroom = Classroom::with(['students' => function($q) {
            $q->orderBy('name');
        }])->findOrFail($request->classroom_id);

        $transcripts = [];

        // 2. Loop setiap siswa untuk ambil data absensinya
        foreach ($classroom->students as $student) {
            // Gabungkan data siswa dengan data absensinya
            $attendanceData = $this->getStudentAttendanceData($student, $month, $year);
            
            $transcripts[] = [
                'student' => $student,
                'dailyLogs' => $attendanceData['dailyLogs'],
                'dailySummary' => $attendanceData['dailySummary'],
                'lessonSummary' => $attendanceData['lessonSummary'],
            ];
        }

        return view('report.transcript_class_print', compact('classroom', 'transcripts', 'dateObj'));
    }

    /**
     * PRIVATE HELPER: Logika Utama Pengambilan Data
     * Digunakan oleh function show() dan printByClass()
     */
    // private function getStudentAttendanceData($student, $month, $year)
    // {
    //     // --- 1. DATA ABSENSI HARIAN (Datang & Pulang) ---
    //     $dailyLogs = DailyAttendance::where('student_id', $student->id)
    //         ->whereMonth('created_at', $month)
    //         ->whereYear('created_at', $year)
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Statistik Harian
    //     $dailySummary = [
    //         'hadir'     => $dailyLogs->where('status', 'hadir')->count(),
    //         'terlambat' => $dailyLogs->where('status', 'terlambat')->count(),
    //         'izin'      => $dailyLogs->where('status', 'izin')->count(),
    //         'sakit'     => $dailyLogs->where('status', 'sakit')->count(),
    //         'alpa'      => $dailyLogs->where('status', 'alpa')->count(),
    //     ];

    //     // --- 2. DATA ABSENSI PEMBELAJARAN (Per Mapel) ---
    //     // PERBAIKAN DI SINI: Join ke schedules dulu, baru ke subjects
    //     $lessonSummary = Attendance::select(
    //             'subjects.name as subject_name',
    //             DB::raw('count(*) as total_meetings'),
    //             DB::raw('sum(case when attendances.status = hadir" then 1 else 0 end) as total_present'),
    //             DB::raw('sum(case when attendances.status = "terlambat" then 1 else 0 end) as total_late'),
    //             DB::raw('sum(case when attendances.status = "sakit" then 1 else 0 end) as total_sick'),
    //             DB::raw('sum(case when attendances.status = "izin" then 1 else 0 end) as total_permission'),
    //             DB::raw('sum(case when attendances.status = "alpa" then 1 else 0 end) as total_alpha')
    //         )
    //         // Join 1: Attendance ke Schedule (untuk dapat subject_id)
    //         ->join('schedules', 'attendances.schedule_id', '=', 'schedules.id') 
    //         // Join 2: Schedule ke Subject (untuk dapat nama mapel)
    //         ->join('subjects', 'schedules.subject_id', '=', 'subjects.id')      
            
    //         ->where('attendances.student_id', $student->id)
    //         ->whereMonth('attendances.created_at', $month)
    //         ->whereYear('attendances.created_at', $year)
    //         ->groupBy('subjects.name') // Group berdasarkan nama mapel
    //         ->get();

    //     return [
    //         'dailyLogs'     => $dailyLogs,
    //         'dailySummary'  => $dailySummary,
    //         'lessonSummary' => $lessonSummary,
    //     ];
    // }

    private function getStudentAttendanceData($student, $month, $year)
    {
        // --- 1. DATA ABSENSI HARIAN (Datang & Pulang) ---
        $dailyLogs = DailyAttendance::where('student_id', $student->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'asc')
            ->get();

        // Statistik Harian
        $dailySummary = [
            'hadir'     => $dailyLogs->where('status', 'hadir')->count(),
            'terlambat' => $dailyLogs->where('status', 'terlambat')->count(),
            'izin'      => $dailyLogs->where('status', 'izin')->count(),
            'sakit'     => $dailyLogs->where('status', 'sakit')->count(),
            'alpa'      => $dailyLogs->where('status', 'alpa')->count(),
        ];

        // --- 2. DATA ABSENSI PEMBELAJARAN (Per Mapel) ---
        // PERBAIKAN: Menggunakan tanda kutip satu (') untuk nilai string SQL
        $lessonSummary = Attendance::select(
                'subjects.name as subject_name',
                DB::raw('count(*) as total_meetings'),
                // Perhatikan penggunaan tanda kutip di bawah ini:
                DB::raw("sum(case when attendances.status = 'hadir' then 1 else 0 end) as total_present"),
                DB::raw("sum(case when attendances.status = 'terlambat' then 1 else 0 end) as total_late"),
                DB::raw("sum(case when attendances.status = 'sakit' then 1 else 0 end) as total_sick"),
                DB::raw("sum(case when attendances.status = 'izin' then 1 else 0 end) as total_permission"),
                DB::raw("sum(case when attendances.status = 'alpa' then 1 else 0 end) as total_alpha")
            )
            // Join 1: Attendance ke Schedule
            ->join('schedules', 'attendances.schedule_id', '=', 'schedules.id') 
            // Join 2: Schedule ke Subject
            ->join('subjects', 'schedules.subject_id', '=', 'subjects.id')      
            
            ->where('attendances.student_id', $student->id)
            ->whereMonth('attendances.created_at', $month)
            ->whereYear('attendances.created_at', $year)
            ->groupBy('subjects.name')
            ->get();

        return [
            'dailyLogs'     => $dailyLogs,
            'dailySummary'  => $dailySummary,
            'lessonSummary' => $lessonSummary,
        ];
    }
}
