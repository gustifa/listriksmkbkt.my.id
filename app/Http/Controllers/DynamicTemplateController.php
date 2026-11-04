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

    public function index()
    {
        $tps = TpMaster::all();$teachers = Teacher::all();

        // Buat folder jika belum ada
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

    public function generateDocument(Request $request)
{
    try {
        $request->validate([
            'template_path' => 'required|string',
            'tp_id' => 'required',
            'teacher_id' => 'required',
        ]);

        $templateRelativePath = $request->template_path;

        // 1. Cek keberadaan file di storage disk 'local' / default
        if (!Storage::exists($templateRelativePath)) {
            // Cek cadangan jika path dikirim tanpa prefix 'public/'
            if (Storage::exists('public/templates/' . basename($templateRelativePath))) {
                $templateRelativePath = 'public/templates/' . basename($templateRelativePath);
            } else {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'File template (' . basename($templateRelativePath) . ') tidak ditemukan di storage server!'
                ], 404);
            }
        }

        // 2. Ambil Absolute Path yang valid
        $fullTemplatePath = Storage::path($templateRelativePath);

        // 3. Inisialisasi TemplateProcessor
        $templateProcessor = new TemplateProcessor($fullTemplatePath);
        $variables = $templateProcessor->getVariables();

        $tp = TpMaster::findOrFail($request->tp_id);
        $teacher = Teacher::findOrFail($request->teacher_id);
        
        $namaMapel = $tp->subject->name ?? $tp->subject_name ?? 'Teknik Ketenagalistrikan';

        $prompt = "Buatkan konten terstruktur dalam format JSON murni untuk variabel berikut: " . json_encode($variables) 
            . "\nBerdasarkan Mata Pelajaran: " . $namaMapel 
            . " dan TP: " . $tp->title;

        $systemInstruction = "Hasilkan HANYA string JSON valid tanpa markup/pembungkus markdown.";

        $aiResponse = $this->geminiService->generateContent($prompt, $systemInstruction);
        $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($aiResponse));
        $aiData = json_decode($cleanJson, true) ?? [];

        foreach ($variables as $variable) {
            if ($variable === 'nama_mapel') {
                $templateProcessor->setValue($variable, $namaMapel);
            } elseif ($variable === 'nama_guru') {
                $templateProcessor->setValue($variable, $teacher->name ?? '-');
            } elseif ($variable === 'tujuan_pembelajaran') {
                $templateProcessor->setValue($variable, $tp->title ?? '-');
            } else {
                $templateProcessor->setValue($variable, $aiData[$variable] ?? '-');
            }
        }

        $outputFilename = 'Generated_' . time() . '.docx';
        
        if (!Storage::exists('public/generated')) {
            Storage::makeDirectory('public/generated');
        }

        $outputPath = Storage::path('public/generated/' . $outputFilename);
        $templateProcessor->saveAs($outputPath);

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen berhasil di-generate!',
            'download_url' => route('dynamic-templates.download', ['filename' => $outputFilename])
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function downloadDocument($filename)
{
    // Cegah directory traversal security issue
    $cleanFilename = basename($filename);
    $filePath = storage_path('app/public/generated/' . $cleanFilename);

    if (!file_exists($filePath)) {
        abort(404, 'File tidak ditemukan.');
    }

    return response()->download($filePath, $cleanFilename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ]);
}

}