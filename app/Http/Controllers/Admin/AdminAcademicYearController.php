<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AdminAcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')
                            ->orderBy('semester', 'asc')
                            ->paginate(10);
        return view('admin.backend.academic_years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.backend.academic_years.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'year'      => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'], // Contoh format: 2025/2026
            'semester'  => ['required', 'in:ganjil,genap'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'year.required'  => 'Tahun pelajaran wajib diisi.',
            'year.regex'     => 'Format tahun pelajaran harus YYYY/YYYY (contoh: 2025/2026).',
            'semester.in'    => 'Pilihan semester harus Ganjil atau Genap.',
        ]);

        $isActive = $request->has('is_active');

        DB::transaction(function () use ($validated, $isActive) {
            // Jika ditandai sebagai "Aktif", nonaktifkan tahun pelajaran lain terlebih dahulu
            if ($isActive) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            // Simpan data baru
            AcademicYear::create([
                'year'      => $validated['year'],
                'semester'  => $validated['semester'],
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('settings.academic-years.index')
                         ->with('success', 'Tahun Pelajaran & Semester berhasil ditambahkan!');
    }

    public function edit($id)
    {
        // Ambil data dari database berdasarkan UUID
        $academicYear = AcademicYear::findOrFail($id);
        return view('admin.backend.academic_years.edit', compact('academicYear'));
    }

    /**
     * Memperbarui data yang ada di database.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'year'      => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'  => ['required', 'in:ganjil,genap'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'year.required' => 'Tahun pelajaran wajib diisi.',
            'year.regex'    => 'Format tahun pelajaran harus YYYY/YYYY (contoh: 2025/2026).',
            'semester.in'   => 'Pilihan semester harus Ganjil atau Genap.',
        ]);

        $isActive = $request->has('is_active');

        // 2. Transaksi Database
        DB::transaction(function () use ($academicYear, $validated, $isActive) {
            // Jika opsi "Aktif" dicentang, nonaktifkan periode lain (kecuali data ini)
            if ($isActive) {
                AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            }

            // Update data yang dipilih
            $academicYear->update([
                'year'      => $validated['year'],
                'semester'  => $validated['semester'],
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('settings.academic-years.index')
                         ->with('success', 'Tahun Pelajaran & Semester berhasil diperbarui!');
    }

    /**
     * Menghapus data dari database.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Cegah penghapusan jika periode ini sedang berstatus Aktif
        if ($academicYear->is_active) {
            return redirect()->route('settings.academic-years.index')
                             ->with('error', 'Gagal menghapus! Periode yang sedang aktif tidak boleh dihapus.');
        }

        $academicYear->delete();

        return redirect()->route('settings.academic-years.index')
                         ->with('success', 'Tahun Pelajaran & Semester berhasil dihapus!');
    }
}
