<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use App\Models\Teacher;
use App\Models\Setting;
use Carbon\Carbon;

class TranskripAbsensiController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = auth()->user();
    //     $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';

    //     $classrooms = collect();
    //     $students = collect();

    //     if ($isWaliKelas) {
    //         // Wali Kelas: Ambil siswa dan kelas binaannya
    //         $teacher = Teacher::where('user_id', $user->id)->first();
    //         $classroomId = $teacher 
    //             ? Classroom::where('homeroom_teacher_id', $teacher->id)->value('id')
    //             : Classroom::where('homeroom_teacher_id', $user->id)->value('id');

    //         $classrooms = Classroom::where('id', $classroomId)->get();
    //         $students = Student::where('classroom_id', $classroomId)->where('status', 'active')->orderBy('name')->get();
    //     } else {
    //         // Admin & BK: Ambil seluruh kelas dan seluruh siswa
    //         $classrooms = Classroom::orderBy('name')->get();
    //         $students = Student::where('status', 'active')->orderBy('name')->get();
    //     }

    //     // Dipastikan nama view menggunakan 'reports.transkrip_index' (pakai 's')
    //     return view('report.transkrip_index', compact('students', 'classrooms', 'isWaliKelas'));
    // }

    // // public function print(Request $request)
    // // {
    // //     $user = auth()->user();
    // //     $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';

    // //     $mode = $request->input('mode', 'student'); 
    // //     $studentId = $request->input('student_id');
    // //     $classroomId = $request->input('classroom_id');
    // //     $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    // //     $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    // //     $studentsQuery = Student::with(['classroom'])->where('status', 'active');

    // //     // Filter Otorisasi & Mode Cetak
    // //     if ($isWaliKelas) {
    // //         $teacher = Teacher::where('user_id', $user->id)->first();
    // //         $waliClassroomId = $teacher 
    // //             ? Classroom::where('homeroom_teacher_id', $teacher->id)->value('id')
    // //             : Classroom::where('homeroom_teacher_id', $user->id)->value('id');

    // //         $studentsQuery->where('classroom_id', $waliClassroomId);
    // //         if ($mode === 'student' && $studentId) {
    // //             $studentsQuery->where('id', $studentId);
    // //         }
    // //     } else {
    // //         if ($mode === 'student' && $studentId) {
    // //             $studentsQuery->where('id', $studentId);
    // //         } elseif ($mode === 'classroom' && $classroomId) {
    // //             $studentsQuery->where('classroom_id', $classroomId);
    // //         }
    // //     }

    // //     $students = $studentsQuery->orderBy('classroom_id')->orderBy('name')->get();

    // //     if ($students->isEmpty()) {
    // //         return back()->with('error', 'Tidak ada data siswa yang ditemukan.');
    // //     }

    // //     $settings = $this->getSchoolData();

    // //     // Rekap 3 jenis absensi
    // //     $reports = [];
    // //     foreach ($students as $st) {
    // //         $gate = DailyAttendance::where('student_id', $st->id)
    // //             ->whereBetween('date', [$startDate, $endDate])
    // //             ->orderBy('date', 'asc')->get();

    // //         $learning = Attendance::where('student_id', $st->id)
    // //             ->whereBetween('date', [$startDate, $endDate])
    // //             ->orderBy('date', 'asc')->get();

    // //         $prayer = PrayerAttendance::where('student_id', $st->id)
    // //             ->whereBetween('date', [$startDate, $endDate])
    // //             ->orderBy('date', 'asc')->get();

    // //         $classroom = $st->classroom;
    // //         $homeroomTeacher = $classroom && $classroom->homeroom_teacher_id 
    // //             ? Teacher::find($classroom->homeroom_teacher_id) : null;
    // //         $counselingTeacher = $classroom && $classroom->counseling_teacher_id 
    // //             ? Teacher::find($classroom->counseling_teacher_id) : null;

    // //         $reports[] = [
    // //             'student' => $st,
    // //             'gate' => $gate,
    // //             'learning' => $learning,
    // //             'prayer' => $prayer,
    // //             'homeroomTeacher' => $homeroomTeacher,
    // //             'counselingTeacher' => $counselingTeacher,
    // //         ];
    // //     }

    // //     return view('report.transkrip_print', compact('reports', 'startDate', 'endDate', 'settings'));
    // // }

    // public function print(Request $request)
    // {
    //     $user = auth()->user();
    //     $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';

    //     $mode = $request->input('mode', 'student'); 
    //     $studentId = $request->input('student_id');
    //     $classroomId = $request->input('classroom_id');
    //     $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    //     $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    //     // Ceklist Komponen Absensi Dinamis
    //     $includeGate = $request->has('include_gate');
    //     $includeLearning = $request->has('include_learning');
    //     $includePrayer = $request->has('include_prayer');

    //     $studentsQuery = Student::with(['classroom'])->where('status', 'active');

    //     if ($isWaliKelas) {
    //         $teacher = Teacher::where('user_id', $user->id)->first();
    //         $waliClassroomId = $teacher 
    //             ? Classroom::where('homeroom_teacher_id', $teacher->id)->value('id')
    //             : Classroom::where('homeroom_teacher_id', $user->id)->value('id');

    //         $studentsQuery->where('classroom_id', $waliClassroomId);
    //         if ($mode === 'student' && $studentId) {
    //             $studentsQuery->where('id', $studentId);
    //         }
    //     } else {
    //         if ($mode === 'student' && $studentId) {
    //             $studentsQuery->where('id', $studentId);
    //         } elseif ($mode === 'classroom' && $classroomId) {
    //             $studentsQuery->where('classroom_id', $classroomId);
    //         }
    //     }

    //     $students = $studentsQuery->orderBy('classroom_id')->orderBy('name')->get();

    //     if ($students->isEmpty()) {
    //         return back()->with('error', 'Tidak ada data siswa yang ditemukan.');
    //     }

    //     $settings = $this->getSchoolData();

    //     $reports = [];
    //     foreach ($students as $st) {
    //         // Tarik data hanya jika komponen dicentang
    //         $gate = $includeGate 
    //             ? DailyAttendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
    //             : collect();

    //         $learning = $includeLearning 
    //             ? Attendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
    //             : collect();

    //         $prayer = $includePrayer 
    //             ? PrayerAttendance::where('student_id', $st->id)->whereBetween('date', [$startDate, $endDate])->orderBy('date', 'asc')->get() 
    //             : collect();

    //         $classroom = $st->classroom;
    //         $homeroomTeacher = $classroom && $classroom->homeroom_teacher_id ? Teacher::find($classroom->homeroom_teacher_id) : null;
    //         $counselingTeacher = $classroom && $classroom->counseling_teacher_id ? Teacher::find($classroom->counseling_teacher_id) : null;

    //         $reports[] = [
    //             'student' => $st,
    //             'gate' => $gate,
    //             'learning' => $learning,
    //             'prayer' => $prayer,
    //             'homeroomTeacher' => $homeroomTeacher,
    //             'counselingTeacher' => $counselingTeacher,
    //         ];
    //     }

    //     return view('report.transkrip_print', compact(
    //         'reports', 'startDate', 'endDate', 'settings', 
    //         'includeGate', 'includeLearning', 'includePrayer'
    //     ));
    // }

    public function index(Request $request)
{
    $user = auth()->user();
    $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';
    $isBK = $user->hasRole('bk') || $user->role === 'bk';

    $classrooms = collect();
    $students = collect();

    if ($isWaliKelas) {
        // Wali Kelas: Ambil kelas binaannya berdasarkan homeroom_teacher_id
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : $user->id;

        $classrooms = Classroom::where('homeroom_teacher_id', $teacherId)->get();
        $classroomIds = $classrooms->pluck('id');

        $students = Student::whereIn('classroom_id', $classroomIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

    } elseif ($isBK) {
        // Guru BK: Ambil kelas bimbingannya berdasarkan counseling_teacher_id
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : $user->id;

        $classrooms = Classroom::where('counseling_teacher_id', $teacherId)->get();
        
        // Jika BK mengampu seluruh kelas bila tidak di-set khusus
        if ($classrooms->isEmpty()) {
            $classrooms = Classroom::orderBy('name')->get();
        }

        $students = Student::where('status', 'active')->orderBy('name')->get();

    } else {
        // Admin: Ambil seluruh kelas dan seluruh siswa
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::where('status', 'active')->orderBy('name')->get();
    }

    return view('report.transkrip_index', compact('students', 'classrooms', 'isWaliKelas', 'isBK'));
}

public function print(Request $request)
{
    $user = auth()->user();
    $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';
    $isBK = $user->hasRole('bk') || $user->role === 'bk';

    $mode = $request->input('mode', 'student'); 
    $studentId = $request->input('student_id');
    $classroomId = $request->input('classroom_id');
    $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    // Ceklist Komponen Absensi Dinamis
    $includeGate = $request->has('include_gate');
    $includeLearning = $request->has('include_learning');
    $includePrayer = $request->has('include_prayer');

    $studentsQuery = Student::with(['classroom.homeroomTeacher', 'classroom.counselingTeacher'])->where('status', 'active');

    // Filter Hak Akses & Mode Cetak
    if ($isWaliKelas) {
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = $teacher ? $teacher->id : $user->id;
        
        $waliClassroomIds = Classroom::where('homeroom_teacher_id', $teacherId)->pluck('id');
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
        return back()->with('error', 'Tidak ada data siswa yang ditemukan.');
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
        
        // Otomatis mengambil Guru Wali Kelas dan Guru BK langsung dari relasi classroom
        $homeroomTeacher = $classroom ? $classroom->homeroomTeacher : null;
        $counselingTeacher = $classroom ? $classroom->counselingTeacher : null;

        $reports[] = [
            'student' => $st,
            'gate' => $gate,
            'learning' => $learning,
            'prayer' => $prayer,
            'homeroomTeacher' => $homeroomTeacher,
            'counselingTeacher' => $counselingTeacher,
        ];
    }

    return view('report.transkrip_print', compact(
        'reports', 'startDate', 'endDate', 'settings', 
        'includeGate', 'includeLearning', 'includePrayer'
    ));
}

    private function getSchoolData()
    {
        return [
            'school_name'   => Setting::where('key', 'school_name')->value('value') ?? 'SMK NEGERI 1 BUKITTINGGI',
            'school_address'=> Setting::where('key', 'school_address')->value('value') ?? '-',
            'school_phone'  => Setting::where('key', 'school_phone')->value('value') ?? '-',
            'school_web'    => Setting::where('key', 'school_web')->value('value') ?? '-',
            'school_email'  => Setting::where('key', 'school_email')->value('value') ?? '-',
            'provinsi_name' => Setting::where('key', 'provinsi_name')->value('value') ?? 'SUMATERA BARAT',
            'logo_left'     => $this->imageToBase64(Setting::where('key', 'logo_left')->value('value')),
            'logo_right'    => $this->imageToBase64(Setting::where('key', 'logo_right')->value('value')),
            'paper_size'    => Setting::where('key', 'paper_size')->value('value') ?? 'a4',
            'paper_orientation' => Setting::where('key', 'paper_orientation')->value('value') ?? 'portrait',
            'margin_top'    => Setting::where('key', 'margin_top')->value('value') ?? '0.5',
            'margin_right'  => Setting::where('key', 'margin_right')->value('value') ?? '1.5',
            'margin_bottom' => Setting::where('key', 'margin_bottom')->value('value') ?? '0.5',
            'margin_left'   => Setting::where('key', 'margin_left')->value('value') ?? '2.5',
            'sign_city'     => Setting::where('key', 'signature_city')->value('value') ?? 'Bukittinggi',
            'sign_title'    => Setting::where('key', 'signature_title')->value('value') ?? 'Kepala Sekolah',
            'sign_name'     => Setting::where('key', 'signature_name')->value('value') ?? 'Gustian Budiarto, M.Pd',
            'sign_nip'      => Setting::where('key', 'signature_nip')->value('value') ?? '-',
            'sign_image'    => $this->imageToBase64(Setting::where('key', 'signature_image')->value('value')),
            'stempel'       => $this->imageToBase64(Setting::where('key', 'stempel')->value('value')),
        ];
    }

    private function imageToBase64($path)
    {
        if (!$path || $path == '-') return null;
        $cleanPath = str_replace('storage/', '', $path);
        $fullPath = storage_path('app/public/' . $cleanPath);

        if (!file_exists($fullPath)) {
            $fullPath = public_path('storage/' . $cleanPath);
        }

        if (file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return null;
    }
}