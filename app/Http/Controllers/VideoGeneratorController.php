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
    // public function generateVideo(Request $request)
    // {
    //     $request->validate([
    //         'tp_id' => 'required',
    //         'video_format' => 'required|in:short,long',
    //     ]);

    //     try {
    //         // 1. Ambil Data TP dari tp_masters atau learning_objectives
    //         $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first() 
    //             ?? DB::table('learning_objectives')->where('id', $request->tp_id)->first();

    //         if (!$tp) {
    //             return response()->json(['status' => 'error', 'message' => 'Data TP tidak ditemukan.'], 404);
    //         }

    //         $judulTp = $tp->title ?? $tp->description ?? 'Praktikum Kelistrikan SMK';
    //         $format = $request->video_format;

    //         // 2. Susun Naskah Video Menggunakan Gemini AI
    //         $systemInstruction = "Anda adalah Scriptwriter Video Edukasi Vokasi SMK. Hasilkan HANYA naskah narasi pengisi suara murni tanpa instruksi kamera atau tanda baca berlebih.";

    //         if ($format === 'short') {
    //             $userPrompt = "Buatkan naskah video SHORT/REELS (Durasi 30-60 Detik) tentang topik praktikum: '{$judulTp}'. Struktur: Apersepsi singkat, 3 poin utama praktikum K3LH, dan motivasi penutup.";
    //         } else {
    //             $userPrompt = "Buatkan naskah video LONG/PEMBELAJARAN (Durasi 2-3 Menit) tentang topik praktikum: '{$judulTp}'. Struktur: Salam pembuka, Pengenalan alat & bahan, Langkah-langkah praktikum terperinci, dan Kesimpulan.";
    //         }

    //         $script = $this->geminiService->generateContent($userPrompt, $systemInstruction);

    //         // 3. Render Video / Voiceover via Service
    //         $result = $this->videoService->generateVideo($script, $format, $judulTp);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => $result['message'],
    //             'provider' => $result['provider'],
    //             'video_url' => $result['video_url'],
    //             'audio_url' => $result['audio_url'],
    //             'script' => $script,
    //             'format' => $format
    //         ], 200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal generate video: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

//     public function generateVideo(Request $request)
// {
//     $request->validate([
//         'tp_id'        => 'required',
//         'video_format' => 'required|in:short,long',
//         'part_count'   => 'nullable|string'
//     ]);

//     try {
//         $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first() 
//             ?? DB::table('learning_objectives')->where('id', $request->tp_id)->first();

//         if (!$tp) {
//             return response()->json(['status' => 'error', 'message' => 'Data TP tidak ditemukan.'], 404);
//         }

//         $judulTp = $tp->title ?? $tp->description ?? 'Praktikum Kelistrikan SMK';
//         $format = $request->video_format;
//         $partCount = $request->part_count ?? 'auto';

//         // Tentukan jumlah part instruksi ke Gemini
//         $partInstruction = "";
//         if ($partCount === '2') {
//             $partInstruction = "Bagi naskah TEPAT menjadi 2 Part (Part 1: Pembukaan & K3LH, Part 2: Langkah Kerja & Penutup).";
//         } elseif ($partCount === '3') {
//             $partInstruction = "Bagi naskah TEPAT menjadi 3 Part (Part 1: Pembukaan/K3LH, Part 2: Langkah Kerja Utama, Part 3: Penutup/Kesimpulan).";
//         } elseif ($partCount === '4') {
//             $partInstruction = "Bagi naskah TEPAT menjadi 4 Part (Part 1: Salam & Apersepsi, Part 2: Peralatan & K3LH, Part 3: Langkah Kerja, Part 4: Kesimpulan).";
//         } else {
//             $partInstruction = ($format === 'short') 
//                 ? "Bagi naskah secara otomatis menjadi 2 atau 3 Part yang seimbang." 
//                 : "Bagi naskah secara otomatis menjadi 3 atau 4 Part yang terstruktur.";
//         }

//         $systemInstruction = "Anda adalah Scriptwriter Video Edukasi Vokasi SMK. Hasilkan HANYA respon berformat JSON valid dengan struktur array 'parts'. Tanpa teks tambahan di luar JSON.";

//         $userPrompt = "Buatkan naskah video {$format} tentang topik: '{$judulTp}'. {$partInstruction}
//         Format JSON wajib:
//         {
//             \"parts\": [
//                 {\"part_name\": \"Nama Part\", \"content\": \"...narasi pengisi suara...\"}
//             ]
//         }";

//         $rawResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
        
//         // Clean and Decode JSON
//         $cleanJson = preg_replace('/```json|```/', '', $rawResponse);
//         $scriptData = json_decode(trim($cleanJson), true);

