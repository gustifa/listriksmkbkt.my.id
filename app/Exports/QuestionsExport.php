<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        return $this->exam->questions()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tipe Soal',
            'Pertanyaan Soal',
            'Opsi A',
            'Opsi B',
            'Opsi C',
            'Opsi D',
            'Opsi E',
            'Kunci Jawaban',
            'Bobot Nilai',
        ];
    }

    public function map($question): array
    {
        static $no = 1;

        $rawOptions = is_string($question->options) ? json_decode($question->options, true) : $question->options;
        $optionsMap = [];
        if (is_array($rawOptions)) {
            foreach ($rawOptions as $opt) {
                if (isset($opt['key']) && isset($opt['text'])) {
                    $optionsMap[$opt['key']] = $opt['text'];
                }
            }
        }

        $rawAnswer = is_string($question->correct_answer) ? json_decode($question->correct_answer, true) : $question->correct_answer;
        $correctAnswerStr = '-';
        if (is_array($rawAnswer)) {
            $correctAnswerStr = implode(',', $rawAnswer);
        } elseif (!empty($rawAnswer)) {
            $correctAnswerStr = (string) $rawAnswer;
        }

        $qTypeMap = [
            'single'          => 'Pilihan Ganda',
            'pilihan_ganda'   => 'Pilihan Ganda',
            'pg'              => 'Pilihan Ganda',
            'multiple'        => 'Multiple Choice',
            'multiple_choice' => 'Multiple Choice',
            'mc'              => 'Multiple Choice',
            'essay'           => 'Essay',
        ];
        $typeLabel = $qTypeMap[strtolower($question->question_type)] ?? strtoupper($question->question_type);

        return [
            $no++,
            $typeLabel,
            $question->question_text,
            $optionsMap['A'] ?? '-',
            $optionsMap['B'] ?? '-',
            $optionsMap['C'] ?? '-',
            $optionsMap['D'] ?? '-',
            $optionsMap['E'] ?? '-',
            $correctAnswerStr,
            $question->score_weight ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D6EFD']
                ]
            ],
        ];
    }
}
