<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid; // <--- 1. Import Trait

class TeachingAssignment extends Model
{
    use HasUuid; // <--- 2. Pasang Trait
    protected $fillable = ['teacher_id', 'subject_id', 'classroom_id', 'academic_year'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
