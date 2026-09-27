<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Program;
use App\Models\Teacher;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::with(['program', 'workshopTeacher'])->latest()->get();
        return view('majors.index', compact('majors'));
    }

    public function create()
    {
        $programs = Program::all();
        $teachers = Teacher::all();
        return view('majors.create', compact('programs', 'teachers'));
    }

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
            'code.unique'   => 'Kode singkatan ini sudah digunakan.',
        ]);

        Major::create([
            'name'                => strtoupper($request->name),
            'code'                => strtoupper($request->code),
            'program_id'          => $request->program_id,
            'workshop_teacher_id' => $request->workshop_teacher_id,
        ]);

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil ditambahkan!');
    }

    public function edit(Major $major)
    {
        $programs = Program::all();
        $teachers = Teacher::all();
        return view('majors.edit', compact('major', 'programs', 'teachers'));
    }

    public function update(Request $request, Major $major)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:20|unique:majors,code,' . $major->id,
            'program_id'          => 'nullable|exists:programs,id',
            'workshop_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama konsentrasi keahlian wajib diisi.',
            'code.required' => 'Kode singkatan wajib diisi.',
            'code.unique'   => 'Kode singkatan ini sudah digunakan.',
        ]);

        $major->update([
            'name'                => strtoupper($request->name),
            'code'                => strtoupper($request->code),
            'program_id'          => $request->program_id,
            'workshop_teacher_id' => $request->workshop_teacher_id,
        ]);

        return redirect()->route('majors.index')->with('success', 'Data jurusan berhasil diperbarui!');
    }

    public function destroy(Major $major)
    {
        $major->delete();
        return redirect()->route('majors.index')->with('success', 'Data jurusan berhasil dihapus!');
    }
}