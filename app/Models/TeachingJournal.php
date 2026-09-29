<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'topic',
        'activity',
        'notes',
        'photo_evidence',
        'date'
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relasi ke Mata Pelajaran via Schedule
     */
    public function subject()
    {
        return $this->hasOneThrough(
            Subject::class,
            Schedule::class,
            'id',          // Foreign key di tabel schedules (schedules.id)
            'id',          // Foreign key di tabel subjects (subjects.id)
            'schedule_id', // Local key di tabel teaching_journals
            'subject_id'   // Local key di tabel schedules
        );
    }

    /**
     * Relasi ke Guru via Schedule
     */
    public function teacher()
    {
        return $this->hasOneThrough(
            User::class,     // Ubah ke Teacher::class jika menggunakan model Teacher
            Schedule::class,
            'id',          // Foreign key di tabel schedules (schedules.id)
            'id',          // Foreign key di tabel users/teachers (users.id)
            'schedule_id', // Local key di tabel teaching_journals
            'teacher_id'   // Local key di tabel schedules
        );
    }
}
