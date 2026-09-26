<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TeacherImport;

class TeacherImportController extends Controller
{
    public function index()
    {
        return view('teachers.import');
    }

    public function store(Request $request)
    {
        $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:5120'
    ]);

        $import = new TeacherImport();
        Excel::import($import, $request->file('file'));

        // Ambil Data
        $successCount = $import->getSuccessCount();
        $duplicates   = $import->getDuplicates();

        // Logika Notifikasi
        if (count($duplicates) > 0) {
            // Susun List HTML untuk Duplikat
            $listHtml = '<ul style="text-align: left; font-size: 0.9em;">';

            // Tampilkan maks 5 error agar tidak kepanjangan
            foreach (array_slice($duplicates, 0, 5) as $msg) {
                $listHtml .= "<li>- $msg</li>";
            }
            if (count($duplicates) > 5) {
                $sisa = count($duplicates) - 5;
                $listHtml .= "<li>... dan <b>$sisa</b> data duplikat lainnya.</li>";
            }
            $listHtml .= '</ul>';

            $notification = [
                'icon'  => ($successCount > 0) ? 'warning' : 'error',
                'title' => 'Import Selesai dengan Catatan',
                'html'  => "Berhasil menyimpan: <b>{$successCount}</b> Guru.<br><br>
                            Gagal (Duplikat): <b>" . count($duplicates) . "</b> Guru:<br>
                            {$listHtml}"
            ];
        } else {
            // Sukses Sempurna
            $notification = [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => "Total $successCount data guru berhasil ditambahkan."
            ];
        }

        return redirect()->back()->with('swal', $notification);
    }
}
