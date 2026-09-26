<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventoryTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'item_id',
        'user_id', // Siapa yang mencatat
        'type',    // 'in' (masuk) atau 'out' (keluar)
        'quantity',
        'date',
        'notes',
        'funding_source',
        'year',
        'receiver'
    ];

    /**
     * Relasi ke master barang
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi ke user/admin yang mencatat
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
