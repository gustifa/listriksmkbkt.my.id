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
    // Naikkan batas waktu eksekusi PHP hingga 5 menit & alokasi memori
    set_time_limit(300);
    ini_set('memory_limit', '512M');

    try {
        $request->validate([
            'template_path' => 'required|string',
            'tp_id' => 'required',
            'teacher_id' => 'required',
        ]);

        $templateRelativePath = $request->template_path;
        
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

        // 1. Ekstrak data dan template
        $templateProcessor = new TemplateProcessor($fullTemplatePath);
        $variables = $templateProcessor->getVariables();

        $tp = TpMaster::findOrFail($request->tp_id);
        $teacher = Teacher::findOrFail($request->teacher_id);
        $namaMapel = $tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan';

        // 2. Olah AI via Gemini Service
        $prompt = "Buatkan konten terstruktur dalam format JSON murni untuk variabel berikut: " . json_encode($variables) 
            . "\nBerdasarkan Mata Pelajaran: " . $namaMapel 
            . " dan TP: " . $tp->title;

        $systemInstruction = "Hasilkan HANYA string JSON valid tanpa markup/pembungkus markdown.";

        $aiResponse = $this->geminiService->generateContent($prompt, $systemInstruction);
        $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
        $aiData = json_decode($cleanJson, true) ?? [];

        // 3. Inject data ke template Word
        foreach ($variables as $variable) {
            if ($variable === 'nama_mapel') {
                $templateProcessor->setValue($variable, $namaMapel);
            } elseif ($variable === 'nama_guru') {
                $templateProcessor->setValue($variable, $teacher->name ?? '-');
            } elseif ($variable === 'nip_guru') {
                $templateProcessor->setValue($variable, $teacher->nip ?? '-');
            } elseif ($variable === 'tujuan_pembelajaran') {
                $templateProcessor->setValue($variable, $tp->title ?? '-');
            } else {
                $templateProcessor->setValue($variable, $aiData[$variable] ?? '-');
            }
        }

        // 4. Simpan ke file temporary & ubah ke Base64
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        $templateProcessor->saveAs($tempFile);
        
        $fileData = file_get_contents($tempFile);
        $base64File = base64_encode($fileData);
        
        @unlink($tempFile);

        $downloadFilename = 'Perangkat_Ajar_' . time() . '.docx';

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen berhasil di-generate!',
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