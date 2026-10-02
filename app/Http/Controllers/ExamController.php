<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamSession;
use App\Models\ExamAnswer;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamController extends Controller
{
    // Indeks Daftar Ujian untuk Admin & Guru
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            // Admin melihat semua ujian
            $exams = Exam::with(['subject', 'teacher', 'classrooms'])
                        ->latest()
                        ->paginate(10);
        } else {
            // Guru hanya melihat ujian yang dibuatnya
            $exams = Exam::with(['subject', 'classrooms'])
                        ->where('teacher_id', $user->teacher->id)
                        ->latest()
                        ->paginate(10);
        }

        return view('exams.index', compact('exams'));
    }

    // Form Tambah Ujian
    public function create()
    {
        $user = Auth::user();
        $academicYears = AcademicYear::where('is_active', true)->get();

        if ($user->hasRole('admin')) {
            // Admin bisa memilih semua guru, mapel, dan kelas
            $teachers = Teacher::all();
            $subjects = Subject::all();
            $classrooms = Classroom::all();
        } else {
            // Guru hanya melihat mapel dan kelas yang diampunya
            $teacher = $user->teacher;
            $teachers = collect([$teacher]);
            $subjects = $teacher->subjects ?? Subject::all(); // Sesuaikan relasi guru ke mapel
            $classrooms = $teacher->classrooms ?? Classroom::all(); // Sesuaikan relasi guru ke kelas
        }

        return view('exams.create', compact('teachers', 'subjects', 'classrooms', 'academicYears'));
    }

    // Simpan Data Ujian
    public function store(Request $request)
    {
        $rules = [
            'title'            => 'required|string|max:255',
            'type'             => 'required|in:pilihan_ganda,multiple_choice,essay,campuran',
            'subject_id'       => 'required|exists:subjects,id',
            'classroom_ids'    => 'required|array',
            'classroom_ids.*'  => 'exists:classrooms,id',
            'duration_minutes' => 'required|integer|min:1',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ];

        // Jika Admin, field guru wajib dipilih. Jika Guru, otomatis mengambil ID guru login.
        if (Auth::user()->hasRole('admin')) {
            $rules['teacher_id'] = 'required|exists:teachers,id';
        }

        $validated = $request->validate($rules);

        // Tentukan teacher_id
        $teacherId = Auth::user()->hasRole('admin')
            ? $request->teacher_id
            : Auth::user()->teacher->id;

        // Simpan data Ujian
        $exam = Exam::create([
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'subject_id'       => $validated['subject_id'],
            'teacher_id'       => $teacherId,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_time'       => $validated['start_time'],
            'end_time'         => $validated['end_time'],
            'is_active'        => $request->has('is_active'),
        ]);

        // Attach relasi ke banyak kelas (Tabel Pivot classroom_exam)
        $exam->classrooms()->attach($request->classroom_ids);

        // Redirect ke halaman import/tambah soal
        return redirect()->route('teacher.exams.show', $exam->id)
                         ->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
    }
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


    // Halaman Show / Detail Ujian untuk Admin & Guru
    public function show(Exam $exam)
    {
        // Load relasi beserta soal-soalnya
        $exam->load(['subject', 'teacher', 'classrooms', 'questions']);

        // Menghitung ringkasan statistik sederhana
        $totalQuestions = $exam->questions->count();
        $totalScoreWeight = $exam->questions->sum('score_weight');

        return view('exams.show', compact('exam', 'totalQuestions', 'totalScoreWeight'));
    }
}
