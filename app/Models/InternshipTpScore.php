<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InternshipTpScore extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scores' => 'array',
        'notes'  => 'array',
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

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }
}