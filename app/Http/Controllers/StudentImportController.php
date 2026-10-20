<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class StudentImportController extends Controller
{
    /**
     * Menampilkan halaman form import.
     */
    public function index()
    {
        return view('students.import');
    }

    /**
     * Memproses file Excel yang diunggah.
     */
    public function store(Request $request)
    {
        // 1. Validasi File (Maksimal 5MB)
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        // Hilangkan batas waktu PHP khusus untuk request import ini
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            // 2. Buat Instance Import
            $import = new StudentImport();

            // 3. Jalankan Import Excel
            Excel::import($import, $request->file('file'));

            // 4. Sinkronisasi Akun User
            // Menjalankan command untuk generate user bagi siswa yang baru diimpor/diupdate
            Artisan::call('siswa:generate-users');

            // 5. Ambil Statistik Hasil Import (Tambah Baru & Update Data)
            $createdCount = $import->getCreatedCount();
            $updatedCount = $import->getUpdatedCount();
            $totalProcessed = $createdCount + $updatedCount;

            // 6. Logika Notifikasi (SweetAlert)
            if ($totalProcessed > 0) {
                $htmlMessage = "Proses import data siswa selesai:<br><ul style='text-align: left; margin-top: 10px;'>";
                
                if ($createdCount > 0) {
                    $htmlMessage .= "<li><b>{$createdCount}</b> data siswa baru berhasil ditambahkan.</li>";
                }
                
                if ($updatedCount > 0) {
                    $htmlMessage .= "<li><b>{$updatedCount}</b> data siswa lama berhasil diperbarui.</li>";
                }
                
                $htmlMessage .= "</ul>Akun login siswa telah disinkronkan otomatis.";

                $notification = [
                    'icon'  => 'success',
                    'title' => 'Import Selesai!',
                    'html'  => $htmlMessage
                ];
            } else {
                $notification = [
                    'icon'  => 'info',
                    'title' => 'Data Kosong',
                    'text'  => 'Tidak ada data siswa valid yang ditemukan atau diproses dari file Excel tersebut.'
                ];
            }

            return redirect()->back()->with('swal', $notification);

        } catch (\Exception $e) {
            // 7. Error Handling
            Log::error('Import Siswa Error: ' . $e->getMessage());

            $notification = [
                'icon'  => 'error',
                'title' => 'Terjadi Kesalahan',
                'text'  => 'Sistem gagal memproses file Excel. Pesan kesalahan: ' . $e->getMessage()
            ];

            // return redirect()->back()->with('swal', $notification);
            return response()->json($notification);
        }
    }
}