<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Teacher extends Model
{
    // Jika kolom primary key Anda adalah string UUID
    protected $keyType = 'string';
    public $incrementing = false;

    // Pastikan nama tabel benar
    protected $table = 'teachers';
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'name',
        'nip',
        'gender',
        'phone',
        'place_of_birth',
        'date_of_birth',
        'address',
        'education_level',
        'major_id',
        'golongan',
        'pangkat',
        'tugas_tambahan',
        'signature', // Tambahan baru
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'id');
    }

    /**
     * Relasi ke User (Akun Login)
     */
    // public function user()
    // {
        
    //     return $this->belongsTo(User::class);
    // }

    /**
     * Relasi ke Jadwal Mengajar
     * (Opsional: Jika nanti jadwal ingin direlasikan ke Teacher, bukan User)
     */
    public function schedules()
    {
        // Asumsi saat ini schedule masih pakai teacher_id (user_id)
        // Jika nanti diubah, relasinya bisa lewat sini
        return $this->hasMany(Schedule::class, 'teacher_id', 'user_id');
    }

    public function teacher()
    {
         return $this->belongsTo(User::class);
    }

    // public function major()
    // {
    //     return $this->belongsTo(Major::class);
    // }

    public function assignments() {
    return $this->hasMany(TeachingAssignment::class);
    }

    
    // --- FITUR BARU: RELASI PKL ---

    /**
     * Relasi ke Internships dimana guru ini diset sebagai Pembimbing (advisor_id)
     */
    public function internships()
    {
        return $this->hasMany(Internship::class, 'advisor_id');
    }

    /**
     * Helper: Cek apakah guru ini memiliki setidaknya satu siswa bimbingan PKL
     * Digunakan di Blade untuk menyembunyikan menu
     */
    public function isAdvisor()
    {
        return $this->internships()->exists();
    }
}
