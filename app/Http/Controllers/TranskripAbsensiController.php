<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TranskripAbsensiController extends Controller
{
    /**
     * Halaman Utama Filter Cetak Transkrip
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $isWaliKelas = $user->hasRole('wali_kelas') || $user->role === 'wali_kelas';
        $isBK        = $user->hasRole('bk') || $user->role === 'bk';

        $classrooms = collect();
        $students   = collect();

        if ($isWaliKelas) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $teacherId = $teacher ? $teacher->id : $user->id;

            $classrooms = Classroom::where('homeroom_teacher_id', $teacherId)
                ->orWhere('homeroom_teacher_id', $user->id)
                ->get();
            $classroomIds = $classrooms->pluck('id');

            $students = Student::whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

        } elseif ($isBK) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $teacherId = $teacher ? $teacher->id : $user->id;

            $classrooms = Classroom::where('counseling_teacher_id', $teacherId)
                ->orWhere('counseling_teacher_id', $user->id)
                ->get();

            if ($classrooms->isEmpty()) {
                $classrooms = Classroom::orderBy('name')->get();
            }

            $students = Student::where('status', 'active')->orderBy('name')->get();

        } else {
            $classrooms = Classroom::orderBy('name')->get();
            $students   = Student::where('status', 'active')->orderBy('name')->get();
        }

        return view('report.transkrip_index', compact('students', 'classrooms', 'isWaliKelas', 'isBK'));
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

        $includeGate     = $request->has('include_gate');
        $includeLearning = $request->has('include_learning');
        $includePrayer   = $request->has('include_prayer');

        $studentsQuery = Student::with(['classroom.homeroomTeacher', 'classroom.counselingTeacher'])
            ->where('status', 'active');

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
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        $reports = [];
        foreach ($students as $st) {
            
            // 1. ABSENSI GERBANG (AUTO TANPA KETERANGAN JIKA TANGGAL KOSONG)
            $gate = collect();
            if ($includeGate) {
                $existingGate = DailyAttendance::where('student_id', $st->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get()
                    ->keyBy(function($item) {
                        return Carbon::parse($item->date)->toDateString();
                    });

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    if ($date->isSunday()) continue; // Skip hari Minggu

                    $dateStr = $date->toDateString();
                    if ($existingGate->has($dateStr)) {
                        $gate->push($existingGate->get($dateStr));
                    } else {
                        $gate->push((object)[
                            'date'           => $dateStr,
                            'arrival_time'   => '-',
                            'departure_time' => '-',
                            'status'         => 'Tanpa Keterangan'
                        ]);
                    }
                }
            }

            // 2. ABSENSI PEMBELAJARAN (KBM)
            $learning = collect();
            if ($includeLearning) {
                $existingLearning = Attendance::with(['schedule.subject', 'schedule.teacher', 'teacher'])
                    ->where('student_id', $st->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get();

                if ($existingLearning->isNotEmpty()) {
                    $learning = $existingLearning;
                } else {
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if ($date->isSunday()) continue;
                        $learning->push((object)[
                            'date'          => $date->toDateString(),
                            'schedule'      => null,
                            'subject_name'  => 'Pelajaran Efektif',
                            'teacher'       => null,
                            'check_in_time' => '-',
                            'status'        => 'Tanpa Keterangan'
                        ]);
                    }
                }
            }

            // 3. ABSENSI SHOLAT & IBADAH
            $prayer = collect();
            if ($includePrayer) {
                $existingPrayer = PrayerAttendance::where('student_id', $st->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get();

                if ($existingPrayer->isNotEmpty()) {
                    $prayer = $existingPrayer;
                } else {
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if ($date->isSunday()) continue;
                        $prayer->push((object)[
                            'date'          => $date->toDateString(),
                            'prayer_name'   => 'Ibadah Harian',
                            'check_in_time' => '-',
                            'status'        => 'Tanpa Keterangan'
                        ]);
                    }
                }
            }

            $classroom = $st->classroom;
            $homeroomTeacher   = $classroom ? $classroom->homeroomTeacher : null;
            $counselingTeacher = $classroom ? $classroom->counselingTeacher : null;

            if (!$homeroomTeacher && $classroom && $classroom->homeroom_teacher_id) {
                $homeroomTeacher = Teacher::where('id', $classroom->homeroom_teacher_id)
                    ->orWhere('user_id', $classroom->homeroom_teacher_id)
                    ->first();
            }

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

        // PENENTUAN NAMA FILE DOWNLOAD PDF
        $nowFormatted = Carbon::now()->format('dmy_Hi');
        if ($mode === 'student' && $students->count() === 1) {
            $studentName = Str::slug($students->first()->name, '_');
            $downloadTitle = "Transkrip_Absensi_" . $studentName . "_" . $nowFormatted;
        } elseif ($mode === 'classroom' && isset($classroomId)) {
            $classroom = Classroom::find($classroomId);
            $className = $classroom ? Str::slug($classroom->name, '_') : 'Kelas';
            $downloadTitle = "Transkrip_Absensi_Kelas_" . $className . "_" . $nowFormatted;
        } else {
            $downloadTitle = "Transkrip_Absensi_Semua_Kelas_" . $nowFormatted;
        }

        return view('report.transkrip_print', compact(
            'reports', 'startDate', 'endDate', 'settings', 
            'includeGate', 'includeLearning', 'includePrayer',
            'downloadTitle'
        ));
    }

    /**
     * Konfigurasi Data Sekolah & Logo Database
     */
    private function getSchoolData()
    {
        $logoLeftPath  = Setting::where('key', 'logo_left')->value('value');
        $logoRightPath = Setting::where('key', 'logo_right')->value('value');

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
            'logo_left'        => ($logoLeftPath && \Storage::disk('public')->exists($logoLeftPath)) ? asset('storage/' . $logoLeftPath) : null,
            'logo_right'       => ($logoRightPath && \Storage::disk('public')->exists($logoRightPath)) ? asset('storage/' . $logoRightPath) : null,
            'sign_image'       => null,
            'stempel'          => null,
        ];
    }
}