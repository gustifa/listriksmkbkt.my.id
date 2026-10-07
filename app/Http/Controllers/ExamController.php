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
use App\Exports\ExamResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;


class ExamController extends Controller
{
    // Indeks Daftar Ujian untuk Admin & Guru
    // public function index()
    // {
    //     $user = Auth::user();

    //     if ($user->hasRole('admin')) {
    //         // Admin melihat semua ujian
    //         $exams = Exam::with(['subject', 'teacher', 'classrooms'])
    //                     ->latest()
    //                     ->paginate(10);
    //     } else {
    //         // Guru hanya melihat ujian yang dibuatnya
    //         $exams = Exam::with(['subject', 'classrooms'])
    //                     ->where('teacher_id', $user->teacher->id)
    //                     ->latest()
    //                     ->paginate(10);
    //     }

    //     return view('exams.index', compact('exams'));
    // }

    // Indeks Daftar Ujian untuk Admin & Guru
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            // Admin melihat semua ujian + hitung jumlah soal
            $exams = Exam::with(['subject', 'teacher', 'classrooms'])
                        ->withCount('questions') // <-- TAMBAHKAN INI
                        ->latest()
                        ->paginate(10);
        } else {
            // Guru hanya melihat ujian miliknya + hitung jumlah soal
            $exams = Exam::with(['subject', 'classrooms'])
                        ->withCount('questions') // <-- TAMBAHKAN INI
                        ->where('teacher_id', $user->teacher->id)
                        ->latest()
                        ->paginate(10);
        }

        return view('exams.index', compact('exams'));
    }

    // Form Tambah Ujian
    public function create()
    {
        /** @var \App\Models\User $user */
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
            'title'               => $validated['title'],
            'type'                => $validated['type'],
            'subject_id'          => $validated['subject_id'],
            'teacher_id'          => $teacherId,
            'academic_year_id'    => $validated['academic_year_id'] ?? null,
            'duration_minutes'    => $validated['duration_minutes'],
            'start_time'          => $validated['start_time'],
            'end_time'            => $validated['end_time'],
            'is_active'           => $request->has('is_active'),
            'randomize_questions' => $request->has('randomize_questions'), // <-- Simpan Fitur Acak Soal
            'randomize_options'   => $request->has('randomize_options'),   // <-- Simpan Fitur Acak Jawaban
        ]);

        // Attach relasi ke banyak kelas (Tabel Pivot classroom_exam)
        $exam->classrooms()->attach($request->classroom_ids);

        // Redirect ke halaman import/tambah soal
        return redirect()->route('guru.exams.show', $exam->id)
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

    // // Siswa Memulai Ujian
    // public function startExam($examId)
    // {
    //     $student = Auth::user()->student; // Relasi ke model Student
    //     $exam = Exam::with('questions')->findOrFail($examId);

    //     $session = ExamSession::firstOrCreate(
    //         ['exam_id' => $examId, 'student_id' => $student->id],
    //         ['start_time' => now(), 'status' => 'ongoing']
    //     );

    //     return view('student.exam.show', compact('exam', 'session'));
    // }

    // // Siswa Submit Ujian & Auto Grading
    // public function submitExam(Request $request, $sessionId)
    // {
    //     $session = ExamSession::findOrFail($sessionId);
    //     $answers = $request->input('answers', []);

    //     $totalScore = 0;
    //     $maxPossibleScore = 0;

    //     foreach ($session->exam->questions as $question) {
    //         $userAnswer = $answers[$question->id] ?? null;
    //         $isCorrect = false;
    //         $scoreGiven = 0;

    //         if ($question->question_type === 'single') {
    //             if (is_array($userAnswer) && count($userAnswer) > 0 && $userAnswer[0] === $question->correct_answer[0]) {
    //                 $isCorrect = true;
    //                 $scoreGiven = $question->score_weight;
    //             }
    //         } elseif ($question->question_type === 'multiple') {
    //             // Pilihan ganda kompleks / centang banyak
    //             sort($userAnswer);
    //             $correct = $question->correct_answer;
    //             sort($correct);
    //             if ($userAnswer === $correct) {
    //                 $isCorrect = true;
    //                 $scoreGiven = $question->score_weight;
    //             }
    //         }

    //         ExamAnswer::updateOrCreate(
    //             ['exam_session_id' => $session->id, 'question_id' => $question->id],
    //             ['answer' => (array) $userAnswer, 'is_correct' => $isCorrect, 'score_given' => $scoreGiven]
    //         );

    //         $totalScore += $scoreGiven;
    //         $maxPossibleScore += $question->score_weight;
    //     }

    //     $finalGrade = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

    //     $session->update([
    //         'submit_time' => now(),
    //         'score' => $finalGrade,
    //         'status' => 'completed',
    //     ]);

    //     return redirect()->route('student.exam.result', $session->id);
    // }

    /**
     * Menampilkan halaman konfirmasi sebelum memulai ujian
     */
    public function startExam(Exam $exam)
    {
        $user = Auth::user();

        // 1. Cek apakah ujian sedang aktif
        if (!$exam->is_active) {
            return redirect()->route('student.dashboard')
                             ->with('error', 'Ujian ini sedang tidak aktif.');
        }

        // 2. Load relasi pendukung & hitung jumlah soal
        $exam->load(['subject', 'teacher']);
        $exam->loadCount('questions');

        // 3. Cek apakah siswa sudah memiliki sesi pengerjaan ujian
        $session = ExamSession::where('exam_id', $exam->id)
                              ->where('user_id', $user->id) // atau student_id
                              ->first();

        return view('student.exams.start', compact('exam', 'session'));
    }

    /**
     * Memproses / menginisiasi sesi ujian saat siswa menekan tombol Mulai Ujian
     */
    public function beginExam(Request $request, Exam $exam)
    {
        $user = Auth::user();

        if (!$exam->is_active) {
            return redirect()->route('student.dashboard')
                             ->with('error', 'Ujian tidak aktif.');
        }

        // Cari atau buat sesi pengerjaan baru
        $session = ExamSession::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ],
            [
                'start_time' => now(),
                'status'     => 'in_progress', // atau 'ongoing'
            ]
        );

        return redirect()->route('student.exam.show', [$exam->id, 'session' => $session->id])
                         ->with('success', 'Ujian berhasil dimulai. Selamat mengerjakan!');
    }


    // Halaman Show / Detail Ujian untuk Admin & Guru
    // public function show(Exam $exam)
    // {
    //     // Load relasi beserta soal-soalnya
    //     $exam->load(['subject', 'teacher', 'classrooms', 'questions']);

    //     // Menghitung ringkasan statistik sederhana
    //     $totalQuestions = $exam->questions->count();
    //     $totalScoreWeight = $exam->questions->sum('score_weight');

    //     return view('exams.show', compact('exam', 'totalQuestions', 'totalScoreWeight'));
    // }

    public function show(Exam $exam)
{
    // Load relasi beserta soal-soalnya
    $exam->load(['subject', 'teacher', 'classrooms', 'questions']);

    // Menghitung ringkasan statistik sederhana
    $totalQuestions = $exam->questions->count();
    $totalScoreWeight = $exam->questions->sum('score_weight');

    // Memproses rekapitulasi pilihan jawaban siswa per soal
    foreach ($exam->questions as $question) {
        // Ambil semua jawaban siswa dari tabel exam_answers
        $answers = \App\Models\ExamAnswer::where('question_id', $question->id)->get();

        $recap = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
            'kosong' => 0,
            'correct_count' => 0,
            'wrong_count' => 0,
            'total_answered' => $answers->count()
        ];

        foreach ($answers as $ans) {
            // Kolom 'answer' menyimpan JSON seperti ["A"] atau ["A", "B"]
            $rawAnswer = $ans->answer;

            // Lakukan decoding jika masih berbentuk string JSON
            if (is_string($rawAnswer)) {
                $rawAnswer = json_decode($rawAnswer, true);
            }

            // Hitung distribusi pilihan opsi
            if (is_array($rawAnswer) && !empty($rawAnswer)) {
                foreach ($rawAnswer as $chosenOpt) {
                    $optKey = strtoupper(trim($chosenOpt));
                    if (array_key_exists($optKey, $recap)) {
                        $recap[$optKey]++;
                    }
                }
            } elseif (!empty($rawAnswer) && is_string($rawAnswer)) {
                $optKey = strtoupper(trim($rawAnswer));
                if (array_key_exists($optKey, $recap)) {
                    $recap[$optKey]++;
                }
            } else {
                $recap['kosong']++;
            }

            // Hitung statistik jawaban benar / salah
            if ($ans->is_correct) {
                $recap['correct_count']++;
            } else {
                $recap['wrong_count']++;
            }
        }

        // Lampirkan data rekap ke objek $question
        $question->recap = $recap;
    }

    return view('exams.show', compact('exam', 'totalQuestions', 'totalScoreWeight'));
}


    public function destroy(Exam $exam)
    {
        $user = Auth::user();

        // Keamanan: Jika user adalah Guru, pastikan hanya bisa menghapus ujian miliknya sendiri
        if (!$user->hasRole('admin') && $exam->teacher_id !== $user->teacher->id) {
            return redirect()->route('exams.index')
                             ->with('error', 'Anda tidak memiliki hak akses untuk menghapus ujian ini.');
        }

        try {
            // 1. Detach / Hapus relasi pivot dengan kelas di tabel classroom_exam
            $exam->classrooms()->detach();

            // 2. Hapus semua soal terkait ujian ini (jika tidak menggunakan Cascade on Delete di Database)
            $exam->questions()->delete();

            // 3. Hapus data ujian utama
            $exam->delete();

            return redirect()->route('exams.index')
                             ->with('success', 'Ujian beserta data terkait berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('exams.index')
                             ->with('error', 'Gagal menghapus ujian: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Exam $exam)
    {
        // Balik status is_active (jika true jadi false, jika false jadi true)
        $exam->update([
            'is_active' => !$exam->is_active,
        ]);

        $statusText = $exam->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Status ujian '{$exam->title}' berhasil {$statusText}.");
    }

    /**
     * Tampilan Laporan Rekap Nilai Ujian
     */
    public function showReport(Exam $exam)
    {
        // Total Bobot Soal Ujian
        $totalMaxScore = $exam->questions()->sum('score_weight');

        // // Ambil Hasil Sesi Ujian Siswa
        // $sessions = ExamSession::with(['student.classroom', 'answers'])
        //     ->where('exam_id', $exam->id)
        //     ->orderBy('finished_at', 'desc')
        //     ->get();
        // Ambil Hasil Sesi Ujian Siswa
        $sessions = ExamSession::with(['student.classroom', 'answers'])
            ->where('exam_id', $exam->id)
            ->orderBy('updated_at', 'desc') // Menggunakan updated_at atau end_time
            ->get();

        // Statistik Ringkas
        $completedSessions = $sessions->where('status', 'completed');
        $averageScore = $completedSessions->avg('total_score') ?? 0;
        $highestScore = $completedSessions->max('total_score') ?? 0;
        $lowestScore = $completedSessions->min('total_score') ?? 0;

        return view('guru.exams.report', compact(
            'exam',
            'sessions',
            'totalMaxScore',
            'averageScore',
            'highestScore',
            'lowestScore'
        ));
    }

    /**
     * Export Rekap Nilai ke Excel
     */
    public function exportExcel(Exam $exam)
    {
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->title);
        $filename = 'Rekap_Nilai_' . $safeTitle . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new ExamResultsExport($exam), $filename);
    }

    public function resetSession(ExamSession $session)
{
    try {
        // 1. Hapus semua jawaban siswa yang terkait dengan sesi ini
        ExamAnswer::where('exam_session_id', $session->id)->delete();

        // 2. Hapus record sesi ujian
        $session->delete();

        return redirect()->back()->with('success', 'Sesi ujian siswa berhasil di-reset. Siswa dapat mengerjakan ujian kembali.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal mereset sesi ujian: ' . $e->getMessage());
    }
}

    /**
     * Memperbarui data ujian (waktu, durasi, target kelas, dll.)
     */
    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'duration_minutes' => 'required|numeric|min:1',
            'start_time'       => 'nullable|date',
            'end_time'         => 'nullable|date|after_or_equal:start_time',
            'classroom_ids'    => 'required|array',
        ]);

        // 1. Update atribut utama ujian
        $exam->update([
            'title'               => $request->title,
            'duration_minutes'    => $request->duration_minutes,
            'start_time'          => $request->start_time,
            'end_time'            => $request->end_time,
            'is_active'           => $request->has('is_active') ? true : false,
            'randomize_questions' => $request->has('randomize_questions') ? true : false, // <-- Update
            'randomize_options'   => $request->has('randomize_options') ? true : false,   // <-- Update
        ]);

        // 2. Sync relasi target kelas (Pivot: classroom_exam)
        $exam->classrooms()->sync($request->classroom_ids);

        return redirect()->back()->with('success', 'Jadwal dan informasi ujian berhasil diperbarui!');
    }

    public function generateToken(Request $request, Exam $exam)
{
    // Generasi 6 Karakter Acak Kapital (Misal: X7K9PQ)
    $newToken = strtoupper(Str::random(6));

    $exam->update([
        'token' => $newToken
    ]);

    return redirect()->back()->with('success', "Token Ujian Berhasil Diperbarui: {$newToken}");
}

