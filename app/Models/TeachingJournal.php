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
     * Relasi ke Mata Pelajaran (Subject)
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
        // Catatan: Sesuaikan 'subject_id' dengan nama kolom foreign key di tabel teaching_journals Anda jika berbeda.
    }

    /**
     * Relasi ke Guru / Pengajar (Teacher / User)
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
        // Catatan: Jika model guru Anda dinamai Teacher, ubah User::class menjadi Teacher::class.
    }
}
