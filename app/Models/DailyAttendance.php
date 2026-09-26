<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class DailyAttendance extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'date',
        'arrival_time',
        'departure_time',
        'status',
        'recorded_by',
        'photo_in',
        'photo_out'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
