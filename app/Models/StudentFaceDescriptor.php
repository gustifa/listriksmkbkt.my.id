<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFaceDescriptor extends Model
{
    use HasFactory, HasUuids;

    /**
     * Nama tabel yang terhubung di database
     *
     * @var string
     */
    protected $table = 'student_face_descriptors';

    /**
     * Konfigurasi Primary Key berbasis UUID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang dapat diisi secara massal (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'descriptor',
        'label',
    ];

    /**
     * Casting otomatis JSON ke Array PHP dan sebaliknya
     *
     * @var array<string, string>
     */
    protected $casts = [
        'descriptor' => 'array',
    ];

    /**
     * Relasi ke Model Student (Setiap descriptor milik 1 siswa)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
