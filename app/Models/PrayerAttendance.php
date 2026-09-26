<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PrayerAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'date',
        'prayer_name',
        'check_in_time',
        'status',
        'photo_evidence',
        'notes',
        'latitude',
        'longitude'

    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke Absensi Sholat
    public function prayer_attendance() {
        return $this->hasMany(PrayerAttendance::class);
    }
}
