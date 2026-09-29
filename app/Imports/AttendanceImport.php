<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;

class AttendanceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Ambil data dari kolom header Excel (nisn, tanggal, jam_masuk, jam_pulang, status, jurnal)
        $nisn      = trim($row['nisn'] ?? '');
        $rawDate   = trim($row['tanggal'] ?? '');
        $jamMasuk  = trim($row['jam_masuk'] ?? '');
        $jamPulang = trim($row['jam_pulang'] ?? '');
        $status    = strtolower(trim($row['status'] ?? 'present'));
        $jurnal    = trim($row['jurnal'] ?? '');

        // Abaikan baris jika NISN atau Tanggal kosong
        if (empty($nisn) || empty($rawDate)) {
            return null;
        }

        // 2. Cari data siswa berdasarkan NISN atau NIS
        $student = Student::where('nisn', $nisn)
            ->orWhere('nis', $nisn)
            ->first();

        if (!$student) {
            return null; // Abaikan jika siswa tidak ditemukan
        }

        // 3. Conversion/Parsing Tanggal yang Fleksibel (DD/MM/YYYY, YYYY-MM-DD, Serial Excel)
        $formattedDate = $this->parseDate($rawDate, $nisn);

        // 4. Normalisasi Status
        $mappedStatus = match ($status) {
            'present', 'hadir', 'h' => 'present',
            'sick', 'sakit', 's'     => 'sick',
            'permit', 'izin', 'i'   => 'permit',
            'absent', 'alpa', 'a'   => 'absent',
            default                 => 'present',
        };

        // 5. Update atau Buat Absensi Baru (UpdateOrCreate)
        return Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date'       => $formattedDate,
            ],
            [
                'check_in'  => $jamMasuk ?: null,
                'check_out' => $jamPulang ?: null,
                'status'    => $mappedStatus,
                'journal'   => $jurnal ?: null,
            ]
        );
    }

    /**
     * Helper Function Parsing Format Tanggal
     */
    private function parseDate($value, $nisn)
    {
        try {
            // Jika tanggal dibaca sebagai Serial Number dari Excel (misal: 45464)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            // Ganti pemisah '/' menjadi '-' agar standar
            $cleaned = str_replace('/', '-', $value);

            // Jika format Tanggal-Bulan-Tahun (contoh: 21-06-2026 atau 21/06/2026)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $cleaned)) {
                return Carbon::createFromFormat('d-m-Y', $cleaned)->format('Y-m-d');
            }

            // Jika format Tahun-Bulan-Tanggal (contoh: 2026-06-21)
            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $cleaned)) {
                return Carbon::parse($cleaned)->format('Y-m-d');
            }

            // Fallback parsing otomatis menggunakan Carbon
            return Carbon::parse($cleaned)->format('Y-m-d');

        } catch (Exception $e) {
            // Lemparkan error agar ditangkap oleh catch (\Exception $e) di Controller
            throw new Exception("Gagal membaca format tanggal: '{$value}' untuk NIS: {$nisn}");
        }
    }
}