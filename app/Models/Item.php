<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Item extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'stock', // Menyimpan total stok saat ini
        'unit',
        'year'
    ];

    /**
     * Relasi ke riwayat transaksi barang (masuk/keluar)
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
