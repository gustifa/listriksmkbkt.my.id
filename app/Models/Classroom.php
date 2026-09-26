<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // Pastikan Trait ini ada

class Classroom extends Model
{
    use HasUuid;

    protected $fillable = ['name', 'major_id', 'homeroom_teacher_id', 'counseling_teacher_id', 'class_leader_id', 'is_pkl_active'];

    // Relasi: Satu Kelas punya banyak Siswa
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Relasi: Satu Kelas punya banyak Jadwal Pelajaran
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Relasi ke Guru sebagai Wali Kelas
     * (Menggunakan nama method 'homeroomTeacher' agar sesuai dengan controller)
     */
    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * Relasi ke Guru sebagai Guru BK
     */
    public function counselingTeacher()
    {
        return $this->belongsTo(Teacher::class, 'counseling_teacher_id');
    }

    /**
     * Relasi ke Siswa sebagai Ketua Kelas
     */
    public function classLeader()
    {
        return $this->belongsTo(Student::class, 'class_leader_id');
    }



}
