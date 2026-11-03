<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Assignment;
use App\Models\LearningObjective;
use App\Models\AcademicYear;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapTugasController extends Controller
{
    // 1. Tampilan Utama Rekapitulasi (Filter Sesuai Jadwal Guru)
    // 1. Tampilan Utama Rekapitulasi (Filter Sesuai Jadwal Guru)
    // public function index(Request $request)
    // {
    //     // Ambil ID Guru yang sedang login
    //     $userId = auth()->id();
    //     $teacherId = \DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;

    //     // Ambil Tahun Ajaran / Semester yang AKTIF
    //     $activeAcademicYear = AcademicYear::where('is_active', true)->first();

    //     // 1. Ambil KELAS yang diajar oleh guru ini dari tabel 'schedules'
    //     $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
    //         $query->select('classroom_id')
    //             ->from('schedules')
    //             ->where('teacher_id', $teacherId);
    //     })->get();

    //     $selectedClassroom = $request->input('classroom_id');

    //     // 2. Ambil MAPEL yang diajar guru ini (jika kelas dipilih, filter sesuai kelas tersebut)
    //     $subjects = Subject::whereIn('id', function ($query) use ($teacherId, $selectedClassroom) {
    //         $query->select('subject_id')
    //             ->from('schedules')
    //             ->where('teacher_id', $teacherId);
            
    //         if ($selectedClassroom) {
    //             $query->where('classroom_id', $selectedClassroom);
    //         }
    //     })->get();

    //     $selectedSubject = $request->input('subject_id');

    //     $assignments = collect();
    //     $learningObjectives = collect();
    //     $rekapData = collect();

    //     if ($selectedClassroom && $selectedSubject) {
    //         // Ambil TP yang dibuat untuk Mata Pelajaran ini
    //         $learningObjectives = LearningObjective::where('subject_id', $selectedSubject)
    //             ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
    //                 $q->where(function($query) use ($activeAcademicYear) {
    //                     $query->where('academic_year_id', $activeAcademicYear->id)
    //                         ->orWhereNull('academic_year_id');
    //                 });
    //             })
    //             ->orderBy('code', 'asc')
    //             ->get();

    //         // Ambil Tugas yang dibuat oleh guru ini di kelas & mapel ini
    //         $assignments = Assignment::with(['learningObjective', 'submissions'])
    //             ->where('classroom_id', $selectedClassroom)
    //             ->where('subject_id', $selectedSubject)
    //             ->where('teacher_id', $teacherId)
    //             ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
    //                 $q->where(function($query) use ($activeAcademicYear) {
    //                     $query->where('academic_year_id', $activeAcademicYear->id)
    //                         ->orWhereNull('academic_year_id');
    //                 });
    //             })
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         // Ambil Siswa di kelas yang dipilih
    //         $students = Student::where('classroom_id', $selectedClassroom)
    //             ->orderBy('name', 'asc')
    //             ->get();

    //         // Susun matriks rekapitulasi penyerahan tugas & nilai
    //         $rekapData = $students->map(function ($student) use ($assignments) {
    //             $statusTugas = [];
    //             $nilaiTugas  = []; // Array untuk menyimpan angka nilai per tugas
    //             $totalH = 0; $totalT = 0; $totalA = 0;

    //             foreach ($assignments as $assignment) {
    //                 $submission = $assignment->submissions
    //                     ->where('student_id', $student->id)
    //                     ->first();

    //                 if (!$submission) {
    //                     $status = 'A';
    //                     $score  = null;
    //                     $totalA++;
    //                 } else {
    //                     $dbStatus = strtolower($submission->status ?? '');

    //                     if ($dbStatus === 'submitted' || $dbStatus === 'h') {
    //                         $status = 'H';
    //                         $totalH++;
    //                     } elseif ($dbStatus === 'late' || $dbStatus === 't') {
    //                         $status = 'T';
    //                         $totalT++;
    //                     } else {
    //                         if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
    //                             $status = 'T';
    //                             $totalT++;
    //                         } else {
    //                             $status = 'H';
    //                             $totalH++;
    //                         }
    //                     }

    //                     // Ambil angka nilai dari DB (misal: 85, 100, atau null)
    //                     $score = $submission->score;
    //                 }

    //                 $statusTugas[$assignment->id] = $status;
    //                 $nilaiTugas[$assignment->id]  = $score; // Disimpan ke array nilai
    //             }

    //             return [
    //                 'student_id' => $student->id,
    //                 'nisn'       => $student->nisn ?? $student->id,
    //                 'nama'       => $student->name,
    //                 'tugas'      => $statusTugas,
    //                 'nilai'      => $nilaiTugas, // Dikirim ke view Blade
    //                 'total_h'    => $totalH,
    //                 'total_t'    => $totalT,
    //                 'total_a'    => $totalA,
    //             ];
    //         });
    //     }

    //     return view('recap.tugas', compact(
    //         'classrooms',
    //         'subjects',
    //         'selectedClassroom',
    //         'selectedSubject',
    //         'learningObjectives',
    //         'assignments',
    //         'rekapData',
    //         'activeAcademicYear'
    //     ));
    // }

    // 1. Tampilan Utama Rekapitulasi (Filter Sesuai Jadwal Guru)
// public function index(Request $request)
// {
//     // Ambil ID Guru yang sedang login
//     $userId = auth()->id();
//     $teacherId = \DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;

//     // Ambil Tahun Ajaran / Semester yang AKTIF
//     $activeAcademicYear = AcademicYear::where('is_active', true)->first();

//     // 1. Ambil KELAS yang diajar oleh guru ini dari tabel 'schedules'
//     $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
//         $query->select('classroom_id')
//               ->from('schedules')
//               ->where('teacher_id', $teacherId);
//     })->get();

//     $selectedClassroom = $request->input('classroom_id');

//     // 2. Ambil MAPEL yang diajar guru ini
//     $subjects = Subject::whereIn('id', function ($query) use ($teacherId, $selectedClassroom) {
//         $query->select('subject_id')
//               ->from('schedules')
//               ->where('teacher_id', $teacherId);
        
//         if ($selectedClassroom) {
//             $query->where('classroom_id', $selectedClassroom);
//         }
//     })->get();

//     $selectedSubject = $request->input('subject_id');

//     $assignments = collect();
//     $learningObjectives = collect();
//     $rekapData = collect();

//     if ($selectedClassroom && $selectedSubject) {
//         // Ambil TP yang dibuat untuk Mata Pelajaran ini
//         $learningObjectives = LearningObjective::where('subject_id', $selectedSubject)
//             ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
//                 $q->where(function($query) use ($activeAcademicYear) {
//                     $query->where('academic_year_id', $activeAcademicYear->id)
//                           ->orWhereNull('academic_year_id');
//                 });
//             })
//             ->orderBy('code', 'asc')
//             ->get();

//         // Ambil Tugas yang dibuat oleh guru ini di kelas & mapel ini
//         $assignments = Assignment::with(['learningObjective', 'submissions'])
//             ->where('classroom_id', $selectedClassroom)
//             ->where('subject_id', $selectedSubject)
//             ->where('teacher_id', $teacherId)
//             ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
//                 $q->where(function($query) use ($activeAcademicYear) {
//                     $query->where('academic_year_id', $activeAcademicYear->id)
//                           ->orWhereNull('academic_year_id');
//                 });
//             })
//             ->orderBy('created_at', 'asc')
//             ->get();

//         // Ambil Siswa di kelas yang dipilih
//         $students = Student::where('classroom_id', $selectedClassroom)
//             ->orderBy('name', 'asc')
//             ->get();

//         // Susun matriks rekapitulasi penyerahan tugas, nilai, dan tanggal kumpul
//         $rekapData = $students->map(function ($student) use ($assignments) {
//             $statusTugas  = [];
//             $nilaiTugas   = [];
//             $tanggalTugas = []; // Array untuk menyimpan tanggal pengumpulan
//             $totalH = 0; $totalT = 0; $totalA = 0;

//             foreach ($assignments as $assignment) {
//                 $submission = $assignment->submissions
//                     ->where('student_id', $student->id)
//                     ->first();

//                 if (!$submission) {
//                     $status       = 'A';
//                     $score        = null;
//                     $submittedAt  = null;
//                     $totalA++;
//                 } else {
//                     $dbStatus = strtolower($submission->status ?? '');

//                     if ($dbStatus === 'submitted' || $dbStatus === 'h') {
//                         $status = 'H';
//                         $totalH++;
//                     } elseif ($dbStatus === 'late' || $dbStatus === 't') {
//                         $status = 'T';
//                         $totalT++;
//                     } else {
//                         if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
//                             $status = 'T';
//                             $totalT++;
//                         } else {
//                             $status = 'H';
//                             $totalH++;
//                         }
//                     }

//                     $score       = $submission->score;
//                     // Format tanggal pengumpulan (Contoh: 15 Oct 2026, 10:35)
//                     $submittedAt = $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') : null;
//                 }

//                 $statusTugas[$assignment->id]  = $status;
//                 $nilaiTugas[$assignment->id]   = $score;
//                 $tanggalTugas[$assignment->id] = $submittedAt;
//             }

//             return [
//                 'student_id'    => $student->id,
//                 'nisn'          => $student->nisn ?? $student->id,
//                 'nama'          => $student->name,
//                 'tugas'         => $statusTugas,
//                 'nilai'         => $nilaiTugas,
//                 'tanggal_kumpul'=> $tanggalTugas, // Sent to View
//                 'total_h'       => $totalH,
//                 'total_t'       => $totalT,
//                 'total_a'       => $totalA,
//             ];
//         });
//     }

//     return view('recap.tugas', compact(
//         'classrooms',
//         'subjects',
//         'selectedClassroom',
//         'selectedSubject',
//         'learningObjectives',
//         'assignments',
//         'rekapData',
//         'activeAcademicYear'
//     ));
// }

// 1. Tampilan Utama Rekapitulasi (Filter Sesuai Jadwal Guru)
    public function index(Request $request)
    {
        // Ambil ID Guru yang sedang login
        $userId = auth()->id();
        $teacherId = \DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;

        // Ambil Tahun Ajaran / Semester yang AKTIF
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        // 1. Ambil KELAS yang diajar oleh guru ini dari tabel 'schedules'
        $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
            $query->select('classroom_id')
                  ->from('schedules')
                  ->where('teacher_id', $teacherId);
        })->get();

        $selectedClassroom = $request->input('classroom_id');

        // 2. Ambil MAPEL yang diajar guru ini
        $subjects = Subject::whereIn('id', function ($query) use ($teacherId, $selectedClassroom) {
            $query->select('subject_id')
                  ->from('schedules')
                  ->where('teacher_id', $teacherId);
            
            if ($selectedClassroom) {
                $query->where('classroom_id', $selectedClassroom);
            }
        })->get();

        $selectedSubject = $request->input('subject_id');

        $assignments = collect();
        $learningObjectives = collect();
        $rekapData = collect();

        if ($selectedClassroom && $selectedSubject) {
            // Ambil TP yang dibuat untuk Mata Pelajaran ini
            $learningObjectives = LearningObjective::where('subject_id', $selectedSubject)
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where(function($query) use ($activeAcademicYear) {
                        $query->where('academic_year_id', $activeAcademicYear->id)
                              ->orWhereNull('academic_year_id');
                    });
                })
                ->orderBy('code', 'asc')
                ->get();

            // Ambil Tugas yang dibuat oleh guru ini di kelas & mapel ini
            $assignments = Assignment::with(['learningObjective', 'submissions'])
                ->where('classroom_id', $selectedClassroom)
                ->where('subject_id', $selectedSubject)
                ->where('teacher_id', $teacherId)
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where(function($query) use ($activeAcademicYear) {
                        $query->where('academic_year_id', $activeAcademicYear->id)
                              ->orWhereNull('academic_year_id');
                    });
                })
                ->orderBy('created_at', 'asc')
                ->get();

            // Ambil Siswa di kelas yang dipilih
            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            // Susun matriks rekapitulasi penyerahan tugas, nilai, tanggal kumpul & feedback
            $rekapData = $students->map(function ($student) use ($assignments) {
                $statusTugas  = [];
                $nilaiTugas   = [];
                $tanggalTugas = [];
                $feedbackTugas = []; // Array untuk menyimpan feedback guru
                $totalH = 0; $totalT = 0; $totalA = 0;

                foreach ($assignments as $assignment) {
                    $submission = $assignment->submissions
                        ->where('student_id', $student->id)
                        ->first();

                    if (!$submission) {
                        $status       = 'A';
                        $score        = null;
                        $submittedAt  = null;
                        $feedback     = '';
                        $totalA++;
                    } else {
                        $dbStatus = strtolower($submission->status ?? '');

                        if ($dbStatus === 'submitted' || $dbStatus === 'h') {
                            $status = 'H';
                            $totalH++;
                        } elseif ($dbStatus === 'late' || $dbStatus === 't') {
                            $status = 'T';
                            $totalT++;
                        } else {
                            if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
                                $status = 'T';
                                $totalT++;
                            } else {
                                $status = 'H';
                                $totalH++;
                            }
                        }

                        $score       = $submission->score;
                        $submittedAt = $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') : null;
                        $feedback    = $submission->feedback ?? '';
                    }

                    $statusTugas[$assignment->id]   = $status;
                    $nilaiTugas[$assignment->id]    = $score;
                    $tanggalTugas[$assignment->id]  = $submittedAt;
                    $feedbackTugas[$assignment->id] = $feedback;
                }

                return [
                    'student_id'    => $student->id,
                    'nisn'          => $student->nisn ?? $student->id,
                    'nama'          => $student->name,
                    'tugas'         => $statusTugas,
                    'nilai'         => $nilaiTugas,
                    'tanggal_kumpul'=> $tanggalTugas,
                    'feedback'      => $feedbackTugas, // Dikirim ke view Blade
                    'total_h'       => $totalH,
                    'total_t'       => $totalT,
                    'total_a'       => $totalA,
                ];
            });
        }

        return view('recap.tugas', compact(
            'classrooms',
            'subjects',
            'selectedClassroom',
            'selectedSubject',
            'learningObjectives',
            'assignments',
            'rekapData',
            'activeAcademicYear'
        ));
    }

    // 2. Simpan TP Baru via AJAX
    public function storeLearningObjective(Request $request)
    {
        $request->validate([
            'subject_id'  => 'required|uuid|exists:subjects,id',
            'code'        => 'required|string|max:50',
            'description' => 'required|string',
        ]);

        try {
            $activeYear = AcademicYear::where('is_active', true)->first();

            $semesterInt = 1;
            if ($activeYear && isset($activeYear->semester)) {
                if (is_numeric($activeYear->semester)) {
                    $semesterInt = (int) $activeYear->semester;
                } else {
                    $semesterInt = (strtolower(trim($activeYear->semester)) === 'ganjil') ? 1 : 2;
                }
            }

            $tp = LearningObjective::create([
                'id'               => (string) Str::uuid(),
                'subject_id'       => $request->subject_id,
                'academic_year_id' => $activeYear?->id,
                'code'             => $request->code,
                'description'      => $request->description,
                'semester'         => $semesterInt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tujuan Pembelajaran (TP) berhasil disimpan!',
                'data'    => $tp
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan TP: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. Simpan Tugas Baru via AJAX
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'classroom_id'          => 'required|uuid|exists:classrooms,id',
            'subject_id'            => 'required|uuid|exists:subjects,id',
            'learning_objective_id' => 'required|uuid|exists:learning_objectives,id',
            'title'                 => 'required|string|max:255',
            'due_date'              => 'required|date',
            'instructions'          => 'nullable|string',
        ]);

        try {
            $activeYear = AcademicYear::where('is_active', true)->first();

            // 1. Cari teacher_id dari jadwal pelajaran (schedules) kelas & mapel ini
            $teacherId = \DB::table('schedules')
                ->where('classroom_id', $request->classroom_id)
                ->where('subject_id', $request->subject_id)
                ->whereNotNull('teacher_id')
                ->value('teacher_id');

            // 2. Jika tidak ditemukan di jadwal, ambil ID guru pertama yang ada di tabel 'teachers'
            if (!$teacherId) {
                $teacherId = \DB::table('teachers')->value('id');
            }

            // 3. Jika tabel teachers masih kosong sama sekali, cegah insert dengan pesan ramah
            if (!$teacherId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data guru tidak ditemukan di tabel teachers. Silakan isi data guru terlebih dahulu.'
                ], 422);
            }

            $assignment = Assignment::create([
                'id'                    => (string) Str::uuid(),
                'classroom_id'          => $request->classroom_id,
                'subject_id'            => $request->subject_id,
                'teacher_id'            => $teacherId, // Menggunakan ID dari tabel teachers yang valid
                'academic_year_id'      => $activeYear?->id,
                'learning_objective_id' => $request->learning_objective_id,
                'title'                 => $request->title,
                'due_date'              => $request->due_date,
                'instructions'          => $request->instructions,
                'max_score'             => 100,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan!',
                'data'    => $assignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. Detail Tugas untuk Modal Informasi SweetAlert2
    public function getAssignmentDetail($id)
    {
        // Menggunakan eager loading 'learningObjective' dan 'subject'
        $assignment = Assignment::with(['subject', 'learningObjective'])->findOrFail($id);
        
        return response()->json([
            'success'      => true,
            'title'        => $assignment->title,
            'tp_code'      => $assignment->learningObjective?->code ?? '-',
            'tp_desc'      => $assignment->learningObjective?->description ?? '-',
            'instructions' => $assignment->instructions ?? 'Tidak ada petunjuk khusus.',
            'due_date'     => $assignment->due_date ? $assignment->due_date->format('d M Y H:i') : '-',
            'subject'      => $assignment->subject?->name ?? '-'
        ]);
    }

    // // 5. Update Status Ceklis Pengumpulan
    // public function toggleStatus(Request $request)
    // {
    //     $request->validate([
    //         'assignment_id' => 'required|uuid|exists:assignments,id',
    //         'student_id'    => 'required|uuid|exists:students,id',
    //         'status'        => 'required|in:H,T,A,submitted,late',
    //     ]);

    //     $assignment = Assignment::findOrFail($request->assignment_id);

    //     if ($request->status === 'A') {
    //         AssignmentSubmission::where('assignment_id', $request->assignment_id)
    //             ->where('student_id', $request->student_id)
    //             ->delete();
    //     } else {
    //         $submittedAt = ($request->status === 'T' || $request->status === 'late') 
    //             ? now()->addHours(2)
    //             : ($assignment->due_date ?? now());

    //         $dbStatus = ($request->status === 'H' || $request->status === 'submitted') ? 'submitted' : 'late';

    //         AssignmentSubmission::updateOrCreate(
    //             [
    //                 'assignment_id' => $request->assignment_id,
    //                 'student_id'    => $request->student_id,
    //             ],
    //             [
    //                 'submitted_at' => $submittedAt,
    //                 'status'       => $dbStatus,
    //                 'created_at'   => now(),
    //                 'updated_at'   => now()
    //             ]
    //         );
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Status pengumpulan berhasil diperbarui!'
    //     ]);
    // }

    // 6. Cetak PDF menggunakan Dompdf
    public function printPdf(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $subjectId = $request->input('subject_id');

        if (!$classroomId || !$subjectId) {
            return back()->with('error', 'Silakan pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        $classroom = Classroom::find($classroomId);
        $subject = Subject::find($subjectId);
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $assignments = Assignment::with('learningObjective')
            ->where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                $q->where(function($query) use ($activeAcademicYear) {
                    $query->where('academic_year_id', $activeAcademicYear->id)
                          ->orWhereNull('academic_year_id');
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $students = Student::where('classroom_id', $classroomId)
            ->orderBy('name', 'asc')
            ->get();

        $rekapData = $students->map(function ($student) use ($assignments) {
            $statusTugas = [];
            $totalH = 0; $totalT = 0; $totalA = 0;

            foreach ($assignments as $assignment) {
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (!$submission) {
                    $status = 'A';
                    $totalA++;
                } else {
                    $dbStatus = strtolower($submission->status ?? '');
                    if ($dbStatus === 'submitted' || $dbStatus === 'h') {
                        $status = 'H';
                        $totalH++;
                    } elseif ($dbStatus === 'late' || $dbStatus === 't') {
                        $status = 'T';
                        $totalT++;
                    } else {
                        if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
                            $status = 'T';
                            $totalT++;
                        } else {
                            $status = 'H';
                            $totalH++;
                        }
                    }
                }
                $statusTugas[$assignment->id] = $status;
            }

            return [
                'student_id' => $student->id,
                'nisn'       => $student->nisn ?? $student->id,
                'nama'       => $student->name,
                'tugas'      => $statusTugas,
                'total_h'    => $totalH,
                'total_t'    => $totalT,
                'total_a'    => $totalA,
            ];
        });

        // Generate PDF dengan Dompdf dalam orientasi Landscape
        $pdf = Pdf::loadView('recap.pdf', compact(
            'classroom',
            'subject',
            'activeAcademicYear',
            'assignments',
            'rekapData'
        ))->setPaper('a4', 'landscape');

        $filename = 'Rekap_Tugas_' . Str::slug($classroom?->name) . '_' . Str::slug($subject?->name) . '.pdf';

        return $pdf->stream($filename);
    }

    // 7. Export Excel
    public function exportExcel(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $subjectId = $request->input('subject_id');

        if (!$classroomId || !$subjectId) {
            return back()->with('error', 'Silakan pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        $classroom = Classroom::find($classroomId);
        $subject = Subject::find($subjectId);
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $assignments = Assignment::with('learningObjective')
            ->where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                $q->where(function($query) use ($activeAcademicYear) {
                    $query->where('academic_year_id', $activeAcademicYear->id)
                          ->orWhereNull('academic_year_id');
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $students = Student::where('classroom_id', $classroomId)
            ->orderBy('name', 'asc')
            ->get();

        $rekapData = $students->map(function ($student) use ($assignments) {
            $statusTugas = [];
            $totalH = 0; $totalT = 0; $totalA = 0;

            foreach ($assignments as $assignment) {
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->first();

                if (!$submission) {
                    $status = 'A';
                    $totalA++;
                } else {
                    $dbStatus = strtolower($submission->status ?? '');
                    if ($dbStatus === 'submitted' || $dbStatus === 'h') {
                        $status = 'H';
                        $totalH++;
                    } elseif ($dbStatus === 'late' || $dbStatus === 't') {
                        $status = 'T';
                        $totalT++;
                    } else {
                        if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
                            $status = 'T';
                            $totalT++;
                        } else {
                            $status = 'H';
                            $totalH++;
                        }
                    }
                }
                $statusTugas[$assignment->id] = $status;
            }

            return [
                'student_id' => $student->id,
                'nisn'       => $student->nisn ?? $student->id,
                'nama'       => $student->name,
                'tugas'      => $statusTugas,
                'total_h'    => $totalH,
                'total_t'    => $totalT,
                'total_a'    => $totalA,
            ];
        });

        $fileName = 'Rekap_Tugas_' . Str::slug($classroom?->name ?? 'Kelas') . '_' . Str::slug($subject?->name ?? 'Mapel') . '.xls';

        return response()->streamDownload(function() use ($classroom, $subject, $activeAcademicYear, $assignments, $rekapData) {
            echo view('rekap.excel', compact(
                'classroom',
                'subject',
                'activeAcademicYear',
                'assignments',
                'rekapData'
            ))->render();
        }, $fileName, [
            "Content-Type" => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Cache-Control" => "max-age=0"
        ]);
    }

    // 5. Update Status & Nilai Pengumpulan (Individual)
    // public function toggleStatus(Request $request)
    // {
    //     $request->validate([
    //         'assignment_id' => 'required|uuid|exists:assignments,id',
    //         'student_id'    => 'required|uuid|exists:students,id',
    //         'status'        => 'required|in:H,T,A,submitted,late',
    //         'score'         => 'nullable|numeric|min:0|max:100', // Validasi input nilai
    //     ]);

    //     $assignment = Assignment::findOrFail($request->assignment_id);

    //     if ($request->status === 'A') {
    //         AssignmentSubmission::where('assignment_id', $request->assignment_id)
    //             ->where('student_id', $request->student_id)
    //             ->delete();
    //     } else {
    //         $submittedAt = ($request->status === 'T' || $request->status === 'late') 
    //             ? now()->addHours(2)
    //             : ($assignment->due_date ?? now());

    //         $dbStatus = ($request->status === 'H' || $request->status === 'submitted') ? 'submitted' : 'late';

    //         AssignmentSubmission::updateOrCreate(
    //             [
    //                 'assignment_id' => $request->assignment_id,
    //                 'student_id'    => $request->student_id,
    //             ],
    //             [
    //                 'submitted_at' => $submittedAt,
    //                 'status'       => $dbStatus,
    //                 'score'        => $request->filled('score') ? $request->score : null, // Simpan Nilai
    //                 'graded_at'    => $request->filled('score') ? now() : null,
    //                 'created_at'   => now(),
    //                 'updated_at'   => now()
    //             ]
    //         );
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Status dan nilai pengumpulan berhasil diperbarui!'
    //     ]);
    // }

    // 5. Update Status, Nilai, Waktu Pengumpulan & Feedback (Individual)
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|uuid|exists:assignments,id',
            'student_id'    => 'required|uuid|exists:students,id',
            'status'        => 'required|in:H,T,A,submitted,late',
            'score'         => 'nullable|numeric|min:0|max:100',
            'submitted_at'  => 'nullable|date',
            'feedback'      => 'nullable|string', // Validasi input feedback
        ]);

        $assignment = Assignment::findOrFail($request->assignment_id);

        if ($request->status === 'A') {
            AssignmentSubmission::where('assignment_id', $request->assignment_id)
                ->where('student_id', $request->student_id)
                ->delete();
        } else {
            if ($request->filled('submitted_at')) {
                $submittedAt = \Carbon\Carbon::parse($request->submitted_at);
            } else {
                $submittedAt = ($request->status === 'T' || $request->status === 'late') 
                    ? now()->addHours(2)
                    : ($assignment->due_date ?? now());
            }

            $dbStatus = ($request->status === 'H' || $request->status === 'submitted') ? 'submitted' : 'late';

            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $request->assignment_id,
                    'student_id'    => $request->student_id,
                ],
                [
                    'submitted_at' => $submittedAt,
                    'status'       => $dbStatus,
                    'score'        => $request->filled('score') ? $request->score : null,
                    'feedback'     => $request->feedback, // Simpan feedback
                    'graded_at'    => $request->filled('score') ? now() : null,
                    'updated_at'   => now()
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Status, nilai, waktu pengumpulan, dan feedback berhasil diperbarui!'
        ]);
    }

    // 8. Pengisian Rekap Tugas Massal
    // public function bulkUpdate(Request $request)
    // {
    //     $validated = $request->validate([
    //         'assignment_id' => 'required|uuid|exists:assignments,id',
    //         'classroom_id'  => 'required|uuid|exists:classrooms,id',
    //         'status'        => 'required|in:H,T,A',
    //         'score'         => 'nullable|numeric|min:0|max:100',
    //     ]);

    //     $students = Student::where('classroom_id', $validated['classroom_id'])->get();

    //     $dbStatusMap = [
    //         'H' => 'submitted',
    //         'T' => 'late',
    //     ];

    //     foreach ($students as $student) {
    //         if ($validated['status'] === 'A') {
    //             AssignmentSubmission::where('assignment_id', $validated['assignment_id'])
    //                 ->where('student_id', $student->id)
    //                 ->delete();
    //         } else {
    //             AssignmentSubmission::updateOrCreate(
    //                 [
    //                     'assignment_id' => $validated['assignment_id'],
    //                     'student_id'    => $student->id,
    //                 ],
    //                 [
    //                     'submitted_at' => now(),
    //                     'status'       => $dbStatusMap[$validated['status']],
    //                     'score'        => $request->filled('score') ? $request->score : null,
    //                     'graded_at'    => $request->filled('score') ? now() : null,
    //                 ]
    //             );
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Rekapitulasi tugas berhasil diperbarui secara massal!'
    //     ]);
    // }
    // 8. Pengisian Rekap Tugas Massal (Sertakan Nilai, Waktu & Feedback Massal)
public function bulkUpdate(Request $request)
{
    // 1. Validasi Input dari Request AJAX
    $validated = $request->validate([
        'assignment_id' => 'required|uuid|exists:assignments,id',
        'classroom_id'  => 'required|uuid|exists:classrooms,id',
        'status'        => 'required|in:H,T,A',
        'score'         => 'nullable|numeric|min:0|max:100',
        'submitted_at'  => 'nullable|date',
        'feedback'      => 'nullable|string',
    ]);

    // 2. Ambil seluruh siswa di kelas terpilih
    $students = Student::where('classroom_id', $validated['classroom_id'])->get();

    $dbStatusMap = [
        'H' => 'submitted',
        'T' => 'late',
    ];

    foreach ($students as $student) {
        if ($validated['status'] === 'A') {
            // Jika status A (Belum Kumpul), hapus record pengumpulan
            AssignmentSubmission::where('assignment_id', $validated['assignment_id'])
                ->where('student_id', $student->id)
                ->delete();
        } else {
            // Tentukan waktu pengumpulan
            if ($request->filled('submitted_at')) {
                $submittedAt = \Carbon\Carbon::parse($request->submitted_at);
            } else {
                $submittedAt = now();
            }

            // Simpan atau buat data baru di database
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $validated['assignment_id'],
                    'student_id'    => $student->id,
                ],
                [
                    'submitted_at' => $submittedAt,
                    'status'       => $dbStatusMap[$validated['status']],
                    'score'        => $request->filled('score') ? $request->score : null,
                    'feedback'     => $request->feedback, // Simpan Feedback Massal
                    'graded_at'    => $request->filled('score') ? now() : null,
                    'updated_at'   => now()
                ]
            );
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Rekapitulasi tugas massal (status, nilai, waktu, dan feedback) berhasil diperbarui!'
    ]);
}
}