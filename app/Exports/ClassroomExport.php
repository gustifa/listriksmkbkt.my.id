<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClassroomExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Classroom::withCount('students')->orderBy('name')->get();
    }

    public function map($classroom): array
    {
        return [
            $classroom->name,
            $classroom->students_count,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Kelas',
            'Jumlah Siswa',
        ];
    }
}