<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IzinSiswa extends Model
{
    use HasUuids; // Pastikan pakai ini karena tabel Anda pakai UUID

    protected $fillable = ['student_id', 'date', 'reason', 'status', 'wa_number'];

    // Relasi ke model Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
