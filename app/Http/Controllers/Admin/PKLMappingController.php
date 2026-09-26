<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;

class PKLMappingController extends Controller
{
    /**
     * Tampilkan Halaman Mapping
     */
    public function index()
    {
        // Ambil semua kelas, urutkan nama
        $classrooms = Classroom::orderBy('name')->get();
        
        return view('admin.pkl.mapping', compact('classrooms'));
    }

    /**
     * Simpan Konfigurasi Mapping
     */
    public function update(Request $request)
    {
        // Ambil ID kelas yang dicentang
        $activeClassIds = $request->input('active_classes', []);

        // 1. Set semua kelas menjadi NON-AKTIF dulu
        Classroom::query()->update(['is_pkl_active' => false]);

        // 2. Set kelas yang dipilih menjadi AKTIF
        if (!empty($activeClassIds)) {
            Classroom::whereIn('id', $activeClassIds)->update(['is_pkl_active' => true]);
        }

        return redirect()->back()->with('success', 'Mapping Kelas PKL berhasil diperbarui. Siswa di kelas terpilih sekarang dapat mengakses fitur PKL.');
    }
}