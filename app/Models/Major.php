<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $fillable = [
        'code',
        'name',
        'program_name',
        'head_of_major',
        'head_of_workshop'
        ];

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
