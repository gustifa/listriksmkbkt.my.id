<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use App\Models\Holiday;
use Carbon\Carbon;

class StudentAttendanceReportController extends Controller
{
    public function index()
    {
        // Ambil semua kelas untuk dropdown filter
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        return view('admin.reports.student_calendar', compact('classrooms'));
    }

    /**
     * AJAX Endpoint: Get list siswa berdasarkan classroom_id
     */
    public function getStudentsByClassroom($classroomId)
    {
        $students = Student::where('classroom_id', $classroomId)
            ->where(function($q) {
                $q->where('status', '1')->orWhere('status', 1)->orWhere('status', 'aktif')->orWhere('status', 'active');
            })
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'nis')
            ->get();

        return response()->json($students);
    }

    /**
     * AJAX Endpoint: Fetch FullCalendar Events
     */
    public function getEvents(Request $request)
    {
        $studentId = $request->query('student_id');
        $start     = $request->query('start');
        $end       = $request->query('end');

        $events = [];

        // 1. DATA HARI LIBUR / KALENDER MERAH ADMIN
        $holidays = Holiday::where(function($q) {
                        $q->where('is_active', true)
                          ->orWhere('is_active', 1)
                          ->orWhere('is_active', 't');
                    })
                    ->whereDate('start_date', '<=', $end)
                    ->whereDate('end_date', '>=', $start)
                    ->get();

        foreach ($holidays as $h) {
            $events[] = [
                'title'           => '🔴 ' . $h->title,
                'start'           => $h->start_date,
                'end'             => Carbon::parse($h->end_date)->addDay()->format('Y-m-d'),
                'display'         => 'block',
                'backgroundColor' => '#fee2e2',
                'borderColor'     => '#fca5a5',
                'textColor'       => '#991b1b',
                'extendedProps'   => [
                    'category'    => 'holiday',
                    'title'       => $h->title,
                    'description' => $h->description ?? 'Hari Libur Sekolah'
                ]
            ];
        }

        // Jika siswa belum dipilih, kembalikan hanya data libur
        if (!$studentId) {
            return response()->json($events);
        }

        // 2. DATA ABSENSI GERBANG SISWA
        $gateAttendances = DailyAttendance::where('student_id', $studentId)
            ->whereBetween('date', [$start, $end])
            ->get();

        foreach ($gateAttendances as $gate) {
            $bgColor = match($gate->status) {
                'hadir'     => '#dcfce7',
                'terlambat' => '#fef3c7',
                'izin', 'sakit' => '#dbeafe',
                default     => '#fee2e2',
            };

            $textColor = match($gate->status) {
                'hadir'     => '#166534',
                'terlambat' => '#92400e',
                'izin', 'sakit' => '#1e40af',
                default     => '#991b1b',
            };

            $events[] = [
                'title'           => '🚪 Gerbang: ' . strtoupper($gate->status),
                'start'           => $gate->date,
                'backgroundColor' => $bgColor,
                'borderColor'     => $textColor,
                'textColor'       => $textColor,
                'extendedProps'   => [
                    'category'       => 'gate',
                    'status'         => strtoupper($gate->status),
                    'arrival_time'   => $gate->arrival_time ? Carbon::parse($gate->arrival_time)->format('H:i') : '-',
                    'departure_time' => $gate->departure_time ? Carbon::parse($gate->departure_time)->format('H:i') : '-',
                    'notes'          => $gate->notes ?? '-'
                ]
            ];
        }

        // 3. DATA ABSENSI PEMBELAJARAN (MAPEL) - GROUPED
        $subjectAttendances = Attendance::with('subject')
            ->where('student_id', $studentId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('date');

        foreach ($subjectAttendances as $date => $subjects) {
            $events[] = [
                'title'           => '📚 Mapel (' . $subjects->count() . ' Diikuti)',
                'start'           => $date,
                'backgroundColor' => '#f3e8ff',
                'borderColor'     => '#c084fc',
                'textColor'       => '#6b21a8',
                'extendedProps'   => [
                    'category' => 'subject',
                    'details'  => $subjects->map(fn($s) => [
                        'subject_name'  => $s->subject->name ?? 'Mapel',
                        'check_in_time' => $s->check_in_time ? Carbon::parse($s->check_in_time)->format('H:i') : '-',
                        'status'        => strtoupper($s->status)
                    ])
                ]
            ];
        }

        // 4. DATA ABSENSI SHOLAT - GROUPED
        $prayerAttendances = PrayerAttendance::where('student_id', $studentId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('date');

        foreach ($prayerAttendances as $date => $prayers) {
            $events[] = [
                'title'           => '🕌 Sholat (' . $prayers->count() . ' Waktu)',
                'start'           => $date,
                'backgroundColor' => '#e0f2fe',
                'borderColor'     => '#38bdf8',
                'textColor'       => '#075985',
                'extendedProps'   => [
                    'category' => 'prayer',
                    'details'  => $prayers->map(fn($p) => [
                        'prayer_name'   => $p->prayer_name ?? 'Sholat',
                        'check_in_time' => $p->check_in_time ? Carbon::parse($p->check_in_time)->format('H:i') : '-',
                        'status'        => strtoupper($p->status)
                    ])
                ]
            ];
        }

        return response()->json($events);
    }
}