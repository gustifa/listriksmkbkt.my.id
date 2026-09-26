<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Subject extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['code', 'name', 'major_id'];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function assignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
