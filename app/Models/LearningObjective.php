<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LearningObjective extends Model
{
    use HasUuids; // PENTING: Mendukung otomatis pengenerate-an UUID

    protected $table = 'learning_objectives';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_id',
        'academic_year_id',
        'code',
        'description',
        'phase',
        'semester',
    ];

    // Relasi ke Mata Pelajaran
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Relasi ke Tahun Ajaran
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    // Relasi ke Tugas-tugas
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'learning_objective_id');
    }
}