<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Industry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'sector', 'address', 'contact_person', 'phone', 'quota',
        'latitude', 'longitude', 'radius' // Tambahkan ini
    ];

    public function internships()
    {
        return $this->hasMany(Internship::class);
    }
}