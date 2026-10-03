<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
    ];

    public function exam() { return $this->belongsTo(Exam::class); }

    public function options()
    {
        // Sesuaikan 'QuestionOption' dengan nama Model Pilihan Jawaban yang ada di project Anda
        return $this->hasMany(QuestionOption::class); 
    }
}