<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Student::with('classroom')->orderBy('classroom_id')->orderBy('name')->get();
    }

    public function map($student): array
    {
        return [
            $student->nis,
            $student->name,
            $student->classroom->name ?? 'Tanpa Kelas',
            $student->phone,
            $student->face_descriptor ? 'Terdaftar' : 'Belum',
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Siswa',
            'Kelas',
            'No HP Ortu',
            'Status Wajah',
        ];
    }
}