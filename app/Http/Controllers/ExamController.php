<?php

namespace App\Http/Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamSession;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamController extends Controller
{
    // Upload Soal oleh Guru (Excel Template)
    public function importQuestions(Request $request, $examId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Abaikan baris header pertama
        for ($i = 2; $i <= count($sheetData); $i++) {
            $row = $sheetData[$i];
            if (empty($row['A'])) continue;

            $type = strtolower($row['A']); // single, multiple, essay
            
            $options = null;
            if ($type !== 'essay') {
                $options = [
                    ['key' => 'A', 'text' => $row['C']],
                    ['key' => 'B', 'text' => $row['D']],
                    ['key' => 'C', 'text' => $row['E']],
                    ['key' => 'D', 'text' => $row['F']],
                ];
            }

            // Kunci jawaban di split koma untuk multiple choice (contoh: "A,C")
            $correctAnswers = !empty($row['G']) ? array_map('trim', explode(',', $row['G'])) : null;

            Question::create([
                'exam_id' => $examId,
                'question_type' => $type,
                'question_text' => $row['B'],
                'options' => $options,
                'correct_answer' => $correctAnswers,
                'score_weight' => $row['H'] ?? 1,
            ]);
        }

        return redirect()->back()->with('success', 'Soal berhasil diunggah.');
    }

    // Siswa Memulai Ujian
    public function startExam($examId)
    {
        $student = Auth::user()->student; // Relasi ke model Student
        $exam = Exam::with('questions')->findOrFail($examId);

        $session = ExamSession::firstOrCreate(
            ['exam_id' => $examId, 'student_id' => $student->id],
            ['start_time' => now(), 'status' => 'ongoing']
        );

        return view('student.exam.show', compact('exam', 'session'));
    }

    // Siswa Submit Ujian & Auto Grading
    public function submitExam(Request $request, $sessionId)
    {
        $session = ExamSession::findOrFail($sessionId);
        $answers = $request->input('answers', []);

        $totalScore = 0;
        $maxPossibleScore = 0;

        foreach ($session->exam->questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = false;
            $scoreGiven = 0;

            if ($question->question_type === 'single') {
                if (is_array($userAnswer) && count($userAnswer) > 0 && $userAnswer[0] === $question->correct_answer[0]) {
                    $isCorrect = true;
                    $scoreGiven = $question->score_weight;
                }
            } elseif ($question->question_type === 'multiple') {
                // Pilihan ganda kompleks / centang banyak
                sort($userAnswer);
                $correct = $question->correct_answer;
                sort($correct);
                if ($userAnswer === $correct) {
                    $isCorrect = true;
                    $scoreGiven = $question->score_weight;
                }
            }

            ExamAnswer::updateOrCreate(
                ['exam_session_id' => $session->id, 'question_id' => $question->id],
                ['answer' => (array) $userAnswer, 'is_correct' => $isCorrect, 'score_given' => $scoreGiven]
            );

            $totalScore += $scoreGiven;
            $maxPossibleScore += $question->score_weight;
        }

        $finalGrade = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

        $session->update([
            'submit_time' => now(),
            'score' => $finalGrade,
            'status' => 'completed',
        ]);

        return redirect()->route('student.exam.result', $session->id);
    }
}