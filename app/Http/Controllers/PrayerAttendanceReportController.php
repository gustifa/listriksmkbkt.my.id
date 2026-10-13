<?php

namespace App\Http\Controllers;
use App\Models\PrayerAttendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Exports\PrayerAttendanceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PrayerAttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate  = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate    = $request->input('end_date', Carbon::now()->toDateString());
        $classId    = $request->input('classroom_id');
        $searchName = $request->input('search_name');
        $prayerName = $request->input('prayer_name'); // Filter Waktu Sholat

        $classrooms = Classroom::all();

        // Query Filter Siswa
        $studentQuery = Student::with('classroom');
        if ($classId) {
            $studentQuery->where('classroom_id', $classId);
        }
        if ($searchName) {
            $studentQuery->where('name', 'iLIKE', '%' . $searchName . '%');
        }
        $students = $studentQuery->get();

        // Query Filter Absensi Sholat
        $attendanceQuery = PrayerAttendance::whereBetween('date', [$startDate, $endDate])
            ->whereIn('student_id', $students->pluck('id'));

        if ($prayerName) {
            $attendanceQuery->where('prayer_name', 'iLIKE', $prayerName);
        }

        $attendances = $attendanceQuery->get();

        // Olah matriks rekap
        $recapData = [];
        foreach ($attendances as $att) {
            $studentId = $att->student_id;
            $pName     = strtolower($att->prayer_name);

            if (!isset($recapData[$studentId])) {
                $recapData[$studentId] = [
                    'subuh'   => 0, 'dzuhur'  => 0, 'ashar'   => 0,
                    'maghrib' => 0, 'isya'    => 0, 'tarawih' => 0, 'total'   => 0,
                ];
            }

            if (isset($recapData[$studentId][$pName])) {
                $recapData[$studentId][$pName]++;
            } else {
                $recapData[$studentId][$pName] = 1;
            }
            $recapData[$studentId]['total']++;
        }

        if ($request->ajax()) {
            return view('admin.prayer_reports.table_partial', compact('students', 'recapData', 'startDate', 'endDate', 'prayerName'))->render();
        }

        return view('admin.prayer_reports.index', compact(
            'students', 'classrooms', 'recapData', 'startDate', 'endDate', 'classId', 'searchName', 'prayerName'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate  = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate    = $request->input('end_date', Carbon::now()->toDateString());
        $classId    = $request->input('classroom_id');
        $searchName = $request->input('search_name');
        $prayerName = $request->input('prayer_name');
        $studentIds = $request->input('student_ids');

        $fileName = 'Rekap_Absensi_Sholat_' . $startDate . '_s_d_' . $endDate . '.xlsx';

        return Excel::download(new PrayerAttendanceExport($startDate, $endDate, $classId, $searchName, $prayerName, $studentIds), $fileName);
    }
}