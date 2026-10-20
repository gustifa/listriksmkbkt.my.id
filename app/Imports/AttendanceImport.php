<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Internship;
use App\Models\Industry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AttendanceImport implements WithMultipleSheets
{
    private $importedCount = 0;
    private $skippedCount = 0;

    public function sheets(): array
    {
        return [
            'Absensi' => new AbsensiSheetImport($this),
        ];
    }

    public function incrementImported($count = 1)
    {
        $this->importedCount += $count;
    }

    public function incrementSkipped($count = 1)
    {
        $this->skippedCount += $count;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}

class AbsensiSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $students = [];
    private $internships = [];
    private $industries = [];
    private $parentImport;

    public function __construct(AttendanceImport $parentImport)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $this->parentImport = $parentImport;

        // Cache data penting ke RAM
        $this->students    = Student::pluck('id', 'nis')->toArray();
        $this->internships = Internship::pluck('id', 'student_id')->toArray();
        $this->industries  = Industry::pluck('id', 'name')->toArray();
    }

    public function collection(Collection $rows)
    {
        $recordsToInsert = [];

        foreach ($rows as $row) {
            $rowArray = array_change_key_case($row->toArray(), CASE_LOWER);

            $nis       = trim($rowArray['username_siswa'] ?? $rowArray['nisn'] ?? $rowArray['nis'] ?? '');
            $waktuRaw  = trim($rowArray['waktu'] ?? $rowArray['tanggal'] ?? $rowArray['date'] ?? '');
            $tempatPkl = trim($rowArray['tempat_pkl'] ?? '');
            $jamMasuk  = trim($rowArray['jam_masuk'] ?? $rowArray['check_in'] ?? '');
            $jamPulang = trim($rowArray['jam_pulang'] ?? $rowArray['check_out'] ?? '');
            $status    = strtolower(trim($rowArray['status'] ?? 'present'));
            $jurnal    = trim($rowArray['catatan'] ?? $rowArray['jurnal'] ?? $rowArray['journal'] ?? '');

            if (empty($nis) || empty($waktuRaw)) {
                continue;
            }

            // 1. Ambil Student ID
            $studentId = $this->students[$nis] ?? null;
            if (!$studentId) {
                $this->parentImport->incrementSkipped();
                continue;
            }

            // 2. Cek/Buat Penempatan PKL (Mencegah Unique Violation)
            $formattedDate = $this->parseDate($waktuRaw);
            $internshipId  = $this->internships[$studentId] ?? null;

            if (!$internshipId) {
                $namaIndustri = (!empty($tempatPkl) && $tempatPkl !== '-') ? $tempatPkl : 'Tempat PKL Belum Diset';

                if (!isset($this->industries[$namaIndustri])) {
                    $indId = (string) Str::uuid();
                    DB::table('industries')->insert([
                        'id'         => $indId,
                        'name'       => $namaIndustri,
                        'address'    => '-',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->industries[$namaIndustri] = $indId;
                }
                $industryId = $this->industries[$namaIndustri];

                $existingInternship = Internship::where('student_id', $studentId)
                    ->where('industry_id', $industryId)
                    ->where('status', 'active')
                    ->first();

                if ($existingInternship) {
                    $internshipId = $existingInternship->id;
                } else {
                    $newInternshipId = (string) Str::uuid();
                    DB::table('internships')->insert([
                        'id'          => $newInternshipId,
                        'student_id'  => $studentId,
                        'industry_id' => $industryId,
                        'start_date'  => $formattedDate,
                        'end_date'    => Carbon::parse($formattedDate)->addMonths(6)->format('Y-m-d'),
                        'status'      => 'active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $internshipId = $newInternshipId;
                }

                $this->internships[$studentId] = $internshipId;
            }

            // 3. Format Waktu
            $formattedJamMasuk  = $this->parseTime($jamMasuk ?: $waktuRaw) ?? '07:00:00';
            $formattedJamPulang = $this->parseTime($jamPulang);

            if ($status === 'pulang') {
                $formattedJamPulang = $formattedJamMasuk;
            }

            // 4. Deteksi Jurnal "Tanpa keterangan" -> Ubah Status ke 'alpha'
            if (empty($jurnal) || str_contains(strtolower($jurnal), 'tanpa keterangan')) {
                $mappedStatus = 'alpha';
                $jurnal = 'Tanpa keterangan';
            } else {
                $mappedStatus = match ($status) {
                    'present', 'hadir', 'h', 'masuk', 'pulang' => 'present',
                    'sick', 'sakit', 's'                       => 'sick',
                    'permit', 'izin', 'i'                     => 'permit',
                    'absent', 'alpa', 'a', 'alpha'            => 'alpha',
                    default                                   => 'present',
                };
            }

            // Kunci Unik Kombinasi (student_id + date)
            $uniqueKey = $studentId . '_' . $formattedDate;

            $recordsToInsert[$uniqueKey] = [
                'id'             => (string) Str::uuid(),
                'student_id'     => $studentId,
                'internship_id'  => $internshipId,
                'date'           => $formattedDate,
                'time'           => $formattedJamMasuk,
                'check_out_time' => $formattedJamPulang,
                'status'         => $mappedStatus,
                'activity_log'   => $jurnal,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        // 5. Upsert Data Ke Absensi
        if (!empty($recordsToInsert)) {
            $cleanRows = array_values($recordsToInsert);

            $batches = array_chunk($cleanRows, 200);
            foreach ($batches as $batch) {
                DB::table('internship_attendances')->upsert(
                    $batch,
                    ['student_id', 'date'],
                    ['status', 'activity_log', 'updated_at']
                );
            }
            $this->parentImport->incrementImported(count($cleanRows));
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function parseDate($value)
    {
        try {
            if (empty($value)) return now()->format('Y-m-d');
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            $cleaned = str_replace('/', '-', $value);
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $cleaned)) {
                return Carbon::createFromFormat('d-m-Y', $cleaned)->format('Y-m-d');
            }
            return Carbon::parse($cleaned)->format('Y-m-d');
        } catch (Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    private function parseTime($value)
    {
        if (empty($value)) return null;
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