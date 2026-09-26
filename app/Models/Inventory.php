<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Inventory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'room_id',
        'name',
        'code',
        'brand',
        'category',
        'quantity',
        'unit',
        'condition',
        'purchase_date',
        'description'
    ];

    /**
     * Relasi ke Ruangan/Bengkel
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
