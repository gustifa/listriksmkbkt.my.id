<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramNotification extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'telegram_subscriber_id',
        'recipient_name',
        'chat_id',
        'title',
        'message',
        'type',
        'status',
        'error_log',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function subscriber()
    {
        return $this->belongsTo(TelegramSubscriber::class, 'telegram_subscriber_id');
    }
}