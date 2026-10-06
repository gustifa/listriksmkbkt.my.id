<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Student;

class StudentExamController extends Controller
{
    /**
     * Helper untuk mengambil ID Student milik user yang sedang login
     */
    private function getStudentId()
    {
        $user = Auth::user();
        return $user->student->id ?? $user->id;
    }

    /**
     * 1. Menampilkan Daftar Ujian Siswa (Index)
     */
    // public function index()
    // {
    //     $studentId = $this->getStudentId();

    //     // Query Ujian Aktif
    //     $activeExams = Exam::with(['subject', 'teacher', 'sessions' => function ($q) use ($studentId) {
    //                         $q->where('student_id', $studentId);
    //                     }])
    //                     ->withCount('questions')
    //                     ->where('is_active', true)
    //                     ->latest()
    //                     ->get();

    //     // Query Ujian Selesai (Riwayat)
    //     $completedExams = Exam::with(['subject', 'sessions' => function ($q) use ($studentId) {
    //                             $q->where('student_id', $studentId)->where('status', 'completed');
    //                         }])
    //                         ->whereHas('sessions', function ($q) use ($studentId) {
    //                             $q->where('student_id', $studentId)->where('status', 'completed');
    //                         })
    //                         ->latest()
    //                         ->get();

    //     return view('students.exam.index', compact('activeExams', 'completedExams'));
    // }

    public function index()
    {
        $studentId = $this->getStudentId();

        // 1. Ambil data siswa yang sedang login beserta ID kelasnya
        $student = \App\Models\Student::findOrFail($studentId);
        $classroomId = $student->classroom_id;

        // 2. Query Ujian Aktif (Hanya untuk kelas siswa)
        $activeExams = Exam::with(['subject', 'teacher', 'classrooms', 'sessions' => function ($q) use ($studentId) {
                                $q->where('student_id', $studentId);
                            }])
                            ->withCount('questions')
                            ->where('is_active', true)
                            // Filter agar hanya mengambil ujian yang mendaftarkan kelas siswa
                            ->whereHas('classrooms', function ($q) use ($classroomId) {
                                $q->where('classrooms.id', $classroomId);
                            })
                            ->latest()
                            ->get();

        // 3. Query Ujian Selesai / Riwayat
        $completedExams = Exam::with(['subject', 'teacher', 'classrooms', 'sessions' => function ($q) use ($studentId) {
                                    $q->where('student_id', $studentId)->where('status', 'completed');
                                }])
                                ->whereHas('sessions', function ($q) use ($studentId) {
                                    $q->where('student_id', $studentId)->where('status', 'completed');
                                })
                                ->latest()
                                ->get();

        return view('students.exam.index', compact('activeExams', 'completedExams'));
    }

    // public function index()
    // {
    //     $studentId = $this->getStudentId();
    //     $user = Auth::user();

    //     // Ambil classroom_id milik siswa (sesuaikan nama property jika berbeda)
    //     $classroomId = $user->student->classroom_id ?? null;

    //     // Base query: Filter ujian yang hanya terhubung dengan kelas siswa ini
    //     $baseExamQuery = Exam::whereHas('classrooms', function ($q) use ($classroomId) {
    //         $q->where('classrooms.id', $classroomId);
    //     });

    //     // 1. Query Ujian Aktif (Sesuai Kelas Siswa)
    //     $activeExams = (clone $baseExamQuery)
    //         ->with(['subject', 'teacher', 'sessions' => function ($q) use ($studentId) {
    //             $q->where('student_id', $studentId);
    //         }])
    //         ->withCount('questions')
    //         ->where('is_active', true)
    //         ->latest()
    //         ->get();

    //     // 2. Query Ujian Selesai (Riwayat)
    //     $completedExams = (clone $baseExamQuery)
    //         ->with(['subject', 'sessions' => function ($q) use ($studentId) {
    //             $q->where('student_id', $studentId)->where('status', 'completed');
    //         }])
    //         ->whereHas('sessions', function ($q) use ($studentId) {
    //             $q->where('student_id', $studentId)->where('status', 'completed');
    //         })
    //         ->latest()
    //         ->get();

    //     return view('students.exam.index', compact('activeExams', 'completedExams'));
    // }

