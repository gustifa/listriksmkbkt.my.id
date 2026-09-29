<?php

namespace App\Imports;

use App\Models\InternshipAttendance;
use App\Models\Internship;
use App\Models\Student;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Lewati baris yang benar-benar kosong total
        if (empty(array_filter($row))) {
            return null;
        }

        // 2. Cek Header NIS / NISN
        $rawNis = $row['nisn'] ?? $row['nis'] ?? null;
        if (empty($rawNis)) {
            // Tampilkan error jika header nis/nisn tidak terdeteksi
            $availableKeys = implode(', ', array_keys($row));
            throw new \Exception("Kolom 'nisn' / 'nis' tidak ditemukan pada Excel. Kolom yang terbaca di file Excel Anda adalah: [{$availableKeys}]");
        }

        // 3. Cek Header Tanggal
        if (empty($row['tanggal'])) {
            throw new \Exception("Kolom 'tanggal' kosong atau tidak ditemukan pada file Excel.");
        }

        $nis = trim((string) $rawNis);

        // 4. Cari Siswa di database
        $student = Student::where('nis', $nis)->first();
        if (!$student) {
            throw new \Exception("Siswa dengan NIS '{$nis}' tidak ditemukan di tabel students.");
        }

        // 5. Cari Internship siswa
        $internship = Internship::where('student_id', $student->id)->first();

        // 6. Parsing Tanggal
        try {
            if (is_numeric($row['tanggal'])) {
                $date = Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
            } else {
                $date = Carbon::parse($row['tanggal'])->format('Y-m-d');
            }
        } catch (\Exception $e) {
            throw new \Exception("Gagal membaca format tanggal: '{$row['tanggal']}' untuk NIS: {$nis}");
        }

        // 7. Format Jam
        $timeIn  = !empty($row['jam_masuk']) ? $this->formatTime($row['jam_masuk']) : null;
        $timeOut = !empty($row['jam_pulang']) ? $this->formatTime($row['jam_pulang']) : null;

        // 8. Mapping Status
        $statusInput = strtolower(trim($row['status'] ?? 'present'));
        $statusMap = [
            'hadir'   => 'present',
            'present' => 'present',
            'sakit'   => 'sick',
            'sick'    => 'sick',
            'izin'    => 'permit',
            'permit'  => 'permit',
        ];
        $status = $statusMap[$statusInput] ?? 'present';

        // 9. Simpan / Update Data Absensi
        return InternshipAttendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date'       => $date,
            ],
            [
                'internship_id'  => $internship ? $internship->id : null,
                'time'           => $timeIn,
                'check_out_time' => $timeOut,
                'status'         => $status,
                'activity_log'   => $row['jurnal'] ?? null,
            ]
        );
    }

    private function formatTime($value)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}