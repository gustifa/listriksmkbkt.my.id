<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Major;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        // Menampilkan daftar mapping
        $assignments = TeachingAssignment::with(['teacher', 'subject', 'classroom'])
                        ->latest()
                        ->get();
        // 2. TAMBAHAN WAJIB: Data untuk Dropdown di Modal Edit
    // Pastikan Anda sudah meng-import Model Teacher, Subject, dan Classroom di atas controller
        $allTeachers = Teacher::with('user')->get();
        $allSubjects = Subject::all();
        $allClassrooms = Classroom::all();
        // 3. Kirim semua data ke view
        return view('teaching_assignments.index', compact('assignments', 'allTeachers', 'allSubjects', 'allClassrooms'));
    }

    public function create()
    {
        $teachers = Teacher::with('major')->orderBy('name')->get();
        $subjects = Subject::with('major')->orderBy('name')->get();
        $majors = Major::get();
        // Load kelas dengan major untuk filtering di frontend/backend
        $classrooms = Classroom::with('major')->orderBy('name')->get();

        return view('teaching_assignments.create', compact('teachers', 'subjects', 'classrooms', 'majors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        // --- VALIDASI KESESUAIAN JURUSAN ---
        $teacher = Teacher::find($request->teacher_id);
        $classroom = Classroom::find($request->classroom_id);

        // Jika Guru memiliki Jurusan (Guru Produktif), pastikan Kelasnya sesuai
        if ($teacher->major_id && $classroom->major_id) {
            if ($teacher->major_id != $classroom->major_id) {
                return back()->withErrors(['classroom_id' => 'Guru Jurusan ' . $teacher->major->code . ' tidak boleh mengajar di Kelas Jurusan ' . $classroom->major->code]);
            }
        }

        // Simpan Mapping
        TeachingAssignment::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year' => date('Y') // Default tahun ini
        ]);

        return redirect()->route('teaching-assignments.index')->with('success', 'Jadwal mengajar berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'subject_id'   => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        // 2. Cek Duplikat (Validasi Logika)
        // Mencegah user mengubah data menjadi sama persis dengan data lain yang sudah ada
        // Contoh: Guru A mengajar Math di Kelas X (sudah ada), lalu kita edit data lain menjadi sama persis.
        $isDuplicate = TeachingAssignment::where('teacher_id', $request->teacher_id)
            ->where('subject_id', $request->subject_id)
            ->where('classroom_id', $request->classroom_id)
            ->where('id', '!=', $id) // Penting: Kecualikan ID yang sedang diedit ini
            ->exists();

        if ($isDuplicate) {
            return redirect()->back()->with('error', 'Gagal update! Kombinasi Guru, Mapel, dan Kelas tersebut sudah ada.');
        }

        try {
            // 3. Proses Update
            $assignment = TeachingAssignment::findOrFail($id);
            
            $assignment->update([
                'teacher_id'   => $request->teacher_id,
                'subject_id'   => $request->subject_id,
                'classroom_id' => $request->classroom_id,
                // Jika ada kolom tahun ajaran yang bisa diedit, tambahkan di sini
                // 'academic_year' => $request->academic_year ?? $assignment->academic_year, 
            ]);

            // 4. Kembali dengan pesan sukses
            return redirect()->back()->with('success', 'Mapping pembelajaran berhasil diperbarui!');

        } catch (\Exception $e) {
            // Handle jika ada error database lain
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        TeachingAssignment::destroy($id);
        return back()->with('success', 'Jadwal dihapus.');
    }
}