    /**
     * 2. Halaman Konfirmasi Awal Sebelum Mulai Ujian
     */
    public function startExam(Exam $exam)
    {
        $studentId = $this->getStudentId();

        if (!$exam->is_active) {
            return redirect()->route('student.exam.index')
                             ->with('error', 'Ujian ini sedang tidak aktif.');
        }

        $exam->load(['subject', 'teacher']);
        $exam->loadCount('questions');

        // Cek sesi yang sudah ada
        $session = ExamSession::where('exam_id', $exam->id)
                              ->where('student_id', $studentId)
                              ->first();

        return view('students.exam.start', compact('exam', 'session'));
    }

    /**
     * 3. Memulai Sesi Ujian (Create/Fetch Session)
     */
    // public function beginExam(Request $request, Exam $exam)
    // {
    //     $studentId = $this->getStudentId();

    //     if (!$exam->is_active) {
    //         return redirect()->route('student.exam.index')
    //                          ->with('error', 'Ujian sedang tidak aktif.');
    //     }

    //     // Cari atau buat sesi pengerjaan baru
    //     $session = ExamSession::firstOrCreate(
    //         [
    //             'exam_id'   => $exam->id,
    //             'student_id' => $studentId,
    //         ],
    //         [
    //             'start_time' => now(),
    //             'status'     => 'ongoing', // Menggunakan enum 'ongoing'
    //         ]
    //     );

    //     return redirect()->route('student.exam.show', [$exam->id, $session->id])
    //                      ->with('success', 'Ujian dimulai. Selamat mengerjakan!');
    // }

//     public function beginExam(Request $request, Exam $exam)
// {
//     $user = Auth::user();

//     if (!$exam->is_active) {
//         return redirect()->route('student.dashboard')
//                          ->with('error', 'Ujian sedang tidak aktif.');
//     }

//     // Pengecekan Token Ujian (jika ujian memiliki token)
//     if (!empty($exam->token)) {
//         $request->validate([
//             'token' => 'required|string',
//         ], [
//             'token.required' => 'Token ujian wajib diisi.'
//         ]);

//         if (strtoupper($request->token) !== strtoupper($exam->token)) {
//             return redirect()->back()->with('error', 'Token ujian yang Anda masukkan salah!');
//         }
//     }

//     // Buat atau ambil sesi ujian
//     $session = ExamSession::firstOrCreate(
//         [
//             'exam_id' => $exam->id,
//             'student_id' => $student->id,
//         ],
//         [
//             'start_time' => now(),
//             'status'     => 'in_progress',
//         ]
//     );

//     return redirect()->route('student.exam.show', [$exam->id, 'session' => $session->id])
//                      ->with('success', 'Ujian berhasil dimulai. Selamat mengerjakan!');
// }

// public function beginExam(Request $request, Exam $exam)
// {
//     /** @var \App\Models\User $user */
//     $user = Auth::user();

//     // 1. Ambil relasi data student dari user yang login
//     $student = $user->student; 

//     // Jika akun user tidak terhubung ke tabel student
//     if (!$student) {
//         return redirect()->back()->with('error', 'Data siswa tidak ditemukan untuk akun ini.');
//     }

//     // 2. Cek apakah ujian sedang aktif
//     if (!$exam->is_active) {
//         return redirect()->route('student.dashboard')
//                          ->with('error', 'Ujian sedang tidak aktif.');
//     }

//     // 3. Validasi Token Ujian (jika ujian menggunakan token)
//     if (!empty($exam->token)) {
//         $request->validate([
//             'token' => 'required|string',
//         ], [
//             'token.required' => 'Token ujian wajib diisi.'
//         ]);

//         if (strtoupper($request->token) !== strtoupper($exam->token)) {
//             return redirect()->back()->with('error', 'Token ujian yang Anda masukkan salah!');
//         }
//     }

//     // 4. Buat atau ambil sesi pengerjaan ujian
//     $session = ExamSession::firstOrCreate(
//         [
//             'exam_id'    => $exam->id,
//             'student_id' => $student->id, // Deklarasi $student sudah ada di atas
//         ],
//         [
//             'start_time' => now(),
//             'status'     => 'in_progress', // Atau 'ongoing'
//         ]
//     );

//     return redirect()->route('student.exam.show', [$exam->id, 'session' => $session->id])
//                      ->with('success', 'Ujian berhasil dimulai. Selamat mengerjakan!');
// }

// 3. Memulai Ujian (Submit Form Token & Mulai Sesi)
    public function beginExam(Request $request, Exam $exam)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ambil relasi student dari user login
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan untuk akun ini.');
        }

        if (!$exam->is_active) {
            return redirect()->route('student.exam.index')
                             ->with('error', 'Ujian sedang tidak aktif.');
        }

        // Validasi Token Ujian jika ada
        if (!empty($exam->token)) {
            $request->validate([
                'token' => 'required|string',
            ], [
                'token.required' => 'Token ujian wajib diisi.'
            ]);

            if (strtoupper(trim($request->token)) !== strtoupper(trim($exam->token))) {
                return redirect()->back()->with('error', 'Token ujian yang Anda masukkan salah!');
            }
        }

        // Buat atau ambil sesi pengerjaan ujian
        $session = ExamSession::firstOrCreate(
            [
                'exam_id'    => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'start_time' => now(),
                'status'     => 'ongoing',
            ]
        );

        return redirect()->route('student.exam.show', [$exam->id, 'session' => $session->id])
                         ->with('success', 'Ujian berhasil dimulai. Selamat mengerjakan!');
    }

    /**
     * 4. Halaman Lembar Pengerjaan Soal (Show)
     */
    // public function show(Exam $exam, ExamSession $session)
    // {
    //     $studentId = $this->getStudentId();

    //     // Validasi Pemilik Sesi
    //     if ($session->student_id !== $studentId || $session->exam_id !== $exam->id) {
    //         return redirect()->route('student.exam.index')->with('error', 'Akses sesi ujian tidak valid.');
    //     }

    //     // Jika ujian sudah selesai, langsung arahkan ke hasil
    //     if ($session->status === 'completed') {
    //         return redirect()->route('student.exam.result', [$exam->id, $session->id]);
    //     }

    //     // Load soal ujian
    //     $questions = $exam->questions()->get();

    //     // Hitung sisa waktu pengerjaan (dalam detik)
    //     $durationSeconds = $exam->duration_minutes * 60;
    //     $elapsedSeconds = now()->diffInSeconds($session->start_time);
    //     $remainingSeconds = max(0, $durationSeconds - $elapsedSeconds);

    //     // Jika waktu sudah habis secara server-side
    //     if ($remainingSeconds <= 0) {
    //         return $this->autoFinishSession($session);
    //     }

    //     // Ambil jawaban yang sudah pernah disimpan
    //     $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

    //     return view('students.exam.show', compact('exam', 'session', 'questions', 'answers', 'remainingSeconds'));
    // }

