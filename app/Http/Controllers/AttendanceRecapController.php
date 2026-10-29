<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceRecapController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        return view('report.weekly_recap_filter', compact('classrooms'));
    }

    public function getStudentsByClassroom($classroomId)
    {
        $students = Student::where('classroom_id', (string)$classroomId)
                    ->orderBy('name', 'asc')
                    ->select('id', 'name', 'nis')
                    ->get();

        return response()->json($students);
    }

    public function weeklyRecap(Request $request)
    {
        $selectedDate =$request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::FRIDAY);

        $classroomId = trim($request->input('classroom_id'));$studentId   = $request->filled('student_id') ? trim($request->input('student_id')) : null;

        // 1. Detail Kelas & Pejabat Pengesahan
        $classroom = null;
        if (!empty($classroomId)) {$classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader'])
                            ->where('id', $classroomId)
                            ->first();
        }

        if (!$classroom) {$classroom = Classroom::with(['homeroomTeacher', 'counselingTeacher', 'classLeader'])->first();
            $classroomId =$classroom->id ?? null;
        }

        // 2. Ambil Daftar Siswa
        $studentsQuery = Student::query();
        if (!empty($classroomId)) {
            $studentsQuery->where('classroom_id', (string)$classroomId);
        }

        if (!empty($studentId)) {
            $studentsQuery->where('id', (string)$studentId);
        }

        $students =$studentsQuery->orderBy('name', 'asc')->get();

        // 3. Ambil Master Time Slots KHUSUS LESSON
        $lessonSlots = DB::table('time_slots')
                        ->where('type', 'lesson')
                        ->orderBy('start_time', 'asc')
                        ->get();

        // 4. Pemetaan Hari & Jadwal Pelajaran ke Rentang Jam (1-10)
        $dayVariations = [
            'Senin'  => ['Senin', 'senin', 'Monday', 'monday', '1'],
            'Selasa' => ['Selasa', 'selasa', 'Tuesday', 'tuesday', '2'],
            'Rabu'   => ['Rabu', 'rabu', 'Wednesday', 'wednesday', '3'],
            'Kamis'  => ['Kamis', 'kamis', 'Thursday', 'thursday', '4'],
            'Jumat'  => ['Jumat', 'jumat', 'Friday', 'friday', '5'],
        ];

        $daySubjectMap  = [];$scheduleJamMap = [];

        foreach ($dayVariations as $indonesianDay =>$variations) {
            for ($j = 1; $j <= 10; $j++) {$daySubjectMap[$indonesianDay][$j] = '';
            }

            $schedules = Schedule::with(['subject'])
                ->where('classroom_id', (string) $classroomId)
                ->whereIn('day', $variations)
                ->orderBy('start_time', 'asc')
                ->get();

            foreach ($schedules as$sch) {
                $subjectCode =$sch->subject->code ?? '';

                foreach ($lessonSlots as $index =>$slot) {
                    $jamNum =$index + 1;

                    if ($slot->start_time >=$sch->start_time && $slot->end_time <=$sch->end_time) {
                        $daySubjectMap[$indonesianDay][$jamNum] =$subjectCode;
                        $scheduleJamMap[$sch->id][] =$jamNum;
                    }
                }
            }
        }

        // 5. Ambil Data Presensi
        $studentIds =$students->pluck('id')->toArray();

        $attendances = Attendance::whereIn('student_id',$studentIds)
                        ->whereBetween('date', [$startOfWeek->format('Y-m-d'),$endOfWeek->format('Y-m-d')])
                        ->get();

        // 6. Matriks Presensi
        $recapMap = [];$summary  = [];

        foreach ($students as$student) {
            $summary[$student->id] = ['sakit' => 0, 'izin' => 0, 'alpa' => 0];
            for ($day = 1; $day <= 5; $day++) {
                for ($jam = 1; $jam <= 10; $jam++) {
                    $recapMap[$student->id][$day][$jam] = '';
                }
            }
        }

        foreach ($attendances as$att) {
            $dayOfWeek = Carbon::parse($att->date)->dayOfWeekIso;

            if ($dayOfWeek <= 5 && isset($recapMap[$att->student_id])) {
                $statusLower = strtolower(trim($att->status));

                // SIMBOL PRESENSI BARU: H, S, I, A
                $code = match($statusLower) {
                    'hadir' => 'H',
                    'sakit' => 'S',
                    'izin'  => 'I',
                    'alpa', 'tanpa keterangan' => 'A',
                    default => 'H'
                };

                $jamIndexes =$scheduleJamMap[$att->schedule_id] ?? [$att->jam_ke ?? 1];

                foreach ($jamIndexes as$jamIndex) {
                    if ($jamIndex >= 1 && $jamIndex <= 10) {$recapMap[$att->student_id][$dayOfWeek][$jamIndex] =$code;
                    }
                }

                if (in_array($statusLower, ['sakit', 'izin', 'alpa', 'tanpa keterangan'])) {$key = ($statusLower === 'tanpa keterangan') ? 'alpa' :$statusLower;
                    $summary[$att->student_id][$key]++;
                }
            }
        }

        return view('report.weekly_recap', compact(
            'students', 
            'recapMap', 
            'summary', 
            'startOfWeek', 
            'endOfWeek', 
            'classroom', 
            'daySubjectMap'
        ));
    }
}