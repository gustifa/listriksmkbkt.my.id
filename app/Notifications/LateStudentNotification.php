<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DailyAttendance;
use Carbon\Carbon;

class LateStudentNotification extends Notification
{
    use Queueable;

    protected $attendance;

    public function __construct(DailyAttendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $student = $this->attendance->student;
        $classroomName = $student->classroom->name ?? '-';
        $time = $this->attendance->arrival_time 
            ? Carbon::parse($this->attendance->arrival_time)->format('H:i') 
            : now()->format('H:i');

        return [
            'title'   => 'Siswa Terlambat Presensi Gerbang',
            'message' => "{$student->name} ({$classroomName}) scan masuk terlambat jam {$time} WIB.",
            'url'     => route('daily_attendance.report') . '?student_id=' . $student->id,
            'icon'    => 'bx-time-five'
        ];
    }
}