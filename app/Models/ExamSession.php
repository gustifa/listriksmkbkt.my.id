<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function exam() { return $this->belongsTo(Exam::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function answers() { return $this->hasMany(ExamAnswer::class); }
}
