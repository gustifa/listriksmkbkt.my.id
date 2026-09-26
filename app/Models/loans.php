<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Loan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'teacher_id',
        'inventory_id',
        'borrow_date',
        'return_date',
        'status',
        'amount',
        'notes'
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}