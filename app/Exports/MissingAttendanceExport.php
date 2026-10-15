<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MissingAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $noArrivalStudents;
    protected $noDepartureStudents;
    protected $missingSubjectStudents;

    public function __construct($noArrivalStudents, $noDepartureStudents, $missingSubjectStudents)
    {
        $this->noArrivalStudents = $noArrivalStudents;
        $this->noDepartureStudents = $noDepartureStudents;
        $this->missingSubjectStudents = $missingSubjectStudents;
    }

    public function collection()
    {
        $data = collect();

        // 1. Siswa Belum Absen Masuk Gerbang
        foreach ($this->noArrivalStudents as $student) {
            $data->push([
                'kategori' => 'Gerbang - Belum Masuk',
                'nis'      => $student->nis ?? '-',
                'nama'     => $student->name,
                'kelas'    => $student->classroom->name ?? '-',
                'detail'   => 'Belum Absen Masuk',
            ]);
        }

        // 2. Siswa Belum Absen Pulang Gerbang
        foreach ($this->noDepartureStudents as $student) {
            $data->push([
                'kategori' => 'Gerbang - Belum Pulang',
                'nis'      => $student->nis ?? '-',
                'nama'     => $student->name,
                'kelas'    => $student->classroom->name ?? '-',
                'detail'   => 'Sudah Masuk, Belum Pulang',
            ]);
        }

        // 3. Siswa Belum Presensi Mapel
        foreach ($this->missingSubjectStudents as $item) {
            $schedule = $item['schedule'];
            foreach ($item['students'] as $student) {
                $data->push([
                    'kategori' => 'Presensi Mapel',
                    'nis'      => $student->nis ?? '-',
                    'nama'     => $student->name,
                    'kelas'    => $schedule->classroom->name ?? '-',
                    'detail'   => ($schedule->subject->name ?? '-') . ' (Guru: ' . ($schedule->teacher->name ?? '-') . ')',
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kategori Laporan',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Keterangan / Mapel',
        ];
    }

    public function map($row): array
    {
        return [
            $row['kategori'],
            $row['nis'],
            $row['nama'],
            $row['kelas'],
            $row['detail'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}