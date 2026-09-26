<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ViolationType;

class ViolationTypeController extends Controller
{
    /**
     * Tampilkan semua jenis pelanggaran
     */
    public function index(Request $request)
    {
        $query = ViolationType::query();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        // Urutkan berdasarkan poin terbesar ke terkecil
        $violationTypes = $query->orderBy('points', 'desc')->paginate(15)->withQueryString();

        return view('admin.violation_types.index', compact('violationTypes'));
    }

    /**
     * Tampilkan form tambah data
     */
    public function create()
    {
        return view('admin.violation_types.create');
    }

    /**
     * Simpan data baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'points'   => 'required|integer|min:1',
            'category' => 'required|in:ringan,sedang,berat',
        ]);

        ViolationType::create($request->all());

        return redirect()->route('admin.violation-types.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit data
     */
    public function edit($id)
    {
        $violationType = ViolationType::findOrFail($id);
        return view('admin.violation_types.edit', compact('violationType'));
    }

    /**
     * Update data di database
     */
    public function update(Request $request, $id)
    {
        $violationType = ViolationType::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'points'   => 'required|integer|min:1',
            'category' => 'required|in:ringan,sedang,berat',
        ]);

        $violationType->update($request->all());

        return redirect()->route('admin.violation-types.index')
            ->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $violationType = ViolationType::findOrFail($id);

        // Cek relasi: Jangan hapus jika sudah ada siswa yang melakukan pelanggaran ini
        // (Asumsi nama relasi di model ViolationType adalah 'studentViolations')
        // Jika belum ada relasi di model, Anda bisa skip pengecekan ini atau gunakan DB query manual.
        /*
        if ($violationType->studentViolations()->exists()) {
            return back()->with('error', 'Gagal hapus! Jenis pelanggaran ini sudah tercatat pada data siswa.');
        }
        */

        $violationType->delete();

        return redirect()->route('admin.violation-types.index')
            ->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }


}