//     public function show(Exam $exam, ExamSession $session)
// {
//     $studentId = $this->getStudentId();

//     // 1. Validasi Pemilik Sesi
//     if ($session->student_id !== $studentId || $session->exam_id !== $exam->id) {
//         return redirect()->route('student.exam.index')->with('error', 'Akses sesi ujian tidak valid.');
//     }

//     // 2. Jika ujian sudah selesai, langsung arahkan ke hasil
//     if ($session->status === 'completed') {
//         return redirect()->route('student.exam.result', [$exam->id, $session->id]);
//     }

//     // 3. Pastikan start_time tercatat saat pertama kali membuka ujian
//     if (!$session->start_time) {
//         $session->update([
//             'start_time' => now(),
//         ]);
//         $startTime = now();
//     } else {
//         $startTime = Carbon::parse($session->start_time);
//     }

//     // 4. Hitung Waktu Selesai yang Pasti (Deadline = start_time + durasi)
//     $deadline = $startTime->copy()->addMinutes($exam->duration_minutes);
//     $now = now();

//     // 5. Hitung Sisa Detik Aktual
//     if ($now->greaterThanOrEqualTo($deadline)) {
//         // Jika waktu sudah lewat dari deadline, otomatis selesaikan sesi
//         return $this->autoFinishSession($session);
//     }

//     // Hitung selisih detik yang tersisa menuju deadline
//     $remainingSeconds = $now->diffInSeconds($deadline);

//     // 6. Load soal ujian & jawaban siswa
//     $questions = $exam->questions()->get();
//     $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

