<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Major;
use App\Models\Teacher;

class MajorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $majors = Major::orderBy('name')->get();
        return view('majors.index', compact('majors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     $teachers = Teacher::orderBy('name', 'asc')->get();
    //     return view('majors.create', compact('teachers'));
    // }

    public function create()
    {
        $programs = \App\Models\Program::orderBy('name', 'asc')->get();
        $teachers = \App\Models\Teacher::orderBy('name', 'asc')->get();
        
        return view('majors.create', compact('programs', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // VALIDASI TAMBAH DATA
    //     $request->validate([
    //         // Nama tabel diasumsikan 'majors'. Jika tabel Anda 'subjects', ganti 'majors' jadi 'subjects'
    //         'name' => 'required|string|max:255|unique:majors,name',
    //         'code' => 'required|string|max:20|unique:majors,code',
    //     ], [
    //         // Custom Error Messages (Bahasa Indonesia)
    //         'name.required' => 'Nama jurusan wajib diisi.',
    //         'name.unique'   => 'Nama jurusan ini sudah terdaftar di database.',
    //         'code.required' => 'Kode jurusan wajib diisi.',
    //         'code.unique'   => 'Kode jurusan ini sudah digunakan.',
    //     ]);

    //     Major::create([
    //         'name' => strtoupper($request->name),
    //         'code' => strtoupper($request->code)
    //     ]);

    //     return redirect()->route('majors.index')->with('success', 'Jurusan berhasil ditambahkan!');
    // }

    /**
     * Menyimpan data jurusan baru ke database.
     */
    public function store(Request $request)
    {
        // VALIDASI DATA
        $request->validate([
            'name' => 'required|string|max:255|unique:majors,name',
            'code' => 'required|string|max:20|unique:majors,code',
            'program_name' => 'required|string|max:255',
            'head_of_major' => 'nullable|string|max:255',
            'head_of_workshop' => 'nullable|string|max:255',
        ], [
            // Custom Error Messages (Bahasa Indonesia)
            'name.required' => 'Nama konsentrasi keahlian wajib diisi.',
            'name.unique'   => 'Nama konsentrasi ini sudah terdaftar di database.',
            'code.required' => 'Kode singkatan wajib diisi.',
            'code.unique'   => 'Kode singkatan ini sudah digunakan.',
            'program_name.required' => 'Nama program keahlian wajib diisi.',
        ]);

        // EKSEKUSI PENYIMPANAN
        Major::create([
            'name'             => strtoupper($request->name),
            'code'             => strtoupper($request->code),
            'program_name'     => $request->program_name,
            'head_of_major'    => $request->head_of_major,
            'head_of_workshop' => $request->head_of_workshop,
        ]);

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $major = Major::findOrFail($id);
        $teachers = Teacher::orderBy('name', 'asc')->get();
        return view('majors.edit', compact('major', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // VALIDASI UPDATE DATA
        $request->validate([
            // unique:table,column,except_id
            'name' => 'required|string|max:255|unique:majors,name,' . $id,
            'code' => 'required|string|max:20|unique:majors,code,' . $id,
        ], [
            // Custom Error Messages
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique'   => 'Nama jurusan ini sudah terdaftar (tidak boleh kembar).',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique'   => 'Kode jurusan ini sudah digunakan oleh jurusan lain.',
        ]);

        $major = Major::findOrFail($id);

        $major->update([
            'name' => strtoupper($request->name),
            'code' => strtoupper($request->code),
            'program_name'     => $request->program_name,
            'head_of_major'    => $request->head_of_major,
            'head_of_workshop' => $request->head_of_workshop,
        ]);

        return redirect()->route('majors.index')
                ->with('success', 'Data Jurusan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $major = Major::findOrFail($id);

        // Opsional: Validasi Relasi (misal: jangan hapus jika ada siswa di jurusan ini)
        // if($major->students()->exists()) {
        //    return back()->with('error', 'Gagal hapus! Masih ada siswa di jurusan ini.');
        // }

        $major->delete();

        return redirect()->route('majors.index')->with('success', 'Jurusan berhasil dihapus!');
    }
}
