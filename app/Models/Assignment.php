<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'classroom_id',
        'title',
        'instructions',
        'file_path',
        'due_date',
        'max_score'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // --- TAMBAHKAN RELASI TEACHER DI SINI ---
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}