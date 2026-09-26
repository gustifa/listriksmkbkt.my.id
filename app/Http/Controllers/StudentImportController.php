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

        try {
            // 2. Buat Instance Import
            $import = new StudentImport();

            // 3. Jalankan Import Excel
            Excel::import($import, $request->file('file'));

            // 4. Sinkronisasi Akun User
            // Menjalankan command untuk generate user bagi siswa yang baru diimpor
            // dan belum memiliki user_id.
            Artisan::call('siswa:generate-users');

            // 5. Ambil Statistik Hasil Import
            $duplicates   = $import->getDuplicates();
            $successCount = $import->getSuccessCount();

            // 6. Logika Notifikasi (SweetAlert)
            if (count($duplicates) > 0) {
                // Kasus: Ada Data Duplikat
                $duplicateList = '<ul style="text-align: left; font-size: 0.9em; margin-bottom:0;">';
                foreach (array_slice($duplicates, 0, 5) as $dup) {
                    $duplicateList .= "<li>- " . e($dup) . "</li>";
                }
                
                if (count($duplicates) > 5) {
                    $sisa = count($duplicates) - 5;
                    $duplicateList .= "<li>... dan <b>{$sisa}</b> lainnya.</li>";
                }
                $duplicateList .= '</ul>';

                $isPartial = $successCount > 0;

                $notification = [
                    'icon'  => $isPartial ? 'warning' : 'error',
                    'title' => 'Import Selesai dengan Catatan',
                    'html'  => $isPartial
                        ? "<b>{$successCount}</b> siswa berhasil disimpan & akun dibuat.<br><br>Namun <b>" . count($duplicates) . "</b> data dilewati karena NIS sudah ada:<br>{$duplicateList}"
                        : "Gagal menyimpan! Semua data (<b>" . count($duplicates) . "</b>) memiliki NIS yang sudah terdaftar.<br>{$duplicateList}"
                ];

            } else {
                // Kasus: Berhasil Semua atau File Kosong
                if ($successCount > 0) {
                    $notification = [
                        'icon'  => 'success',
                        'title' => 'Import Berhasil!',
                        'text'  => "Total {$successCount} data siswa berhasil diimport dan akun login telah siap digunakan."
                    ];
                } else {
                    $notification = [
                        'icon'  => 'info',
                        'title' => 'Data Kosong',
                        'text'  => 'Tidak ada data valid yang ditemukan dalam file Excel tersebut.'
                    ];
                }
            }

            return redirect()->back()->with('swal', $notification);

        } catch (\Exception $e) {
            // 7. Error Handling
            Log::error('Import Siswa Error: ' . $e->getMessage());

            $notification = [
                'icon'  => 'error',
                'title' => 'Terjadi Kesalahan',
                'text'  => 'Sistem gagal memproses file. Pastikan format header (nis, nama_siswa, kelas) sudah benar. Pesan: ' . $e->getMessage()
            ];

            return redirect()->back()->with('swal', $notification);
        }
    }
}