<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasUuids;

    protected $fillable = ['year', 'semester', 'is_active'];

    // Scope untuk mengambil Semester & Tahun Ajaran yang Aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
