<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InternshipGrade extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'internship_id', 'student_id', 'teacher_id',
        'discipline', 'teamwork', 'initiative', 'responsibility',
        'technical_mastery', 'work_quality',
        'final_score', 'notes'
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
