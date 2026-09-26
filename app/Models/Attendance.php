<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class Attendance extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    protected $guarded = [];

    // --- BAGIAN INI YANG HILANG/ERROR ---

    // Relasi ke Siswa (belongsTo = Milik satu siswa)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke Jadwal (belongsTo = Milik satu jadwal)
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Relasi ke Guru (User)
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // Relasi ke Kelas
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // --- BAGIAN INI YANG HILANG ---
    // Relasi ke Mata Pelajaran (Subject)
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    // ------------------------------

    // Relasi ke Absensi
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
