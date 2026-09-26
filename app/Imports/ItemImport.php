<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryTransaction;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ItemImport implements ToCollection, WithHeadingRow, WithChunkReading
{

       // Properti untuk menghitung hasil import
    public $importedCount = 0;
    public $skippedCount = 0;

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Validasi minimal: pastikan ada kode dan nama barang
            // Note: 'kode' dan 'nama_barang' harus sesuai dengan header di file Excel
            if (empty($row['kode']) || empty($row['nama_barang'])) {
                continue;
            }

            $code = trim($row['kode']);

            // 2. Cek duplikasi berdasarkan kode barang
            if (Item::where('code', $code)->exists()) {
                $this->skippedCount++;
                continue; // Skip jika kode sudah ada di database
            }

            // 3. Proses Simpan dengan Transaction
            DB::transaction(function () use ($row, $code) {
                $stock = isset($row['stok_awal']) ? (int) $row['stok_awal'] : 0;

                // Simpan ke Tabel Master Barang
                $item = Item::create([
                    'code'        => $code,
                    'name'        => trim($row['nama_barang']),
                    'unit'        => $row['satuan'] ?? 'Pcs',
                    'description' => $row['deskripsi'] ?? null,
                    'stock'       => $stock,
                ]);

                // Jika ada stok awal, otomatis buat riwayat transaksinya
                if ($stock > 0) {
                    InventoryTransaction::create([
                        'item_id'        => $item->id,
                        'user_id'        => Auth::id(),
                        'type'           => 'in',
                        'quantity'       => $stock,
                        'date'           => now(),
                        'funding_source' => $row['sumber_dana'] ?? 'Tidak Diketahui',
                        'year'           => $row['tahun_anggaran'] ?? date('Y'),
                        'notes'          => 'Stok Awal (Hasil Import)',
                    ]);
                }

                $this->importedCount++;
            });
        }
    }

    // Server hanya akan membaca 100 baris per tahap
    public function chunkSize(): int
    {
        return 100;
    }

}
