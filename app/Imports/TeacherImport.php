<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeacherImport implements ToModel, WithHeadingRow
{
    private $successCount = 0;
    private $duplicates = []; // Menyimpan daftar error duplikat

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 1. Validasi Dasar (Data Kosong)
        if (empty($row['email']) || empty($row['nama'])) {
            return null; // Skip baris kosong
        }

        // 2. Cek Duplikat EMAIL (di tabel Users)
        if (User::where('email', $row['email'])->exists()) {
            // Catat error dan skip
            $this->duplicates[] = "Email <b>{$row['email']}</b> sudah terdaftar (Nama: {$row['nama']})";
            return null;
        }

        // 3. Cek Duplikat NIP (di tabel Teachers) - Jika NIP ada isinya
        if (!empty($row['nip']) && Teacher::where('nip', $row['nip'])->exists()) {
            // Catat error dan skip
            $this->duplicates[] = "NIP <b>{$row['nip']}</b> sudah terdaftar pada guru lain (Nama: {$row['nama']})";
            return null;
        }

        // --- Jika Lolos Validasi, Proses Simpan ---

        // A. Buat User Baru
        $user = User::create([
            'email'    => $row['email'],
            'name'     => $row['nama'],
            'jenis_user'     => 'guru',
            'password' => Hash::make($row['password'] ?? 'guru123'),
        ]);

        $user->assignRole('guru');

        // B. Buat Data Guru Baru
        Teacher::create([
            'user_id'         => $user->id,
            'name'            => $row['nama'],
            'nip'             => $row['nip'] ?? null,
            'gender'          => $row['jk'] ?? null,
            'phone'           => $row['hp'] ?? null,
            'place_of_birth'  => $row['tempat_lahir'] ?? null,
            'address'         => $row['alamat'] ?? null,
            'education_level' => $row['pendidikan'] ?? null,
        ]);

        $this->successCount++;

        return null; // Return null karena kita handle create manual di atas
    }

    // --- Getters untuk Controller ---

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getDuplicates()
    {
        return $this->duplicates;
    }
}
