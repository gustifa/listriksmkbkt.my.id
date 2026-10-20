<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

// HAPUS WithBatchInserts untuk mendukung UUID
class StudentImport implements ToModel, WithHeadingRow, WithChunkReading
{
    private $existingNis = [];
    private $classrooms = [];
    private $users = [];
    private $duplicates = [];
    private $successCount = 0;

    public function __construct()
    {
        // 1. Perpanjang batas waktu eksekusi script menjadi 5 menit
        set_time_limit(300);

        // 2. PRE-FETCH DATA KE MEMORI (RAM)
        // Mencegah N+1 query: Mengambil semua data awal sekaligus dalam 3 query ringkas
        $this->existingNis = Student::pluck('nis')->flip()->toArray();
        $this->classrooms  = Classroom::pluck('id', 'name')->toArray(); // Format: ['XII RPL 1' => 'uuid-...']
        $this->users       = User::pluck('id', 'email')->toArray();    // Format: ['1001@siswa.sekolah.id' => 'uuid-...']
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 1. Validasi Dasar (Cegah Baris Kosong)
        if (empty($row['nis']) || empty($row['nama_siswa']) || empty($row['kelas'])) {
            return null;
        }

        $nis   = trim($row['nis']);
        $nama  = ucwords(strtolower(trim($row['nama_siswa'])));
        $kelas = strtoupper(trim($row['kelas']));

        // 2. CEK DUPLIKASI NIS (Cepat via Memori RAM, 0 Query DB)
        if (isset($this->existingNis[$nis])) {
            $this->duplicates[] = "{$nis} - {$nama}";
            return null;
        }

        // Tandai NIS agar jika ada NIS ganda dalam file Excel yang sama tetap terdeteksi
        $this->existingNis[$nis] = true;

        // 3. CARI / BUAT KELAS (Pencarian via Memori RAM)
        if (!isset($this->classrooms[$kelas])) {
            // Menggunakan Eloquent agar UUID Classroom tergenerate otomatis
            $classroom = Classroom::create(['name' => $kelas]);
            $this->classrooms[$kelas] = $classroom->id; // Simpan ke cache lokal
        }
        $classroomId = $this->classrooms[$kelas];

        // 4. CLEANING NOMOR HP
        $phone = null;
        if (!empty($row['no_hp'])) {
            $phone = preg_replace('/[^0-9]/', '', (string)$row['no_hp']);
            if (str_starts_with($phone, '62')) {
                $phone = '0' . substr($phone, 2);
            }
        }

        // 5. FITUR TAMBAHAN: CEK USER_ID (Cepat via Memori RAM, 0 Query DB)
        $email  = $nis . '@siswa.sekolah.id';
        $userId = $this->users[$email] ?? null;

        // 6. SIMPAN SISWA BARU
        $this->successCount++;

        return new Student([
            'nis'          => $nis,
            'name'         => $nama,
            'classroom_id' => $classroomId,
            'phone'        => $phone,
            'user_id'      => $userId,
        ]);
    }

    /**
     * Memproses data per chunk agar penggunaan RAM tetap efisien
     */
    public function chunkSize(): int
    {
        return 500;
    }

    // --- GETTERS ---

    public function getDuplicates()
    {
        return $this->duplicates;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
