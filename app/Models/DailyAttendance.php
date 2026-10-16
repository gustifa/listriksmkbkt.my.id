<?php

namespace App\Models;

use App\Traits\BelongsToActiveAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DailyAttendance extends Model
{
    use HasFactory, BelongsToActiveAcademicYear, HasUuid, LogsActivity;

    protected $fillable = [
        'student_id',
        'date',
        'arrival_time',
        'departure_time',
        'status',
        'recorded_by',
        'photo_in',
        'photo_out',
        'academic_year_id', // Tambahkan ini
    ];

    // Konfigurasi Spatie Log v5.1
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logAll()
    //         ->logOnlyDirty()
    //         ->useLogName('Presensi Gerbang');
    // }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty()
        ->useLogName('Presensi Gerbang')
        ->setDescriptionForEvent(function (string $eventName) {
            $studentName = $this->student->name ?? 'Siswa';

            return match ($eventName) {
                'created' => "Siswa {$studentName} berhasil melakukan presensi gerbang",
                'updated' => "Mengubah data presensi gerbang siswa {$studentName}",
                'deleted' => "Membatalkan/menghapus presensi gerbang siswa {$studentName}",
                default   => "Aktivitas presensi gerbang siswa {$studentName} ({$eventName})"
            };
        });
    }

    // Aktifkan kembali fungsi konfigurasi log ini
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logAll()
    //         ->logOnlyDirty()
    //         ->dontLogEmptyChanges() // <-- Ubah bagian ini
    //         ->useLogName('Manajemen Berita');
    // }

    

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected static function booted()
    {
        // Panggil boot trait jika menggunakan trait
        static::bootBelongsToActiveAcademicYear();

        // Otomatis isi academic_year_id saat membuat data baru jika belum diisi
        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $model->academic_year_id = AcademicYear::where('is_active', true)->value('id');
            }
        });
    }
}
