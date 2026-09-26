<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentViolation extends Model {
    use HasUuids;
    protected $fillable = ['student_id', 'violation_type_id', 'date', 'note', 'reported_by'];

    public function type() {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }
    
    public function student() {
        return $this->belongsTo(Student::class);
    }
}