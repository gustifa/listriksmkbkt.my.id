<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <-- Import trait UUID

class Announcement extends Model
{
    use HasFactory, HasUuids; // <-- Aktifkan trait HasUuids

    // Karena UUID berupa string, pastikan key type dan incrementing disesuaikan
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'content',
        'target_role',
        'classroom_id',
        'attachment',
        'created_by',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}