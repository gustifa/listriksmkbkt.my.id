<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MbgAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mbg_attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'date',
        
        // --- Kolom Legacy / Utama ---
        'check_in_time',   // Waktu pertama kali scan (biasanya sama dengan taken_at)
        'status',          // 'taken' (sedang makan) atau 'returned' (selesai)
        'method',          // Metode scan utama
        'image_evidence',  // Foto bukti utama
        
        // --- Data Pengambilan (Take) ---
        'taken_at',
        'taken_method',
        'taken_image',

        // --- Data Pengembalian (Return) ---
        'returned_at',
        'returned_method',
        'returned_image',

        // --- Meta Data ---
        'recorded_by',
    ];

    /**
     * Relasi ke Model Student (Siswa)
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}