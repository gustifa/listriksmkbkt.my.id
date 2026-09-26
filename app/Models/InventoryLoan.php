<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InventoryLoan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inventory_id',
        'borrower_name',
        'quantity',
        'loan_date',
        'return_date',
        'status',
        'notes'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
