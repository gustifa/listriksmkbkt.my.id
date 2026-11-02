<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TpMaster;
use App\Models\Major;
use Illuminate\Http\Request;

class TpMasterController extends Controller
{
    public function index()
    {
        $tpMasters = TpMaster::with('major')->orderBy('code', 'asc')->get();
        $majors = Major::orderBy('name', 'asc')->get();
        return view('guru.tp_master.index', compact('tpMasters', 'majors'));
    }

    /**
     * Simpan Master TP Baru (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'code'                => 'required|string|max:50',
            'title'               => 'required|string|max:255',
            'max_score_per_item' => 'required|integer|min:1|max:10',
            'indicators'          => 'required|array|min:1',
            'indicators.*.name'   => 'required|string',
        ]);

        $indicators = [];
        foreach ($request->indicators as $idx => $item) {
            $indicators[] = [
                'id'       => $idx + 1,
                'name'     => $item['name'],
                'criteria' => $item['criteria'] ?? '',
            ];
        }

        $tp = TpMaster::create([
            'major_id'           => $request->major_id ?: null,
            'code'               => strtoupper($request->code),
            'title'              => $request->title,
            'max_score_per_item' => $request->max_score_per_item,
            'indicators'         => $indicators,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Master TP berhasil ditambahkan!',
            'data'    => $tp
        ]);
    }

    /**
     * Detail TP untuk Modal Edit (AJAX)
     */
    // public function show($id)
    // {
    //     $tpMaster = TpMaster::with('major')->findOrFail($id);
    //     return response()->json([
    //         'status' => 'success',
    //         'data'   => $tpMaster
    //     ]);
    // }
    /**
     * Detail TP untuk Modal Edit & View (AJAX)
     */
    public function show($id)
    {
        $tpMaster = TpMaster::with('major')->find($id);

        if (!$tpMaster) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data TP tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $tpMaster
        ]);
    }

    /**
 * Update Master TP (AJAX)
 */
