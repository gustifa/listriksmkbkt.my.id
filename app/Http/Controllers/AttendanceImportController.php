<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use Illuminate\Support\Facades\Log;

class AttendanceImportController extends Controller
{
    /**
     * Menampilkan halaman form import absensi PKL.
     */
    public function index()
    {
        return view('attendance.import');
    }

    /**
     * Memproses file Excel absensi PKL yang diunggah.
     */
    public function store(Request $request)
    {
        // 1. Validasi File (Maksimal 20MB karena data absensi bisa >10.000 baris)
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:20480'
        ]);

        // Bebaskan batas waktu & memori khusus untuk proses import besar
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            // 2. Buat Instance & Jalankan Import
            $import = new AttendanceImport();
            
            // Meng-import file Excel
            Excel::import($import, $request->file('file'));

            // 3. Ambil Jumlah Data yang Berhasil Diproses
            $importedCount = $import->getImportedCount();

            // 4. Susun Notifikasi SweetAlert
            if ($importedCount > 0) {
                $notification = [
                    'icon'  => 'success',
                    'title' => 'Import Absensi Berhasil!',
                    'text'  => "Sebanyak {$importedCount} data absensi PKL siswa berhasil diimport dan diperbarui ke database."
                ];
            } else {
                $notification = [
                    'icon'  => 'info',
                    'title' => 'Data Kosong / Tidak Cocok',
                    'text'  => 'Tidak ada data absensi valid yang cocok dengan NIS siswa di database.'
                ];
            }

            return redirect()->back()->with('swal', $notification);

        } catch (\Exception $e) {
            Log::error('Import Absensi PKL Error: ' . $e->getMessage());

            $notification = [
                'icon'  => 'error',
                'title' => 'Terjadi Kesalahan',
                'text'  => 'Gagal memproses file Excel absensi. Pesan error: ' . $e->getMessage()
            ];

            return redirect()->back()->with('swal', $notification);
        }
    }
}