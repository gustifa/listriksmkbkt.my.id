<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MajorController extends Controller
{
    /**
     * Display listing / JSON for DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $majors = Major::with(['program.teacher', 'workshopTeacher'])->latest()->get();
            return response()->json(['data' => $majors]);
        }

        $programs = Program::orderBy('name', 'asc')->get();
        return view('majors.index', compact('programs'));
    }

    /**
     * Get available teachers (Guru yang belum ditugaskan sebagai Kabeng)
     */
    public function getAvailableTeachers()
    {
        $assignedTeacherIds = Major::whereNotNull('workshop_teacher_id')
                                   ->pluck('workshop_teacher_id')
                                   ->toArray();

        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                           ->orderBy('name', 'asc')
                           ->get();

        return response()->json($teachers);
    }

    /**
     * Store Data via AJAX & Sync Otomatis ke User (major_id & Role kabeng)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:20|unique:majors,code',
            'program_id'          => 'nullable|exists:programs,id',
            'workshop_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama konsentrasi keahlian wajib diisi.',
            'code.required' => 'Kode singkatan wajib diisi.',
            'code.unique'   => 'Kode singkatan ini sudah digunakan oleh jurusan lain.',
            'program_id.exists' => 'Program keahlian tidak ditemukan.',
            'workshop_teacher_id.exists' => 'Guru tidak ditemukan.'
        ]);

        try {
            // 1. Simpan data major terlebih dahulu
            $major = new Major();
            $major->id = (string) Str::uuid();
            $major->name = strtoupper($request->name);
            $major->code = strtoupper($request->code);
            $major->program_id = $request->program_id;
            $major->workshop_teacher_id = $request->workshop_teacher_id;
            $major->save();

            // 2. Sync otomatis major_id dan role kabeng pada tabel users
            if ($request->filled('workshop_teacher_id')) {
                $teacher = Teacher::find($request->workshop_teacher_id);
                if ($teacher && $teacher->user_id) {
                    $user = User::find($teacher->user_id);
                    if ($user) {
                        $user->major_id = $major->id;
                        $user->save();

                        if (!$user->hasRole('kabeng')) {
                            $user->assignRole('kabeng');
                        }
                    }
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Jurusan / Konsentrasi berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit Data Modal via AJAX
     */
    public function edit(string $id)
    {
        $major = Major::findOrFail($id);
        $programs = Program::orderBy('name', 'asc')->get();

        // Ambil guru yang belum ditugaskan + guru Kabeng saat ini
        $assignedTeacherIds = Major::whereNotNull('workshop_teacher_id')
                                   ->where('id', '!=', $id)
                                   ->pluck('workshop_teacher_id')
                                   ->toArray();

        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                           ->orderBy('name', 'asc')
                           ->get();

        return response()->json([
            'major'    => $major,
            'programs' => $programs,
            'teachers' => $teachers
        ]);
    }

    /**
     * Update Data via AJAX & Re-sync User Scope
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:20|unique:majors,code,' . $id,
            'program_id'          => 'nullable|exists:programs,id',
            'workshop_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama konsentrasi keahlian wajib diisi.',
            'code.required' => 'Kode singkatan wajib diisi.',
            'code.unique'   => 'Kode singkatan ini sudah digunakan oleh jurusan lain.',
        ]);

        try {
            $major = Major::findOrFail($id);
            $oldTeacherId = $major->workshop_teacher_id;
            $newTeacherId = $request->workshop_teacher_id;

            // 1. Update data major
            $major->name = strtoupper($request->name);
            $major->code = strtoupper($request->code);
            $major->program_id = $request->program_id;
            $major->workshop_teacher_id = $newTeacherId;
            $major->save();

            // 2. Re-sync User Scope jika Kabeng berubah
            if ($oldTeacherId && $oldTeacherId !== $newTeacherId) {
                $oldTeacher = Teacher::find($oldTeacherId);
                if ($oldTeacher && $oldTeacher->user_id) {
                    User::where('id', $oldTeacher->user_id)->update(['major_id' => null]);
                }
            }

            if ($newTeacherId) {
                $newTeacher = Teacher::find($newTeacherId);
                if ($newTeacher && $newTeacher->user_id) {
                    $user = User::find($newTeacher->user_id);
                    if ($user) {
                        $user->major_id = $major->id;
                        $user->save();

                        if (!$user->hasRole('kabeng')) {
                            $user->assignRole('kabeng');
                        }
                    }
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Jurusan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Destroy Data via AJAX
     */
    public function destroy(string $id)
    {
        try {
            $major = Major::findOrFail($id);

            if ($major->workshop_teacher_id) {
                $teacher = Teacher::find($major->workshop_teacher_id);
                if ($teacher && $teacher->user_id) {
                    User::where('id', $teacher->user_id)->update(['major_id' => null]);
                }
            }

            $major->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Jurusan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}