public function update(Request $request, $id)
{
    // Cek jika ID tidak valid/kosong
    if (!$id || $id === '0') {
        return response()->json([
            'status'  => 'error',
            'message' => 'ID Master TP tidak valid.'
        ], 400);
    }

    $tpMaster = TpMaster::find($id);
    if (!$tpMaster) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Data Master TP tidak ditemukan.'
        ], 404);
    }

    $request->validate([
        'code'                => 'required|string|max:50',
        'title'               => 'required|string|max:255',
        'max_score_per_item' => 'required|integer|min:1|max:10',
        'indicators'          => 'required|array|min:1',
        'indicators.*.name'   => 'required|string',
    ]);

    $indicators = [];
    foreach ($request->indicators as $idx => $item) {
        $indicators[] = [
            'id'       => $idx + 1,
            'name'     => $item['name'],
            'criteria' => $item['criteria'] ?? '',
        ];
    }

    $tpMaster->update([
        'major_id'           => $request->major_id ?: null,
        'code'               => strtoupper($request->code),
        'title'              => $request->title,
        'max_score_per_item' => $request->max_score_per_item,
        'indicators'         => $indicators,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Master TP berhasil diperbarui!'
    ]);
}

    /**
     * Hapus Master TP (AJAX)
     */
    public function destroy($id)
    {
        $tpMaster = TpMaster::findOrFail($id);
        $tpMaster->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Master TP berhasil dihapus!'
        ]);
    }

    /**
     * Parse File (DOCX/PDF) untuk Mengisi Indikator Otomatis via AJAX
     */
    // public function parseFile(Request $request)
    // {
    //     $request->validate([
    //         'document' => 'required|file|mimes:docx,pdf,txt|max:4096'
    //     ]);

    //     $file = $request->file('document');
    //     $extractedText = '';

    //     if ($file->getClientOriginalExtension() === 'docx') {
    //         $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());
    //         foreach ($phpWord->getSections() as $section) {
    //             foreach ($section->getElements() as $element) {
    //                 if (method_exists($element, 'getText')) {
    //                     $extractedText .= $element->getText() . "\n";
    //                 }
    //             }
    //         }
    //     } else {
    //         $extractedText = file_get_contents($file->getRealPath());
    //     }

    //     // Pecah teks berdasarkan baris baru untuk membuat array indikator
    //     $lines = explode("\n", $extractedText);
    //     $indicators = [];
    //     $i = 1;

    //     foreach ($lines as $line) {
    //         $line = trim($line);
    //         if (!empty($line) && strlen($line) > 3) {
    //             $indicators[] = [
    //                 'id'       => $i++,
    //                 'name'     => $line,
    //                 'criteria' => 'Diimpor otomatis dari file: ' . $file->getClientOriginalName()
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'status'     => 'success',
    //         'indicators' => array_slice($indicators, 0, 15) // Mengambil maksimal 15 baris sampel indikator
    //     ]);
    // }
    /**
     * Parse File (DOCX/TXT) untuk Mengisi Indikator Otomatis via AJAX
     */
    // public function parseFile(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'document' => 'required|file|mimes:docx,txt|max:4096'
    //         ]);

    //         $file = $request->file('document');
    //         $extension = strtolower($file->getClientOriginalExtension());
    //         $extractedText = '';

    //         if ($extension === 'docx') {
    //             if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
    //                 return response()->json([
    //                     'status'  => 'error',
    //                     'message' => 'Library PHPWord belum terinstal. Jalankan "composer require phpoffice/phpword".'
    //                 ], 500);
    //             }

    //             $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());

    //             foreach ($phpWord->getSections() as $section) {
    //                 foreach ($section->getElements() as $element) {
    //                     // Baca Paragraf Biasa
    //                     if (method_exists($element, 'getText')) {
    //                         $extractedText .= $element->getText() . "\n";
    //                     } 
    //                     // Baca Tabel (Lembaran Observasi biasanya berbentuk Tabel)
    //                     elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
    //                         foreach ($element->getRows() as $row) {
    //                             foreach ($row->getCells() as $cell) {
    //                                 foreach ($cell->getElements() as $cellElement) {
    //                                     if (method_exists($cellElement, 'getText')) {
    //                                         $extractedText .= $cellElement->getText() . " ";
    //                                     }
    //                                 }
    //                             }
    //                             $extractedText .= "\n";
    //                         }
    //                     }
    //                 }
    //             }
    //         } else {
    //             $extractedText = file_get_contents($file->getRealPath());
    //         }

    //         // Bersihkan teks dan ekstrak menjadi indikator
    //         $lines = explode("\n", $extractedText);
    //         $indicators = [];
    //         $i = 1;

    //         foreach ($lines as $line) {
    //             $line = trim($line);
    //             // Filter baris yang relevan (mengabaikan baris kosong atau header pendek)
    //             if (!empty($line) && strlen($line) > 5 && !preg_match('/^(no|skor|nama|tempat|program|monitoring|mengetahui)/i', $line)) {
    //                 $indicators[] = [
    //                     'id'       => $i++,
    //                     'name'     => $line,
    //                     'criteria' => 'Diimpor dari berkas: ' . $file->getClientOriginalName()
    //                 ];
    //             }
    //         }

    //         if (empty($indicators)) {
    //             return response()->json([
    //                 'status'  => 'error',
    //                 'message' => 'Tidak ditemukan teks indikator yang valid pada dokumen ini.'
    //             ], 422);
    //         }

    //         return response()->json([
    //             'status'     => 'success',
    //             'indicators' => array_slice($indicators, 0, 20) // Ambil maksimal 20 baris indikator
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Gagal membaca berkas: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Parse File (DOCX/TXT) dengan Pemisahan Kolom & Pembersihan Karakter yang Presisi
     */
    public function parseFile(Request $request)
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:docx,txt|max:4096'
            ]);

            $file = $request->file('document');
            $extension = strtolower($file->getClientOriginalExtension());
            $extractedText = '';

            if ($extension === 'docx') {
                if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Library PHPWord belum terinstal.'
                    ], 500);
                }

                $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath());

                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $extractedText .= $element->getText() . "\n";
                        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                            foreach ($element->getRows() as $row) {
                                $rowText = [];
                                foreach ($row->getCells() as $cell) {
                                    $cellText = '';
                                    foreach ($cell->getElements() as $cellElement) {
                                        if (method_exists($cellElement, 'getText')) {
                                            $cellText .= $cellElement->getText() . ' ';
                                        }
                                    }
                                    if (trim($cellText) !== '') {
                                        $rowText[] = trim($cellText);
                                    }
                                }
                                if (!empty($rowText)) {
                                    $extractedText .= implode(" | ", $rowText) . "\n";
                                }
                            }
                        }
                    }
                }
            } else {
                $extractedText = file_get_contents($file->getRealPath());
            }

            $lines = explode("\n", $extractedText);
            $indicators = [];
            $i = 1;

            foreach ($lines as $line) {
                $line = trim($line);

                // Abaikan baris header, jenis pekerjaan, dan footer rekap skor
                if (empty($line) || preg_match('/^(no|skor|nama|tempat|program|monitoring|mengetahui|lembaran|tujuan|jenis pekerjaan|jumlah skor|total skor|nilai akhir)/i', $line)) {
                    continue;
                }

                // Bersihkan penomoran awal (misal: "1. ", "1)", "1 ")
                $line = preg_replace('/^\d+[\.\)\s]+/', '', $line);
                
                // Hilangkan simbol pipe di awal/akhir string
                $line = trim($line, " |");

                $name = '';
                $criteria = '';

                // Kasus 1: Teks memiliki pemisah "|" (Hasil pembacaan kolom tabel Word)
                if (strpos($line, '|') !== false) {
                    $parts = array_values(array_filter(array_map('trim', explode('|', $line))));
                    
                    if (count($parts) >= 2) {
                        // Jika kolom 1 lebih pendek dari kolom 2, maka kolom 1 adalah Nama dan kolom 2 adalah Kriteria
                        if (strlen($parts[0]) <= strlen($parts[1])) {
                            $name = $parts[0];
                            $criteria = implode(" - ", array_slice($parts, 1));
                        } else {
                            $name = $parts[count($parts) - 1];
                            $criteria = implode(" - ", array_slice($parts, 0, count($parts) - 1));
                        }
                    } else if (count($parts) == 1) {
                        $name = $parts[0];
                    }
                } 
                // Kasus 2: Teks biasa tanpa "|"
                else {
                    // Cek pola kalimat: Judul Singkat (Nama) disusul Deskripsi Kriteria
                    if (preg_match('/^([A-Za-z0-9\s\/]{3,30})\s+(Memasang|Melakukan|Menggunakan|Memahami|Mengoperasikan|Melaksanakan|Menangani|Menjaga|Menerapkan|Datang|Mampu|Berkomunikasi|Mengambil|Cepat|Menunjukkan)(.*)$/i', $line, $matches)) {
                        $name = trim($matches[1]);
                        $criteria = trim($matches[2] . $matches[3]);
                    } else {
                        $name = $line;
                    }
                }

                // Bersihkan penomoran sisa di awal nama & kriteria
                $name = preg_replace('/^\d+[\.\)\s]+/', '', trim($name));
                $criteria = preg_replace('/^\d+[\.\)\s]+/', '', trim($criteria));

                if (!empty($name) && strlen($name) > 2) {
                    $indicators[] = [
                        'id'       => $i++,
                        'name'     => $name,
                        'criteria' => $criteria ?: 'Indikator observasi Praktik Kerja Lapangan.'
                    ];
                }
            }

            if (empty($indicators)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ditemukan teks indikator yang valid pada berkas ini.'
                ], 422);
            }

            return response()->json([
                'status'     => 'success',
                'indicators' => array_values($indicators)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membaca berkas: ' . $e->getMessage()
            ], 500);
        }
    }
}