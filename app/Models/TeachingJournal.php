<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'topic',
        'activity',
        'notes',
        'photo_evidence',
        'date'
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
