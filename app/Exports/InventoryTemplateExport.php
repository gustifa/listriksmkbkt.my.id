<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventoryTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        // Berikan 1 contoh baris agar guru tidak bingung cara mengisinya
        return [
            [
                'kode' => 'TITL-001',
                'nama_barang' => 'Tang Kombinasi Tekiro',
                'satuan' => 'Pcs',
                'stok_awal' => '10',
                'deskripsi' => 'Lokasi Rak A1',
                'sumber_dana' => 'Dana BOS',
                'tahun_anggaran' => '2026',
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'kode',
            'nama_barang',
            'satuan',
            'stok_awal',
            'deskripsi',
            'sumber_dana',
            'tahun_anggaran',
        ];
    }

    public function title(): string
    {
        return 'Template Import Barang';
    }
}
