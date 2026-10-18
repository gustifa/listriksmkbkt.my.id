<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use Carbon\Carbon;
use App\Models\Setting;

class TranskripAbsensiController extends Controller
{
    /**
     * Halaman Utama Filter Cetak Transkrip
     */
    public function index(Request $request)
{
    $user = auth()->user();
    
    // Deteksi Role Pengguna
    $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';
    $isBK        = $user->hasRole('bk') || $user->role === 'bk';

    $classrooms = collect();
    $students   = collect();

    if ($isWaliKelas) {
        // 1. Cari ID Guru dari user yang login
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : $user->id;

        // 2. Ambil HANYA kelas yang diampu oleh Wali Kelas ini
        $classrooms = Classroom::where('homeroom_teacher_id', $teacherId)
            ->orWhere('homeroom_teacher_id', $user->id)
            ->get();
            
        $classroomIds = $classrooms->pluck('id');

        // 3. Ambil HANYA siswa yang berada di kelas binaan tersebut
        $students = Student::whereIn('classroom_id', $classroomIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

    } elseif ($isBK) {
        // Guru BK: Ambil kelas bimbingannya
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : $user->id;

        $classrooms = Classroom::where('counseling_teacher_id', $teacherId)
            ->orWhere('counseling_teacher_id', $user->id)
            ->get();

        // Jika tidak diset khusus per kelas, berikan seluruh kelas untuk BK
        if ($classrooms->isEmpty()) {
            $classrooms = Classroom::orderBy('name')->get();
        }

        $students = Student::where('status', 'active')->orderBy('name')->get();

    } else {
        // Admin: Ambil seluruh kelas dan siswa
        $classrooms = Classroom::orderBy('name')->get();
        $students   = Student::where('status', 'active')->orderBy('name')->get();
    }

    // Ambil data absensi KBM beserta relasi mapel dan guru
    $learning = $includeLearning 
        ? Attendance::with(['schedule.subject', 'schedule.teacher', 'teacher'])
            ->where('student_id', $st->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get() 
        : collect();

    return view('report.transkrip_index', compact('students', 'classrooms', 'isWaliKelas', 'isBK', 'learning'));
}

    /**
     * Halaman Cetak Transkrip Absensi A4
     */
    public function print(Request $request)
    {
        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';
        $isBK        = $user->hasRole('bk') || $user->role === 'bk';

        $mode        = $request->input('mode', 'student'); 
        $studentId   = $request->input('student_id');
        $classroomId = $request->input('classroom_id');
        $startDate   = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate     = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Dynamic Checkbox Components
        $includeGate     = $request->has('include_gate');
        $includeLearning = $request->has('include_learning');
        $includePrayer   = $request->has('include_prayer');

        $studentsQuery = Student::with(['classroom.homeroomTeacher', 'classroom.counselingTeacher'])
            ->where('status', 'active');

        // Filter Hak Akses & Mode Cetak
        if ($isWaliKelas) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $teacherId = $teacher ? $teacher->id : $user->id;

            $waliClassroomIds = Classroom::where('homeroom_teacher_id', $teacherId)
                ->orWhere('homeroom_teacher_id', $user->id)
                ->pluck('id');

            $studentsQuery->whereIn('classroom_id', $waliClassroomIds);

            if ($mode === 'student' && $studentId) {
                $studentsQuery->where('id', $studentId);
            }
        } else {
            if ($mode === 'student' && $studentId) {
                $studentsQuery->where('id', $studentId);
            } elseif ($mode === 'classroom' && $classroomId) {
                $studentsQuery->where('classroom_id', $classroomId);
            }
        }

        $students = $studentsQuery->orderBy('classroom_id')->orderBy('name')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada data siswa yang ditemukan untuk dicetak.');
        }

        $settings = $this->getSchoolData();

        $reports = [];
        foreach ($students as $st) {
            $gate = $includeGate 
                ? DailyAttendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
                : collect();

            $learning = $includeLearning 
                ? Attendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
                : collect();

            $prayer = $includePrayer 
                ? PrayerAttendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
                : collect();

            $classroom = $st->classroom;

            // Resolusi Fallback Wali Kelas
            $homeroomTeacher = $classroom ? $classroom->homeroomTeacher : null;
            if (!$homeroomTeacher && $classroom && $classroom->homeroom_teacher_id) {
                $homeroomTeacher = Teacher::where('id', $classroom->homeroom_teacher_id)
                    ->orWhere('user_id', $classroom->homeroom_teacher_id)
                    ->first();
            }

            // Resolusi Fallback Guru BK
            $counselingTeacher = $classroom ? $classroom->counselingTeacher : null;
            if (!$counselingTeacher && $classroom && $classroom->counseling_teacher_id) {
                $counselingTeacher = Teacher::where('id', $classroom->counseling_teacher_id)
                    ->orWhere('user_id', $classroom->counseling_teacher_id)
                    ->first();
            }

            $reports[] = [
                'student'           => $st,
                'gate'              => $gate,
                'learning'          => $learning,
                'prayer'            => $prayer,
                'homeroomTeacher'   => $homeroomTeacher,
                'counselingTeacher' => $counselingTeacher,
            ];
        }

        return view('report.transkrip_print', compact(
            'reports', 'startDate', 'endDate', 'settings', 
            'includeGate', 'includeLearning', 'includePrayer'
        ));
    }

    /**
     * Konfigurasi Data Sekolah & TTD
     */
    private function getSchoolData()
{
    // Ambil path file logo dari tabel settings database
    $logoLeftPath  = Setting::where('key', 'logo_left')->value('value');  // Contoh: 'settings/logo_provinsi.png'
    $logoRightPath = Setting::where('key', 'logo_right')->value('value'); // Contoh: 'settings/logo_smk.png'

    return [
        'school_name'      => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
        'provinsi_name'    => Setting::where('key', 'provinsi_name')->value('value') ?? 'SUMATERA BARAT',
        'school_address'   => Setting::where('key', 'school_address')->value('value') ?? 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kota Bukittinggi',
        'school_email'     => Setting::where('key', 'school_email')->value('value') ?? 'info@smkn1bukittinggi.sch.id',
        'school_web'       => Setting::where('key', 'school_web')->value('value') ?? 'www.smkn1bukittinggi.sch.id',
        'sign_title'       => 'Kepala Sekolah',
        'sign_name'        => Setting::where('key', 'headmaster_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
        'sign_nip'         => Setting::where('key', 'headmaster_nip')->value('value') ?? '19740731 200801 1 002',
        'sign_city'        => 'Bukittinggi',
        'paper_size'       => 'a4',
        'paper_orientation'=> 'portrait',
        
        // Pembacaan URL file logo yang tersimpan di storage
        'logo_left'  => ($logoLeftPath && \Storage::disk('public')->exists($logoLeftPath)) ? asset('storage/' . $logoLeftPath) : null,
        'logo_right' => ($logoRightPath && \Storage::disk('public')->exists($logoRightPath)) ? asset('storage/' . $logoRightPath) : null,
        'sign_image' => null,
        'stempel'    => null,
    ];
}
}