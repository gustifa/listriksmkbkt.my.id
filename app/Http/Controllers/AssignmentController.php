<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\LearningObjective; // <- TAMBAHKAN BARIS INI

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

//     public function index()
// {
//     $user = auth()->user();
//     $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

//     if ($user->hasRole('siswa') && $user->student) {
//         $query->where('classroom_id', $user->student->classroom_id);
//     } 
//     elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
//         $query->where('teacher_id', $user->teacher->id);
//     }

//     $assignments = $query->paginate(10);
//     $allSubjects = Subject::all();
//     $allClassrooms = Classroom::all();

//     return view('assignments.index', compact('assignments', 'allSubjects', 'allClassrooms'));
// }

// public function index()
// {
//     $user = auth()->user();
//     $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

//     if ($user->hasRole('siswa') && $user->student) {
//         // Filter kelas siswa DAN hanya tugas yang sudah dipublikasikan
//         $query->where('classroom_id', $user->student->classroom_id)
//               ->where('is_published', true);
//     } 
//     elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
//         // Guru tetap bisa melihat semua tugas buatannya (baik draft maupun terpublish)
//         $query->where('teacher_id', $user->teacher->id);
//     }

//     $assignments = $query->paginate(10);
//     $allSubjects = Subject::all();
//     $allClassrooms = Classroom::all();

//     return view('assignments.index', compact('assignments', 'allSubjects', 'allClassrooms'));
// }

//     public function index()
// {
//     $user = auth()->user();
//     $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

//     if ($user->hasRole('siswa') && $user->student) {
//         $query->where('classroom_id', $user->student->classroom_id)
//               ->where('is_published', true);
//     } 
//     elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
//         $query->where('teacher_id', $user->teacher->id);
//     }

//     $assignments = $query->paginate(10);
//     $allSubjects = Subject::all();

//     // Filter Target Kelas: Jika Guru, ambil hanya kelas yang ada di jadwal mengajarnya
//     if ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
//         $teacherId = $user->teacher->id;
//         $allClassrooms = Classroom::whereIn('id', function ($q) use ($teacherId) {
//             $q->select('classroom_id')
//               ->from('schedules')
//               ->where('teacher_id', $teacherId);
//         })->get();
//     } else {
//         // Jika Admin / Super Admin, tampilkan seluruh kelas
//         $allClassrooms = Classroom::all();
//     }

//     $learningObjectives = LearningObjective::all();

//     return view('assignments.index', compact('assignments', 'allSubjects', 'allClassrooms', 'learningObjectives'));
// }

/**
 * Tampilkan daftar tugas siswa dengan filter kelas sesuai jadwal guru.
 */
public function index()
{
    $user = auth()->user();
    $query = Assignment::with(['subject', 'classroom', 'teacher', 'submissions'])->latest();

    // 1. Filter Akses berdasarkan Role User
    if ($user->hasRole('siswa') && $user->student) {
        // Siswa hanya melihat tugas di kelasnya yang sudah dipublikasikan (is_published = true)
        $query->where('classroom_id', $user->student->classroom_id)
              ->where('is_published', true);
    } 
    elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
        // Guru biasa hanya melihat tugas yang dibuat oleh dirinya sendiri
        $query->where('teacher_id', $user->teacher->id);
    }

    $assignments = $query->paginate(10);
    $allSubjects = Subject::all();

    // 2. Filter Target Kelas untuk Modal Edit: 
    // Jika Guru, ambil HANYA kelas yang ada di jadwal mengajar (schedules) miliknya
    if ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
        $teacherId = $user->teacher->id;
        $allClassrooms = Classroom::whereIn('id', function ($q) use ($teacherId) {
            $q->select('classroom_id')
              ->from('schedules')
              ->where('teacher_id', $teacherId);
        })->get();
    } else {
        // Jika Admin / Super Admin, tampilkan seluruh kelas
        $allClassrooms = Classroom::all();
    }

    // 3. Ambil data Tujuan Pembelajaran (TP)
    $learningObjectives = LearningObjective::all();

    return view('assignments.index', compact(
        'assignments', 
        'allSubjects', 
        'allClassrooms', 
        'learningObjectives'
    ));
}

    // public function create()
    // {
    //     $subjects = Subject::all();
    //     // Hanya ambil kelas yang AKTIF
    //     $classrooms = Classroom::where('is_active', true)
    //         ->orderBy('name', 'asc')
    //         ->get();
    //     $teachers = Teacher::all();

    //     return view('assignments.create', compact('subjects', 'classrooms', 'teachers'));
    // }

//     public function create()
// {
//     $user = auth()->user();
    
