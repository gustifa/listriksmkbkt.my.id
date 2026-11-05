<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Inventory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'room_id',
        'type',             // Wajib ada agar tidak terlewat saat insert
        'category',
        'brand_model',
        'quantity',
        'min_quantity',
        'unit',
        'condition',
        'funding_source',
        'acquisition_year',
        'description',
    ];

    /**
     * Relasi ke Ruangan/Bengkel
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }
}
