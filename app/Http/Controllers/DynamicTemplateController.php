<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TpMaster;
use App\Models\Teacher;
use App\Models\Inventory;
use App\Services\GeminiApiService;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class DynamicTemplateController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiApiService $geminiService)
    {
        $this->geminiService =$geminiService;
    }

    public function index()
    {
        $tpsMaster = DB::table('tp_masters')
            ->select('id', 'code', 'title')
            ->get();

        $tpsObjectives = DB::table('learning_objectives')
            ->select('id', 'code', 'description as title')
            ->get();

        $tps =$tpsMaster->merge($tpsObjectives);$teachers = Teacher::all();

        if (!Storage::exists('public/templates')) {
            Storage::makeDirectory('public/templates');
        }

        $uploadedTemplates = Storage::files('public/templates');

        return view('templates.index', compact('tps', 'teachers', 'uploadedTemplates'));
    }

    public function uploadTemplate(Request $request)
    {
        try {
            $request->validate([
                'template_file' => 'required|file|mimes:docx|max:10240',
                'template_name' => 'required|string|max:100'
            ]);

            $file =$request->file('template_file');
            $filename = time() . '_' . Str::slug($request->template_name) . '.docx';
            
            $file->storeAs('public/templates',$filename);

            return response()->json([
                'status' => 'success',
                'message' => 'Template Word (.docx) berhasil diunggah!'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengunggah template: ' . $e->getMessage()
            ], 422);
        }
    }

    private function safeString($data)
    {
        if (is_null($data)) return '-';
        if (is_string($data) || is_numeric($data) || is_bool($data)) return (string)$data;

        if (is_array($data) || is_object($data)) {$result = [];
            foreach ((array) $data as $key =>$val) {
                if (is_array($val) || is_object($val)) {$result[] = is_numeric($key) ?$this->safeString($val) : "{$key}: " . $this->safeString($val);
                } else {
                    $result[] = is_numeric($key) ? (string)$val : "{$key}: {$val}";
                }
            }
            return implode("\n• ", $result);
        }

        return (string) $data;
    }

    private function setValueWithLineBreaks($templateProcessor, $variable,$text)
    {
        $text = (string)$text;
        $text = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'],$text);
        
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        
        if (count($lines) > 1) {$formattedText = implode("</w:t><w:br/><w:t>", $lines);$templateProcessor->setValue($variable,$formattedText);
        } else {
            $templateProcessor->setValue($variable,$text);
        }
    }

    private function buildDynamicPrompt($judulTp,$indikatorTp, $elemenKode,$teacher, array $aiVariables,$namaMapel)
    {
        $inventoryList = 'Multimeter, Trainer Board, Power Supply DC, Cable Tester, APD Bengkel, SOP K3LH';
        if (class_exists(\App\Models\Inventory::class)) {
            $invData = \App\Models\Inventory::pluck('name')->take(8)->implode(', ');
            if (!empty($invData)) $inventoryList =$invData;
        }

        $variableList = json_encode(array_values($aiVariables), JSON_PRETTY_PRINT);

        return <<<PROMPT
Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi & Instruktur Senior Teknik Ketenagalistrikan di SMK Negeri 1 Bukittinggi.

TUGAS UTAMA:
Susunlah konten perangkat ajar (Modul Ajar / Jobsheet / RPP) yang SANGAT CERDAS, MENDALAM, TERSTRUKTUR, RELEVAN DENGAN DUNIA KERJA (IDUKA), dan BERBASIS HOTS (C3-C6) berdasarkan data acuan berikut:
=================================================================================
- SATUAN PENDIDIKAN   : SMK NEGERI 1 BUKITTINGGI
- MATA PELAJARAN      : {$namaMapel}
- TUJUAN PEMBELAJARAN : {$judulTp}
- KODE / ELEMEN       : {$elemenKode}
- INDIKATOR KINERJA   : {$indikatorTp}
- GURU PENGAMPU       : {$teacher->name} (NIP. {$teacher->nip})
- INVENTARIS BENGKEL  : {$inventoryList}
=================================================================================

DAFTAR VARIABLE PLACEHOLDER YANG WAJIB DIISI:
{$variableList}

PRINSIP KECERDASAN ARTIFISIAL DALAM MENYUSUN ISI (WAJIB PATUHI):
1. SPESIFIK & TEKNIS VOKASI: DILARANG memberikan kata-kata generik. Gunakan komponen fisik nyata, alat ukur spesifik, rumus kelistrikan, dan aturan K3LH relevan.
2. MODEL PEMBELAJARAN (Sintaks PjBL / PBL): Uraikan aktivitas Guru dan Siswa secara operasional.
3. PROFIL LULUSAN: Pilih Dimensi Profil Lulusan yang tepat beserta deskripsi integrasinya.
4. ASESMEN & RUBRIK: Sajikan instrumen asesmen dan rubrik kinerja terukur.
5. FORMAT OUTPUT: Hasilkan HANYA string JSON VALID berbentuk teks multiline murni dengan poin bullet (•).
PROMPT;
    }

    private function generateAutoTemplateStream()
    {
        $phpWord = new PhpWord();
        
        $section =$phpWord->addSection([
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 1134,
            'marginRight' => 1134,
        ]);

        $titleStyle = ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '0F2240'];$subTitleStyle = ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '0051A3'];
        
        $section->addText('LEMBAR KERJA PESERTA DIDIK / JOBSHEET PRAKTIK',$titleStyle, ['alignment' => Jc::CENTER]);
        $section->addText('${topik_pembelajaran}', $subTitleStyle, ['alignment' => Jc::CENTER]);$section->addTextBreak(1);

        $tableStyle = ['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 80];
        $phpWord->addTableStyle('IdentityTable',$tableStyle);
        $table =$section->addTable('IdentityTable');

        $rowsData = [
            ['Satuan Pendidikan', 'SMK NEGERI 1 BUKITTINGGI'],
            ['Program Keahlian', 'Teknik Ketenagalistrikan'],
            ['Mata Pelajaran', '${nama_mapel}'],
            ['Topik / Modul Ajar', '${tujuan_pembelajaran}'],
            ['Guru Pengampu', '${nama_guru} (NIP.${nip_guru})'],
            ['Nama Kelompok / Anggota', 'Kelompok: ......... / Nama: 1. .........  2. .........  3. .........']
        ];

        foreach ($rowsData as$row) {
            $table->addRow();$table->addCell(2500, ['bgColor' => '0F2240'])->addText($row[0], ['bold' => true, 'color' => 'FFFFFF', 'size' => 9.5]);$table->addCell(300, ['bgColor' => 'F0F4F8'])->addText(':', ['bold' => true, 'size' => 9.5]);
            $table->addCell(6200, ['bgColor' => 'F0F4F8'])->addText($row[1], ['size' => 9.5]);
        }

        $section->addTextBreak(1);

        $addSectionBox = function($title, $variable) use ($section) {
            $section->addText($title, ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '0F2240']);
            $boxTable =$section->addTable(['borderSize' => 0]);
            $boxTable->addRow();$cell = $boxTable->addCell(9000, ['bgColor' => 'F8FAFC']);$cell->addText($variable, ['name' => 'Arial', 'size' => 9.5, 'color' => '1E293B']);$section->addTextBreak(1);
        };

        $addSectionBox('I. TUJUAN PEMBELAJARAN & INDIKATOR KINERJA', '${tujuan_pembelajaran_detail}');$addSectionBox('II. KESELAMATAN KERJA (K3) DAN PETUNJUK UMUM', '${keselamatan_kerja}');$addSectionBox('III. ALAT DAN BAHAN PRAKTIKUM', '${alat_dan_bahan}');$addSectionBox('IV. RINGKASAN TEORI & SKEMATIK RANGKAIAN', '${ringkasan_teori}');$addSectionBox('V. LANGKAH-LANGKAH KERJA PERCOBAAN', '${langkah_kerja}');$addSectionBox('VI. LEMBAR PENILAIAN & RUBRIK KINERJA', '${rubrik_penilaian}');

        $tempPath = tempnam(sys_get_temp_dir(), 'tpl_auto_');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempPath);

        return $tempPath;
    }

    /**
     * Core Method: Process & Generate Word (.docx) - Support Database & Manual TP Input
     */
    public function generateDocument(Request $request)
    {
        set_time_limit(300);
        daq: ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'teacher_id' => 'required',
                'tp_mode'    => 'nullable|in:database,manual',
            ]);

            $isAutoGenerated = false;

            if (empty($request->template_path) || $request->template_path === 'auto') {$fullTemplatePath = $this->generateAutoTemplateStream();$isAutoGenerated = true;
            } else {
                $templateRelativePath =$request->template_path;
                
                if (!Storage::exists($templateRelativePath)) {
                    if (Storage::exists('public/templates/' . basename($templateRelativePath))) {
                        $templateRelativePath = 'public/templates/' . basename($templateRelativePath);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'File template tidak ditemukan di server.'], 404);
                    }
                }

                $fullTemplatePath = Storage::path($templateRelativePath);
            }

            $templateProcessor = new TemplateProcessor($fullTemplatePath);
            $variables =$templateProcessor->getVariables();

            $judulTp = "";
            $indikatorTp = "Standar kompetensi keahlian vokasi SMK";
            $elemenKode = "TP-KLI-01";
            $namaMapel = "Teknik Ketenagalistrikan";

            // Tangani Input TP (Manual vs Database)
            if ($request->tp_mode === 'manual' || $request->filled('tp_manual')) {
                $judulTp = trim($request->tp_manual);
                if (empty($judulTp)) { return response()->json(['status' => 'error', 'message' => 'Tujuan Pembelajaran (TP) manual tidak boleh kosong.'], 422); } } else {$request->validate(['tp_id' => 'required']);

                $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first();$sourceTable = 'tp_masters';

                if (!$tp) {$tp = DB::table('learning_objectives')->where('id', $request->tp_id)->first();$sourceTable = 'learning_objectives';
                }

                if (!$tp) {
                    return response()->json(['status' => 'error', 'message' => 'Data TP tidak ditemukan di database.'], 404);
                }

                if ($sourceTable === 'tp_masters') {
                    $judulTp =$this->safeString($tp->title ?? $tp->description ?? '-');
                    $indikatorTp =$this->safeString($tp->indicators ?? $tp->title ?? 'Kompetensi keahlian vokasi SMK');
                    $elemenKode = $this->safeString($tp->code ?? '-');
                    
                    if (!empty($tp->major_id)) {
                        $major = DB::table('majors')->where('id',$tp->major_id)->first();
                        if ($major) $namaMapel =$major->name;
                    }
                } else {
                    $judulTp =$this->safeString($tp->description ?? $tp->title ?? '-');
                    $indikatorTp = $this->safeString($tp->description ?? 'Kompetensi keahlian vokasi SMK');
                    $elemenKode = $this->safeString($tp->code ?? '-');
                    
                    if (!empty($tp->subject_id)) {
                        $subject = DB::table('subjects')->where('id',$tp->subject_id)->first();
                        if ($subject) $namaMapel =$subject->name;
                    }
                }
            }

            $teacher = Teacher::findOrFail($request->teacher_id);$namaGuru = $this->safeString($teacher->name ?? '-');
            $nipGuru = $this->safeString($teacher->nip ?? '-');

            $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_values(array_diff($variables,$staticKeys));

            $systemInstruction = "Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi & Instruktur Senior. Output HARUS murni string JSON valid tanpa markup markdown (tanpa ```json).";
            
            $userPrompt = $this->buildDynamicPrompt($judulTp, $indikatorTp, $elemenKode, $teacher, $aiVariables, $namaMapel);

            $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
            
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
            $cleanJson = trim($cleanJson, '`');
            $aiData = json_decode($cleanJson, true) ?? [];

            foreach ($variables as $variable) {
                $varLower = strtolower($variable);

                if (in_array($varLower, ['nama_mapel', 'mapel'])) {
                    $templateProcessor->setValue($variable, $namaMapel);
                } elseif (in_array($varLower, ['nama_guru', 'guru'])) {
                    $templateProcessor->setValue($variable, $namaGuru);
                } elseif (in_array($varLower, ['nip_guru', 'nip'])) {
                    $templateProcessor->setValue($variable, $nipGuru);
                } elseif (in_array($varLower, ['tujuan_pembelajaran', 'tp'])) {
                    $templateProcessor->setValue($variable, $judulTp);
                } else {
                    $rawValue = $aiData[$variable] ?? $aiData[$varLower] ?? null;

                    if ($rawValue === null) {
                        foreach ($aiData as $k => $v) {
                            if (strtolower($k) === $varLower) {
                                $rawValue = $v;
                                break;
                            }
                        }
                    }

                    $safeValue = $this->safeString($rawValue ?? 'Konten disesuaikan dengan TP: ' . $judulTp);
                    $this->setValueWithLineBreaks($templateProcessor, $variable, $safeValue);
                }
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'docx_out_');
            $templateProcessor->saveAs($tempFile);
            
            $fileData = file_get_contents($tempFile);
            $base64File = base64_encode($fileData);
            
            @unlink($tempFile);
            if ($isAutoGenerated && file_exists($fullTemplatePath)) {
                @unlink($fullTemplatePath);
            }

            $downloadFilename = 'Perangkat_Ajar_' . Str::slug(Str::limit($judulTp, 30)) . '_' . time() . '.docx';

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen berhasil disusun dan disesuaikan secara cerdas dengan TP!',
                'filename' => $downloadFilename,
                'file_data' => $base64File,
                'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateImage(Request $request)
{
    try {
        $request->validate([
            'tp_mode' => 'nullable|in:database,manual',
        ]);

        $judulTp = "";
        if ($request->tp_mode === 'manual' || $request->filled('tp_manual')) {
            $judulTp = trim($request->tp_manual);
        } else {
            $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first() 
                ?? DB::table('learning_objectives')->where('id', $request->tp_id)->first();
            $judulTp = $tp->title ?? $tp->description ?? 'Praktikum Kelistrikan';
        }

        // Panggil Image Generator Service
        $imageService = new \App\Services\ImageGeneratorService();
        $result = $imageService->generateIllustration($judulTp);

        return response()->json([
            'status' => 'success',
            'image_url' => $result['image_url'],
            'message' => 'Ilustrasi gambar praktikum berhasil dibuat!'
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal generate gambar: ' . $e->getMessage()
        ], 500);
    }
}
}