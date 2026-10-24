<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $programs = Program::with('teacher')->orderBy('name', 'asc')->get();
            return response()->json(['data' => $programs]);
        }

        $assignedTeacherIds = Program::whereNotNull('program_teacher_id')
                                     ->pluck('program_teacher_id')
                                     ->toArray();

        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                           ->orderBy('name', 'asc')
                           ->get();

        return view('admin.programs.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code',
            'program_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama program keahlian wajib diisi.',
            'code.required' => 'Kode program wajib diisi.',
            'code.unique' => 'Kode program ini sudah digunakan oleh program lain.',
            'program_teacher_id.exists' => 'Guru tidak ditemukan di database.'
        ]);

        try {
            // 1. Buat data program terlebih dahulu secara langsung
            $program = new Program();
            $program->id = (string) Str::uuid();
            $program->name = $request->name;
            $program->code = strtoupper($request->code);
            $program->program_teacher_id = $request->program_teacher_id;
            $program->save();

            // 2. Setelah data terisi di tabel programs, baru update tabel users
            if ($request->filled('program_teacher_id')) {
                $teacher = Teacher::find($request->program_teacher_id);
                if ($teacher && $teacher->user_id) {
                    $user = User::find($teacher->user_id);
                    if ($user) {
                        $user->program_id = $program->id;
                        $user->save();

                        if (!$user->hasRole('kaprog')) {
                            $user->assignRole('kaprog');
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Program Keahlian berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $program = Program::findOrFail($id);

        $assignedTeacherIds = Program::whereNotNull('program_teacher_id')
                                     ->where('id', '!=', $id)
                                     ->pluck('program_teacher_id')
                                     ->toArray();

        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                           ->orderBy('name', 'asc')
                           ->get();

        return response()->json([
            'program' => $program,
            'teachers' => $teachers
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code,' . $id,
            'program_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama program keahlian wajib diisi.',
            'code.required' => 'Kode program wajib diisi.',
            'code.unique' => 'Kode program ini sudah digunakan oleh program lain.',
            'program_teacher_id.exists' => 'Guru tidak ditemukan di database.'
        ]);

        try {
            $program = Program::findOrFail($id);
            $oldTeacherId = $program->program_teacher_id;
            $newTeacherId = $request->program_teacher_id;

            // Update data program
            $program->name = $request->name;
            $program->code = strtoupper($request->code);
            $program->program_teacher_id = $newTeacherId;
            $program->save();

            // Lepas scope dari Kaprog lama jika ada pergantian
            if ($oldTeacherId && $oldTeacherId !== $newTeacherId) {
                $oldTeacher = Teacher::find($oldTeacherId);
                if ($oldTeacher && $oldTeacher->user_id) {
                    User::where('id', $oldTeacher->user_id)->update(['program_id' => null]);
                }
            }

            // Pasang scope ke Kaprog baru
            if ($newTeacherId) {
                $newTeacher = Teacher::find($newTeacherId);
                if ($newTeacher && $newTeacher->user_id) {
                    $user = User::find($newTeacher->user_id);
                    if ($user) {
                        $user->program_id = $program->id;
                        $user->save();

                        if (!$user->hasRole('kaprog')) {
                            $user->assignRole('kaprog');
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Program Keahlian berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $program = Program::findOrFail($id);

            if ($program->program_teacher_id) {
                $teacher = Teacher::find($program->program_teacher_id);
                if ($teacher && $teacher->user_id) {
                    User::where('id', $teacher->user_id)->update(['program_id' => null]);
                }
            }

            $program->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Program Keahlian berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Get Available Teachers for Modal (Daftar Guru yang Belum Ditugaskan)
 */
public function getAvailableTeachers()
{
    $assignedTeacherIds = Program::whereNotNull('program_teacher_id')
                                 ->pluck('program_teacher_id')
                                 ->toArray();

    $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                       ->orderBy('name', 'asc')
                       ->get();

    return response()->json($teachers);
}
}