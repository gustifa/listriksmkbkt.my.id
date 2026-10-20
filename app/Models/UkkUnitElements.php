<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UkkUnitElements extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['ukk_unit_id', 'element_name', 'category_type', 'work_step'];

    public function unit()
    {
        return $this->belongsTo(UkkUnit::class, 'ukk_unit_id');
    }
}
