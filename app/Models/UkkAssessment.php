<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UkkAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ukk_assessments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'ukk_package_id',
        'exam_date',
        'internal_examiner_id',
        'external_examiner_id',
        'score_internal',
        'score_external',
        'final_score',
        'status',
        'notes'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function package()
    {
        return $this->belongsTo(UkkPackage::class, 'ukk_package_id');
    }

    public function internalExaminer()
    {
        return $this->belongsTo(Teacher::class, 'internal_examiner_id');
    }

    // public function externalExaminer()
    // {
    //     return $this->belongsTo(User::class, 'external_examiner_id');
    // }

    public function externalExaminer()
{
    return $this->belongsTo(ExternalExaminer::class, 'external_examiner_id');
}
}