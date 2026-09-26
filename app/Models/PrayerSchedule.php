<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayerSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'subuh',
        'dhuha',
        'dzuhur',
        'ashar',
        'maghrib',
        'isya',
    ];

    // Casting agar date dibaca sebagai Carbon object
    protected $casts = [
        'date' => 'date',
    ];
}
