<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'program_teacher_id',
    ];

    /**
     * Relasi ke Majors (Satu Program Keahlian memiliki banyak Konsentrasi/Major)
     */
    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    public function teacher()
    {
        // Hubungkan ke tabel Teacher melalui kolom program_teacher_id
        return $this->belongsTo(Teacher::class, 'program_teacher_id');
    }

    /**
     * Relasi ke Majors (Satu Program Keahlian memiliki banyak Konsentrasi/Major)
     */
    // public function majors()
    // {
    //     return $this->hasMany(Major::class, 'program_id');
    // }
}
