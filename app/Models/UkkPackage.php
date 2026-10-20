<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UkkPackage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ukk_packages';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['major_code', 'major_name', 'package_name', 'academic_year', ];

    public function units()
    {
        return $this->hasMany(UkkUnit::class, 'ukk_package_id');
    }

    public function assessments()
    {
        return $this->hasMany(UkkAssessment::class, 'ukk_package_id');
    }
}