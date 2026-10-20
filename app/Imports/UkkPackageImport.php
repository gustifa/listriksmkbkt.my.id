<?php

namespace App\Imports;

use App\Models\UkkPackage;
use App\Models\UkkUnit;
use App\Models\UkkUnitElementS;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class UkkPackageImport implements WithMultipleSheets, SkipsUnknownSheets
{
    /**
     * Daftarkan handler sheet secara dinamis.
     * Menggunakan SkipsUnknownSheets agar tidak error jika jumlah sheet kurang dari 5.
     */
    public function sheets(): array
    {
        return [
            0 => new SingleSheetUkkImport(),
            1 => new SingleSheetUkkImport(),
            2 => new SingleSheetUkkImport(),
            3 => new SingleSheetUkkImport(),
            4 => new SingleSheetUkkImport(),
            5 => new SingleSheetUkkImport(),
        ];
    }

    /**
     * Abaikan sheet yang tidak ditemukan/di luar jangkauan (out of bounds)
     */
    public function onUnknownSheet($sheetName)
    {
        // Biarkan kosong agar sheet berlebih/kurang diabaikan secara aman
    }
}

class SingleSheetUkkImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $currentPackage = null;
        $currentUnit = null;

        foreach ($rows as $row) {
            $col1 = trim($row[1] ?? ''); // JUDUL PAKET
            $col2 = trim($row[2] ?? ''); // KODE UNIT
            $col3 = trim($row[3] ?? ''); // JUDUL UNIT
            $col4 = trim($row[4] ?? ''); // ELEMEN
            $col5 = trim($row[5] ?? ''); // UTAMA
            $col6 = trim($row[6] ?? ''); // PENDUKUNG
            $col7 = trim($row[7] ?? ''); // LANGKAH KERJA / JABATAN KERJA

            // Skip Header
            if (str_contains(strtoupper($col1), 'JUDUL PAKET') || str_contains(strtoupper($col2), 'KODE UNIT')) {
                continue;
            }

            // 1. Set / Buat Paket Baru jika Judul Paket ditemukan
            if (!empty($col1) && strlen($col1) > 8) {
                $currentPackage = UkkPackage::firstOrCreate(
                    ['package_name' => $col1, 'major_code' => 'TITL'],
                    ['academic_year' => '2026/2027']
                );
            }

            if (!$currentPackage) {
                $currentPackage = UkkPackage::firstOrCreate(
                    ['package_name' => 'PAKET KEAHLIAN TITL', 'major_code' => 'TITL'],
                    ['academic_year' => '2026/2027']
                );
            }

            // 2. Set / Buat Unit Baru jika Kode Unit ditemukan
            if (!empty($col2)) {
                $currentUnit = UkkUnit::firstOrCreate([
                    'ukk_package_id' => $currentPackage->id,
                    'unit_code'      => $col2,
                ], [
                    'unit_title'   => !empty($col3) ? $col3 : 'Unit ' . $col2,
                    'job_position' => !empty($col7) ? $col7 : null,
                ]);
            }

            // 3. Tambahkan Elemen ke Unit Aktif
            if ($currentUnit && !empty($col4)) {
                $categoryType = !empty($col6) ? 'PENDUKUNG' : 'UTAMA';

                UkkUnitElementS::create([
                    'ukk_unit_id'   => $currentUnit->id,
                    'element_name'  => $col4,
                    'category_type' => $categoryType,
                    'work_step'     => !empty($col7) && empty($col2) ? $col7 : null,
                ]);
            }
        }
    }
}