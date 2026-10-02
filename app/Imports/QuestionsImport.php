<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    protected $examId;

    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    public function model(array $row)
    {
        // Skip jika kolom jenis_soal atau teks_soal kosong
        if (empty($row['jenis_soal']) || empty($row['teks_soal'])) {
            return null;
        }

        $type = strtolower(trim($row['jenis_soal'])); // single, multiple, essay

        $options = null;
        if ($type !== 'essay') {
            $options = [
                ['key' => 'A', 'text' => $row['opsi_a'] ?? ''],
                ['key' => 'B', 'text' => $row['opsi_b'] ?? ''],
                ['key' => 'C', 'text' => $row['opsi_c'] ?? ''],
                ['key' => 'D', 'text' => $row['opsi_d'] ?? ''],
            ];
        }

        // Kunci jawaban di-split koma untuk multiple choice (misal: "A,C")
        $correctAnswer = null;
        if (!empty($row['kunci_jawaban'])) {
            $correctAnswer = array_map('trim', explode(',', strtoupper($row['kunci_jawaban'])));
        }

        return new Question([
            'exam_id'        => $this->examId,
            'question_type'  => $type,
            'question_text'  => $row['teks_soal'],
            'options'        => $options,
            'correct_answer' => $correctAnswer,
            'score_weight'   => $row['bobot'] ?? 1,
        ]);
    }
}
