<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RamadanJournal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'date', 'fasting_status',
        'prayer_subuh', 'prayer_dzuhur', 'prayer_ashar', 'prayer_maghrib', 'prayer_isya',
        'prayer_tarawih', 'prayer_witir', 'prayer_dhuha', 'prayer_tahajud',
        'read_quran', 'surah_name', 'ayat_range', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'prayer_subuh' => 'boolean',
        'prayer_dzuhur' => 'boolean',
        'prayer_ashar' => 'boolean',
        'prayer_maghrib' => 'boolean',
        'prayer_isya' => 'boolean',
        'prayer_tarawih' => 'boolean',
        'prayer_witir' => 'boolean',
        'prayer_dhuha' => 'boolean',
        'prayer_tahajud' => 'boolean',
        'read_quran' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}