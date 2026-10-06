<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
        ];

    public function session() { return $this->belongsTo(ExamSession::class, 'exam_session_id'); }
    public function question() { return $this->belongsTo(Question::class); }
}
