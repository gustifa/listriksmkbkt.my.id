<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Major;

class SubjectController extends Controller
{
    /**
     * Tampilkan Daftar Mapel
     */
    public function index()
    {
        // Ambil data urut abjad nama
        $subjects = Subject::orderBy('name')->get();
        return view('subjects.index', compact('subjects'));
    }

    /**
     * Tampilkan Form Tambah Mapel
     */
    public function create()
    {
       // Ambil data jurusan untuk dropdown
        $majors = Major::all();
        return view('subjects.create', compact('majors'));
    }

    /**
     * Proses Simpan Mapel Baru
     */
    public function store(Request $request)
    {
        // 1. VALIDASI DATA
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'required|string|max:20|unique:subjects,code', // Kode harus unik
        ], [
            // Custom Error Message (Indonesia)
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique'   => 'Nama mata pelajaran ini sudah ada.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique'   => 'Kode mata pelajaran ini sudah digunakan.',
            'code.max'      => 'Kode maksimal 20 karakter.',
        ]);

        // 2. SIMPAN KE DATABASE
        Subject::create([
            // ucwords: Membuat Huruf Besar Di Awal Kata (Contoh: Bahasa Indonesia)
            'name' => ucwords(strtolower($request->name)),
            // strtoupper: Membuat HURUF BESAR SEMUA (Contoh: BINDO)
            'code' => strtoupper($request->code),
            'major_id' => $request->major_id
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    /**
     * Tampilkan Form Edit
     */
    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Proses Update Mapel
     */
    public function update(Request $request, $id)
    {
        // 1. VALIDASI UPDATE (Penting: Pengecualian ID)
        $request->validate([
            // unique:table,column,except_id
            'name' => 'required|string|max:255|unique:subjects,name,' . $id,
            'code' => 'required|string|max:20|unique:subjects,code,' . $id,
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique'   => 'Nama mata pelajaran ini sudah ada.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique'   => 'Kode mata pelajaran ini sudah digunakan mapel lain.',
        ]);

        // 2. UPDATE DATABASE
        $subject = Subject::findOrFail($id);
        $subject->update([
            'name' => ucwords(strtolower($request->name)),
            'code' => strtoupper($request->code)
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    /**
     * Hapus Mapel
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);

        // Opsional: Cek apakah mapel sedang dipakai di jadwal?
        // if($subject->schedules()->exists()) {
        //    return back()->with('error', 'Gagal hapus! Mapel ini sedang digunakan di jadwal.');
        // }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Mata Pelajaran dihapus!');
    }
}
