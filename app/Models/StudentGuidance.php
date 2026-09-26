<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentGuidance extends Model {
    use HasUuids;
    protected $fillable = [
        'student_id', 
        'teacher_id', 
        'date', 
        'problem_summary', 
        'advice', 
        'student_commitment', 
        'status', 'role_context', 
        'photo_evidence', 
        'agreement_file',
        'is_summoned',
        'summon_date',
        'summon_time',
        'summon_file'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function teacher() {
        return $this->belongsTo(Teacher::class);
    }
}