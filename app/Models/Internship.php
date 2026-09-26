<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Internship extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'industry_id', 'advisor_id', 'start_date', 'end_date', 'status', 'advisor_status', 'parent_consent_file'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'advisor_id');
    }

     /**
     * Relasi ke InternshipGrade (Nilai PKL)
     * One-to-One: Satu internship punya satu nilai akhir.
     */
    public function grade()
    {
        return $this->hasOne(InternshipGrade::class);
    }
}
