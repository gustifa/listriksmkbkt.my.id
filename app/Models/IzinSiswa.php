
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IzinSiswa extends Model
{
    use HasFactory;

    protected $table = 'izin_siswas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'student_id',
        'type',
        'date',
        'end_date',
        'reason',
        'status',
        'attachment_path',
        'telegram_chat_id',
        'wa_number',
        'approved_by',
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

    /**
     * Relasi ke Model Student (Orang Tua / Anak)
     */
    // Relasi ke model Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
