<?php

namespace App\Http\Controllers;

use App\Models\UkkPackage;
use App\Models\UkkUnit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UkkPackageImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UkkPackageController extends Controller
{
    public function index()
    {
        $packages = UkkPackage::with('units')->orderBy('created_at', 'desc')->get();
        return view('ukk.packages.index', compact('packages'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            Excel::import(new UkkPackageImport, $request->file('excel_file'));
            return back()->with('success', 'Master Paket & Elemen UKK berhasil di-import!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal meng-import: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'major_code'    => 'required|string',
            'package_name'  => 'required|string',
            'academic_year' => 'required|string',
        ]);

        UkkPackage::create($request->only(['major_code', 'package_name', 'academic_year']));

        return back()->with('success', 'Paket UKK berhasil ditambahkan.');
    }

    public function show($id)
    {
        $package = UkkPackage::with(['units.elements'])->findOrFail($id);
        return response()->json($package);
    }

    /**
     * Download Format/Template Excel Import UKK
     */
    // public function downloadTemplate()
    // {
    //     $filePath = base_path('DAFTAR UNIT PAKET TITL.xlsx');
        
    //     if (file_exists($filePath)) {
    //         return response()->download($filePath, 'Template_Import_UKK_TITL.xlsx');
    //     }

    //     return back()->with('error', 'File template contoh tidak ditemukan pada server.');
    // }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PNGLMPKN UTM & PNDKG');

        // Header Sesuai Format Importer
        $headers = [
            'NO',
            'JUDUL PAKET',
            'KODE UNIT',
            'JUDUL UNIT',
            'ELEMEN',
            'UTAMA',
            'PENDUKUNG',
            'LANGKAH KERJA / JABATAN KERJA'
        ];

        // Set Header di Baris 2
        $sheet->fromArray($headers, NULL, 'A2');

        // Contoh Data Isian
        $data = [
            [
                '1',
                'OPERATOR PEMANFAATAN TEGANGAN RENDAH PADA PEKERJAAN PEMASANGAN INSTALASI PENERANGAN 3 FASA DILENGKAPI DENGAN SUMBERTEGANGAN CADANGAN',
                'D.35.144.03.028.1',
                'Melaksanakan Pengoperasian Komponen dan Sirkit Instalasi Pemanfaatan Tenaga Listrik Tegangan Rendah',
                'Menyiapkan pelaksanaan',
                '',
                'Menyiapkan pelaksanaan',
                'Operator pemanfaatan tegangan rendah'
            ],
            [
                '', '', '', '',
                'Melaksanakan persiapan pengoperasian',
                'Melaksanakan persiapan pengoperasian',
                '', ''
            ],
            [
                '', '', '', '',
                'Melaksanakan pengoperasian',
                'Melaksanakan pengoperasian',
                '', ''
            ],
            [
                '', '', '', '',
                'Mengisi laporan hasil pengoperasian',
                'Mengisi laporan hasil pengoperasian',
                '', ''
            ]
        ];

        // Set Data Mulai Baris 3
        $sheet->fromArray($data, NULL, 'A3');

        // Format Styling Header
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Template_Import_UKK_TITL.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function destroy($id)
    {
        $package = UkkPackage::findOrFail($id);
        $package->delete();

        return back()->with('success', 'Paket UKK berhasil dihapus.');
    }

    public function storeUnit(Request $request, $packageId)
    {
        $request->validate([
            'unit_code'    => 'required|string',
            'unit_title'   => 'required|string',
            'element_name' => 'required|string',
        ]);

        UkkUnit::create([
            'ukk_package_id' => $packageId,
            'unit_code'      => $request->unit_code,
            'unit_title'     => $request->unit_title,
            'element_name'   => $request->element_name,
        ]);

        return back()->with('success', 'Unit kompetensi berhasil ditambahkan.');
    }

    public function destroyUnit($unitId)
    {
        $unit = UkkUnit::findOrFail($unitId);
        $unit->delete();

        return back()->with('success', 'Unit kompetensi berhasil dihapus.');
    }
}