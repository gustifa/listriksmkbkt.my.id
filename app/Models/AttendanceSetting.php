<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    // HANYA kolom database yang boleh ada di sini
    protected $fillable = [
        'start_check_in_time',
        'late_limit_time',
        'early_departure_time'
    ];
}
