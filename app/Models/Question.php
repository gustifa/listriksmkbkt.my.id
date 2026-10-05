<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'score_weight'   => 'integer',
    ];

    public function exam() { return $this->belongsTo(Exam::class); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
