<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Major extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'majors';

    // Konfigurasi Primary Key UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'program_id',
        'workshop_teacher_id',
    ];

    // Relasi ke Program Keahlian
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    // Relasi ke Kepala Bengkel (Guru)
    public function workshopTeacher()
    {
        return $this->belongsTo(Teacher::class, 'workshop_teacher_id');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}