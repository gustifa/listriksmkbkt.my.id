<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TpMaster extends Model
{
    use HasFactory;

    // Matikan auto-increment karena menggunakan UUID
    public $incrementing = false;

    // Set tipe primary key sebagai string (UUID)
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'indicators' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }
}