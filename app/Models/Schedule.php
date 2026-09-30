<?php

namespace App\Models;

use App\Traits\BelongsToActiveAcademicYear;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Schedule extends Model
{
    use HasUuid, BelongsToActiveAcademicYear; // <--- 2. Pasang Trait
    // protected $guarded = [];
     protected $fillable = [
        'teacher_id',
        'classroom_id',
        'subject_id',
        'room_id', // Pastikan ini ditambahkan
        'day',
        'start_time',
        'end_time',
        'academic_year_id', // Tambahkan ini
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

    protected static function booted()
    {
        // Panggil boot trait jika menggunakan trait
        static::bootBelongsToActiveAcademicYear();

        // Otomatis isi academic_year_id saat membuat data baru jika belum diisi
        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $model->academic_year_id = AcademicYear::where('is_active', true)->value('id');
            }
        });
    }


}
