<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Kegiatan extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'kegiatans'; // Opsional, Laravel akan otomatis mendeteksi nama jamak bahasa Inggris, tapi baik untuk kejelasan.

    protected $fillable = [
        'nama_kegiatan',
        'tanggal',
        'kode_unik',
        'deskripsi',
        'latitude', 
        'longitude', 
        'waktu_berakhir', 
        'radius'
    ];

    /**
     * Relasi One-to-Many ke tabel Absensi
     * Satu kegiatan bisa memiliki banyak data absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'kegiatan_id');
    }
}