//     return view('students.exam.show', compact('exam', 'session', 'questions', 'answers', 'remainingSeconds'));
// }

    // public function show(Exam $exam, ExamSession $session)
    // {
    //     $studentId = $this->getStudentId();

    //     // 1. Validasi Pemilik Sesi
    //     if ($session->student_id !== $studentId || $session->exam_id !== $exam->id) {
    //         return redirect()->route('student.exam.index')->with('error', 'Akses sesi ujian tidak valid.');
    //     }

    //     // 2. Validasi Target Kelas Siswa
    //     $student = Student::findOrFail($studentId);
    //     $isTargetClass = $exam->classrooms()->where('classrooms.id', $student->classroom_id)->exists();

    //     if (!$isTargetClass) {
    //         return redirect()->route('student.exam.index')
    //                         ->with('error', 'Anda tidak terdaftar sebagai peserta pada kelas ujian ini.');
    //     }

    //     // 3. Jika ujian sudah selesai, langsung arahkan ke hasil
    //     if ($session->status === 'completed') {
    //         return redirect()->route('student.exam.result', [$exam->id, $session->id]);
    //     }

    //     // 4. Pastikan start_time tercatat saat pertama kali membuka ujian
    //     if (!$session->start_time) {
    //         $session->update([
    //             'start_time' => now(),
    //         ]);
    //         $startTime = now();
    //     } else {
    //         $startTime = Carbon::parse($session->start_time);
    //     }

    //     // 5. Hitung Waktu Selesai yang Pasti (Deadline = start_time + durasi)
    //     $deadline = $startTime->copy()->addMinutes($exam->duration_minutes);
    //     $now = now();

    //     // 6. Hitung Sisa Detik Aktual
    //     if ($now->greaterThanOrEqualTo($deadline)) {
    //         // Jika waktu sudah lewat dari deadline, otomatis selesaikan sesi
    //         return $this->autoFinishSession($session);
    //     }

    //     // Hitung selisih detik yang tersisa menuju deadline
    //     $remainingSeconds = $now->diffInSeconds($deadline);

    //     // 7. Load soal ujian & jawaban siswa
    //     $questions = $exam->questions()->get();
    //     $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

    //     return view('students.exam.show', compact('exam', 'session', 'questions', 'answers', 'remainingSeconds'));
    // }

    /**
     * 4. Halaman Lembar Pengerjaan Soal (Show dengan Fitur Acak Soal & Opsi)
     */
    public function show(Exam $exam, ExamSession $session)
    {
        $studentId = $this->getStudentId();

        // 1. Validasi Pemilik Sesi
        if ($session->student_id !== $studentId || $session->exam_id !== $exam->id) {
            return redirect()->route('student.exam.index')->with('error', 'Akses sesi ujian tidak valid.');
        }

        // 2. Validasi Target Kelas Siswa
        $student = Student::findOrFail($studentId);
        $isTargetClass = $exam->classrooms()->where('classrooms.id', $student->classroom_id)->exists();

        if (!$isTargetClass) {
            return redirect()->route('student.exam.index')
                            ->with('error', 'Anda tidak terdaftar sebagai peserta pada kelas ujian ini.');
        }

        // 3. Jika ujian sudah selesai, langsung arahkan ke hasil
        if ($session->status === 'completed') {
            return redirect()->route('student.exam.result', [$exam->id, $session->id]);
        }

        // 4. Pastikan start_time tercatat saat pertama kali membuka ujian
        if (!$session->start_time) {
            $session->update([
                'start_time' => now(),
            ]);
            $startTime = now();
        } else {
            $startTime = Carbon::parse($session->start_time);
        }

        // 5. Hitung Waktu Selesai yang Pasti (Deadline = start_time + durasi)
        $deadline = $startTime->copy()->addMinutes($exam->duration_minutes);
        $now = now();

        // 6. Hitung Sisa Detik Aktual
        if ($now->greaterThanOrEqualTo($deadline)) {
            // Jika waktu sudah lewat dari deadline, otomatis selesaikan sesi
            return $this->autoFinishSession($session);
        }

        // Hitung selisih detik yang tersisa menuju deadline
        $remainingSeconds = $now->diffInSeconds($deadline);

        // 7. Load Soal Ujian (Dengan Logika Acak Soal jika Diaktifkan)
        $questionsQuery = $exam->questions();

        if ($exam->randomize_questions) {
            // Mengacak soal dengan seed ID Sesi agar urutan acak tetap konsisten per siswa saat direfresh
            $questionsQuery->inRandomOrder($session->id);
        } else {
            $questionsQuery->orderBy('created_at', 'asc');
        }

        $questions = $questionsQuery->get();

        // 8. Logika Acak Opsi Jawaban (jika Diaktifkan)
        if ($exam->randomize_options) {
            foreach ($questions as $q) {
                $optionsData = is_string($q->options) ? json_decode($q->options, true) : $q->options;

                if (is_array($optionsData) && !empty($optionsData)) {
                    // Acak urutan array opsi untuk setiap soal
                    shuffle($optionsData);
                    $q->options = $optionsData;
                }
            }
        }

        // 9. Ambil Jawaban Siswa
        $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

        return view('students.exam.show', compact('exam', 'session', 'questions', 'answers', 'remainingSeconds'));
    }

    /**
     * 5. Autosave Jawaban Siswa via AJAX
     */
    public function autosave(Request $request, Exam $exam, ExamSession $session)
    {
        $request->validate([
            'question_id' => 'required',
            'answer'      => 'nullable',
        ]);

        if ($session->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Ujian telah selesai.'], 403);
        }

        // Format jawaban agar selalu menjadi array untuk kolom JSON 'answer'
        $answerData = is_array($request->answer) ? $request->answer : ($request->answer !== null && $request->answer !== '' ? [$request->answer] : []);

        ExamAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'question_id'     => $request->question_id,
            ],
            [
                'answer' => $answerData, // Disimpan dalam format JSON
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Jawaban tersimpan']);
    }

    /**
     * 6. Menyelesaikan Ujian (Finish)
     */
    public function finishExam(Exam $exam, ExamSession $session)
    {
        if ($session->status === 'completed') {
            return redirect()->route('student.exam.result', [$exam->id, $session->id]);
        }

        // Hitung dan simpan nilai
        $this->calculateScore($exam, $session);

        $session->update([
            'status'      => 'completed',
            'submit_time' => now(), // Menggunakan kolom submit_time
        ]);

        return redirect()->route('student.exam.result', [$exam->id, $session->id])
                         ->with('success', 'Ujian berhasil diselesaikan!');
    }

    /**
     * 7. Halaman Hasil Nilai Ujian (Result)
     */
    public function result(Exam $exam, ExamSession $session)
    {
        $studentId = $this->getStudentId();

        if ($session->student_id !== $studentId) {
            abort(403);
        }

        $session->load(['exam.subject']);

        return view('students.exam.result', compact('exam', 'session'));
    }

    /**
     * Helper: Menghitung Nilai Otomatis Ujian
     */
    private function calculateScore(Exam $exam, ExamSession $session)
    {
        $questions = $exam->questions->keyBy('id');
        $answers = ExamAnswer::where('exam_session_id', $session->id)->get();

        $totalQuestions = $questions->count();
        if ($totalQuestions === 0) return;

        $totalScore = 0;

        foreach ($answers as $ans) {
            $question = $questions->get($ans->question_id);
            if (!$question) continue;

            $userAns = is_array($ans->answer) ? $ans->answer : json_decode($ans->answer, true) ?? [];
            $isCorrect = false;
            $scoreGiven = 0;

            // Memeriksa kunci jawaban dari kolom pada tabel questions (misal: correct_answer)
            if (isset($question->correct_answer)) {
                $correctAns = is_array($question->correct_answer) ? $question->correct_answer : [$question->correct_answer];

                // Pengecekan kesamaan isi array jawaban
                if (!empty($userAns) && empty(array_diff($userAns, $correctAns)) && empty(array_diff($correctAns, $userAns))) {
                    $isCorrect = true;
                    $scoreGiven = 100 / $totalQuestions; // Bobot nilai per soal
                }
            }

            // Simpan detail per soal ke exam_answers
            $ans->update([
                'is_correct'  => $isCorrect,
                'score_given' => $scoreGiven,
            ]);

            $totalScore += $scoreGiven;
        }

        // Simpan total skor akhir ke exam_sessions
        $session->update([
            'score' => round($totalScore, 2),
        ]);
    }

    /**
     * Helper: Menutup otomatis sesi jika waktu habis
     */
    private function autoFinishSession(ExamSession $session)
    {
        $this->calculateScore($session->exam, $session);

        $session->update([
            'status'      => 'completed',
            'submit_time' => now(),
        ]);

        return redirect()->route('student.exam.result', [$session->exam_id, $session->id])
                         ->with('error', 'Waktu pengerjaan Anda telah habis!');
    }
}
