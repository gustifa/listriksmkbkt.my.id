<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SyncLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'table_name',
        'records_count',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}