<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ViolationType extends Model {
    use HasUuids;
    protected $fillable = ['name', 'points', 'category'];

    public function violation_type()
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }
}