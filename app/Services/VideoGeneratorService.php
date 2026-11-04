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
            return $keyRecord->api_key;
        }

        return env(strtoupper($provider) . '_API_KEY', null);
    }

    /**
 * 100% GRATIS: Generate Voiceover Bahasa Indonesia dengan Multiple API Fallback
 */
public function generateFreeVoiceover(string $text)
{
    $filename = 'audio_' . time() . '_' . uniqid() . '.mp3';
    $saveDir = storage_path('app/public/audio');

    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0755, true);
    }

    $savePath = $saveDir . '/' . $filename;

    // Bersihkan teks dari markdown/karakter aneh dan batasi panjang untuk GET Request
    $cleanText = strip_tags($text);
    $cleanText = preg_replace('/[*_#`~]/', '', $cleanText);
    $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));
    $shortText = mb_substr($cleanText, 0, 400); // Ambil 400 karakter pertama agar request tidak error 414/403

    // PROVIDER 1: StreamElements (Edge TTS id-ID-ArdiNeural)
    try {
        $urlEdge = "https://api.streamelements.com/kappa/v2/speech?voice=id-ID-ArdiNeural&text=" . rawurlencode($shortText);
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ])->timeout(15)->get($urlEdge);

        if ($response->successful() && strlen($response->body()) > 100) {
            file_put_contents($savePath, $response->body());
            return asset('storage/audio/' . $filename);
        }
    } catch (Exception $e) {
        // Lanjut ke fallback provider 2 jika stream elements gagal
    }

    // PROVIDER 2 (FALLBACK): Google Translate TTS (gTTS Bahasa Indonesia)
    try {
        $urlGtts = "https://translate.google.com/translate_tts?ie=UTF-8&q=" . rawurlencode($shortText) . "&tl=id&client=tw-ob";
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ])->timeout(15)->get($urlGtts);

        if ($response->successful() && strlen($response->body()) > 100) {
            file_put_contents($savePath, $response->body());
            return asset('storage/audio/' . $filename);
        }
    } catch (Exception $e) {
        // Jika keduanya gagal, throw error
    }

    throw new Exception("Koneksi API TTS Gratis sedang dibatasi server. Silakan coba kembali atau gunakan API Key HeyGen/Luma.");
}

    /**
     * Core Method Public: Multi-Model Renderer (HeyGen -> Luma -> Fallback Gratis)
     */
    public function generateVideo(string $script, string $format = 'short', string $topic = '')
    {
        // 1. Cek API Key HeyGen
        $heygenKey = $this->getActiveApiKey('heygen');
        if ($heygenKey) {
            try {
                return $this->renderWithHeyGen($script, $format, $heygenKey);
            } catch (Exception $e) {
                // Fallback jika gagal/habis kuota
            }
        }

        // 2. Cek API Key Luma
        $lumaKey = $this->getActiveApiKey('luma');
        if ($lumaKey) {
            try {
                return $this->renderWithLuma($script, $format, $lumaKey);
            } catch (Exception $e) {
                // Fallback jika gagal
            }
        }

        // 3. Fallback 100% Gratis: Voiceover Audio Bahasa Indonesia via Edge TTS
        $audioUrl = $this->generateFreeVoiceover($script);

        return [
            'status' => 'completed',
            'provider' => 'Edge TTS Engine (100% Gratis)',
            'video_url' => null,
            'audio_url' => $audioUrl,
            'message' => 'Naskah & Voiceover Bahasa Indonesia berhasil dibuat gratis!'
        ];
    }

    /**
     * Render via HeyGen API
     */
    private function renderWithHeyGen(string $script, string $format, string $apiKey)
    {
        $dimension = ($format === 'short') ? ['width' => 720, 'height' => 1280] : ['width' => 1280, 'height' => 720];

        $response = Http::withHeaders([
            'X-Api-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(45)->post('https://api.heygen.com/v2/video/generate', [
            'video_inputs' => [[
                'character' => ['type' => 'avatar', 'avatar_id' => 'Daisy-Expressive-20240508'],
                'voice' => ['type' => 'text', 'input_text' => $script, 'voice_id' => 'id-ID-ArdiNeural']
            ]],
            'dimension' => $dimension
        ]);

        if ($response->successful()) {
            return [
                'status' => 'completed',
                'provider' => 'HeyGen AI (Database Key)',
                'video_url' => $response->json('data.video_url'),
                'audio_url' => null,
                'message' => 'Video Avatar AI berhasil di-render!'
            ];
        }

        throw new Exception("HeyGen API Error");
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
        ])->timeout(45)->post('https://api.lumalabs.ai/dream-machine/v1/generations', [
            'prompt' => $prompt,
            'aspect_ratio' => $aspectRatio,
        ]);

        if ($response->successful()) {
            return [
                'status' => 'completed',
                'provider' => 'Luma AI (Database Key)',
                'video_url' => $response->json('assets.video'),
                'audio_url' => null,
                'message' => 'Video animasi Luma AI berhasil di-render!'
            ];
        }

        throw new Exception("Luma API Error");
    }
}