<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceScan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'scan_type',
        'image_path',
        'uploaded_by',
        'scan_date',
        'status',
        'notes',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}