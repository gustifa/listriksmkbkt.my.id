<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Alumni extends Model
{
    use HasFactory, HasUuids;

    // Nama tabel spesifik di PostgreSQL (alumnis)
    protected $table = 'alumnis';

    // Pengaturan Primary Key berbasis UUID
    protected $keyType = 'string';
    public $incrementing = false;

    // Kolom yang diizinkan untuk dikirim secara Mass-Assignment
    protected $fillable = [
        'id',
        'student_id',
        'graduation_year',
        'graduation_date',
        'current_status',
        'company_or_campus',
        'phone',
        'address',
    ];

    /**
     * Relasi balik ke Model Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}