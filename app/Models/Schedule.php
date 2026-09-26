<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Schedule extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    // protected $guarded = [];
     protected $fillable = [
        'teacher_id',
        'classroom_id',
        'subject_id',
        'room_id', // Pastikan ini ditambahkan
        'day',
        'start_time',
        'end_time',
        // 'room' // Kolom string lama bisa dihapus atau dibiarkan sebagai fallback
    ];

    /**
     * Relasi: Jadwal ini milik Guru siapa?
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Relasi: Jadwal ini untuk Kelas apa?
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relasi: Jadwal ini Mata Pelajaran apa?
     * (Fungsi ini yang sebelumnya hilang menyebabkan error)
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relasi: Jadwal ini punya banyak Absensi
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke Ruangan
    public function room()
    {
        return $this->belongsTo(Room::class);
    }


}
