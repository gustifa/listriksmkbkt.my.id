<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;

class StudentImport implements OnEachRow, WithStartRow, WithChunkReading
{
    private $classrooms = [];
    private $users = [];
    private $createdCount = 0;
    private $updatedCount = 0;

    public function __construct()
    {
        // 1. Perpanjang batas waktu eksekusi script menjadi 5 menit
        set_time_limit(300);

        // 2. PRE-FETCH DATA KE MEMORI (RAM)
        $this->classrooms = Classroom::pluck('id', 'name')->toArray(); // ['XII RPL 1' => 'uuid-...']
        $this->users      = User::pluck('id', 'email')->toArray();    // ['1001@siswa.sekolah.id' => 'uuid-...']
    }

    /**
     * Data siswa Dapodik dimulai pada baris ke-6 (baris 1-3 judul/meta, 4-5 header bertingkat)
     */
    public function startRow(): int
    {
        return 6;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        // 1. Validasi Dasar (Kolom B: Nama [$row[1]], Kolom C: NIPD [$row[2]])
        if (empty($row[1]) || empty($row[2])) {
            return;
        }

        $nis   = trim((string)$row[2]); // NIPD Dapodik
        $nama  = trim((string)$row[1]); // Nama Siswa
        $kelas = !empty($row[42]) ? strtoupper(trim((string)$row[42])) : null; // Rombel Saat Ini

        // 2. CARI / BUAT KELAS
        $classroomId = null;
        if ($kelas) {
            if (!isset($this->classrooms[$kelas])) {
                $classroom = Classroom::create(['name' => $kelas]);
                $this->classrooms[$kelas] = $classroom->id;
            }
            $classroomId = $this->classrooms[$kelas];
        }

        // 3. CLEANING NOMOR TELEPON/HP
        $rawPhone = $row[19] ?? $row[18] ?? null; // Prioritas Kolom T (HP), lalu Kolom S (Telepon)
        $phone = null;
        if (!empty($rawPhone)) {
            $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
            if (str_starts_with($phone, '62')) {
                $phone = '0' . substr($phone, 2);
            }
        }

        // 4. FORMAT TANGGAL LAHIR (Kolom G: Tanggal Lahir)
        $dob = null;
        if (!empty($row[6])) {
            try {
                $dob = \Carbon\Carbon::parse($row[6])->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null;
            }
        }

        // 5. CEK USER_ID (Cepat via Memori RAM)
        $email  = $nis . '@siswa.sekolah.id';
        $userId = $this->users[$email] ?? null;

        // 6. KONDISI: TAMBAH BARU ATAU UPDATE DATA LAMA
        $student = Student::where('nis', $nis)->first();

        $dataToSave = [
            'user_id'      => $userId,
            'nisn'         => !empty($row[4]) ? trim((string)$row[4]) : null,   // Col E: NISN
            'nik'          => !empty($row[7]) ? trim((string)$row[7]) : null,   // Col H: NIK
            'name'         => $nama,                          // Col B: Nama
            'gender'       => !empty($row[3]) ? trim((string)$row[3]) : 'L',    // Col D: JK
            'pob'          => !empty($row[5]) ? trim((string)$row[5]) : null,   // Col F: Tempat Lahir
            'dob'          => $dob,                           // Col G: Tanggal Lahir
            'religion'     => !empty($row[8]) ? trim((string)$row[8]) : null,   // Col I: Agama
            'phone'        => $phone,                         // Col T/S: HP/Telepon
            'email'        => !empty($row[20]) ? trim((string)$row[20]) : null, // Col U: E-Mail
            'address'      => !empty($row[9]) ? trim((string)$row[9]) : null,   // Col J: Alamat
            'rt'           => !empty($row[10]) ? trim((string)$row[10]) : null, // Col K: RT
            'rw'           => !empty($row[11]) ? trim((string)$row[11]) : null, // Col L: RW
            'dusun'        => !empty($row[12]) ? trim((string)$row[12]) : null, // Col M: Dusun
            'subdistrict'  => !empty($row[13]) ? trim((string)$row[13]) : null, // Col N: Kelurahan
            'district'     => !empty($row[14]) ? trim((string)$row[14]) : null, // Col O: Kecamatan
            'postal_code'  => !empty($row[15]) ? trim((string)$row[15]) : null, // Col P: Kode Pos
            'classroom_id' => $classroomId,                   // Col AQ (42): Rombel
            'previous_school' => !empty($row[56]) ? trim((string)$row[56]) : null, // Col BE: Sekolah Asal

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
            'guardian_name'      => !empty($row[36]) ? trim((string)$row[36]) : null,
            'guardian_nik'       => !empty($row[41]) ? trim((string)$row[41]) : null,
            'guardian_education' => !empty($row[38]) ? trim((string)$row[38]) : null,
            'guardian_job'       => !empty($row[39]) ? trim((string)$row[39]) : null,
            'guardian_income'    => !empty($row[40]) ? trim((string)$row[40]) : null,
        ];

        if ($student) {
            // JIKA NIS SUDAH ADA -> UPDATE DATA LAMA
            $student->update($dataToSave);
            $this->updatedCount++;
        } else {
            // JIKA NIS BELUM ADA -> BUAT DATA BARU (Dengan UUID Baru)
            $dataToSave['id']          = (string) Str::uuid();
            $dataToSave['nis']         = $nis;
            $dataToSave['status']      = 'active';
            $dataToSave['is_promoted'] = false;

            Student::create($dataToSave);
            $this->createdCount++;
        }
    }

    /**
     * Memproses data per chunk agar penggunaan RAM tetap efisien
     */
    public function chunkSize(): int
    {
        return 500;
    }

    // --- GETTERS UNTUK METRIK HASIL IMPORT ---

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }
}