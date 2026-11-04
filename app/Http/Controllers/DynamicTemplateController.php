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
     * Upload Template .docx baru oleh User via AJAX
     */
    public function uploadTemplate(Request $request)
    {
        try {
            $request->validate([
                'template_file' => 'required|file|mimes:docx|max:10240',
                'template_name' => 'required|string|max:100'
            ]);

            $file = $request->file('template_file');
            $filename = time() . '_' . Str::slug($request->template_name) . '.docx';
            
            // Simpan file ke storage lokal
            $file->storeAs('public/templates', $filename);

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
     * Generate Dokumen via Gemini AI & Download Langsung via Base64 (Bypass 403/404)
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
                return response()->json([
                    'status' => 'error', 
                    'message' => 'File template tidak ditemukan di server.'
                ], 404);
            }
        }

        $fullTemplatePath = Storage::path($templateRelativePath);

        // 1. Load Template & Deteksi Semua Variable Placeholder ${...}
        $templateProcessor = new TemplateProcessor($fullTemplatePath);
        $variables =$templateProcessor->getVariables();

        // 2. Ambil Data Detail dari PostgreSQL
        $tp = TpMaster::with('subject')->findOrFail($request->tp_id);$teacher = Teacher::findOrFail($request->teacher_id);$namaMapel = $tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan';
        $judulTp =$tp->title;
        $indikatorTp = is_array($tp->indicators) ? implode(', ', $tp->indicators) : ($tp->indicators ?? 'Sesuai standar elemen kompetensi SMK');

        // Filter variabel yang BUKAN variabel statis (karena variabel statis akan kita isi manual)
        $staticKeys = ['nama_mapel', 'nama_guru', 'nip_guru', 'tujuan_pembelajaran', 'tp', 'mapel', 'guru', 'nip'];$aiVariables = array_diff($variables,$staticKeys);

        // 3. Susun Prompt Spesifik & Ketat
        $systemInstruction = "Anda adalah Pakar Pengembang Kurikulum Merdeka SMK Vokasi bidang Teknik Ketenagalistrikan dan Elektronika. "
            . "TUGAS UTAMA: Hasilkan konten pembelajaran yang 100% RELEVAN, SPESIFIK, dan AKURAT sesuai dengan Tujuan Pembelajaran (TP) yang diberikan. "
            . "DILARANG keras membuat materi umum/generik yang tidak ada hubungannya dengan TP tersebut. "
            . "OUTPUT HARUS BERUPA JSON VALID tanpa tanda ```json atau markup markdown lainnya.";

        $userPrompt = "Saya sedang menyusun dokumen perangkat ajar SMK dengan acuan data resmi berikut:\n"
            . "----------------------------------------\n"
            . "- MATA PELAJARAN: {$namaMapel}\n"
            . "- TUJUAN PEMBELAJARAN (TP): {$judulTp}\n"
            . "- INDIKATOR/ELEMEN KUNCI: {$indikatorTp}\n"
            . "- GURU PENGAMPU: {$teacher->name}\n"
            . "----------------------------------------\n\n"
            . "Tolong buatkan isi konten teknis yang mendalam dan spesifik untuk mengisi variabel-variabel berikut:\n"
            . json_encode(array_values($aiVariables)) . "\n\n"
            . "KETENTUAN PENTING:\n"
            . "1. Buatlah jawaban berbentuk objek JSON dengan KEY/KUNCI persis sama seperti daftar variabel di atas.\n"
            . "2. Setiap nilai (value) harus secara LANGSUNG membahas, mempraktikkan, atau mengevaluasi materi: '{$judulTp}'.\n"
            . "3. Gunakan bahasa teknis vokasi yang profesional, jelas, dan siap pakai di kelas/bengkel SMK.";

        // 4. Panggil Gemini AI Service
        $aiResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
        
        // Pembersihan Format JSON
        $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiResponse));
        $cleanJson = trim($cleanJson, '`');
        $aiData = json_decode($cleanJson, true) ?? [];

        // 5. Inject Data Statis & Data AI ke File Word
        foreach ($variables as $variable) {
            $varLower = strtolower($variable);

            // Mapping Variabel Statis
            if (in_array($varLower, ['nama_mapel', 'mapel'])) {
                $templateProcessor->setValue($variable, $namaMapel);
            } elseif (in_array($varLower, ['nama_guru', 'guru'])) {
                $templateProcessor->setValue($variable, $teacher->name ?? '-');
            } elseif (in_array($varLower, ['nip_guru', 'nip'])) {
                $templateProcessor->setValue($variable, $teacher->nip ?? '-');
            } elseif (in_array($varLower, ['tujuan_pembelajaran', 'tp'])) {
                $templateProcessor->setValue($variable, $judulTp);
            } else {
                // Mapping Hasil AI
                $val = $aiData[$variable] ?? $aiData[$varLower] ?? null;

                if (is_array($val)) {
                    $val = implode("\n• ", $val);
                }

                $templateProcessor->setValue($variable, $val ?? '-');
            }
        }

        // 6. Simpan Temporary & Konversi ke Base64 untuk Direct Download
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