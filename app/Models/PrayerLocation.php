<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayerLocation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'prayer_locations';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
    ];

    // CASTING TIPE DATA AGAR POSTGRESQL MEMBACA TRUE/FALSE SECARA PRESISI
    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
    ];
}