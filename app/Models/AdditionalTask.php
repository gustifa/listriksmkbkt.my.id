<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasUuid;

class AdditionalTask extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'hours', 'type'];

    

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'additional_task_id');
    }
}