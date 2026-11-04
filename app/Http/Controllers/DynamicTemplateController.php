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
     * Tampilan Utama
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
     * Method Upload Template (Selesaikan error Call to undefined method)
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
            
            // Simpan file ke storage
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
     * Helper Konversi Data jenis apapun ke String murni
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
     * Helper Menyuntikkan Teks ke Word dengan Line Break (<w:br/>)
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
     * Process & Generate Word (.docx)
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
                    return response()->json(['status' => 'error', 'message' => 'File template tidak ditemukan.'], 404);
                }
            }

            $fullTemplatePath = Storage::path($templateRelativePath);

            $templateProcessor = new TemplateProcessor($fullTemplatePath);
            $variables =$templateProcessor->getVariables();

            $tp = TpMaster::with('subject')->findOrFail($request->tp_id);
            $teacher = Teacher::findOrFail($request->teacher_id);
            
            $namaMapel =$this->safeString($tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan');
            $judulTp =$this->safeString($tp->title);$indikatorTp = $this->safeString($tp->indicators ?? 'Standar kompetensi SMK');
            $namaGuru = $this->safeString($teacher->name ?? 'GUSTIFA FAUZAN, S.Pd');
            $nipGuru = $this->safeString($teacher->nip ?? '-');

            $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_values(array_diff($variables,$staticKeys));

            $systemInstruction = "Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi. "
                . "TUGAS: Hasilkan konten pembelajaran yang 100% RELEVAN dan SPESIFIK sesuai Tujuan Pembelajaran (TP). "
                . "OUTPUT WAJIB BERUPA OBJEK JSON VALID tanpa markup ```json.";

            $userPrompt = "Saya sedang menyusun dokumen perangkat ajar SMK dengan acuan data resmi berikut:\n"
                . "----------------------------------------\n"
                . "- MATA PELAJARAN: {$namaMapel}\n"
                . "- TUJUAN PEMBELAJARAN (TP): {$judulTp}\n"
                . "- INDIKATOR/ELEMEN KUNCI: {$indikatorTp}\n"
                . "- GURU PENGAMPU: {$namaGuru}\n"
                . "----------------------------------------\n\n"
                . "Tolong buatkan isi konten teknis yang mendalam dan spesifik untuk mengisi variabel-variabel berikut:\n"
                . json_encode($aiVariables) . "\n\n"
                . "KETENTUAN PENTING:\n"
                . "1. Buatlah jawaban berbentuk objek JSON dengan KEY/KUNCI persis seperti daftar variabel di atas.\n"
                . "2. Setiap nilai (value) HARUS berbentuk STRING murni atau TEKS TUNGGAL.\n"
                . "3. Bahas materi spesifik tentang '{$judulTp}'.";

            $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
            
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
            $cleanJson = trim($cleanJson, '`');
            $aiData = json_decode($cleanJson, true) ?? [];

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

            $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
            $templateProcessor->saveAs($tempFile);
            
            $fileData = file_get_contents($tempFile);
            $base64File = base64_encode($fileData);
            @unlink($tempFile);

            $downloadFilename = 'Jobsheet_' . Str::slug($judulTp) . '_' . time() . '.docx';

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen berhasil disesuaikan dengan TP!',
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