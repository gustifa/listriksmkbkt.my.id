<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classroom;
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
        // Pastikan NIS, Nama, dan Kelas ada isinya (tidak kosong)
        if (empty($row['nis']) || empty($row['nama_siswa']) || empty($row['kelas'])) {
            return null;
        }

        $nis  = trim($row['nis']);
        $nama = ucwords(strtolower(trim($row['nama_siswa']))); // Ubah jadi: Budi Santoso
        $kelas = strtoupper(trim($row['kelas'])); // Ubah jadi: X TJKT 1

        // 2. CEK DUPLIKASI NIS
        // Cek di Database apakah NIS sudah ada
        if (Student::where('nis', $nis)->exists()) {
            // Catat data duplikat untuk laporan
            $this->duplicates[] = "{$nis} - {$nama}";
            return null; // Skip, jangan simpan
        }

        // 3. CARI / BUAT KELAS
        // Menggunakan firstOrCreate agar tidak membuat kelas ganda jika sudah ada
        $classroom = Classroom::firstOrCreate(
            ['name' => $kelas]
        );

        // 4. CLEANING NOMOR HP (Opsional tapi PENTING)
        // Excel seringkali berisi '0812-3456' atau '62812...'. Kita ambil angkanya saja.
        $phone = null;
        if (!empty($row['no_hp'])) {
            // Hapus semua karakter selain angka
            $phone = preg_replace('/[^0-9]/', '', $row['no_hp']);

            // Opsional: Jika ingin format 08..., bisa ditambahkan logika di sini
            // Contoh: Jika diawali 62, ganti jadi 0
            if (substr($phone, 0, 2) == '62') {
                $phone = '0' . substr($phone, 2);
            }
        }

        // 5. SIMPAN SISWA BARU
        $this->successCount++;

        return new Student([
            'nis'          => $nis,
            'name'         => $nama,
            'classroom_id' => $classroom->id,
            'phone'        => $phone,
            // Pastikan kolom 'phone' atau 'no_hp' ada di tabel 'students' database Anda
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
