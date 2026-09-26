<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_number',
        'message',
        'status',
        'api_response',
    ];

    /**
     * Helper untuk membersihkan log lama (lebih dari 7 hari)
     */
    public static function pruneOldLogs()
    {
        return self::where('created_at', '<', now()->subDays(7))->delete();
    }
}