//     // Jika user adalah GURU
//     if ($user->teacher) {
//         $teacherId = $user->teacher->id;

//         // Ambil KELAS yang diajar oleh guru ini dari tabel schedules
//         $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
//             $query->select('classroom_id')
//                   ->from('schedules')
//                   ->where('teacher_id', $teacherId);
//         })->get();

//         // Ambil MATA PELAJARAN yang diajar oleh guru ini dari tabel schedules
//         $subjects = Subject::whereIn('id', function ($query) use ($teacherId) {
//             $query->select('subject_id')
//                   ->from('schedules')
//                   ->where('teacher_id', $teacherId);
//         })->get();
//     } else {
//         // Jika ADMIN / SUPER ADMIN, tampilkan semua kelas dan mapel
//         $classrooms = Classroom::all();
//         $subjects = Subject::all();
//     }

//     $teachers = Teacher::all();

//     // Ambil TP berdasarkan mapel jadwal guru (jika ada)
//     $learningObjectives = LearningObjective::whereIn('subject_id', $subjects->pluck('id'))->get();

//     return view('assignments.create', compact('subjects', 'classrooms', 'teachers', 'learningObjectives'));
// }

    // 1. Method Render Form Create
public function create()
{
    $user = auth()->user();
    
    if ($user->teacher) {
        $teacherId = $user->teacher->id;

        // Ambil HANYA KELAS yang diampu oleh guru ini di tabel schedules
        $classrooms = Classroom::whereIn('id', function ($query) use ($teacherId) {
            $query->select('classroom_id')
                  ->from('schedules')
                  ->where('teacher_id', $teacherId);
        })->get();
    } else {
        // Untuk Admin / Super Admin
        $classrooms = Classroom::all();
    }

    $teachers = Teacher::all();

    // Subject dan TP akan di-load via AJAX setelah Kelas dipilih
    return view('assignments.create', compact('classrooms', 'teachers'));
}

