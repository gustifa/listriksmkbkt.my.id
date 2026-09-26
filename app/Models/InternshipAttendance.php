<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InternshipAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'internship_id', 'student_id', 'date', 'time',
        'status', 'activity_log', 'photo_path', 'latitude', 'longitude',
        'check_out_time', 'photo_out_path'
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
