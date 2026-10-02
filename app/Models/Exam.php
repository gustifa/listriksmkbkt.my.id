<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function classrooms() { return $this->belongsToMany(Classroom::class, 'classroom_exam'); }
    public function questions() { return $this->hasMany(Question::class); }
    public function sessions() { return $this->hasMany(ExamSession::class); }
}