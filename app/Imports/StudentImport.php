<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentImport implements ToCollection, WithStartRow, WithChunkReading
{
    private $classrooms = [];
    private $users = [];
    private $existingStudents = [];
    private $createdCount = 0;
    private $updatedCount = 0;

    public function __construct()
    {
        set_time_limit(600); // Batas waktu 10 menit
        ini_set('memory_limit', '512M');

        // Pre-fetch data referensi ke memori RAM untuk mencegah N+1 Query
        $this->classrooms       = Classroom::pluck('id', 'name')->toArray();
        $this->users            = User::pluck('id', 'email')->toArray();
        $this->existingStudents = Student::pluck('id', 'nis')->toArray(); // ['NIS' => 'UUID']
    }

    /**
     * Data siswa Dapodik dimulai pada baris ke-6 (baris 1-3 judul, 4-5 header bertingkat)
     */
    public function startRow(): int
    {
        return 6;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validasi Dasar (Kolom B: Nama [$row[1]], Kolom C: NIPD/NIS [$row[2]])
            if (empty($row[1]) || empty($row[2])) {
                continue;
            }

            $nis   = trim((string)$row[2]);
            $nama  = trim((string)$row[1]);
            $kelas = !empty($row[42]) ? strtoupper(trim((string)$row[42])) : null;

            // Handle Kelas
            $classroomId = null;
            if ($kelas) {
                if (!isset($this->classrooms[$kelas])) {
                    $classroom = Classroom::create(['name' => $kelas]);
                    $this->classrooms[$kelas] = $classroom->id;
                }
                $classroomId = $this->classrooms[$kelas];
            }

            // Cleanup Nomor HP
            $rawPhone = $row[19] ?? $row[18] ?? null;
            $phone = null;
            if (!empty($rawPhone)) {
                $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
                if (str_starts_with($phone, '62')) {
                    $phone = '0' . substr($phone, 2);
                }
            }

            // Format Tanggal Lahir
            $dob = null;
            if (!empty($row[6])) {
                try {
                    $dob = \Carbon\Carbon::parse($row[6])->format('Y-m-d');
                } catch (\Exception $e) {
                    $dob = null;
                }
            }

            $email  = $nis . '@siswa.sekolah.id';
            $userId = $this->users[$email] ?? null;

            $dataToSave = [
                'user_id'          => $userId,
                'nisn'             => !empty($row[4]) ? trim((string)$row[4]) : null,
                'nik'              => !empty($row[7]) ? trim((string)$row[7]) : null,
                'name'             => $nama,
                'gender'           => !empty($row[3]) ? trim((string)$row[3]) : 'L',
                'pob'              => !empty($row[5]) ? trim((string)$row[5]) : null,
                'dob'              => $dob,
                'religion'         => !empty($row[8]) ? trim((string)$row[8]) : null,
                'phone'            => $phone,
                'email'            => !empty($row[20]) ? trim((string)$row[20]) : null,
                'address'          => !empty($row[9]) ? trim((string)$row[9]) : null,
                'rt'               => !empty($row[10]) ? trim((string)$row[10]) : null,
                'rw'               => !empty($row[11]) ? trim((string)$row[11]) : null,
                'dusun'            => !empty($row[12]) ? trim((string)$row[12]) : null,
                'subdistrict'      => !empty($row[13]) ? trim((string)$row[13]) : null,
                'district'         => !empty($row[14]) ? trim((string)$row[14]) : null,
                'postal_code'      => !empty($row[15]) ? trim((string)$row[15]) : null,
                'classroom_id'     => $classroomId,
                'previous_school'  => !empty($row[56]) ? trim((string)$row[56]) : null,

                // Data Ayah
                'father_name'      => !empty($row[24]) ? trim((string)$row[24]) : null,
                'father_nik'       => !empty($row[29]) ? trim((string)$row[29]) : null,
                'father_education' => !empty($row[26]) ? trim((string)$row[26]) : null,
                'father_job'       => !empty($row[27]) ? trim((string)$row[27]) : null,
                'father_income'    => !empty($row[28]) ? trim((string)$row[28]) : null,

                // Data Ibu
                'mother_name'      => !empty($row[30]) ? trim((string)$row[30]) : null,
                'mother_nik'       => !empty($row[35]) ? trim((string)$row[35]) : null,
                'mother_education' => !empty($row[32]) ? trim((string)$row[32]) : null,
                'mother_job'       => !empty($row[33]) ? trim((string)$row[33]) : null,
                'mother_income'    => !empty($row[34]) ? trim((string)$row[34]) : null,

                // Data Wali
                'guardian_name'    => !empty($row[36]) ? trim((string)$row[36]) : null,
                'guardian_nik'     => !empty($row[41]) ? trim((string)$row[41]) : null,
                'guardian_education'=> !empty($row[38]) ? trim((string)$row[38]) : null,
                'guardian_job'     => !empty($row[39]) ? trim((string)$row[39]) : null,
                'guardian_income'  => !empty($row[40]) ? trim((string)$row[40]) : null,
            ];

            // Menggunakan Eloquent updateOrCreate agar pemetaan nama kolom akurat dan aman
            if (isset($this->existingStudents[$nis])) {
                $student = Student::where('nis', $nis)->first();
                if ($student) {
                    $student->update($dataToSave);
                    $this->updatedCount++;
                }
            } else {
                $dataToSave['id']          = (string) Str::uuid();
                $dataToSave['nis']         = $nis;
                $dataToSave['status']      = 'active';
                $dataToSave['is_promoted'] = false;

                $newStudent = Student::create($dataToSave);
                $this->existingStudents[$nis] = $newStudent->id;
                $this->createdCount++;
            }
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }
}