<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Program;
use Illuminate\Support\Str;


class ProgramController extends Controller
// {
//     public function index(){
//         $programs = Program::latest()->get();
//         return view('admin.programs.index', compact('programs'));
//     }
//     // Contoh untuk create()
//     public function create() {
//         $teachers = Teacher::orderBy('name', 'asc')->get();
//         return view('admin.programs.create', compact('teachers'));
//     }

//     // Contoh untuk edit()
//     public function edit(string $id) {
//         $program = Program::findOrFail($id);
//         $teachers = Teacher::orderBy('name', 'asc')->get();
//         return view('programs.edit', compact('program', 'teachers'));
//     }
// }

{
    /**
     * Menampilkan daftar program keahlian (Index)
     */
    public function index()
    {
        // Mengambil semua program beserta data gurunya (Penilai)
        $programs = Program::with('teacher')->orderBy('name', 'asc')->get();
        return view('admin.programs.index', compact('programs'));
    }

    /**
     * Menampilkan form untuk menambah program baru (Create)
     */
    public function create()
    {
        // Mengambil daftar guru untuk dropdown Ketua Program / Penilai
        // $teachers = Teacher::orderBy('name', 'asc')->get();

        // 1. Ambil array ID guru yang sudah terdaftar sebagai ketua program
        $assignedTeacherIds = Program::whereNotNull('program_teacher_id')
                                     ->pluck('program_teacher_id')
                                     ->toArray();

        // dd($assignedTeacherIds);

        // 2. Tampilkan HANYA guru yang ID-nya tidak ada di array $assignedTeacherIds
        $teachers = Teacher::whereNotIn('id', $assignedTeacherIds)
                           ->orderBy('name', 'asc')
                           ->get();
        return view('admin.programs.create', compact('teachers'));
    }

    /**
     * Menyimpan data program keahlian baru ke database (Store)
     */
    public function store(Request $request)
    {
        // 1. Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code',
            'program_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            // Custom Error Messages (Bahasa Indonesia)
            'name.required' => 'Nama program keahlian wajib diisi.',
            'code.required' => 'Kode program wajib diisi.',
            'code.unique' => 'Kode program ini sudah digunakan oleh program lain.',
            'program_teacher_id.exists' => 'Guru yang dipilih tidak ditemukan di database.'
        ]);

        // 2. Simpan ke tabel programs
        Program::create([
            'id' => (string) Str::uuid(), // Generate UUID manual (opsional jika Model belum pakai trait HasUuids)
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'program_teacher_id' => $request->program_teacher_id,
        ]);

        // 3. Redirect kembali ke index dengan pesan sukses
        return redirect()->route('programs.index')
                         ->with('success', 'Program Keahlian berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit untuk program yang sudah ada (Edit)
     */
    public function edit(string $id)
    {
        $program = Program::findOrFail($id);
        $teachers = Teacher::orderBy('name', 'asc')->get();
        
        return view('admin.programs.edit', compact('program', 'teachers'));
    }

    /**
     * Memperbarui data program keahlian di database (Update)
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi input dari form
        $request->validate([
            'name' => 'required|string|max:255',
            // Unique code dikecualikan untuk ID program ini sendiri
            'code' => 'required|string|max:50|unique:programs,code,' . $id,
            'program_teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.required' => 'Nama program keahlian wajib diisi.',
            'code.required' => 'Kode program wajib diisi.',
            'code.unique' => 'Kode program ini sudah digunakan oleh program lain.',
            'program_teacher_id.exists' => 'Guru yang dipilih tidak ditemukan di database.'
        ]);

        // 2. Cari data dan update
        $program = Program::findOrFail($id);
        $program->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'program_teacher_id' => $request->program_teacher_id,
        ]);

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('programs.index')
                         ->with('success', 'Program Keahlian berhasil diperbarui!');
    }

    /**
     * Menghapus program keahlian dari database (Destroy)
     */
    public function destroy(string $id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return redirect()->route('programs.index')
                         ->with('success', 'Program Keahlian berhasil dihapus!');
    }
}
