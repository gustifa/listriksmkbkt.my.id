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
        $this->geminiService = $geminiService;
    }

    /**
     * Tampilan utama generator perangkat ajar
     */
    public function index()
    {
        $tps = TpMaster::all();
        $teachers = Teacher::all();

        // Buat direktori penyimpanan template jika belum ada
        if (!Storage::exists('public/templates')) {
            Storage::makeDirectory('public/templates');
        }

        $uploadedTemplates = Storage::files('public/templates');

        return view('templates.index', compact('tps', 'teachers', 'uploadedTemplates'));
    }

    /**
     * Helper Fungsi untuk mengonversi data jenis apa pun (Array, Object, JSON) ke String murni.
     * Mengunci error 'Array to string conversion' secara total.
     */
    /**
     * Helper khusus untuk menyuntikkan teks AI ke Word dengan Line Break (<w:br/>)
     */
    private function setValueWithLineBreaks($templateProcessor, $variable,$text)
    {
        $text = (string)$text;
        // Ganti new line biasa dengan format multiline milik PhpOffice/PhpWord
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
        
        if (count($lines) > 1) { // Gunakan metode cloneValue / setComplexValue jika tersedia, atau konversi ke line break Word$formattedText = implode("<w:br/>", array_map('htmlspecialchars', $lines));$templateProcessor->setValue($variable,$formattedText);
        } else {
            $templateProcessor->setValue($variable, htmlspecialchars($text));
        }
    }

    private function safeString($data)
    {
        if (is_null($data)) return '-';
        if (is_string($data) || is_numeric($data)) return (string)$data;

        if (is_array($data) || is_object($data)) {$result = [];
            foreach ((array) $data as$key => $val) {$valStr = is_array($val) || is_object($val) ? json_encode($val) : (string)$val;
                $result[] = is_numeric($key) ?$valStr : "{$key}: {$valStr}";
            }
            return implode("\n• ", $result);
        }
        return (string) $data;
    }

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
                    return response()->json(['status' => 'error', 'message' => 'File template tidak ditemukan.'], 404);
                }
            }

            $fullTemplatePath = Storage::path($templateRelativePath);

            // 1. Load Template Word
            $templateProcessor = new TemplateProcessor($fullTemplatePath);
            
            // Ambil SEMUA variabel yang ada pada dokumen Word
            $variables =$templateProcessor->getVariables();

            // 2. Ambil data aktual dari PostgreSQL
            $tp = TpMaster::with('subject')->findOrFail($request->tp_id);
            $teacher = Teacher::findOrFail($request->teacher_id);
            
            $namaMapel =$this->safeString($tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan');
            $judulTp =$this->safeString($tp->title);$indikatorTp = $this->safeString($tp->indicators ?? 'Kompetensi Keahlian SMK');
            $namaGuru = $this->safeString($teacher->name ?? '-');
            $nipGuru = $this->safeString($teacher->nip ?? '-');

            // Filter variabel yang BUKAN variabel identitas
            $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_values(array_diff($variables,$staticKeys));

            // 3. Susun Prompt AI yang Sangat Spesifik
            $systemInstruction = "Anda adalah Ahli Kurikulum SMK Vokasi. "
                . "TUGAS: Buatkan konten praktikum yang 100% SPESIFIK membahas topik TP yang diberikan. "
                . "DILARANG mengembalikan data umum/default. "
                . "OUTPUT WAJIB BERUPA JSON VALID TANPA MARKDOWN (tanpa ```json).";

            $userPrompt = "Buatkan isi konten praktikum spesifik untuk:\n"
                . "Mata Pelajaran: {$namaMapel}\n"
                . "Tujuan Pembelajaran (TP): {$judulTp}\n"
                . "Indikator: {$indikatorTp}\n\n"
                . "Isilah variabel-variabel berikut sesuai konteks TP '{$judulTp}':\n"
                . json_encode($aiVariables) . "\n\n"
                . "Keluarkah JSON dengan KUNCI/KEY persis seperti daftar variabel di atas!";

            // 4. Panggil Gemini AI
            $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
            
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
            $cleanJson = trim($cleanJson, '`');
            $aiData = json_decode($cleanJson, true) ?? [];

            // 5. Inject Ke Template Word
            foreach ($variables as $variable) {
                $varLower = strtolower($variable);

                // Variabel Identitas Statis
                if (in_array($varLower, ['nama_mapel', 'mapel'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($namaMapel));
                } elseif (in_array($varLower, ['nama_guru', 'guru'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($namaGuru));
                } elseif (in_array($varLower, ['nip_guru', 'nip'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($nipGuru));
                } elseif (in_array($varLower, ['tujuan_pembelajaran', 'tp'])) {
                    $templateProcessor->setValue($variable, htmlspecialchars($judulTp));
                } else {
                    // Variabel Konten AI (Cari yang paling cocok di array JSON AI)
                    $rawValue = $aiData[$variable] ?? $aiData[$varLower] ?? null;

                    // Jika AI tidak mengembalikan key tersebut, cari yang mirip
                    if ($rawValue === null) {
                        foreach ($aiData as $k => $v) {
                            if (strtolower($k) === $varLower) {
                                $rawValue = $v;
                                break;
                            }
                        }
                    }

                    $safeValue = $this->safeString($rawValue ?? 'Konten disesuaikan dengan TP: ' . $judulTp);
                    
                    // Inject menggunakan Line Break khusus Word
                    $this->setValueWithLineBreaks($templateProcessor, $variable, $safeValue);
                }
            }

            // 6. Simpan Temporary File & Convert Ke Base64
            $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
            $templateProcessor->saveAs($tempFile);
            
            $fileData = file_get_contents($tempFile);
            $base64File = base64_encode($fileData);
            @unlink($tempFile);

            $downloadFilename = 'Jobsheet_' . Str::slug($judulTp) . '_' . time() . '.docx';

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen & Konten Isi Berhasil Diperbarui Sesuai TP!',
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