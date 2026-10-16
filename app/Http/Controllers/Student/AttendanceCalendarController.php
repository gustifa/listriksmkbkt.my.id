<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\PrayerAttendance;
use App\Models\Holiday;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceCalendarController extends Controller
{
    public function index()
    {
        return view('siswa.kalender.index');
    }

    public function getEvents(Request $request)
    {
        $student = Student::where('user_id', Auth::id())->first();
        if (!$student) {
            return response()->json([]);
        }

        $start = $request->query('start');
        $end   = $request->query('end');

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

        // 2. DATA ABSENSI GERBANG
        $gateAttendances = DailyAttendance::where('student_id', $student->id)
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
            ->where('student_id', $student->id)
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
        $prayerAttendances = PrayerAttendance::where('student_id', $student->id)
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