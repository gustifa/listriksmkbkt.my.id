<?php

namespace App\Imports;

use App\Models\Industry;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class IndustryImport implements ToModel, WithHeadingRow
{
    private $importedCount = 0;
    private $skippedCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Validasi dasar: Lewati jika nama perusahaan atau alamat kosong
        if (empty($row['nama_perusahaan']) || empty($row['alamat'])) {
            return null;
        }

        $name = trim($row['nama_perusahaan']);

        // Cek duplikasi berdasarkan nama perusahaan
        if (Industry::where('name', $name)->exists()) {
            $this->skippedCount++;
            return null; // Skip jika nama DU/DI sudah ada
        }

        $this->importedCount++;

        return new Industry([
            // ID akan otomatis di-generate oleh trait HasUuids di model
            'name'           => $name,
            'sector'         => $row['sektor'] ?? null,
            'address'        => $row['alamat'],
            'contact_person' => $row['kontak_person'] ?? null,
            'phone'          => $row['telepon'] ?? null,
            'quota'          => isset($row['kuota']) ? (int) $row['kuota'] : 0,
        ]);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
