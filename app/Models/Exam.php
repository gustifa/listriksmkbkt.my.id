<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exam extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    // public function classrooms() { return $this->belongsToMany(Classroom::class, 'classroom_exam'); }
    public function questions() { return $this->hasMany(Question::class); }
    public function sessions() { return $this->hasMany(ExamSession::class); }

    // Relasi Guru Kolaborator (Team Teaching)
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'exam_teacher', 'exam_id', 'teacher_id')
                    ->using(ExamTeacherPivot::class)
                    ->withTimestamps();
    }

    // Relasi Target Kelas
    // public function classrooms(): BelongsToMany
    // {
    //     return $this->belongsToMany(Classroom::class, 'classroom_exam', 'exam_id', 'classroom_id')
    //                 ->withTimestamps();
    // }

    // Ubah bagian ini di app/Models/Exam.php:
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_exam', 'exam_id', 'classroom_id');
    }

    // Helper Pengecekan Hak Akses Guru (Pembuat Utama atau Kolaborator)
    public function hasAccess(?string $teacherId): bool
    {
        if (!$teacherId) return false;
        return $this->teacher_id === $teacherId || $this->collaborators()->where('teachers.id', $teacherId)->exists();
    }
}