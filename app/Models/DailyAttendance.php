<?php

namespace App\Models;

use App\Traits\BelongsToActiveAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class DailyAttendance extends Model
{
    use HasFactory, BelongsToActiveAcademicYear, HasUuid;

    protected $fillable = [
        'student_id',
        'date',
        'arrival_time',
        'departure_time',
        'status',
        'recorded_by',
        'photo_in',
        'photo_out',
        'academic_year_id', // Tambahkan ini
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
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
