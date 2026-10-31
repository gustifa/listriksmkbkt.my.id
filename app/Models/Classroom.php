<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // Pastikan Trait ini ada


class Classroom extends Model
{
    use HasUuid;

    protected $fillable = ['name', 'telegram_chat_id', 'major_id', 'homeroom_teacher_id', 'counseling_teacher_id', 'class_leader_id', 'is_pkl_active', 'is_active'];

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

//     public function scopeAccessibleBy($query, User $user)
// {
//     // Admin & Super Admin melihat semua
//     if ($user->hasRole(['admin', 'super-admin']) || ($user->can('classrooms.view-all') && !$user->hasRole(['kabeng', 'kaproka', 'walas', 'bk']))) {
//         return $query;
//     }

//     $teacher = $user->teacher;
//     if (!$teacher) {
//         return $query->whereRaw('1 = 0');
//     }

//     return $query->where(function ($q) use ($user, $teacher) {

//         // WALAS
//         if ($user->hasRole('walas')) {
//             $q->orWhere('homeroom_teacher_id', $teacher->id);
//         }

//         // BK
//         if ($user->hasRole('bk')) {
//             $q->orWhere('counseling_teacher_id', $teacher->id);
//         }

//         // KABENG: Cek via workshop_teacher_id di majors ATAU major_id langsung milik guru
//         if ($user->hasRole('kabeng')) {
//             $q->orWhereHas('major', function ($m) use ($teacher) {
//                 $m->where('workshop_teacher_id', $teacher->id);
//             });

//             if ($teacher->major_id) {
//                 $q->orWhere('major_id', $teacher->major_id);
//             }
//         }

//         // KAPROKA: Cek via program_teacher_id di programs ATAU major_id milik guru
//         if ($user->hasRole('kaprog')) {
//             $q->orWhereHas('major.program', function ($p) use ($teacher) {
//                 $p->where('program_teacher_id', $teacher->id);
//             });

//             if ($teacher->major_id) {
//                 $q->orWhere('major_id', $teacher->major_id);
//             }
//         }

//     });
// }

    public function scopeAccessibleBy($query, User $user)
{
    // Admin & Super Admin melihat semua
    if ($user->hasRole(['admin', 'super_admin', 'super-admin']) || ($user->can('classrooms.view-all') && !$user->hasRole(['kabeng', 'kaprog', 'wali_kelas', 'guru_bk']))) {
        return $query;
    }

    $teacher = $user->teacher;
    if (!$teacher) {
        return $query->whereRaw('1 = 0');
    }

    return $query->where(function ($q) use ($user, $teacher) {

        // WALI KELAS
        if ($user->hasRole(['wali_kelas', 'walas'])) {
            $q->orWhere('homeroom_teacher_id', $teacher->id);
        }

        // GURU BK
        if ($user->hasRole(['guru_bk', 'bk'])) {
            $q->orWhere('counseling_teacher_id', $teacher->id);
        }

        // KABENG
        if ($user->hasRole('kabeng')) {
            $q->orWhereHas('major', function ($m) use ($teacher) {
                $m->where('workshop_teacher_id', $teacher->id);
            });
            if ($teacher->major_id) {
                $q->orWhere('major_id', $teacher->major_id);
            }
        }

        // KAPROG / KAPROKA
        if ($user->hasRole(['kaprog', 'kaproka'])) {
            $q->orWhereHas('major.program', function ($p) use ($teacher) {
                $p->where('program_teacher_id', $teacher->id);
            });
            if ($teacher->major_id) {
                $q->orWhere('major_id', $teacher->major_id);
            }
        }

    });
}



}
