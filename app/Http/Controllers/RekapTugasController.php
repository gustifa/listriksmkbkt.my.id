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
    // 1. Tampilan Utama Rekapitulasi
    public function index(Request $request)
    {
        $userId = auth()->id();
        $teacherId = \DB::table('teachers')->where('user_id', $userId)->value('id') ?? $userId;

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
            $query->select('classroom_id')
                  ->from('schedules')
                  ->where('teacher_id', $teacherId);
        })->get();

        $selectedClassroom = $request->input('classroom_id');

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
            $learningObjectives = LearningObjective::where('subject_id', $selectedSubject)
                ->when($activeAcademicYear, function($q) use ($activeAcademicYear) {
                    $q->where(function($query) use ($activeAcademicYear) {
                        $query->where('academic_year_id', $activeAcademicYear->id)
                              ->orWhereNull('academic_year_id');
                    });
                })
                ->orderBy('code', 'asc')
                ->get();

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

            $students = Student::where('classroom_id', $selectedClassroom)
                ->orderBy('name', 'asc')
                ->get();

            $totalBobotAktif = $assignments
                ->reject(fn($asg) => (bool) ($asg->is_ignored ?? false))
                ->sum(fn($asg) => (float) ($asg->weight ?? 1));

            $rekapData = $students->map(function ($student) use ($assignments, $totalBobotAktif) {
                $statusTugas   = [];
                $nilaiTugas    = [];
                $tanggalTugas  = [];
                $feedbackTugas = [];
                $totalH = 0; $totalT = 0; $totalA = 0;
                $totalNilaiTerbobot = 0;

                foreach ($assignments as $assignment) {
                    $submission = $assignment->submissions
                        ->where('student_id', $student->id)
                        ->first();

                    $isIgnored = (bool) ($assignment->is_ignored ?? false);
                    $weight    = $isIgnored ? 0 : (float) ($assignment->weight ?? 1);

                    if (!$submission) {
                        $status       = 'A';
                        $score        = null;
                        $submittedAt  = null;
                        $feedback     = '';
                        
                        if (!$isIgnored) {
                            $totalA++;
                        }
                    } else {
                        $dbStatus = strtolower($submission->status ?? '');

                        if ($dbStatus === 'submitted' || $dbStatus === 'h') {
                            $status = 'H';
                            if (!$isIgnored) $totalH++;
                        } elseif ($dbStatus === 'late' || $dbStatus === 't') {
                            $status = 'T';
                            if (!$isIgnored) $totalT++;
                        } else {
                            if ($submission->submitted_at && $assignment->due_date && $submission->submitted_at->gt($assignment->due_date)) {
                                $status = 'T';
                                if (!$isIgnored) $totalT++;
                            } else {
                                $status = 'H';
                                if (!$isIgnored) $totalH++;
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

                    if (!$isIgnored && !is_null($score)) {
                        $totalNilaiTerbobot += ((float)$score * $weight);
                    }
                }

                $rataRata = $totalBobotAktif > 0 ? round($totalNilaiTerbobot / $totalBobotAktif, 1) : 0;

                $rawPhone = $student->parent_phone ?? $student->phone ?? '';
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                } elseif (str_starts_with($cleanPhone, '8')) {
                    $cleanPhone = '62' . $cleanPhone;
                }

                return [
                    'student_id'    => $student->id,
                    'nisn'          => $student->nisn ?? $student->id,
                    'nama'          => $student->name,
                    'phone'         => $cleanPhone,
                    'tugas'         => $statusTugas,
                    'nilai'         => $nilaiTugas,
                    'tanggal_kumpul'=> $tanggalTugas,
                    'feedback'      => $feedbackTugas,
                    'total_h'       => $totalH,
                    'total_t'       => $totalT,
                    'total_a'       => $totalA,
                    'rata_rata'     => $rataRata,
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

            $teacherId = \DB::table('schedules')
                ->where('classroom_id', $request->classroom_id)
                ->where('subject_id', $request->subject_id)
                ->whereNotNull('teacher_id')
                ->value('teacher_id');

            if (!$teacherId) {
                $teacherId = \DB::table('teachers')->value('id');
            }

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
                'teacher_id'            => $teacherId,
                'academic_year_id'      => $activeYear?->id,
                'learning_objective_id' => $request->learning_objective_id,
                'title'                 => $request->title,
                'due_date'              => $request->due_date,
                'instructions'          => $request->instructions,
                'max_score'             => 100,
                'weight'                => 1,
                'is_ignored'            => false,
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

    // 5. Update Status, Nilai, Waktu Pengumpulan & Feedback (Mendukung Penerapan Kelompok)
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'assignment_id'      => 'required|uuid|exists:assignments,id',
            'student_id'         => 'required|uuid|exists:students,id',
            'status'             => 'required|in:H,T,A,submitted,late',
            'score'              => 'nullable|numeric|min:0|max:100',
            'submitted_at'       => 'nullable|date',
            'feedback'           => 'nullable|string',
            'group_student_ids'  => 'nullable|array',
            'group_student_ids.*' => 'uuid|exists:students,id',
        ]);

        $assignment = Assignment::findOrFail($request->assignment_id);

        // Gabungkan ID Siswa Utama dengan Anggota Kelompok
        $targetStudentIds = array_unique(array_merge([$request->student_id], $request->input('group_student_ids', [])));

        // Buat Informasi Catatan Kelompok jika memilih lebih dari 1 siswa
        $finalFeedback = $request->feedback;
        if (count($targetStudentIds) > 1) {
            $memberNames = Student::whereIn('id', $targetStudentIds)->pluck('name')->toArray();
            $groupNote = "\n[Kelompok: " . implode(', ', $memberNames) . "]";
            
            // Hindari duplikasi catatan kelompok jika sudah ada
            if (!str_contains($finalFeedback ?? '', '[Kelompok:')) {
                $finalFeedback = trim(($finalFeedback ?? '') . $groupNote);
            }
        }

        foreach ($targetStudentIds as $stId) {
            if ($request->status === 'A') {
                AssignmentSubmission::where('assignment_id', $request->assignment_id)
                    ->where('student_id', $stId)
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
                        'student_id'    => $stId,
                    ],
                    [
                        'submitted_at' => $submittedAt,
                        'status'       => $dbStatus,
                        'score'        => $request->filled('score') ? $request->score : null,
                        'feedback'     => $finalFeedback,
                        'graded_at'    => $request->filled('score') ? now() : null,
                        'updated_at'   => now()
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status, nilai, dan feedback kelompok berhasil diperbarui!'
        ]);
    }

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

    // 8. Pengisian Rekap Tugas Massal
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|uuid|exists:assignments,id',
            'classroom_id'  => 'required|uuid|exists:classrooms,id',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'uuid|exists:students,id',
            'status'        => 'required|in:H,T,A',
            'score'         => 'nullable|numeric|min:0|max:100',
            'submitted_at'  => 'nullable|date',
            'feedback'      => 'nullable|string',
        ]);

        $students = Student::whereIn('id', $validated['student_ids'])->get();

        $dbStatusMap = [
            'H' => 'submitted',
            'T' => 'late',
        ];

        foreach ($students as $student) {
            if ($validated['status'] === 'A') {
                AssignmentSubmission::where('assignment_id', $validated['assignment_id'])
                    ->where('student_id', $student->id)
                    ->delete();
            } else {
                if ($request->filled('submitted_at')) {
                    $submittedAt = \Carbon\Carbon::parse($request->submitted_at);
                } else {
                    $submittedAt = now();
                }

                AssignmentSubmission::updateOrCreate(
                    [
                        'assignment_id' => $validated['assignment_id'],
                        'student_id'    => $student->id,
                    ],
                    [
                        'submitted_at' => $submittedAt,
                        'status'       => $dbStatusMap[$validated['status']],
                        'score'        => $request->filled('score') ? $request->score : null,
                        'feedback'     => $request->feedback,
                        'graded_at'    => $request->filled('score') ? now() : null,
                        'updated_at'   => now()
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekapitulasi tugas massal berhasil diperbarui untuk ' . count($students) . ' siswa terpilih!'
        ]);
    }

    // 9. Update Bobot & Status Abaikan Tugas secara Dinamis
    public function updateWeights(Request $request)
    {
        $weights = $request->input('weights', []);
        $ignored = $request->input('ignored', []);

        $allAssignmentIds = array_keys($weights);

        foreach ($allAssignmentIds as $assignmentId) {
            $isIgnored = isset($ignored[$assignmentId]) && ($ignored[$assignmentId] == 1 || $ignored[$assignmentId] == '1');
            $weightVal = $isIgnored ? 0 : (float) ($weights[$assignmentId] ?? 1);

            Assignment::where('id', $assignmentId)->update([
                'weight'     => $weightVal,
                'is_ignored' => $isIgnored,
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bobot dan status tugas berhasil diperbarui secara dinamis!'
        ]);
    }
}