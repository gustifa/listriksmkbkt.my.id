<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class QuestionsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $examId;

    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    /**
     * Mengonversi tiap baris Excel ke Model Question
     */
    public function model(array $row)
    {
        // 1. Mapping Tipe Soal ke Constraint Database ('single', 'multiple', 'essay')
        $rawType = strtolower(trim($row['tipe_soal'] ?? 'pilihan ganda'));
        $typeMapping = [
            'pilihan ganda'   => 'single',
            'pilihan_ganda'   => 'single',
            'single'          => 'single',
            'pg'              => 'single',
            'multiple choice' => 'multiple',
            'multiple_choice' => 'multiple',
            'multiple'        => 'multiple',
            'mc'              => 'multiple',
            'essay'           => 'essay',
            'uraian'          => 'essay',
        ];
        $questionType = $typeMapping[$rawType] ?? 'single';

        // 2. Format Opsi Jawaban (JSON) untuk Pilihan Ganda & Multiple Choice
        $optionsData = null;
        if (in_array($questionType, ['single', 'multiple'])) {
            $formattedOptions = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $key) {
                $columnName = 'opsi_' . strtolower($key);
                $optionVal = trim((string)($row[$columnName] ?? ''));

                if (!empty($optionVal) && $optionVal !== '-') {
                    $formattedOptions[] = [
                        'key'  => $key,
                        'text' => $optionVal
                    ];
                }
            }
            $optionsData = $formattedOptions;
        }

        // 3. Format Kunci Jawaban (JSON Array)
        $rawAnswer = trim((string)($row['kunci_jawaban'] ?? ''));
        $correctAnswerData = [];

        if ($questionType === 'essay') {
            $correctAnswerData = [$rawAnswer];
        } else {
            // Pisahkan jika ada multiple choice (contoh: "A,B" atau "A, C")
            if ($rawAnswer !== '-' && $rawAnswer !== '') {
                $answers = explode(',', $rawAnswer);
                foreach ($answers as $ans) {
                    $cleaned = strtoupper(trim($ans));
                    if (!empty($cleaned) && $cleaned !== '-') {
                        $correctAnswerData[] = $cleaned;
                    }
                }
            }
        }

        // 4. Create Record Question
        return new Question([
            'exam_id'        => $this->examId,
            'question_type'  => $questionType,
            'question_text'  => $row['pertanyaan_soal'],
            'options'        => $optionsData,
            'correct_answer' => $correctAnswerData,
            'score_weight'   => (int) ($row['bobot_nilai'] ?? 1),
        ]);
    }

    /**
     * Aturan Validasi per Baris File Excel
     */
    public function rules(): array
    {
        return [
            'pertanyaan_soal' => 'required|string',
            'kunci_jawaban'   => 'required',
            'bobot_nilai'     => 'required|numeric|min:1',
        ];
    }

    /**
     * Pesan Kustom Jika Validasi Excel Gagal
     */
    public function customValidationMessages()
    {
        return [
            'pertanyaan_soal.required' => 'Baris dalam file Excel memiliki Pertanyaan Soal yang kosong.',
            'kunci_jawaban.required'   => 'Baris dalam file Excel memiliki Kunci Jawaban yang kosong.',
            'bobot_nilai.required'     => 'Baris dalam file Excel memiliki Bobot Nilai yang kosong.',
        ];
    }
}
