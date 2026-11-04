<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TpMaster;
use App\Models\Teacher;
use App\Services\GeminiApiService;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class DynamicTemplateController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiApiService $geminiService)
    {
        $this->geminiService =$geminiService;
    }

    /**
     * Tampilan Utama Generator Perangkat Ajar
     */
    public function index()
    {
        $tps = TpMaster::all();$teachers = Teacher::all();

        if (!Storage::exists('public/templates')) {
            Storage::makeDirectory('public/templates');
        }

        $uploadedTemplates = Storage::files('public/templates');

        return view('templates.index', compact('tps', 'teachers', 'uploadedTemplates'));
    }

    /**
     * Upload Template .docx Baru (Selesaikan Error Call to Undefined Method)
     */
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

    /**
     * Helper Konversi Data (Array/Object/JSON) ke String Murni (Mencegah Array to string conversion)
     */
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

    /**
     * Helper Menyuntikkan Teks Multiline ke Word dengan Tag <w:br/>
     */
    private function setValueWithLineBreaks($templateProcessor, $variable,$text)
    {
        $text = (string)$text;
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        
        if (count($lines) > 1) {$formattedText = implode("<w:br/>", array_map('htmlspecialchars', $lines));$templateProcessor->setValue($variable,$formattedText);
        } else {
            $templateProcessor->setValue($variable, htmlspecialchars($text));
        }
    }

    /**
     * Generator Prompt Dinamis Berdasarkan Data TP & Variabel Template Word
     */
    private function buildDynamicPrompt($tp,$teacher, array $aiVariables,$namaMapel)
    {
        $judulTp =$this->safeString($tp->title);$indikatorTp = $this->safeString($tp->indicators ?? 'Kompetensi keahlian standar SMK Merdeka');
        $elemenKode =$this->safeString($tp->code ?? $tp->element_code ?? '-');

        $variableList = json_encode(array_values($aiVariables), JSON_PRETTY_PRINT);

        return <<<PROMPT
Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi bidang {$namaMapel}.

TOLONG BUATKAN KONTEN TEKNIS SPESIFIK UNTUK DOKUMEN PERANGKAT AJAR DENGAN RINCIAN BERIKUT:
=================================================================================
- MATA PELAJARAN      : {$namaMapel}
- TUJUAN PEMBELAJARAN : {$judulTp}
- KODE / ELEMEN       : {$elemenKode}
- INDIKATOR KINERJA   : {$indikatorTp}
- GURU PENGAMPU       : {$teacher->name}
=================================================================================

KONTEN YANG HARUS DIISI (VARIABLE PLACEHOLDER):
{$variableList}

ATURAN GENERASI KONTEN (WAJIB DIPATUHI):
1. Hasil HARUS berupa JSON VALID tunggal dengan kunci/key PERSIS SAMA seperti daftar variabel di atas.
2. Setiap nilai (value) HARUS berbentuk STRING murni yang siap dimasukkan ke dokumen Word.
3. Seluruh materi, K3, langkah kerja praktikum, teori, dan rubrik penilaian HARUS BERFOKUS 100% pada topik: "{$judulTp}".
4. DILARANG KERAS memberikan teks umum/generik seperti "Lakukan praktikum dengan baik". Berikan instruksi teknis vokasi yang detail dan sistematis.
5. Gunakan format poin bertingkat dengan karakter bullet (•) jika berisi beberapa langkah/poin.
PROMPT;
    }

    /**
     * Eksekusi Generate Dokumen Word via Gemini AI & Direct Base64 Download
     */
    public function generateDocument(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'template_path' => 'required|string',
                'tp_id' => 'required',
                'teacher_id' => 'required',
            ]);

            $templateRelativePath =$request->template_path;
            
            if (!Storage::exists($templateRelativePath)) {
                if (Storage::exists('public/templates/' . basename($templateRelativePath))) {
                    $templateRelativePath = 'public/templates/' . basename($templateRelativePath);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'File template tidak ditemukan di server.'], 404);
                }
            }

            $fullTemplatePath = Storage::path($templateRelativePath);

            // 1. Load Template & Deteksi Semua Variable Placeholder ${...}
            $templateProcessor = new TemplateProcessor($fullTemplatePath);
            $variables =$templateProcessor->getVariables();

            // 2. Ambil Data Detail dari PostgreSQL
            $tp = TpMaster::with('subject')->findOrFail($request->tp_id);
            $teacher = Teacher::findOrFail($request->teacher_id);
            
            $namaMapel =$this->safeString($tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan');
            $judulTp =$this->safeString($tp->title);$namaGuru = $this->safeString($teacher->name ?? '-');
            $nipGuru = $this->safeString($teacher->nip ?? '-');

            // Filter variabel statis dan variabel AI
            $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_values(array_diff($variables,$staticKeys));

            // 3. Susun Prompt Dinamis
            $systemInstruction = "Anda adalah Asisten AI Kurikulum SMK Merdeka yang ahli dalam Vokasi Teknik Ketenagalistrikan. Output HARUS murni string JSON valid tanpa markup markdown (tanpa ```json).";
            
            $userPrompt = $this->buildDynamicPrompt($tp, $teacher, $aiVariables, $namaMapel);

            // 4. Panggil Gemini Service
            $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
            
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
            $cleanJson = trim($cleanJson, '`');
            $aiData = json_decode($cleanJson, true) ?? [];

            // 5. Inject Hasil ke File Word
            foreach ($variables as $variable) {
                $varLower = strtolower($variable);

                if (in_array($varLower, ['nama_mapel', 'mapel'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($namaMapel));
                } elseif (in_array($varLower, ['nama_guru', 'guru'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($namaGuru));
                } elseif (in_array($varLower, ['nip_guru', 'nip'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($nipGuru));
                } elseif (in_array($varLower, ['tujuan_pembelajaran', 'tp'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($judulTp));
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

            // 6. Simpan Temporary & Konversi Base64 untuk Direct Download (Bypass 403/404)
            $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
            $templateProcessor->saveAs($tempFile);
            
            $fileData = file_get_contents($tempFile);
            $base64File = base64_encode($fileData);
            @unlink($tempFile);

            $downloadFilename = 'Perangkat_Ajar_' . Str::slug($judulTp) . '_' . time() . '.docx';

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen berhasil di-generate secara dinamis!',
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
}