<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\StudentViolation;
use App\Models\ViolationType;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutoRecordAbsentViolations extends Command
{
    protected $signature = 'attendance:record-absent-violations';
    protected $description = 'Otomatis mencatat poin pelanggaran alpa bagi siswa yang tidak absen hari ini';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // 1. CEK HARI LIBUR / AKHIR PEKAN
        if (Carbon::today()->isWeekend()) {
            $this->info('Hari ini adalah akhir pekan (Sabtu/Minggu). Proses dilewati.');
            return 0;
        }

        // 2. Cari Jenis Pelanggaran 'Tanpa Keterangan' / 'Tampa Keterangan' / 'Alpa'
        $violationType = ViolationType::where(function ($query) {
            $query->where('name', 'ILIKE', '%tanpa keterangan%')
                  ->orWhere('name', 'ILIKE', '%tampa keterangan%')
                  ->orWhere('name', 'ILIKE', '%alpa%')
                  ->orWhere('name', 'ILIKE', '%alpha%');
        })->first();

        if (!$violationType) {
            $this->error('Jenis pelanggaran Tanpa Keterangan / Alpa tidak ditemukan di database.');
            return 1;
        }

        // 3. Ambil seluruh ID siswa yang SUDAH ABSEN hari ini
        $attendedStudentIds = DailyAttendance::where('date', $today)
            ->pluck('student_id')
            ->toArray();

        // 4. PERUBAHAN UTAMA: Ambil siswa aktif HANYA dari KELAS yang OTOMATISASI ALPA-NYA AKTIF
        $absentStudents = Student::where('status', 'active')
            ->whereHas('classroom', function ($query) {
                $query->where('is_auto_absent_enabled', true);
            })
            ->whereNotIn('id', $attendedStudentIds)
            ->get();

        $count = 0;

        foreach ($absentStudents as $student) {
            StudentViolation::firstOrCreate(
                [
                    'student_id'        => $student->id,
                    'date'              => $today,
                    'violation_type_id' => $violationType->id,
                ],
                [
                    'id'          => (string) Str::uuid(),
                    'note'        => 'Tanpa Keterangan / Tidak mengambil absensi harian',
                    'reported_by' => null, // null untuk otomatisasi sistem
                    'is_synced'   => false,
                ]
            );

            $count++;
        }

        $this->info("Berhasil mencatat {$count} siswa alpa ke tabel pelanggaran.");
        return 0;
    }
}