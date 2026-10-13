<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'file_path',
        'submitted_at',
        'score',
        'feedback',
        'graded_at',
        'graded_by',
        'status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function grader()
    {
        return $this->belongsTo(Teacher::class, 'graded_by');
    }
}