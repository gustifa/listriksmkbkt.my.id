<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'program_id',
        'workshop_teacher_id',
    ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    // Relasi ke Program (opsional jika ada Model Program)
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    // Relasi ke Guru Bengkel (opsional jika ada Model Teacher)
    public function workshopTeacher()
    {
        return $this->belongsTo(Teacher::class, 'workshop_teacher_id');
    }
}