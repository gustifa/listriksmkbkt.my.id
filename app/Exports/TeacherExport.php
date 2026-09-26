<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TeacherExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Teacher::with('user')->get();
    }

    public function map($teacher): array
    {
        return [
            $teacher->user->name ?? '-',
            $teacher->nip,
            $teacher->gender == 'L' ? 'Laki-laki' : 'Perempuan',
            $teacher->phone,
            $teacher->user->email ?? '-',
            $teacher->address,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'NIP',
            'Jenis Kelamin',
            'No HP',
            'Email Login',
            'Alamat',
        ];
    }
}