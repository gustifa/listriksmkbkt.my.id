<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    /**
     * Menampilkan daftar pengumpulan tugas siswa untuk satu tugas tertentu (View Guru/Admin)
     */
    public function index(Assignment $assignment)
    {
        $assignment->load(['subject', 'classroom', 'teacher', 'submissions.student.user']);

        return view('assignments.show', compact('assignment'));
    }

    /**
     * Menyimpan / mengunggah jawaban tugas dari siswa (View Siswa)
     */
    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'submission_text' => 'nullable|string',
            'file'            => 'nullable|file|mimes:pdf,docx,pptx,zip,rar,png,jpg,jpeg|max:10240',
        ]);

        // Memastikan user yang login terhubung ke data Siswa
        $student = Auth::user()->student;
        if (!$student) {
            return back()->with('error', 'Akun Anda tidak terdaftar sebagai siswa.');
        }

        // Cek apakah siswa sudah pernah mengumpulkan tugas ini sebelumnya
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $filePath = $submission ? $submission->file_path : null;

        // Jika siswa mengunggah berkas baru
        if ($request->hasFile('file')) {
            // Hapus berkas lama jika ada
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('submissions', 'public');
        }

        // Tentukan status pengumpulan (terlambat atau tepat waktu)
        $isLate = now()->gt($assignment->due_date);
        $status = $isLate ? 'late' : 'submitted';

        AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id'    => $student->id,
            ],
            [
                'submission_text' => $request->submission_text,
                'file_path'       => $filePath,
                'submitted_at'    => now(),
                'status'          => $status,
            ]
        );

        $message = $isLate 
            ? 'Tugas berhasil dikumpulkan (Terlambat).' 
            : 'Tugas berhasil dikumpulkan tepat waktu.';

        return back()->with('success', $message);
    }

    /**
     * Memberikan nilai dan masukan/feedback pada pengumpulan tugas siswa (View Guru/Admin)
     */
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'score'    => 'required|numeric|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string',
        ]);

        $teacher = Auth::user()->teacher;

        $submission->update([
            'score'     => $validated['score'],
            'feedback'  => $validated['feedback'],
            'graded_at' => now(),
            'graded_by' => $teacher ? $teacher->id : null,
            'status'    => 'graded',
        ]);

        return back()->with('success', 'Nilai dan masukan berhasil disimpan.');
    }
}