// 2. Method AJAX untuk Ambil Mapel berdasarkan Kelas & Guru
public function getSubjectsByClassroom($classroomId)
{
    $user = auth()->user();
    
    $query = Subject::whereIn('id', function ($q) use ($classroomId, $user) {
        $q->select('subject_id')
          ->from('schedules')
          ->where('classroom_id', $classroomId);
          
        // Jika guru biasa, batasi sesuai jadwal mengajar guru tersebut
        if ($user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        }
    });

    $subjects = $query->get();

    return response()->json($subjects);
}

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'subject_id'   => 'required|uuid',
    //         'classroom_id' => 'required|uuid',
    //         'teacher_id'   => 'nullable|uuid',
    //         'title'        => 'required|string|max:255',
    //         'instructions' => 'required|string',
    //         'due_date'     => 'required|date',
    //         'max_score'    => 'required|numeric|min:1|max:100',
    //         'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
    //     ]);

    //     if ($request->hasFile('file')) {
    //         $validated['file_path'] = $request->file('file')->store('assignments', 'public');
    //     }

    //     // Penanganan Teacher ID
    //     $user = Auth::user();
    //     if ($user->teacher) {
    //         $validated['teacher_id'] = $user->teacher->id;
    //     } elseif ($request->filled('teacher_id')) {
    //         $validated['teacher_id'] = $request->teacher_id;
    //     } else {
    //         $firstTeacher = Teacher::first();
    //         if (!$firstTeacher) {
    //             return back()->with('error', 'Belum ada data guru terdaftar.');
    //         }
    //         $validated['teacher_id'] = $firstTeacher->id;
    //     }

    //     Assignment::create($validated);

    //     return redirect()->route('assignments.index')->with('success', 'Tugas berhasil dibuat.');
    // }

    public function store(Request $request)
{
    $validated = $request->validate([
        'subject_id'            => 'required|uuid|exists:subjects,id',
        'classroom_id'          => 'required|uuid|exists:classrooms,id',
        'teacher_id'            => 'nullable|uuid|exists:teachers,id',
        'learning_objective_id' => 'nullable|uuid|exists:learning_objectives,id', // Tambahkan validasi TP
        'title'                 => 'required|string|max:255',
        'instructions'          => 'required|string',
        'due_date'              => 'required|date',
        'max_score'             => 'required|numeric|min:1|max:100',
        'file'                  => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
        'is_published'          => 'nullable|boolean',
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

    // Status Publikasi ke Siswa (True jika dicentang, False jika tidak)
    $validated['is_published'] = $request->has('is_published') ? true : false;

    // Tambahkan Academic Year ID Aktif secara Otomatis
    $activeAcademicYear = AcademicYear::where('is_active', true)->first();
    $validated['academic_year_id'] = $activeAcademicYear?->id;

    Assignment::create($validated);

    return redirect()->route('assignments.index')->with('success', 'Tugas berhasil dibuat.');
}

    public function show(Assignment $assignment)
    {
        $assignment->load(['subject', 'classroom', 'teacher', 'submissions.student.user']);

        return view('assignments.show', compact('assignment'));
    }

    // public function update(Request $request, Assignment $assignment)
    // {
    //     $validated = $request->validate([
    //         'subject_id'   => 'required|uuid',
    //         'classroom_id' => 'required|uuid',
    //         'title'        => 'required|string|max:255',
    //         'instructions' => 'required|string',
    //         'due_date'     => 'required|date',
    //         'max_score'    => 'required|numeric|min:1|max:100',
    //         'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
    //          'is_published'          => 'nullable|boolean',
    //     ]);

    //     if ($request->hasFile('file')) {
    //         if ($assignment->file_path) {
    //             Storage::disk('public')->delete($assignment->file_path);
    //         }
    //         $validated['file_path'] = $request->file('file')->store('assignments', 'public');
    //     }

    //     $assignment->update($validated);

    //     return redirect()->route('assignments.index')->with('success', 'Tugas berhasil diperbarui.');
    // }

//     public function update(Request $request, Assignment $assignment)
// {
//     $validated = $request->validate([
//         'subject_id'            => 'required|uuid|exists:subjects,id',
//         'classroom_id'          => 'required|uuid|exists:classrooms,id',
//         'learning_objective_id' => 'nullable|uuid|exists:learning_objectives,id',
//         'title'                 => 'required|string|max:255',
//         'instructions'          => 'required|string',
//         'due_date'              => 'required|date',
//         'max_score'             => 'required|numeric|min:1|max:100',
//         'file'                  => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
//         'is_published'          => 'nullable|boolean',
//     ]);

//     // Format nilai maksimal agar selalu integer bulat (menghindari issue format 100,00)
//     $validated['max_score'] = (int) $request->input('max_score');

//     // Penanganan status publikasi (jika dicentang = true, jika tidak dicentang = false)
//     $validated['is_published'] = $request->has('is_published');

//     // Penanganan penggantian berkas file lampiran
//     if ($request->hasFile('file')) {
//         if ($assignment->file_path) {
//             Storage::disk('public')->delete($assignment->file_path);
//         }
//         $validated['file_path'] = $request->file('file')->store('assignments', 'public');
//     }

//     // Update data tugas ke database PostgreSQL
//     $assignment->update($validated);

//     return redirect()->route('assignments.index')->with('success', 'Tugas berhasil diperbarui.');
// }
/**
 * Update data tugas siswa (Termasuk TP, Mapel, Kelas, File, & is_published).
 */
public function update(Request $request, Assignment $assignment)
{
    $validated = $request->validate([
        'subject_id'            => 'required|uuid|exists:subjects,id',
        'classroom_id'          => 'required|uuid|exists:classrooms,id',
        'learning_objective_id' => 'nullable|uuid|exists:learning_objectives,id', // Validasi Tujuan Pembelajaran (TP)
        'title'                 => 'required|string|max:255',
        'instructions'          => 'required|string',
        'due_date'              => 'required|date',
        'max_score'             => 'required|numeric|min:1|max:100',
        'file'                  => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
        'is_published'          => 'nullable|boolean',
    ]);

    // Ensure learning_objective_id is set to null if empty/not selected
    $validated['learning_objective_id'] = $request->filled('learning_objective_id') 
        ? $request->input('learning_objective_id') 
        : null;

    // Convert max_score to integer
    $validated['max_score'] = (int) $request->input('max_score');

    // Handle is_published boolean switch
    $validated['is_published'] = $request->has('is_published');

    // Handle file attachment replacement
    if ($request->hasFile('file')) {
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }
        $validated['file_path'] = $request->file('file')->store('assignments', 'public');
    }

    // Save changes to database
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

    public function getTpBySubject($subjectId)
{
    // Ambil TP yang sesuai dengan Mata Pelajaran yang dipilih
    $learningObjectives = LearningObjective::where('subject_id', $subjectId)->get();

    return response()->json($learningObjectives);
}

public function storeTp(Request $request)
{
    $validated = $request->validate([
        'subject_id'  => 'required|uuid|exists:subjects,id',
        'code'        => 'nullable|string|max:50',
        'description' => 'required|string',
    ]);

    $tp = LearningObjective::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Tujuan Pembelajaran berhasil ditambahkan!',
        'data'    => $tp
    ]);
}


}