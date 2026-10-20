<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UkkUnit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ukk_units';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['ukk_package_id', 'unit_code', 'unit_title', 'element_name'];

    public function package()
    {
        return $this->belongsTo(UkkPackage::class, 'ukk_package_id');
    }
}