public function unsubmittedStudents(Exam $exam)
{
    // Load relasi kelas target ujian
    $exam->load('classrooms');

    // Ambil semua ID kelas target
    $classroomIds = $exam->classrooms->pluck('id');

    // Ambil daftar ID siswa yang sudah membuat sesi ujian (baik sedang mengerjakan / sudah selesai)
    $participatedStudentIds = ExamSession::where('exam_id', $exam->id)
        ->pluck('student_id')
        ->toArray();

    // Ambil data siswa yang terdaftar di kelas target tetapi ID-nya TIDAK ADA di $participatedStudentIds
    $unsubmittedStudents = \App\Models\Student::with('classroom')
        ->whereIn('classroom_id', $classroomIds)
        ->whereNotIn('id', $participatedStudentIds)
        ->orderBy('classroom_id')
        ->orderBy('name')
        ->get();

    // Mengelompokkan siswa berdasarkan kelas
    $groupedByClass = $unsubmittedStudents->groupBy(function($student) {
        return $student->classroom->name ?? 'Tanpa Kelas';
    });

    return view('guru.exams.unsubmitted', compact('exam', 'groupedByClass', 'unsubmittedStudents'));
}

/**
     * Menyelesaikan ujian siswa secara paksa oleh Admin / Guru
     */
    // public function finishSessionByAdmin(ExamSession $session)
    // {
    //     try {
    //         // Ambil semua jawaban yang sudah disubmit oleh siswa di sesi ini
    //         $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

    //         // Hitung total skor dari akumulasi nilai jawaban
    //         $totalScore = 0;
    //         foreach ($answers as $ans) {
    //             $totalScore += $ans->score_given ?? 0;
    //         }

    //         // Update status sesi menjadi completed/selesai, catat waktu submit, dan simpan skornya
    //         $session->update([
    //             'status'      => 'completed',
    //             'submit_time' => now(),
    //             'score'       => $totalScore,
    //         ]);

    //         return redirect()->back()->with('success', 'Ujian siswa berhasil diselesaikan oleh Admin/Guru.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Gagal menyelesaikan ujian: ' . $e->getMessage());
    //     }
    // }

    public function finishSessionByAdmin(ExamSession $session)
{
    try {
        // Load relasi jawaban beserta detail soalnya
        $answers = ExamAnswer::with('question')->where('exam_session_id', $session->id)->get();

        $totalScore = 0;

        foreach ($answers as $ans) {
            $question = $ans->question;
            $scoreGiven = 0;

            if ($question) {
                // Decode jawaban siswa (karena di DB bertipe json)
                $studentAnswer = is_string($ans->answer) ? json_decode($ans->answer, true) : $ans->answer;

                // Jika soal Pilihan Ganda / PG Kompleks
                if (isset($question->correct_answer)) {
                    $correctAnswer = is_string($question->correct_answer)
                        ? json_decode($question->correct_answer, true)
                        : $question->correct_answer;

                    // Cocokkan jawaban siswa dengan kunci jawaban
                    if ($studentAnswer == $correctAnswer) {
                        $scoreGiven = $question->score ?? $question->weight ?? 10; // Sesuaikan bobot nilai per soal
                    }
                }
            }

            // Update score_given pada masing-masing jawaban jika belum ada
            $ans->update([
                'score_given' => $scoreGiven,
                'is_correct'  => $scoreGiven > 0 ? true : false,
            ]);

            $totalScore += $scoreGiven;
        }

        // Update status sesi exam_sessions
        $session->update([
            'status'      => 'completed',
            'submit_time' => now(),
            'score'       => $totalScore,
        ]);

        return redirect()->back()->with('success', 'Ujian siswa berhasil diselesaikan dan nilai berhasil dihitung.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal menyelesaikan ujian: ' . $e->getMessage());
    }
}