//         if (!isset($scriptData['parts'])) {
//             $scriptData = [
//                 'parts' => [
//                     ['part_name' => 'Naskah Utama', 'content' => $rawResponse]
//                 ]
//             ];
//         }

//         $fullScript = implode("\n\n", array_column($scriptData['parts'], 'content'));

//         // Render via Service
//         $result = $this->videoService->generateVideo($fullScript, $format, $judulTp);

//         return response()->json([
//             'status'    => 'success',
//             'message'   => $result['message'],
//             'provider'  => $result['provider'],
//             'video_url' => $result['video_url'],
//             'audio_url' => $result['audio_url'],
//             'parts'     => $scriptData['parts'],
//             'format'    => $format
//         ], 200);

//     } catch (Exception $e) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Gagal generate video: ' . $e->getMessage()
//         ], 500);
//     }
// }

public function generateVideo(Request $request)
{
    $request->validate([
        'video_format' => 'required|in:short,long',
        'tp_mode'      => 'nullable|in:database,manual',
        'part_count'   => 'nullable|string'
    ]);

    try {
        $judulTp = "";

        // Penanganan Mode TP (Manual vs Database)
        if ($request->tp_mode === 'manual' || $request->filled('tp_manual')) {
            $judulTp = trim($request->tp_manual);
        } else {
            $tp = DB::table('tp_masters')->where('id', $request->tp_id)->first() 
                ?? DB::table('learning_objectives')->where('id', $request->tp_id)->first();

            if (!$tp) {
                return response()->json(['status' => 'error', 'message' => 'Data TP tidak ditemukan di database.'], 404);
            }
            $judulTp = $tp->title ?? $tp->description ?? 'Praktikum Kelistrikan SMK';
        }

        if (empty($judulTp)) {
            return response()->json(['status' => 'error', 'message' => 'Tujuan Pembelajaran (TP) tidak boleh kosong.'], 400);
        }

        $format = $request->video_format;
        $partCount = $request->part_count ?? 'auto';

        // Tentukan instruksi pembagian Part
        $partInstruction = "";
        if ($partCount === '2') {
            $partInstruction = "Bagi naskah TEPAT menjadi 2 Part (Part 1: Pembukaan & K3LH, Part 2: Langkah Kerja & Penutup).";
        } elseif ($partCount === '3') {
            $partInstruction = "Bagi naskah TEPAT menjadi 3 Part (Part 1: Pembukaan/K3LH, Part 2: Langkah Kerja Utama, Part 3: Penutup/Kesimpulan).";
        } elseif ($partCount === '4') {
            $partInstruction = "Bagi naskah TEPAT menjadi 4 Part (Part 1: Salam & Apersepsi, Part 2: Peralatan & K3LH, Part 3: Langkah Kerja, Part 4: Kesimpulan).";
        } else {
            $partInstruction = ($format === 'short') 
                ? "Bagi naskah secara otomatis menjadi 2 atau 3 Part yang seimbang." 
                : "Bagi naskah secara otomatis menjadi 3 atau 4 Part yang terstruktur.";
        }

        $systemInstruction = "Anda adalah Scriptwriter Video Edukasi Vokasi SMK. Hasilkan HANYA respon berformat JSON valid dengan struktur array 'parts'. Tanpa teks tambahan di luar JSON.";

        $userPrompt = "Buatkan naskah video {$format} tentang topik/TP: '{$judulTp}'. {$partInstruction}
        Format JSON wajib:
        {
            \"parts\": [
                {\"part_name\": \"Nama Part\", \"content\": \"...narasi pengisi suara...\"}
            ]
        }";

        $rawResponse = $this->geminiService->generateContent($userPrompt, $systemInstruction);
        
        $cleanJson = preg_replace('/```json|```/', '', $rawResponse);
        $scriptData = json_decode(trim($cleanJson), true);

        if (!isset($scriptData['parts'])) {
            $scriptData = [
                'parts' => [
                    ['part_name' => 'Naskah Utama', 'content' => $rawResponse]
                ]
            ];
        }

        $fullScript = implode("\n\n", array_column($scriptData['parts'], 'content'));
        $result = $this->videoService->generateVideo($fullScript, $format, $judulTp);

        return response()->json([
            'status'    => 'success',
            'message'   => $result['message'],
            'provider'  => $result['provider'],
            'video_url' => $result['video_url'],
            'audio_url' => $result['audio_url'],
            'parts'     => $scriptData['parts'],
            'format'    => $format
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal generate video: ' . $e->getMessage()
        ], 500);
    }
}
}