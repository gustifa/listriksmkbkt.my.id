<?php

namespace App\Http\Controllers;

use App\Models\CourseMaterial;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CourseMaterialController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = CourseMaterial::with(['subject', 'classroom', 'teacher'])->latest();

        // 1. JIKA USER ADALAH SISWA: Filter berdasarkan kelas siswa
        if ($user->hasRole('siswa') && $user->student) {
            $studentClassroomId = $user->student->classroom_id;

            $query->where(function ($q) use ($studentClassroomId) {
                $q->where('classroom_id', $studentClassroomId)
                  ->orWhereNull('classroom_id');
            })->where('is_published', true);
        } 
        // 2. JIKA USER ADALAH GURU (Bukan Admin): Hanya tampilkan materi miliknya sendiri
        elseif ($user->hasRole('guru') && !$user->hasAnyRole(['admin', 'super_admin']) && $user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        }

        $materials = $query->paginate(10);
        $allSubjects = Subject::all();
        $allClassrooms = Classroom::all();

        return view('materials.index', compact('materials', 'allSubjects', 'allClassrooms'));
    }

    // public function create()
    // {
    //     $subjects = Subject::all();
    //     $classrooms = Classroom::all();
    //     $teachers = Teacher::all();

    //     return view('materials.create', compact('subjects', 'classrooms', 'teachers'));
    // }

    public function create()
    {
        $subjects = Subject::all();
        
        // Hanya ambil kelas yang AKTIF
        $classrooms = Classroom::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
            
        $teachers = Teacher::all();

        return view('materials.create', compact('subjects', 'classrooms', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'   => 'required|uuid',
            'classroom_id' => 'nullable|uuid',
            'teacher_id'   => 'nullable|uuid',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
            'video_url'    => 'nullable|url',
        ]);

        $user = auth()->user();
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

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $validated['is_published'] = $request->has('is_published');

        CourseMaterial::create($validated);

        return redirect()->route('materials.index')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(CourseMaterial $material)
    {
        return view('materials.show', compact('material'));
    }

    /**
     * METHOD EDIT (YANG SEBELUMNYA HILANG/BELUM ADA)
     */
    // public function edit(CourseMaterial $material)
    // {
    //     // Jika dipanggil via Modal / AJAX JS
    //     if (request()->ajax() || request()->wantsJson()) {
    //         return response()->json($material);
    //     }

    //     // Jika diakses via halaman terpisah
    //     $subjects = Subject::all();
    //     $classrooms = Classroom::all();
    //     $teachers = Teacher::all();

    //     return view('materials.edit', compact('material', 'subjects', 'classrooms', 'teachers'));
    // }

    /**
     * Mengembalikan data JSON materi untuk diisikan ke Form Modal Edit
     */
    public function edit(CourseMaterial $material)
    {
        // Selalu kembalikan data JSON agar tidak membutuhkan berkas materials/edit.blade.php
        return response()->json($material);
    }

    public function update(Request $request, CourseMaterial $material)
    {
        $validated = $request->validate([
            'subject_id'   => 'required|uuid',
            'classroom_id' => 'nullable|uuid',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,docx,pptx,zip,rar|max:10240',
            'video_url'    => 'nullable|url',
        ]);

        if ($request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $validated['is_published'] = $request->has('is_published');

        $material->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil diperbarui.'
            ]);
        }

        return redirect()->route('materials.index')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(CourseMaterial $material)
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Materi berhasil dihapus.');
    }
}