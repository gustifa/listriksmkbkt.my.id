<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Tambahkan ini jika pakai UUID

class Room extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'type',
        'location',
        'capacity',
        'description'
    ];

    // Relasi: Satu ruangan bisa dipakai banyak jadwal
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
