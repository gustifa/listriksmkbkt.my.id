<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiApiService;
use App\Services\VideoGeneratorService;
use Illuminate\Support\Facades\DB;
use Exception;

class VideoGeneratorController extends Controller
{
    protected $geminiService;
    protected $videoService;

    public function __construct(GeminiApiService $geminiService, VideoGeneratorService $videoService)
    {
        $this->geminiService = $geminiService;
        $this->videoService = $videoService;
    }

    /**
     * Generate Video Pembelajaran (Short / Long)
     */
    public function generateVideo(Request $request)
    {
        $request->validate([
            'tp_id' => 'required',
            'video_format' => 'required|in:short,long',
        ]);

        try {
            // 1. Ambil Data TP dari tp_masters atau learning_objectives
            $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first() 
                ?? DB::table('learning_objectives')->where('id', $request->tp_id)->first();

            if (!$tp) {
                return response()->json(['status' => 'error', 'message' => 'Data TP tidak ditemukan.'], 404);
            }

            $judulTp = $tp->title ?? $tp->description ?? 'Praktikum Kelistrikan SMK';
            $format = $request->video_format;

            // 2. Susun Naskah Video Menggunakan Gemini AI
            $systemInstruction = "Anda adalah Scriptwriter Video Edukasi Vokasi SMK. Hasilkan HANYA naskah narasi pengisi suara murni tanpa instruksi kamera atau tanda baca berlebih.";

            if ($format === 'short') {
                $userPrompt = "Buatkan naskah video SHORT/REELS (Durasi 30-60 Detik) tentang topik praktikum: '{$judulTp}'. Struktur: Apersepsi singkat, 3 poin utama praktikum K3LH, dan motivasi penutup.";
            } else {
                $userPrompt = "Buatkan naskah video LONG/PEMBELAJARAN (Durasi 2-3 Menit) tentang topik praktikum: '{$judulTp}'. Struktur: Salam pembuka, Pengenalan alat & bahan, Langkah-langkah praktikum terperinci, dan Kesimpulan.";
            }

            $script = $this->geminiService->generateContent($userPrompt, $systemInstruction);

            // 3. Render Video / Voiceover via Service
            $result = $this->videoService->generateVideo($script, $format, $judulTp);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'provider' => $result['provider'],
                'video_url' => $result['video_url'],
                'audio_url' => $result['audio_url'],
                'script' => $script,
                'format' => $format
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal generate video: ' . $e->getMessage()
            ], 500);
        }
    }
}