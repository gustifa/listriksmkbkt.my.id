<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Setting;
use App\Models\DailyAttendance; // Gerbang
use App\Models\Attendance;      // Pembelajaran
use App\Models\PrayerAttendance;// Sholat
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Halaman Utama: Menampilkan daftar kelas yang diampu (Walas / BK)
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Ambil kelas dimana dia adalah Walas ATAU Guru BK
        $myClasses = Classroom::where('homeroom_teacher_id', $teacher->id)
                        ->orWhere('counseling_teacher_id', $teacher->id)
                        ->orderBy('name')
                        ->get();

        return view('guru.monitoring.index', compact('myClasses'));
    }

    /**
     * Halaman Detail: Rekap Absensi Satu Kelas
     */
    public function show(Request $request, $classroomId)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        $classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher'])->findOrFail($classroomId);
        
        // Ambil Siswa di kelas ini
        $students = Student::where('classroom_id', $classroomId)->orderBy('name')->get();
        $studentIds = $students->pluck('id');

        // 1. Data Absensi Gerbang (Harian)
        $gateData = DailyAttendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get()
                    ->keyBy('student_id');

        // 2. Data Absensi Pembelajaran (Mapel) - Rekap per siswa
        // Hitung berapa kali Alpa/Sakit/Izin hari ini di berbagai mapel
        $learningData = Attendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get()
                    ->groupBy('student_id');

        // 3. Data Absensi Sholat
        // Ambil sholat apa saja yang sudah dilakukan hari ini
        $prayerData = PrayerAttendance::whereIn('student_id', $studentIds)
                    ->whereDate('date', $date)
                    ->get()
                    ->groupBy('student_id');

        return view('guru.monitoring.show', compact(
            'classroom', 
            'students', 
            'date', 
            'gateData', 
            'learningData', 
            'prayerData'
        ));
    }

    /**
     * Cetak Laporan Monitoring (Gerbang/Mapel/Sholat)
     */
    public function printReport(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'type' => 'required|in:gate,learning,prayer',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
        ]);

        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher'])->findOrFail($request->classroom_id);

        // Validasi Akses: Hanya Walas atau BK kelas tersebut
        if ($classroom->homeroom_teacher_id != $teacher->id && $classroom->counseling_teacher_id != $teacher->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak laporan kelas ini.');
        }

        $students = Student::where('classroom_id', $classroom->id)->orderBy('name')->get();
        $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();

        $data = [];
        $title = "";

        // Logika Pengambilan Data Berdasarkan Tipe
        if ($request->type == 'gate') {
            $title = "Laporan Absensi Kehadiran (Gerbang)";
            // Ambil data DailyAttendance
            $records = DailyAttendance::whereIn('student_id', $students->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            // Format Data
            foreach ($students as $student) {
                $logs = $records->get($student->id, collect());
                $data[] = [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'hadir' => $logs->whereIn('status', ['hadir', 'present'])->count(),
                    'terlambat' => $logs->whereIn('status', ['terlambat', 'late'])->count(),
                    'sakit' => $logs->whereIn('status', ['sakit', 'sick'])->count(),
                    'izin' => $logs->whereIn('status', ['izin', 'permit'])->count(),
                    'alpa' => $logs->whereIn('status', ['alpa', 'alpha'])->count(),
                ];
            }
        } 
        elseif ($request->type == 'learning') {
            $title = "Laporan Absensi Pembelajaran (Mapel)";
            // Ambil data Attendance (Mapel)
            $records = Attendance::whereIn('student_id', $students->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $logs = $records->get($student->id, collect());
                $data[] = [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'hadir' => $logs->whereIn('status', ['hadir', 'present'])->count(),
                    'sakit' => $logs->whereIn('status', ['sakit', 'sick'])->count(),
                    'izin' => $logs->whereIn('status', ['izin', 'permit'])->count(),
                    'alpa' => $logs->whereIn('status', ['alpa', 'alpha'])->count(), // Ini yang penting (Bolos)
                    'total_mapel' => $logs->count()
                ];
            }
        } 
        elseif ($request->type == 'prayer') {
            $title = "Laporan Absensi Sholat";
            $records = PrayerAttendance::whereIn('student_id', $students->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $logs = $records->get($student->id, collect());
                $data[] = [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'hadir' => $logs->where('status', 'hadir')->count(), // Sholat dilakukan
                    'uzur' => $logs->where('status', 'uzur')->count(),
                    'alpha' => $logs->where('status', 'alpa')->count(),
                    'total_sholat' => $logs->count()
                ];
            }
        }

        // Data Sekolah untuk Kop
        $school = [
            'name' => Setting::value('school_name', 'SMK NEGERI 1 BUKITTINGGI'),
            'address' => Setting::value('school_address', 'Jalan Iskandar Teja Sukmana'),
            'phone' => Setting::value('school_phone', ''),
            'email' => Setting::value('school_email', ''),
            'logo_left' => Setting::value('logo_left'),
            'logo_right' => Setting::value('logo_right'),
            'provinsi_name' => Setting::value('provinsi_name', 'SUMATERA BARAT'),
            'sign_city' => Setting::value('sign_city', 'Bukittinggi'),
        ];

        $periodLabel = $startDate->translatedFormat('F Y');

        $pdf = Pdf::loadView('guru.pdf.monitoring_report', compact('data', 'title', 'classroom', 'school', 'periodLabel', 'teacher', 'request'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Monitoring_' . str_replace(' ', '_', $classroom->name) . '.pdf');
    }
}