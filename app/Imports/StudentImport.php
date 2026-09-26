<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    private $duplicates = [];
    private $successCount = 0;

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

        // 2. CEK DUPLIKASI NIS di Tabel Student
        if (Student::where('nis', $nis)->exists()) {
            $this->duplicates[] = "{$nis} - {$nama}";
            return null; 
        }

        // 3. CARI / BUAT KELAS
        $classroom = Classroom::firstOrCreate(
            ['name' => $kelas]
        );

        // 4. CLEANING NOMOR HP
        $phone = null;
        if (!empty($row['no_hp'])) {
            $phone = preg_replace('/[^0-9]/', '', $row['no_hp']);
            if (substr($phone, 0, 2) == '62') {
                $phone = '0' . substr($phone, 2);
            }
        }

        // 5. FITUR TAMBAHAN: CEK USER_ID (Auto-Link)
        // Kita asumsikan email user menggunakan format: NIS@siswa.sekolah.id
        $email = $nis . '@siswa.sekolah.id';
        $existingUser = User::where('email', $email)->first();
        
        // Ambil ID user jika ditemukan, jika tidak biarkan null (nanti di-generate via Command)
        $userId = $existingUser ? $existingUser->id : null;

        // 6. SIMPAN SISWA BARU
        $this->successCount++;

        return new Student([
            'nis'          => $nis,
            'name'         => $nama,
            'classroom_id' => $classroom->id,
            'phone'        => $phone,
            'user_id'      => $userId, // Set user_id secara otomatis jika akun user sudah ada
        ]);
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