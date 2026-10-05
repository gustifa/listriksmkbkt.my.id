<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    protected $examId;
    protected $type;

    public function __construct($examId, $type)
    {
        $this->examId = $examId;

        // Pemetaan (Mapping) tipe soal agar sesuai dengan CHECK Constraint PostgreSQL
        $this->type = match ($type) {
            'pilihan_ganda'   => 'single',   // Ubah sesuai yang diizinkan database (misal: 'single' atau 'pg')
            'multiple_choice' => 'multiple', // Ubah 'multiple_choice' menjadi 'multiple' (atau 'pg_kompleks')
            'essay'           => 'essay',
            default           => $type,
        };
    }

    public function model(array $row)
    {
        // Skip jika kolom soal_pertanyaan kosong
        if (empty($row['soal_pertanyaan'])) {
            return null;
        }

        // Susun opsi A-E jika bukan tipe essay
        $options = null;
        if ($this->type !== 'essay') {
            $options = [
                ['key' => 'A', 'text' => $row['opsi_a'] ?? ''],
                ['key' => 'B', 'text' => $row['opsi_b'] ?? ''],
                ['key' => 'C', 'text' => $row['opsi_c'] ?? ''],
                ['key' => 'D', 'text' => $row['opsi_d'] ?? ''],
                ['key' => 'E', 'text' => $row['opsi_e'] ?? ''],
            ];

            // Filter opsi yang kosong
            $options = array_values(array_filter($options, fn($opt) => !empty($opt['text'])));
        }

        // Format Kunci Jawaban
        $correctAnswer = null;
        if ($this->type === 'essay') {
            $correctAnswer = $row['pedoman_kunci_jawaban'] ?? null;
        } elseif (!empty($row['kunci_jawaban'])) {
            $correctAnswer = array_map('trim', explode(',', strtoupper($row['kunci_jawaban'])));
            
            if ($this->type === 'single' && count($correctAnswer) === 1) {
                $correctAnswer = $correctAnswer[0];
            }
        }

        return new Question([
            'exam_id'        => $this->examId,
            'question_type'  => $this->type,
            'question_text'  => $row['soal_pertanyaan'],
            'options'        => $options,
            'correct_answer' => $correctAnswer,
            'score_weight'   => $row['bobot_nilai'] ?? 1,
        ]);
    }
}