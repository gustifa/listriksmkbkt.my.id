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
    // Indeks Daftar Ujian untuk Admin & Guru (Mendukung Team Teaching)
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $exams = Exam::with(['subject', 'teacher', 'classrooms', 'collaborators'])
                        ->withCount('questions')
                        ->latest()
                        ->paginate(10);
        } else {
            $teacherId = optional($user->teacher)->id;

            // Mengambil ujian buatan sendiri ATAU ujian yang dikolaborasikan
            $exams = Exam::with(['subject', 'classrooms', 'collaborators'])
                        ->withCount('questions')
                        ->where(function($query) use ($teacherId) {
                            $query->where('teacher_id', $teacherId)
                                  ->orWhereHas('collaborators', function($q) use ($teacherId) {
                                      $q->where('teachers.id', $teacherId);
                                  });
                        })
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
            $teachers = Teacher::all();
            $subjects = Subject::all();
            $classrooms = Classroom::all();
        } else {
            $teacher = $user->teacher;
            // Ambil daftar guru lain untuk pilihan kolaborator
            $teachers = Teacher::where('id', '!=', optional($teacher)->id)->get();
            $subjects = optional($teacher)->subjects ?? Subject::all();
            $classrooms = optional($teacher)->classrooms ?? Classroom::all();
        }

        return view('exams.create', compact('teachers', 'subjects', 'classrooms', 'academicYears'));
    }

    // Simpan Data Ujian Baru
    public function store(Request $request)
    {
        $rules = [
            'title'              => 'required|string|max:255',
            'type'               => 'required|in:pilihan_ganda,multiple_choice,essay,campuran',
            'subject_id'         => 'required|exists:subjects,id',
            'classroom_ids'      => 'required|array',
            'classroom_ids.*'    => 'exists:classrooms,id',
            'duration_minutes'   => 'required|integer|min:1',
            'start_time'         => 'required|date',
            'end_time'           => 'required|date|after:start_time',
            'academic_year_id'   => 'nullable|exists:academic_years,id',
            'collaborator_ids'   => 'nullable|array',
            'collaborator_ids.*' => 'exists:teachers,id',
        ];

        if (Auth::user()->hasRole('admin')) {
            $rules['teacher_id'] = 'required|exists:teachers,id';
        }

        $validated = $request->validate($rules);

        $teacherId = Auth::user()->hasRole('admin')
            ? $request->teacher_id
            : Auth::user()->teacher->id;

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
            'is_team_teaching'    => $request->has('is_team_teaching'),
            'allow_review'        => $request->has('allow_review'),
            'show_correct_answer' => $request->has('show_correct_answer'),
            'randomize_questions' => $request->has('randomize_questions'),
            'randomize_options'   => $request->has('randomize_options'),
            'enable_anti_cheat'   => $request->has('enable_anti_cheat'),
            'show_score'   => $request->has('show_score'),
        ]);

        $exam->classrooms()->attach($request->classroom_ids);

        // Simpan relasi Team Teaching jika diaktifkan
        if ($request->has('is_team_teaching') && $request->has('collaborator_ids')) {
            $exam->collaborators()->sync($request->input('collaborator_ids', []));
        }

        return redirect()->route('guru.exams.show', $exam->id)
                         ->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
    }

    // Upload Soal oleh Guru (Excel Template)
    public function importQuestions(Request $request, $examId)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        for ($i = 2; $i <= count($sheetData); $i++) {
            $row = $sheetData[$i];
            if (empty($row['A'])) continue;

            $type = strtolower($row['A']);

            $options = null;
            if ($type !== 'essay') {
                $options = [
                    ['key' => 'A', 'text' => $row['C']],
                    ['key' => 'B', 'text' => $row['D']],
                    ['key' => 'C', 'text' => $row['E']],
                    ['key' => 'D', 'text' => $row['F']],
                ];
            }

            $correctAnswers = !empty($row['G']) ? array_map('trim', explode(',', $row['G'])) : null;

            Question::create([
                'exam_id'        => $examId,
                'question_type'  => $type,
                'question_text'  => $row['B'],
                'options'        => $options,
                'correct_answer' => $correctAnswers,
                'score_weight'   => $row['H'] ?? 1,
            ]);
        }

        return redirect()->back()->with('success', 'Soal berhasil diunggah.');
    }

    /**
     * Menampilkan halaman konfirmasi sebelum memulai ujian
     */
    public function startExam(Exam $exam)
    {
        $user = Auth::user();

        if (!$exam->is_active) {
            return redirect()->route('student.dashboard')
                             ->with('error', 'Ujian ini sedang tidak aktif.');
        }

        $exam->load(['subject', 'teacher']);
        $exam->loadCount('questions');

        $session = ExamSession::where('exam_id', $exam->id)
                              ->where('user_id', $user->id)
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

        $session = ExamSession::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ],
            [
                'start_time' => now(),
                'status'     => 'in_progress',
            ]
        );

        return redirect()->route('student.exam.show', [$exam->id, 'session' => $session->id])
                         ->with('success', 'Ujian berhasil dimulai. Selamat mengerjakan!');
    }

    // Detail Ujian untuk Admin & Guru (Dengan Proteksi Akses Team Teaching)
    public function show(Exam $exam)
    {
        $user = Auth::user();
        $teacherId = optional($user->teacher)->id;

        // Proteksi Hak Akses
        if (!$user->hasRole('admin') && !$exam->hasAccess($teacherId)) {
            return redirect()->route('exams.index')->with('error', 'Anda tidak memiliki akses ke ujian ini.');
        }

        $exam->load(['subject', 'teacher', 'classrooms', 'questions', 'collaborators']);
        
        // Ambil daftar guru lain untuk modal Team Teaching
        $availableTeachers = Teacher::where('id', '!=', $exam->teacher_id)->get();

        $totalQuestions = $exam->questions->count();
        $totalScoreWeight = $exam->questions->sum('score_weight');

        foreach ($exam->questions as $question) {
            $answers = \App\Models\ExamAnswer::where('question_id', $question->id)->get();

            $recap = [
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
                'kosong' => 0,
                'correct_count' => 0,
                'wrong_count' => 0,
                'total_answered' => $answers->count()
            ];

            foreach ($answers as $ans) {
                $rawAnswer = $ans->answer;

                if (is_string($rawAnswer)) {
                    $rawAnswer = json_decode($rawAnswer, true);
                }

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

                if ($ans->is_correct) {
                    $recap['correct_count']++;
                } else {
                    $recap['wrong_count']++;
                }
            }

            $question->recap = $recap;
        }

        return view('exams.show', compact('exam', 'totalQuestions', 'totalScoreWeight', 'availableTeachers'));
    }

    public function destroy(Exam $exam)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin') && $exam->teacher_id !== optional($user->teacher)->id) {
            return redirect()->route('exams.index')
                             ->with('error', 'Anda tidak memiliki hak akses untuk menghapus ujian ini.');
        }

        try {
            $exam->classrooms()->detach();
            $exam->collaborators()->detach();
            $exam->questions()->delete();
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
        $totalMaxScore = $exam->questions()->sum('score_weight');

        $sessions = ExamSession::with(['student.classroom', 'answers'])
            ->where('exam_id', $exam->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $completedSessions = $sessions->where('status', 'completed');
        $averageScore = $completedSessions->avg('score') ?? 0;
        $highestScore = $completedSessions->max('score') ?? 0;
        $lowestScore = $completedSessions->min('score') ?? 0;

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
            ExamAnswer::where('exam_session_id', $session->id)->delete();
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

        $exam->update([
            'title'               => $request->title,
            'duration_minutes'    => $request->duration_minutes,
            'start_time'          => $request->start_time,
            'end_time'            => $request->end_time,
            'is_active'           => $request->has('is_active'),
            'allow_review'        => $request->has('allow_review'),
            'show_correct_answer' => $request->has('show_correct_answer'),
            'randomize_questions' => $request->has('randomize_questions'),
            'randomize_options'   => $request->has('randomize_options'),
            'enable_anti_cheat'   => $request->has('enable_anti_cheat'),
            'show_score'   => $request->has('show_score'),
        ]);

        $exam->classrooms()->sync($request->classroom_ids);

        return redirect()->back()->with('success', 'Jadwal dan informasi ujian berhasil diperbarui!');
    }

    public function generateToken(Request $request, Exam $exam)
    {
        $newToken = strtoupper(Str::random(6));

        $exam->update([
            'token' => $newToken
        ]);

        return redirect()->back()->with('success', "Token Ujian Berhasil Diperbarui: {$newToken}");
    }

    public function unsubmittedStudents(Exam $exam)
    {
        $exam->load('classrooms');
        $classroomIds = $exam->classrooms->pluck('id');

        $participatedStudentIds = ExamSession::where('exam_id', $exam->id)
            ->pluck('student_id')
            ->toArray();

        $unsubmittedStudents = \App\Models\Student::with('classroom')
            ->whereIn('classroom_id', $classroomIds)
            ->whereNotIn('id', $participatedStudentIds)
            ->orderBy('classroom_id')
            ->orderBy('name')
            ->get();

        $groupedByClass = $unsubmittedStudents->groupBy(function($student) {
            return $student->classroom->name ?? 'Tanpa Kelas';
        });

        return view('guru.exams.unsubmitted', compact('exam', 'groupedByClass', 'unsubmittedStudents'));
    }

    public function finishSessionByAdmin(ExamSession $session)
    {
        try {
            $exam = $session->exam()->with('questions')->first();
            $questions = $exam->questions;

            $totalMaxScore = $questions->sum('score_weight') ?: $questions->sum('score') ?: ($questions->count() * 10);

            if ($totalMaxScore == 0) {
                return redirect()->back()->with('error', 'Gagal menghitung: Total bobot soal ujian adalah 0.');
            }

            $totalEarnedScore = 0;

            foreach ($questions as $question) {
                $ans = ExamAnswer::where('exam_session_id', $session->id)
                    ->where('question_id', $question->id)
                    ->first();

                if (!$ans || empty($ans->answer)) {
                    continue;
                }

                $weight = $question->score_weight ?? $question->score ?? 10;
                $scoreGiven = 0;
                $isCorrect = false;

                $studentAnswer = is_string($ans->answer) ? json_decode($ans->answer, true) : $ans->answer;
                $correctAnswer = is_string($question->correct_answer) ? json_decode($question->correct_answer, true) : $question->correct_answer;

                if (is_array($studentAnswer) && is_array($correctAnswer)) {
                    sort($studentAnswer);
                    sort($correctAnswer);
                    if ($studentAnswer === $correctAnswer) {
                        $isCorrect = true;
                    }
                } else {
                    if ($studentAnswer == $correctAnswer) {
                        $isCorrect = true;
                    }
                }

                if ($isCorrect) {
                    $scoreGiven = $weight;
                    $totalEarnedScore += $weight;
                }

                $ans->update([
                    'score_given' => $scoreGiven,
                    'is_correct'  => $isCorrect,
                ]);
            }

            $finalGrade = min(100, round(($totalEarnedScore / $totalMaxScore) * 100, 1));

            $session->update([
                'status'      => 'completed',
                'submit_time' => now(),
                'score'       => $finalGrade,
            ]);

            return redirect()->back()->with('success', 'Ujian berhasil diselesaikan paksa. Nilai akhir: ' . $finalGrade);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyelesaikan ujian: ' . $e->getMessage());
        }
    }

    public function getStudentsByAnswer(Question $question, Request $request)
    {
        try {
            $option = strtoupper(trim($request->query('option', '')));

            $answers = ExamAnswer::where('question_id', $question->id)
                ->with(['session.student.classroom'])
                ->get();

            $students = [];

            foreach ($answers as $ans) {
                $session = $ans->session ?? $ans->examSession ?? null;
                if (!$session) {
                    continue;
                }

                $student = $session->student ?? null;

                $studentName = optional($student)->name
                    ?? optional(optional($session)->user)->name
                    ?? 'Siswa Tanpa Nama';

                $className = optional(optional($student)->classroom)->name
                    ?? optional(optional(optional($session)->user)->classroom)->name
                    ?? '-';

                $rawAnswer = $ans->answer;
                if (is_string($rawAnswer)) {
                    $decoded = json_decode($rawAnswer, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $rawAnswer = $decoded;
                    }
                }

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

    /**
     * Analisis Butir Soal Ilmiah (Tingkat Kesukaran & Daya Beda)
     */
    public function itemAnalysis(Exam $exam)
    {
        $exam->load(['subject', 'questions']);

        $completedSessions = ExamSession::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->orderBy('score', 'desc')
            ->get();

        $totalStudents = $completedSessions->count();

        if ($totalStudents === 0) {
            return redirect()->back()->with('error', 'Belum ada siswa yang menyelesaikan ujian ini untuk dianalisis.');
        }

        $groupSize = max(1, (int) round($totalStudents * 0.27));
        $upperSessions = $completedSessions->take($groupSize)->pluck('id');
        $lowerSessions = $completedSessions->take(-$groupSize)->pluck('id');

        $analysisResult = [];

        foreach ($exam->questions as $index => $question) {
            $answers = ExamAnswer::where('question_id', $question->id)->get();

            $correctCount = $answers->where('is_correct', true)->count();
            $facilityValue = $totalStudents > 0 ? ($correctCount / $totalStudents) : 0;

            if ($facilityValue > 0.70) {
                $difficultyCategory = 'Mudah';
                $difficultyBadge = 'bg-success';
            } elseif ($facilityValue >= 0.30) {
                $difficultyCategory = 'Sedang (Ideal)';
                $difficultyBadge = 'bg-primary';
            } else {
                $difficultyCategory = 'Sukar';
                $difficultyBadge = 'bg-danger';
            }

            $upperCorrect = ExamAnswer::where('question_id', $question->id)
                ->whereIn('exam_session_id', $upperSessions)
                ->where('is_correct', true)
                ->count();

            $lowerCorrect = ExamAnswer::where('question_id', $question->id)
                ->whereIn('exam_session_id', $lowerSessions)
                ->where('is_correct', true)
                ->count();

            $discriminationIndex = $groupSize > 0 ? (($upperCorrect - $lowerCorrect) / $groupSize) : 0;

            if ($discriminationIndex >= 0.40) {
                $discriminationCategory = 'Sangat Baik';
            } elseif ($discriminationIndex >= 0.30) {
                $discriminationCategory = 'Baik';
            } elseif ($discriminationIndex >= 0.20) {
                $discriminationCategory = 'Cukup (Perlu Revisi)';
            } else {
                $discriminationCategory = 'Buruk (Dibuang/Diganti)';
            }

            $analysisResult[] = [
                'no' => $index + 1,
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'correct_count' => $correctCount,
                'wrong_count' => $totalStudents - $correctCount,
                'facility_value' => number_format($facilityValue, 2),
                'difficulty_category' => $difficultyCategory,
                'difficulty_badge' => $difficultyBadge,
                'discrimination_index' => number_format($discriminationIndex, 2),
                'discrimination_category' => $discriminationCategory,
            ];
        }

        return view('guru.exams.item_analysis', compact('exam', 'totalStudents', 'analysisResult'));
    }

    /**
     * Export Laporan Analisis Butir Soal Lengkap ke Format PDF / Cetak
     */
    public function exportItemAnalysisPdf(Exam $exam)
    {
        $exam->load(['subject', 'questions']);

        $completedSessions = ExamSession::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->orderBy('score', 'desc')
            ->get();

        $totalStudents = $completedSessions->count();

        if ($totalStudents === 0) {
            return redirect()->back()->with('error', 'Belum ada data siswa untuk dicetak.');
        }

        $groupSize = max(1, (int) round($totalStudents * 0.27));
        $upperSessions = $completedSessions->take($groupSize)->pluck('id');
        $lowerSessions = $completedSessions->take(-$groupSize)->pluck('id');

        $analysisResult = [];
        $easyCount = 0;
        $mediumCount = 0;
        $hardCount = 0;

        foreach ($exam->questions as $index => $question) {
            $answers = ExamAnswer::where('question_id', $question->id)->get();
            $correctCount = $answers->where('is_correct', true)->count();
            $facilityValue = $totalStudents > 0 ? ($correctCount / $totalStudents) : 0;

            if ($facilityValue > 0.70) {
                $difficultyCategory = 'Mudah';
                $easyCount++;
            } elseif ($facilityValue >= 0.30) {
                $difficultyCategory = 'Sedang (Ideal)';
                $mediumCount++;
            } else {
                $difficultyCategory = 'Sukar';
                $hardCount++;
            }

            $upperCorrect = ExamAnswer::where('question_id', $question->id)
                ->whereIn('exam_session_id', $upperSessions)
                ->where('is_correct', true)
                ->count();

            $lowerCorrect = ExamAnswer::where('question_id', $question->id)
                ->whereIn('exam_session_id', $lowerSessions)
                ->where('is_correct', true)
                ->count();

            $discriminationIndex = $groupSize > 0 ? (($upperCorrect - $lowerCorrect) / $groupSize) : 0;

            if ($discriminationIndex >= 0.40) {
                $discriminationCategory = 'Sangat Baik';
            } elseif ($discriminationIndex >= 0.30) {
                $discriminationCategory = 'Baik';
            } elseif ($discriminationIndex >= 0.20) {
                $discriminationCategory = 'Cukup';
            } else {
                $discriminationCategory = 'Buruk';
            }

            $analysisResult[] = [
                'no' => $index + 1,
                'question_text' => $question->question_text,
                'correct_count' => $correctCount,
                'wrong_count' => $totalStudents - $correctCount,
                'facility_value' => number_format($facilityValue, 2),
                'difficulty_category' => $difficultyCategory,
                'discrimination_index' => number_format($discriminationIndex, 2),
                'discrimination_category' => $discriminationCategory,
            ];
        }

        $summary = [
            'total_questions' => count($exam->questions),
            'total_students' => $totalStudents,
            'easy' => $easyCount,
            'medium' => $mediumCount,
            'hard' => $hardCount
        ];

        return view('guru.exams.item_analysis_pdf', compact('exam', 'analysisResult', 'summary'));
    }

    /**
     * Memperbarui Daftar Guru Kolaborator (Team Teaching)
     */
    public function updateTeamTeaching(Request $request, Exam $exam)
    {
        $user = Auth::user();

        // Hanya Pembuat Utama (Owner) atau Admin yang boleh mengelola Team Teaching
        if (!$user->hasRole('admin') && $exam->teacher_id !== optional($user->teacher)->id) {
            return redirect()->back()->with('error', 'Hanya pembuat utama ujian yang dapat mengelola Team Teaching.');
        }

        $request->validate([
            'collaborator_ids'   => 'nullable|array',
            'collaborator_ids.*' => 'exists:teachers,id',
        ]);

        $collaboratorIds = $request->input('collaborator_ids', []);

        // Update status flag is_team_teaching
        $exam->update([
            'is_team_teaching' => count($collaboratorIds) > 0,
        ]);

        // Sinkronisasi tabel pivot exam_teacher
        $exam->collaborators()->sync($collaboratorIds);

        return redirect()->back()->with('success', 'Daftar Team Teaching / Guru Kolaborator berhasil diperbarui!');
    }

    public function unblockStudentSession($sessionId)
    {
        $session = ExamSession::findOrFail($sessionId);
        
        // Reset status penguncian dan hitungan kecurangan
        $session->is_blocked = false;
        $session->violation_count = 0; // Atau biarkan tetap 2 jika ingin memberi 1x kesempatan lagi
        $session->save();

        return redirect()->back()->with('success', 'Akses ujian siswa berhasil dibuka kembali.');
    }

    public function toggleScore(Request $request, Exam $exam)
{
    $exam->update([
        'show_score' => $request->has('show_score'),
    ]);

    return redirect()->back()->with('success', 'Status visibilitas nilai berhasil diperbarui!');
}
}