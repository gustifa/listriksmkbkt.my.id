<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScannerDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_token',
        'device_name',
        'status',
        'last_active_at'
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];
}
