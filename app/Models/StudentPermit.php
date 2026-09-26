<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentPermit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_permits';

    protected $fillable = [
        'student_id',
        'date',
        'time_out',
        'time_in',
        'reason',
        'description',
        'status',
        'method',
        'image_evidence',
        'recorded_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
