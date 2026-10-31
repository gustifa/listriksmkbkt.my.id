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
        'end_check_in_time',
        'early_departure_time',
        'latitude',
        'longitude',
        'radius_meters',
        'is_geofence_active', // <--- Tambahkan ini
        'auto_absent_time', // <--- Tambahkan ini
        'send_photo_telegram',
        'learning_mode',
        'enable_telegram_notification',
        'enable_wa_notification',
        'telegram_bot_token',
        'telegram_admin_chat_id',
        'telegram_bot_username',
        'telegram_admin_group_link',
    ];

    protected $casts = [
        'send_photo_telegram' => 'boolean',
        'is_geofence_active'  => 'boolean',
        'enable_telegram_notification' => 'boolean',
        'enable_wa_notification'       => 'boolean',
    ];
}
