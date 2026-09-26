<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClassroomExport;

class ClassroomController extends Controller
{
    /**
     * Tampilkan Daftar Kelas
     */
    public function index(Request $request)
    {
        //$query = Classroom::query();

        // Tambahkan with('students') untuk eager loading data siswa (Mencegah N+1 Query)
        $query = Classroom::with('students');

        // Fitur Pencarian
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $classrooms = $query->orderBy('name', 'asc')->paginate(10);

        return view('classrooms.index', compact('classrooms'));
    }

    /**
     * Form Tambah Kelas
     */
    public function create()
    {
        return view('classrooms.create');
    }

    /**
     * Simpan Kelas Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:classrooms,name',
        ], [
            'name.unique' => 'Nama kelas ini sudah ada, gunakan nama lain.'
        ]);

        Classroom::create([
            'name' => strtoupper($request->name) // Paksa huruf besar
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan!');
    }

    /**
     * Form Edit Kelas
     */
    public function edit($id)
    {
        $classroom = Classroom::findOrFail($id);
        return view('classrooms.edit', compact('classroom'));
    }

    /**
     * Update Data Kelas
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            // Unique validasi kecuali ID ini sendiri
            'name' => 'required|string|max:50|unique:classrooms,name,' . $id,
        ]);

        $classroom = Classroom::findOrFail($id);
        $classroom->update([
            'name' => strtoupper($request->name)
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Data kelas berhasil diperbarui!');
    }

    /**
     * Hapus Kelas
     */
    public function destroy($id)
    {
        $classroom = Classroom::findOrFail($id);

        // Opsional: Cek relasi sebelum hapus (agar tidak error constraint)
        if ($classroom->students()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Masih ada siswa di kelas ini.');
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus!');
    }

    public function export()
    {
        return Excel::download(new ClassroomExport, 'Data-Kelas.xlsx');
    }
}
