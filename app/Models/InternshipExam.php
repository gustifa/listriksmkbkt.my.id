<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InternshipExam extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'internship_id',
        'examiner_id',
        'exam_date',
        'score_materi',
        'score_gambar',
        'score_sistematika',
        'score_tampilan',
        'subtotal_laporan',
        'score_pakaian',
        'score_bahasa',
        'score_penguasaan',
        'score_jawaban',
        'subtotal_presentasi',
        'total_score',
        'status',
        'revision_notes',
    ];

    protected $casts = [
        'exam_date'           => 'date',
        'score_materi'        => 'float',
        'score_gambar'        => 'float',
        'score_sistematika'   => 'float',
        'score_tampilan'      => 'float',
        'subtotal_laporan'    => 'float',
        'score_pakaian'       => 'float',
        'score_bahasa'        => 'float',
        'score_penguasaan'    => 'float',
        'score_jawaban'       => 'float',
        'subtotal_presentasi' => 'float',
        'total_score'         => 'float',
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

    public function examiner()
    {
        return $this->belongsTo(Teacher::class, 'examiner_id');
    }

    
}