<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    // public function index()
    // {
    //     $user = auth()->user();
    //     $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

    //     // JIKA USER ADALAH SISWA: Filter tugas khusus kelas siswa
    //     if ($user->hasRole('siswa') && $user->student) {
    //         $studentClassroomId = $user->student->classroom_id;

    //         $query->where('classroom_id', $studentClassroomId);
    //     }

    //     $assignments = $query->paginate(10);
    //     $allSubjects = Subject::all();
    //     $allClassrooms = Classroom::all();

    //     return view('assignments.index', compact('assignments', 'allSubjects', 'allClassrooms'));
    // }

    public function index()
{
    $user = auth()->user();
    $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

    if ($user->hasRole('siswa') && $user->student) {
        $query->where('classroom_id', $user->student->classroom_id);
    } 
    elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
        $query->where('teacher_id', $user->teacher->id);
    }

    $assignments = $query->paginate(10);
    $allSubjects = Subject::all();
    $allClassrooms = Classroom::all();

    return view('assignments.index', compact('assignments', 'allSubjects', 'allClassrooms'));
}

    public function create()
    {
        $subjects = Subject::all();
        // Hanya ambil kelas yang AKTIF
        $classrooms = Classroom::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
        $teachers = Teacher::all();

        return view('assignments.create', compact('subjects', 'classrooms', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'   => 'required|uuid',
            'classroom_id' => 'required|uuid',
            'teacher_id'   => 'nullable|uuid',
            'title'        => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_date'     => 'required|date',
            'max_score'    => 'required|numeric|min:1|max:100',
            'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('assignments', 'public');
        }

        // Penanganan Teacher ID
        $user = Auth::user();
        if ($user->teacher) {
            $validated['teacher_id'] = $user->teacher->id;
        } elseif ($request->filled('teacher_id')) {
            $validated['teacher_id'] = $request->teacher_id;
        } else {
            $firstTeacher = Teacher::first();
            if (!$firstTeacher) {
                return back()->with('error', 'Belum ada data guru terdaftar.');
            }
            $validated['teacher_id'] = $firstTeacher->id;
        }

        Assignment::create($validated);

        return redirect()->route('assignments.index')->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['subject', 'classroom', 'teacher', 'submissions.student.user']);

        return view('assignments.show', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'subject_id'   => 'required|uuid',
            'classroom_id' => 'required|uuid',
            'title'        => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_date'     => 'required|date',
            'max_score'    => 'required|numeric|min:1|max:100',
            'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($assignment->file_path) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('assignments', 'public');
        }

        $assignment->update($validated);

        return redirect()->route('assignments.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment)
    {
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }
        $assignment->delete();

        return redirect()->route('assignments.index')->with('success', 'Tugas berhasil dihapus.');
    }
}