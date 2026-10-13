<?php

namespace App\Exports;

use App\Models\PrayerAttendance;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PrayerAttendanceExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $classId;
    protected $searchName;
    protected $prayerName;
    protected $studentIds;

    public function __construct($startDate, $endDate, $classId = null, $searchName = null, $prayerName = null, $studentIds = null)
    {
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->classId    = $classId;
        $this->searchName = $searchName;
        $this->prayerName = $prayerName;
        $this->studentIds = is_string($studentIds) ? explode(',', $studentIds) : $studentIds;
    }

    public function view(): View
    {
        $studentQuery = Student::with('classroom');

        if (!empty($this->studentIds)) {
            $studentQuery->whereIn('id', $this->studentIds);
        } else {
            if ($this->classId) {
                $studentQuery->where('classroom_id', $this->classId);
            }
            if ($this->searchName) {
                $studentQuery->where('name', 'iLIKE', '%' . $this->searchName . '%');
            }
        }

        $students = $studentQuery->get();

        $attendanceQuery = PrayerAttendance::whereBetween('date', [$this->startDate, $this->endDate])
            ->whereIn('student_id', $students->pluck('id'));

        if ($this->prayerName) {
            $attendanceQuery->where('prayer_name', 'iLIKE', $this->prayerName);
        }

        $attendances = $attendanceQuery->get();

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

        return view('admin.prayer_reports.excel', [
            'students'   => $students,
            'recapData'  => $recapData,
            'startDate'  => $this->startDate,
            'endDate'    => $this->endDate,
            'prayerName' => $this->prayerName,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}