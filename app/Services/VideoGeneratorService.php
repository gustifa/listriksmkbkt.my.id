<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Exception;

class VideoGeneratorService
{
    /**
     * Ambil API Key Aktif dari Database api_keys secara Dinamis
     */
    private function getActiveApiKey(string $provider)
    {
        $keyRecord = ApiKey::where('provider', strtolower($provider))
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();

        if ($keyRecord && !empty($keyRecord->api_key)) {
            return trim($keyRecord->api_key);
        }

        return env(strtoupper($provider) . '_API_KEY', null);
    }

    /**
     * Core Method: Multi-Model Renderer (HeyGen -> Luma -> Fallback Gratis)
     */
    public function generateVideo(string $script, string $format = 'short', string $topic = '')
    {
        // 1. Cek API Key HeyGen
        $heygenKey = $this->getActiveApiKey('heygen');
        if ($heygenKey) {
            try {
                return $this->renderWithHeyGen($script, $format, $heygenKey);
            } catch (Exception $e) {
                // Log error jika HeyGen gagal dan lanjut ke fallback
                logger()->error("HeyGen Render Error: " . $e->getMessage());
            }
        }

        // 2. Cek API Key Luma
        $lumaKey = $this->getActiveApiKey('luma');
        if ($lumaKey) {
            try {
                return $this->renderWithLuma($script, $format, $lumaKey);
            } catch (Exception $e) {
                logger()->error("Luma Render Error: " . $e->getMessage());
            }
        }

        // 3. Fallback 100% Gratis: Voiceover Audio Bahasa Indonesia via Edge TTS / gTTS
        $audioUrl = $this->generateFreeVoiceover($script);

        return [
            'status' => 'completed',
            'provider' => 'Gemini AI Scriptwriter Engine (Free Fallback)',
            'video_url' => null,
            'audio_url' => $audioUrl,
            'message' => 'Naskah AI Scriptwriter berhasil disusun!'
        ];
    }

    /**
     * Render via HeyGen API v2 (Handling Asynchronous Job)
     */
    private function renderWithHeyGen(string $script, string $format, string $apiKey)
    {
        $dimension = ($format === 'short') 
            ? ['width' => 720, 'height' => 1280] 
            : ['width' => 1280, 'height' => 720];

        // Step 1: Submit Task Generate Video ke HeyGen
        $response = Http::withHeaders([
            'X-Api-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.heygen.com/v2/video/generate', [
            'video_inputs' => [[
                'character' => [
                    'type' => 'avatar', 
                    'avatar_id' => 'Daisy-Expressive-20240508',
                    'avatar_style' => 'normal'
                ],
                'voice' => [
                    'type' => 'text', 
                    'input_text' => mb_substr($script, 0, 1000), 
                    'voice_id' => '2d5b0e6cf1064e0a829143926bf4c6e9' // Voice ID Default / Bahasa Indonesia
                ]
            ]],
            'dimension' => $dimension
        ]);

        if (!$response->successful()) {
            throw new Exception("HeyGen Submit API Failed: " . $response->body());
        }

        $videoId = $response->json('data.video_id');
        if (!$videoId) {
            throw new Exception("HeyGen Video ID tidak ditemukan pada respon.");
        }

        // Step 2: Polling status hingga video selesai di-render (Maksimal 12 kali polling / ~24 detik)
        $videoUrl = null;
        for ($i = 0; $i < 12; $i++) {
            sleep(2);
            $statusResponse = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])->get("https://api.heygen.com/v1/video_status.get?video_id={$videoId}");

            if ($statusResponse->successful()) {
                $statusData = $statusResponse->json('data');
                if (isset($statusData['status']) && $statusData['status'] === 'completed') {
                    $videoUrl = $statusData['video_url'];
                    break;
                } elseif (isset($statusData['status']) && $statusData['status'] === 'failed') {
                    throw new Exception("HeyGen Rendering Failed: " . ($statusData['error'] ?? 'Unknown Error'));
                }
            }
        }

        if ($videoUrl) {
            return [
                'status' => 'completed',
                'provider' => 'HeyGen AI Cloud',
                'video_url' => $videoUrl,
                'audio_url' => null,
                'message' => 'Video MP4 Avatar AI berhasil di-render!'
            ];
        }

        throw new Exception("Proses render HeyGen membutuhkan waktu lebih lama. Video ID: " . $videoId);
    }

    /**
     * Render via Luma AI API
     */
    private function renderWithLuma(string $prompt, string $format, string $apiKey)
    {
        $aspectRatio = ($format === 'short') ? '9:16' : '16:9';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.lumalabs.ai/dream-machine/v1/generations', [
            'prompt' => mb_substr($prompt, 0, 500),
            'aspect_ratio' => $aspectRatio,
        ]);

        if ($response->successful()) {
            return [
                'status' => 'completed',
                'provider' => 'Luma AI Cloud',
                'video_url' => $response->json('assets.video'),
                'audio_url' => null,
                'message' => 'Video animasi MP4 Luma AI berhasil di-render!'
            ];
        }

        throw new Exception("Luma API Error: " . $response->body());
    }

    /**
     * Fallback Free Voiceover
     */
    public function generateFreeVoiceover(string $text)
    {
        $filename = 'audio_' . time() . '_' . uniqid() . '.mp3';
        $saveDir = storage_path('app/public/audio');

        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $savePath = $saveDir . '/' . $filename;
        $cleanText = strip_tags($text);
        $cleanText = preg_replace('/[*_#`~]/', '', $cleanText);
        $shortText = mb_substr(trim(preg_replace('/\s+/', ' ', $cleanText)), 0, 300);

        try {
            $urlEdge = "https://api.streamelements.com/kappa/v2/speech?voice=id-ID-ArdiNeural&text=" . rawurlencode($shortText);
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(8)->get($urlEdge);

            if ($response->successful() && strlen($response->body()) > 100) {
                file_put_contents($savePath, $response->body());
                return asset('storage/audio/' . $filename);
            }
        } catch (\Exception $e) {}

        return null;
    }
}