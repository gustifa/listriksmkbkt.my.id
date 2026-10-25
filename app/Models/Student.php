<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // <--- 1. Import Trait
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import Trait SoftDeletes

class Student extends Model
{
    use HasUuid, SoftDeletes; // <--- 2. Pasang Trait
    // protected $guarded = [];
//     protected $fillable = [
//     'nis',
//     'name',
//     'gender',
//     'classroom_id',
//     'phone', // Pastikan ini ada jika Anda mengimport no_hp
//     'user_id',
//     'signature', // Tambahan baru
//     'status',
//     'last_classroom_id',
//     'is_promoted'
// ];

    protected $fillable = [
        'id', 'user_id', 'nis', 'nisn', 'nik', 'name', 'gender', 'pob', 'dob', 'religion',
        'phone', 'email', 'address', 'rt', 'rw', 'dusun', 'subdistrict', 'district', 'postal_code',
        'status', 'classroom_id', 'is_promoted', 'previous_school',
        'father_name', 'father_nik', 'father_education', 'father_job', 'father_income',
        'mother_name', 'mother_nik', 'mother_education', 'mother_job', 'mother_income',
        'guardian_name', 'guardian_nik', 'guardian_education', 'guardian_job', 'guardian_income',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Relasi ke Absensi
    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke tabel daily_attendances
    public function dailyAttendances()
    {
        return $this->hasMany(DailyAttendance::class, 'student_id');
    }

    // Relasi ke Absensi Sholat
    public function prayer_attendance() {
        return $this->hasMany(PrayerAttendance::class);
    }

    public function violationPoints()
    {
        // Student has many ViolationType THROUGH Violation
        return $this->hasManyThrough(
            ViolationType::class,
            Violation::class,
            'student_id',        // Foreign key di tabel violations
            'id',                // Foreign key di tabel violation_types (biasanya id)
            'id',                // Local key di tabel students
            'violation_type_id'  // Local key di tabel violations
        );
    }

    /**
     * Relasi ke tabel internships (Tempat PKL)
     */
    public function internships()
    {
        return $this->hasMany(Internship::class);
    }

    /**
     * Relasi ke riwayat pelanggaran (Bimbingan Konseling)
     */
    public function violations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    /**
     * Relasi ke riwayat pembinaan / konseling
     */
    public function guidances()
    {
        return $this->hasMany(StudentGuidance::class);
    }

    /**
     * Relasi ke Kelas Terakhir (Sebelum Lulus)
     */
    public function lastClassroom()
    {
        return $this->belongsTo(Classroom::class, 'last_classroom_id');
    }

    /**
     * Relasi ke Data Alumni
     */
    public function alumni()
    {
        return $this->hasOne(Alumni::class, 'student_id');
    }

    public function permits()
    {
        return $this->hasMany(StudentPermission::class, 'student_id');
    }

    /**
     * Relasi ke FaceDescriptor
     */


    public function faceDescriptors(): HasMany
    {
        return $this->hasMany(StudentFaceDescriptor::class, 'student_id', 'id');
    }


    /**
     * Scope untuk memfilter siswa sesuai role dan atribut user yang login.
     */
    // public function scopeAccessibleBy($query, User $user)
    // {
    //     // 1. Admin & Super Admin: Bisa melihat semua siswa
    //     if ($user->hasRole(['admin', 'super-admin']) || $user->can('students.view-all')) {
    //         return $query;
    //     }

    //     $teacher = $user->teacher;

    //     // Jika bukan admin dan tidak terikat data guru, blokir akses
    //     if (!$teacher) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where(function ($q) use ($user, $teacher) {

    //         // 2. WALAS: Hanya siswa di kelas yang diwalikannya
    //         if ($user->hasRole('walas')) {
    //             $classroomIds = $teacher->classrooms()->pluck('id');
    //             $q->orWhereIn('classroom_id', $classroomIds);
    //         }

    //         // 3. KAPROKA & KABENG: Hanya siswa di jurusannya (Major)
    //         if ($user->hasRole(['kaproka', 'kabeng'])) {
    //             if ($teacher->major_id) {
    //                 $q->orWhereHas('classroom', function ($classQuery) use ($teacher) {
    //                     $classQuery->where('major_id', $teacher->major_id);
    //                 });
    //             }
    //         }

    //         // 4. BK: Siswa di jurusan binaannya (atau sesuaikan dengan kebutuhan)
    //         if ($user->hasRole('bk')) {
    //             if ($teacher->major_id) {
    //                 $q->orWhereHas('classroom', function ($classQuery) use ($teacher) {
    //                     $classQuery->where('major_id', $teacher->major_id);
    //                 });
    //             } else {
    //                 $q->orWhereRaw('1 = 1'); // Jika BK diberi akses ke semua siswa
    //             }
    //         }

    //     });
    // }

    public function scopeAccessibleBy($query, User $user)
{
    // Admin & Super Admin melihat seluruh siswa
    if ($user->hasRole(['admin', 'super-admin']) || ($user->can('students.view-all') && !$user->hasRole(['kabeng', 'kaproka', 'walas', 'bk']))) {
        return $query;
    }

    $teacher = $user->teacher;
    if (!$teacher) {
        return $query->whereRaw('1 = 0');
    }

    return $query->where(function ($q) use ($user, $teacher) {

        // WALAS: Siswa di kelas yang diwalikannya
        if ($user->hasRole('walas')) {
            $q->orWhereHas('classroom', function ($c) use ($teacher) {
                $c->where('homeroom_teacher_id', $teacher->id);
            });
        }

        // BK: Siswa di kelas yang dibimbingnya
        if ($user->hasRole('bk')) {
            $q->orWhereHas('classroom', function ($c) use ($teacher) {
                $c->where('counseling_teacher_id', $teacher->id);
            });
        }

        // KABENG: Siswa di jurusan/bengkel yang dipimpin (majors.workshop_teacher_id)
        if ($user->hasRole('kabeng')) {
            $q->orWhereHas('classroom.major', function ($m) use ($teacher) {
                $m->where('workshop_teacher_id', $teacher->id);
            });
        }

        // KAPROKA: Siswa di Program Keahlian yang dipimpin (programs.program_teacher_id)
        if ($user->hasRole('kaprog')) {
            $q->orWhereHas('classroom.major.program', function ($p) use ($teacher) {
                $p->where('program_teacher_id', $teacher->id);
            });
        }

    });
}


}
