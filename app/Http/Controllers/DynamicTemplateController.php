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
     * Helper Fungsi untuk mengonversi data jenis apa pun (Array, Object, JSON) ke String murni.
     * Mengunci error 'Array to string conversion' secara total.
     */
    private function safeString($data)
    {
        if (is_null($data)) {
            return '-';
        }

        if (is_string($data) || is_numeric($data) || is_bool($data)) {
            return (string) $data;
        }

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
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'File template tidak ditemukan di server.'
                    ], 404);
                }
            }

            $fullTemplatePath = Storage::path($templateRelativePath);

            // 1. Ekstrak Template Processor
            $templateProcessor = new TemplateProcessor($fullTemplatePath);
            $variables =$templateProcessor->getVariables();

            // 2. Ambil data PostgreSQL & Konversi Aman ke String
            $tp = TpMaster::with('subject')->findOrFail($request->tp_id);
            $teacher = Teacher::findOrFail($request->teacher_id);
            
            $namaMapel =$this->safeString($tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan');
            $judulTp =$this->safeString($tp->title);$indikatorTp = $this->safeString($tp->indicators ?? 'Standar kompetensi SMK');
            $namaGuru = $this->safeString($teacher->name ?? '-');
            $nipGuru = $this->safeString($teacher->nip ?? '-');

            // Pisahkan kunci statis dan variabel AI
            $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_diff($variables,$staticKeys);

            // 3. Susun Prompt AI
            $systemInstruction = "Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi. "
                . "TUGAS UTAMA: Hasilkan konten pembelajaran yang 100% RELEVAN dan SPESIFIK sesuai Tujuan Pembelajaran (TP). "
                . "OUTPUT HARUS BERUPA JSON VALID tanpa markup ```json.";

            $userPrompt = "Saya sedang menyusun dokumen perangkat ajar SMK dengan acuan data resmi berikut:\n"
                . "----------------------------------------\n"
                . "- MATA PELAJARAN: {$namaMapel}\n"
                . "- TUJUAN PEMBELAJARAN (TP): {$judulTp}\n"
                . "- INDIKATOR/ELEMEN KUNCI: {$indikatorTp}\n"
                . "- GURU PENGAMPU: {$namaGuru}\n"
                . "----------------------------------------\n\n"
                . "Tolong buatkan isi konten teknis yang mendalam dan spesifik untuk mengisi variabel-variabel berikut:\n"
                . json_encode(array_values($aiVariables)) . "\n\n"
                . "KETENTUAN PENTING:\n"
                . "1. Buatlah jawaban berbentuk objek JSON dengan KEY/KUNCI persis sama seperti daftar variabel di atas.\n"
                . "2. Setiap nilai (value) HARUS berbentuk STRING murni atau TEKS TUNGGAL.\n"
                . "3. Bahas materi spesifik tentang '{$judulTp}'.";

            // 4. Panggil Gemini AI Service
            $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
            
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
            $cleanJson = trim($cleanJson, '`');
            $aiData = json_decode($cleanJson, true);

            if (!is_array($aiData)) {
                $aiData = [];
            }

            // 5. Inject Ke Template Word Menggunakan safeString()
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
                    // Ambil nilai dari AI & Konversi AMAN ke String murni
                    $rawValue = $aiData[$variable] ?? $aiData[$varLower] ?? '-';
                    $safeValue = $this->safeString($rawValue);

                    $templateProcessor->setValue($variable, $safeValue);
                }
            }

            // 6. Simpan Temporary & Return Data Base64
            $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
            $templateProcessor->saveAs($tempFile);
            
            $fileData = file_get_contents($tempFile);
            $base64File = base64_encode($fileData);
            @unlink($tempFile);

            $downloadFilename = 'Perangkat_Ajar_' . Str::slug($judulTp) . '_' . time() . '.docx';

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