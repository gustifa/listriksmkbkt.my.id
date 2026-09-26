<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'waktu_hadir',
        'latitude', 
        'longitude', 
    ];


    /**
     * Relasi ke Model User (Pemilik Absensi)
     */
    public function user()
    {
        // Parameter 'user_id' adalah nama kolom di tabel absensi Bapak
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Model Kegiatan (Opsional, agar bisa panggil $absensi->kegiatan->nama)
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

}
