<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermitReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'is_active',
    ];
}
