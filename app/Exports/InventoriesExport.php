<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoriesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $roomId;

    public function __construct($roomId = null)
    {
        $this->roomId = $roomId;
    }

    public function collection()
    {
        $query = Inventory::with('room');
        if ($this->roomId) {
            $query->where('room_id', $this->roomId);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Bengkel / Ruangan',
            'Tipe',
            'Kategori',
            'Merk/Spesifikasi',
            'Stok Tersedia',
            'Stok Min.',
            'Satuan',
            'Kondisi',
            'Sumber Dana',
            'Tahun Pengadaan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->code,
            $row->name,
            $row->room ? $row->room->name : '-',
            strtoupper($row->type),
            $row->category,
            $row->brand_model ?? '-',
            $row->quantity,
            $row->min_quantity,
            $row->unit,
            ucfirst(str_replace('_', ' ', $row->condition)),
            $row->funding_source,
            $row->acquisition_year ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => '2B6CB0']]],
        ];
    }
}