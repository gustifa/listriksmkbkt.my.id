<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Schedule;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MissingAttendanceExport;

class AttendanceReportController extends Controller
{
    public function checkMissingAttendance(Request $request)
    {
        $today = $request->input('date', Carbon::today()->toDateString());
        $data = $this->getMissingData($request);

        return view('attendance.missing_report', array_merge($data, ['today' => $today]));
    }

    public function exportPdf(Request $request)
    {
        $today = $request->input('date', Carbon::today()->toDateString());
        $data = $this->getMissingData($request);

        $pdf = Pdf::loadView('attendance.missing_report_pdf', array_merge($data, ['today' => $today]))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('Rekap_Belum_Absen_' . $today . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $today = $request->input('date', Carbon::today()->toDateString());
        $data = $this->getMissingData($request);

        return Excel::download(
            new MissingAttendanceExport($data['noArrivalStudents'], $data['noDepartureStudents'], $data['missingSubjectStudents']),
            'Rekap_Belum_Absen_' . $today . '.xlsx'
        );
    }

    /**
     * Reusable Logic Fetch Data Monitoring
     */
    // private function getMissingData(Request $request)
    // {
    //     $today = $request->input('date', Carbon::today()->toDateString());
    //     $currentTime = Carbon::now()->format('H:i:s');
        
    //     $dayNameIso = strtolower(Carbon::parse($today)->locale('id')->isoFormat('dddd'));
    //     $dayNameEn  = strtolower(Carbon::parse($today)->format('l'));
    //     $classroomId = $request->input('classroom_id');

    //     // Helper closure status fleksibel (1 / '1' / 'aktif' / 'active')
    //     $statusCondition = function($q) {
    //         $q->where('status', '1')
    //           ->orWhere('status', 1)
    //           ->orWhere('status', 'aktif')
    //           ->orWhere('status', 'active');
    //     };

    //     // 1. Belum Absen Masuk Gerbang
    //     $noArrivalQuery = Student::query()
    //         ->where($statusCondition)
    //         ->whereDoesntHave('dailyAttendances', function ($query) use ($today) {
    //             $query->whereDate('date', $today);
    //         })
    //         ->with('classroom');

    //     // 2. Belum Absen Pulang Gerbang
    //     $noDepartureQuery = Student::query()
    //         ->where($statusCondition)
    //         ->whereHas('dailyAttendances', function ($query) use ($today) {
    //             $query->whereDate('date', $today)
    //                   ->whereNotNull('arrival_time')
    //                   ->whereNull('departure_time');
    //         })
    //         ->with('classroom');

    //     if ($classroomId) {
    //         $noArrivalQuery->where('classroom_id', $classroomId);
    //         $noDepartureQuery->where('classroom_id', $classroomId);
    //     }

    //     $noArrivalStudents = $noArrivalQuery->orderBy('name', 'asc')->get();
    //     $noDepartureStudents = $noDepartureQuery->orderBy('name', 'asc')->get();

    //     // 3. Belum Presensi Mapel
    //     $schedulesQuery = Schedule::where(function($q) use ($dayNameIso, $dayNameEn) {
    //             $q->whereRaw('LOWER(day) = ?', [$dayNameIso])
    //               ->orWhereRaw('LOWER(day) = ?', [$dayNameEn]);
    //         });

    //     if ($classroomId) {
    //         $schedulesQuery->where('classroom_id', $classroomId);
    //     }

    //     $schedules = $schedulesQuery->where('start_time', '<=', $currentTime)
    //         ->with(['classroom', 'subject', 'teacher'])
    //         ->get();

    //     $missingSubjectStudents = [];

    //     foreach ($schedules as $schedule) {
    //         $studentsInClass = Student::where('classroom_id', $schedule->classroom_id)
    //             ->where($statusCondition)
    //             ->whereDoesntHave('attendances', function ($query) use ($today, $schedule) {
    //                 $query->where('schedule_id', $schedule->id)
    //                       ->whereDate('date', $today)
    //                       ->whereNotNull('check_in_time');
    //             })
    //             ->orderBy('name', 'asc')
    //             ->get();

    //         if ($studentsInClass->isNotEmpty()) {
    //             $missingSubjectStudents[] = [
    //                 'schedule' => $schedule,
    //                 'students' => $studentsInClass
    //             ];
    //         }
    //     }

    //     return compact('noArrivalStudents', 'noDepartureStudents', 'missingSubjectStudents');
    // }

    private function getMissingData(Request $request)
{
    $today = $request->input('date', Carbon::today()->toDateString());
    $currentTime = Carbon::now()->format('H:i:s');
    
    $dayNameIso = strtolower(Carbon::parse($today)->locale('id')->isoFormat('dddd'));
    $dayNameEn  = strtolower(Carbon::parse($today)->format('l'));
    $classroomId = $request->input('classroom_id');

    // Helper condition status aktif
    $statusCondition = function($q) {
        $q->where('status', '1')
          ->orWhere('status', 1)
          ->orWhere('status', 'aktif')
          ->orWhere('status', 'active');
    };

    // 1. BELUM ABSEN MASUK GERBANG (Hanya Siswa yang Punya Kelas)
    $noArrivalQuery = Student::query()
        ->whereNotNull('classroom_id') // <--- HANYA SISWA YANG PUNYA KELAS
        ->where($statusCondition)
        ->whereDoesntHave('dailyAttendances', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })
        ->with('classroom');

    // 2. BELUM ABSEN PULANG GERBANG (Hanya Siswa yang Punya Kelas)
    $noDepartureQuery = Student::query()
        ->whereNotNull('classroom_id') // <--- HANYA SISWA YANG PUNYA KELAS
        ->where($statusCondition)
        ->whereHas('dailyAttendances', function ($query) use ($today) {
            $query->whereDate('date', $today)
                  ->whereNotNull('arrival_time')
                  ->whereNull('departure_time');
        })
        ->with('classroom');

    if ($classroomId) {
        $noArrivalQuery->where('classroom_id', $classroomId);
        $noDepartureQuery->where('classroom_id', $classroomId);
    }

    $noArrivalStudents = $noArrivalQuery->orderBy('name', 'asc')->get();
    $noDepartureStudents = $noDepartureQuery->orderBy('name', 'asc')->get();

    // 3. BELUM PRESENSI MAPEL (Sudah otomatis memfilter per kelas jadwal)
    $schedulesQuery = Schedule::where(function($q) use ($dayNameIso, $dayNameEn) {
            $q->whereRaw('LOWER(day) = ?', [$dayNameIso])
              ->orWhereRaw('LOWER(day) = ?', [$dayNameEn]);
        });

    if ($classroomId) {
        $schedulesQuery->where('classroom_id', $classroomId);
    }

    $schedules = $schedulesQuery->where('start_time', '<=', $currentTime)
        ->with(['classroom', 'subject', 'teacher'])
        ->get();

    $missingSubjectStudents = [];

    foreach ($schedules as $schedule) {
        $studentsInClass = Student::where('classroom_id', $schedule->classroom_id)
            ->whereNotNull('classroom_id') // <--- HANYA SISWA YANG PUNYA KELAS
            ->where($statusCondition)
            ->whereDoesntHave('attendances', function ($query) use ($today, $schedule) {
                $query->where('schedule_id', $schedule->id)
                      ->whereDate('date', $today)
                      ->whereNotNull('check_in_time');
            })
            ->orderBy('name', 'asc')
            ->get();

        if ($studentsInClass->isNotEmpty()) {
            $missingSubjectStudents[] = [
                'schedule' => $schedule,
                'students' => $studentsInClass
            ];
        }
    }

    return compact('noArrivalStudents', 'noDepartureStudents', 'missingSubjectStudents');
}
}