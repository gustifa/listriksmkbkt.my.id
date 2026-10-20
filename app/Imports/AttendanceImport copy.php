<?php

namespace App\Imports;

use App\Models\InternshipAttendance; // atau App\Models\Attendance tergantung nama Model Anda
use App\Models\Student;
use App\Models\Internship;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;

class AttendanceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Mengubah key array menjadi lowercase
        $row = array_change_key_case($row, CASE_LOWER);

        $nis       = trim($row['nisn'] ?? $row['nis'] ?? '');
        $rawDate   = trim($row['tanggal'] ?? $row['date'] ?? '');
        $jamMasuk  = trim($row['jam_masuk'] ?? $row['check_in'] ?? '');
        $jamPulang = trim($row['jam_pulang'] ?? $row['check_out'] ?? '');
        $status    = strtolower(trim($row['status'] ?? 'present'));
        $jurnal    = trim($row['jurnal'] ?? $row['journal'] ?? '');

        // Abaikan baris kosong
        if (empty($nis) && empty($rawDate)) {
            return null;
        }

        if (empty($nis)) {
            throw new Exception("Ditemukan baris dengan Tanggal '{$rawDate}' tetapi NIS/NISN kosong.");
        }

        // 1. Cari data siswa berdasarkan kolom 'nis'
        $student = Student::where('nis', $nis)->first();

        if (!$student) {
            throw new Exception("Siswa dengan NIS '{$nis}' tidak ditemukan di database.");
        }

        // 2. Cari data Penempatan PKL Siswa (internship_id)
        $internship = Internship::where('student_id', $student->id)->first();

        if (!$internship) {
            throw new Exception("Siswa dengan NIS '{$nis}' ({{ $student->name }}) belum terdaftar pada data Penempatan PKL (Internship).");
        }

        // 3. Parsing Tanggal & Jam
        $formattedDate      = $this->parseDate($rawDate, $nis);
        $formattedJamMasuk  = $this->parseTime($jamMasuk) ?? '07:00:00'; // Fallback default time jika kosong
        $formattedJamPulang = $this->parseTime($jamPulang);

        // 4. Normalisasi Status
        $mappedStatus = match ($status) {
            'present', 'hadir', 'h' => 'present',
            'sick', 'sakit', 's'     => 'sick',
            'permit', 'izin', 'i'   => 'permit',
            'absent', 'alpa', 'a'   => 'absent',
            default                 => 'present',
        };

        // 5. Simpan / Update ke Tabel internship_attendances
        return InternshipAttendance::updateOrCreate(
            [
                'student_id'    => $student->id,
                'internship_id' => $internship->id,
                'date'          => $formattedDate,
            ],
            [
                'time'           => $formattedJamMasuk,
                'check_out_time' => $formattedJamPulang,
                'status'         => $mappedStatus,
                'activity_log'   => $jurnal ?: null,
            ]
        );
    }

    /**
     * Parsing Tanggal Excel / String ke Format YYYY-MM-DD
     */
    private function parseDate($value, $nis)
    {
        try {
            if (empty($value)) {
                throw new Exception("Tanggal kosong.");
            }

            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            $cleaned = str_replace('/', '-', $value);

            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $cleaned)) {
                return Carbon::createFromFormat('d-m-Y', $cleaned)->format('Y-m-d');
            }

            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $cleaned)) {
                return Carbon::parse($cleaned)->format('Y-m-d');
            }

            return Carbon::parse($cleaned)->format('Y-m-d');

        } catch (Exception $e) {
            throw new Exception("Gagal membaca format tanggal: '{$value}' untuk NIS: {$nis}");
        }
    }

    /**
     * Parsing Jam Excel / String ke Format HH:MM:SS
     */
    private function parseTime($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
}