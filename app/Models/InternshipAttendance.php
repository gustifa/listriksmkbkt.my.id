<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. WAJIB IMPORT
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternshipAttendance extends Model
{
    use HasFactory, HasUuids; // 2. WAJIB GUNAKAN TRAIT HASUUIDS

    protected $table = 'internship_attendances';

    // 3. WAJIB SET KEY TYPE & INCREMENTING
    protected $keyType = 'string';
    public $incrementing = false;

    // 4. WAJIB DAFTARKAN SEMUA KOLOM DALAM $fillable
    protected $fillable = [
        'internship_id',
        'student_id',
        'date',
        'time',
        'check_out_time',
        'status',
        'activity_log',
        'photo_path',
        'latitude',
        'longitude',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }
}