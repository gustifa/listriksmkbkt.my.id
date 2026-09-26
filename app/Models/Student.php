<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Student extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    // protected $guarded = [];
    protected $fillable = [
    'nis',
    'name',
    'classroom_id',
    'phone', // Pastikan ini ada jika Anda mengimport no_hp
    'user_id',
    'signature' // Tambahan baru
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Relasi ke Absensi
    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke tabel daily_attendances
    public function dailyAttendances()
    {
        return $this->hasMany(DailyAttendance::class, 'student_id');
    }

    // Relasi ke Absensi Sholat
    public function prayer_attendance() {
        return $this->hasMany(PrayerAttendance::class);
    }

    public function violationPoints()
    {
        // Student has many ViolationType THROUGH Violation
        return $this->hasManyThrough(
            ViolationType::class,
            Violation::class,
            'student_id',        // Foreign key di tabel violations
            'id',                // Foreign key di tabel violation_types (biasanya id)
            'id',                // Local key di tabel students
            'violation_type_id'  // Local key di tabel violations
        );
    }

    /**
     * Relasi ke tabel internships (Tempat PKL)
     */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    /**
     * Relasi ke riwayat pelanggaran (Bimbingan Konseling)
     */
    public function violations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    /**
     * Relasi ke riwayat pembinaan / konseling
     */
    public function guidances()
    {
        return $this->hasMany(StudentGuidance::class);
    }


}
