<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InternshipTimeline extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Helper untuk cek status otomatis berdasarkan tanggal
    public function getCalculatedStatusAttribute()
    {
        $now = Carbon::now();
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return 'completed'; // Sudah lewat
        }
        
        if ($now->gte($this->start_date)) {
            return 'active'; // Sedang berlangsung
        }

        return 'upcoming'; // Belum mulai
    }
}