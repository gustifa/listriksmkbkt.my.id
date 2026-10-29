<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // IMPORT DOMPDF

class AttendanceRecapController extends Controller
{
    public function index()
    {
        // $classrooms = Classroom::orderBy('name', 'asc')->get();
        $classrooms = Classroom::where('is_active', true)
                           ->orderBy('name', 'asc')
                           ->get();
        return view('report.weekly_recap_filter', compact('classrooms'));
    }

    public function getStudentsByClassroom($classroomId)
    {
        $students = Student::where('classroom_id', (string) $classroomId)
                    ->orderBy('name', 'asc')
                    ->select('id', 'name', 'nis')
                    ->get();

        return response()->json($students);
    }

    private function getBase64Image($path)
    {
        if (empty($path)) return null;

        $fullPath = public_path('storage/' . $path);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . $path);
        }

        if (file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return null;
    }

    private function prepareRecapData(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek  = Carbon::parse($selectedDate)->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = Carbon::parse($selectedDate)->endOfWeek(Carbon::FRIDAY);

        $weekDates = [
            'Senin'  => $startOfWeek->copy()->addDays(0)->format('d/m/Y'),
            'Selasa' => $startOfWeek->copy()->addDays(1)->format('d/m/Y'),
            'Rabu'   => $startOfWeek->copy()->addDays(2)->format('d/m/Y'),
            'Kamis'  => $startOfWeek->copy()->addDays(3)->format('d/m/Y'),
            'Jumat'  => $startOfWeek->copy()->addDays(4)->format('d/m/Y'),
        ];

        $classroomId = trim($request->input('classroom_id'));
        $studentId   = $request->filled('student_id') ? trim($request->input('student_id')) : null;

        // 1. Settings & Logo
        $settingsRaw = DB::table('settings')->get()->pluck('value', 'key');
        
        $logoLeftPath  = $settingsRaw['logo_left_st'] ?? $settingsRaw['logo_left'] ?? null;
        $logoRightPath = $settingsRaw['logo_right_st'] ?? $settingsRaw['logo_right'] ?? null;

        $settings = [
            'provinsi_name'  => $settingsRaw['provinsi_name'] ?? 'PEMERINTAH PROVINSI SUMATERA BARAT',
            'school_name'    => $settingsRaw['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI',
            'school_address' => $settingsRaw['school_address'] ?? 'Jl. Iskandar Teja Sukmana - Padang Gamuak, Kota Bukittinggi -',
            'school_phone'   => $settingsRaw['school_phone'] ?? '',
            'school_email'   => $settingsRaw['school_email'] ?? 'info@smkn1bukittinggi.sch.id',
            'school_web'     => $settingsRaw['school_web'] ?? 'www.smkn1bukittinggi.sch.id',
            'logo_left'      => $this->getBase64Image($logoLeftPath),
            'logo_right'     => $this->getBase64Image($logoRightPath),
            'kepsek_nama'    => $settingsRaw['ttd_surat'] ?? $settingsRaw['signature_name'] ?? 'GUSTIAN BUDIARTO, M.Pd.',
            'kepsek_nip'     => $settingsRaw['signature_nip'] ?? $settingsRaw['nip_surat'] ?? '19740731 200801 1 002',
        ];

        // 2. Detail Kelas
        $classroom = null;
        if (!empty($classroomId)) {
            $classroom = Classroom::with([
                'homeroomTeacher', 
                'counselingTeacher', 
                'classLeader', 
                'major'
            ])->where('id', $classroomId)->first();
        }

        if (!$classroom) {
            $classroom = Classroom::with([
                'homeroomTeacher', 
                'counselingTeacher', 
                'classLeader', 
                'major'
            ])->first();
            $classroomId = $classroom->id ?? null;
        }

        // 3. Daftar Siswa
        $studentsQuery = Student::query();
        if (!empty($classroomId)) {
            $studentsQuery->where('classroom_id', (string) $classroomId);
        }

        if (!empty($studentId)) {
            $studentsQuery->where('id', (string) $studentId);
        }

        $students = $studentsQuery->orderBy('name', 'asc')->get();

        // 4. Pejabat Pengesahan
        $walasNama = optional($classroom->homeroomTeacher)->name ?? 'SATRIAL FITRI';
        $walasNip  = optional($classroom->homeroomTeacher)->nip ?? '197510042006041000';

        $bkNama    = optional($classroom->counselingTeacher)->name ?? 'BENI OKTA';
        $bkNip     = optional($classroom->counselingTeacher)->nip ?? '198010042010011015';

        $ketuaNama = optional($classroom->classLeader)->name ?? ($students->first()->name ?? 'Alif Rafiqi Azri');
        $ketuaNis  = optional($classroom->classLeader)->nis ?? ($students->first()->nis ?? '36789');

        $officers = [
            'walas_nama' => $walasNama,
            'walas_nip'  => $walasNip,
            'bk_nama'    => $bkNama,
            'bk_nip'     => $bkNip,
            'ketua_nama' => $ketuaNama,
            'ketua_nis'  => $ketuaNis,
        ];

        // 5. Time Slots
        $lessonSlots = DB::table('time_slots')
                        ->where('type', 'lesson')
                        ->orderBy('start_time', 'asc')
                        ->get();

        // 6. Pemetaan Jadwal
        $dayVariations = [
            1 => ['Senin', 'senin', 'Monday', 'monday', '1'],
            2 => ['Selasa', 'selasa', 'Tuesday', 'tuesday', '2'],
            3 => ['Rabu', 'rabu', 'Wednesday', 'wednesday', '3'],
            4 => ['Kamis', 'kamis', 'Thursday', 'thursday', '4'],
            5 => ['Jumat', 'jumat', 'Friday', 'friday', '5'],
        ];

        $daySubjectMap   = [];
        $daySubjectNum   = [];
        $scheduleJamMap  = [];
        $schedulesPerDay = [];

        $daysList = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];

        foreach ($dayVariations as $dayNum => $variations) {
            $indonesianDay = $daysList[$dayNum];
            
            for ($j = 1; $j <= 10; $j++) {
                $daySubjectMap[$indonesianDay][$j] = '';
                $daySubjectNum[$dayNum][$j] = '';
            }

            $schedules = Schedule::with(['subject'])
                ->where('classroom_id', (string) $classroomId)
                ->whereIn('day', $variations)
                ->orderBy('start_time', 'asc')
                ->get();

            $schedulesPerDay[$dayNum] = $schedules;

            foreach ($schedules as $sch) {
                $subjectCode = $sch->subject->code ?? '';

                foreach ($lessonSlots as $index => $slot) {
                    $jamNum = $index + 1;

                    if ($slot->start_time >= $sch->start_time && $slot->end_time <= $sch->end_time) {
                        $daySubjectMap[$indonesianDay][$jamNum] = $subjectCode;
                        $daySubjectNum[$dayNum][$jamNum]        = $subjectCode;
                        $scheduleJamMap[$sch->id][]             = $jamNum;
                    }
                }
            }
        }

        // 7. Data Presensi Real
        $studentIds = $students->pluck('id')->toArray();

        $attendances = Attendance::whereIn('student_id', $studentIds)
                        ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                        ->get();

        // 8. Inisialisasi Matriks Presensi & Summary
        $recapMap = [];
        $summary  = [];

        foreach ($students as $student) {
            $summary[$student->id] = ['sakit' => 0, 'izin' => 0, 'alpa' => 0];
            for ($day = 1; $day <= 5; $day++) {
                for ($jam = 1; $jam <= 10; $jam++) {
                    $recapMap[$student->id][$day][$jam] = '';
                }
            }
        }

        foreach ($attendances as $att) {
            $dayOfWeek = Carbon::parse($att->date)->dayOfWeekIso;

            if ($dayOfWeek <= 5 && isset($recapMap[$att->student_id])) {
                $statusLower = strtolower(trim($att->status));

                $code = match($statusLower) {
                    'hadir' => '✓',
                    'sakit' => 'S',
                    'izin'  => 'I',
                    default => 'X'
                };

                $jamIndexes = $scheduleJamMap[$att->schedule_id] ?? [$att->jam_ke ?? 1];

                foreach ($jamIndexes as $jamIndex) {
                    if ($jamIndex >= 1 && $jamIndex <= 10) {
                        $recapMap[$att->student_id][$dayOfWeek][$jamIndex] = $code;
                    }
                }

                if ($statusLower === 'sakit') {
                    $summary[$att->student_id]['sakit']++;
                } elseif ($statusLower === 'izin') {
                    $summary[$att->student_id]['izin']++;
                }
            }
        }

        // 9. Oto-fill 'X' Merah (Hanya Hari yang Dilalui)
        $todayDate = Carbon::now()->format('Y-m-d');

        foreach ($students as $student) {
            for ($day = 1; $day <= 5; $day++) {
                $currentDayDate = $startOfWeek->copy()->addDays($day - 1)->format('Y-m-d');

                if ($currentDayDate > $todayDate) {
                    continue;
                }

                foreach ($schedulesPerDay[$day] as $sch) {
                    $jamIndexes = $scheduleJamMap[$sch->id] ?? [];
                    
                    if (!empty($jamIndexes)) {
                        $firstJam = $jamIndexes[0];
                        $currentVal = $recapMap[$student->id][$day][$firstJam];

                        if (empty($currentVal)) {
                            foreach ($jamIndexes as $jIdx) {
                                $recapMap[$student->id][$day][$jIdx] = 'X';
                            }
                            $summary[$student->id]['alpa']++;
                        }
                    }
                }
            }
        }

        return compact(
            'students', 
            'recapMap', 
            'summary', 
            'startOfWeek', 
            'endOfWeek', 
            'weekDates',
            'classroom', 
            'daySubjectMap',
            'settings',
            'officers'
        );
    }

    // METHOD PRATINJAU HTML DI BROWSER
    public function weeklyRecap(Request $request)
    {
        $data = $this->prepareRecapData($request);
        return view('report.weekly_recap', $data);
    }

    // METHOD DOWNLOAD PDF LANGSUNG DENGAN DOMPDF
    public function downloadPdf(Request $request)
    {
        $data = $this->prepareRecapData($request);

        // Load view khusus dompdf / view yang sama
        $pdf = Pdf::loadView('report.weekly_recap_pdf', $data)
                  ->setPaper('a4', 'landscape')
                  ->setWarnings(false);

        $filename = 'Rekap_Presensi_Mingguan_' . str_replace(' ', '_', $data['classroom']->name ?? 'Kelas') . '.pdf';

        return $pdf->download($filename);
    }
}