public function getStudentsByAnswer(Question $question, Request $request)
{
    try {
        $option = strtoupper(trim($request->query('option', '')));

        // Menggunakan relasi 'session' (bukan examSession)
        $answers = ExamAnswer::where('question_id', $question->id)
            ->with(['session.student.classroom'])
            ->get();

        $students = [];

        foreach ($answers as $ans) {
            // Ambil sesi pengerjaan (mencoba relasi session atau examSession)
            $session = $ans->session ?? $ans->examSession ?? null;
            if (!$session) {
                continue;
            }

            // Ambil data siswa
            $student = $session->student ?? null;

            // Ambil nama siswa dan kelas
            $studentName = optional($student)->name
                ?? optional(optional($session)->user)->name
                ?? 'Siswa Tanpa Nama';

            $className = optional(optional($student)->classroom)->name
                ?? optional(optional(optional($session)->user)->classroom)->name
                ?? '-';

            // Parsing jawaban siswa
            $rawAnswer = $ans->answer;
            if (is_string($rawAnswer)) {
                $decoded = json_decode($rawAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $rawAnswer = $decoded;
                }
            }

            // Pengecekan kecocokan opsi jawaban
            $isMatch = false;

            if ($option === 'KOSONG') {
                if (empty($rawAnswer)) {
                    $isMatch = true;
                }
            } else {
                if (is_array($rawAnswer)) {
                    $normalized = array_map(function($v) {
                        return strtoupper(trim((string)$v));
                    }, $rawAnswer);
                    $isMatch = in_array($option, $normalized, true);
                } elseif (!empty($rawAnswer)) {
                    $isMatch = (strtoupper(trim((string)$rawAnswer)) === $option);
                }
            }

            if ($isMatch) {
                $students[] = [
                    'name'  => $studentName,
                    'class' => $className,
                ];
            }
        }

        return response()->json([
            'status'   => 'success',
            'option'   => $option,
            'students' => $students
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine()
        ], 500);
    }
}

}
