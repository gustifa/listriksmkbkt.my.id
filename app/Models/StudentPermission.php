<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class StudentPermission extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'date',
        'time_out',
        'time_back',
        'reason',
        'status'

    ];

    /**
     * Relasi ke Model Student.
     * Izin (Permission) dimiliki oleh satu Siswa.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
