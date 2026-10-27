<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CbtExamScheduledNotification extends Notification
{
    use Queueable;

    // DEKLARASIKAN PROPERTI PUBLIC/PROTECTED DI SINI
    public $exam;

    /**
     * Create a new notification instance.
     */
    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    // public function toArray(object $notifiable): array
    // {
    //     //     return [
    //     //     'exam_id'     => $this->exam->id,
    //     //     'title'       => 'Ujian CBT Baru Dijadwalkan!',
    //     //     'subject'     => optional($this->exam->subject)->name ?? 'Mata Pelajaran',
    //     //     'exam_title'  => $this->exam->title ?? $this->exam->name,
    //     //     'start_time'  => $this->exam->start_time,
    //     //     'duration'    => $this->exam->duration_minutes . ' Menit',
    //     //     // Mengarahkan ke route konfirmasi ujian siswa
    //     //     'url'         => route('student.exam.start', $this->exam->id),
    //     //     'icon'        => 'bx-edit',
    //     //     'type'        => 'cbt_scheduled'
    //     // ];
        
    //     // return [
    //     //     'exam_id'     => $this->exam->id,
    //     //     'title'       => 'Ujian CBT Baru Dijadwalkan!',
    //     //     'subject'     => optional($this->exam->subject)->name ?? 'Mata Pelajaran',
    //     //     'exam_title'  => $this->exam->title ?? $this->exam->name,
    //     //     'start_time'  => $this->exam->start_time,
    //     //     'duration'    => $this->exam->duration_minutes . ' Menit',
    //     //     // Tambahkan info token (akan bernilai null jika belum di-generate)
    //     //     'token'       => $this->exam->token ?? null, 
    //     //     'url'         => route('student.exam.start', $this->exam->id),
    //     //     'icon'        => 'bx-edit',
    //     //     'type'        => 'cbt_scheduled'
    //     // ];
    //     // Cek apakah ujian sudah memiliki token
    //     $tokenInfo = !empty($this->exam->token) ? ' [Token: ' . $this->exam->token . ']' : '';

    //     return [
    //         'exam_id'     => $this->exam->id,
    //         // Gabungkan info token langsung ke judul agar otomatis tampil di header
    //         'title'       => 'Ujian CBT Baru Dijadwalkan!' . $tokenInfo,
    //         'subject'     => optional($this->exam->subject)->name ?? 'Mata Pelajaran',
    //         'exam_title'  => ($this->exam->title ?? $this->exam->name) . $tokenInfo,
    //         'start_time'  => $this->exam->start_time,
    //         'duration'    => $this->exam->duration_minutes . ' Menit',
    //         'token'       => $this->exam->token ?? null,
    //         'url'         => route('student.exam.start', $this->exam->id),
    //         'icon'        => 'bx-edit',
    //         'type'        => 'cbt_scheduled'
    //     ];
    // }

    public function toArray(object $notifiable): array
    {
        // 1. Format Waktu Mulai & Selesai
        $startTime = $this->exam->start_time 
            ? \Carbon\Carbon::parse($this->exam->start_time)->translatedFormat('d M Y, H:i') 
            : '-';
        $endTime = $this->exam->end_time 
            ? \Carbon\Carbon::parse($this->exam->end_time)->translatedFormat('d M Y, H:i') 
            : '-';

        // 2. Format Informasi Tambahan
        $subjectName = optional($this->exam->subject)->name ?? 'Mata Pelajaran';
        $examTitle   = $this->exam->title ?? $this->exam->name ?? 'Ujian CBT';
        $tokenInfo   = !empty($this->exam->token) ? " | Token: {$this->exam->token}" : '';

        // 3. Gabungkan seluruh detail ke dalam 'title' agar terbaca di UI Header
        $fullNotificationText = "Ujian CBT: {$subjectName} ({$examTitle})\n"
            . "Waktu: {$startTime} - {$endTime} WIB{$tokenInfo}";

        return [
            'exam_id'     => $this->exam->id,
            'title'       => $fullNotificationText,
            'subject'     => $subjectName,
            'exam_title'  => $examTitle,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'duration'    => $this->exam->duration_minutes . ' Menit',
            'token'       => $this->exam->token ?? null,
            'url'         => route('student.exam.start', $this->exam->id),
            'icon'        => 'bx-edit',
            'type'        => 'cbt_scheduled'
        ];
    }
}