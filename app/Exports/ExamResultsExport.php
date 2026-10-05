<?php

namespace App\Exports;

use App\Models\Exam;
use App\Models\ExamSession;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamResultsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        return ExamSession::with(['student.classroom'])
            ->where('exam_id', $this->exam->id)
            ->where('status', 'completed')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS / NISN',
            'Nama Siswa',
            'Kelas',
            'Waktu Mulai',
            'Waktu Selesai',
            'Status',
            'Nilai Total',
        ];
    }

    public function map($session): array
    {
        static $no = 1;

        return [
            $no++,
            $session->student->nisn ?? $session->student->nis ?? '-',
            $session->student->name ?? '-',
            $session->student->classroom->name ?? '-',
            $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('d/m/Y H:i') : '-',
            $session->finished_at ? \Carbon\Carbon::parse($session->finished_at)->format('d/m/Y H:i') : '-',
            'Selesai',
            $session->total_score ?? $session->score ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '198754']
                ]
            ],
        ];
    }
}
