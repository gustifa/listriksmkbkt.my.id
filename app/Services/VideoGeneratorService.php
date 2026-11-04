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
     * 100% GRATIS: Generate Voiceover Bahasa Indonesia Menggunakan Edge TTS
     */
    public function generateFreeVoiceover(string $text, string $voice = 'id-ID-ArdiNeural')
    {
        $filename = 'audio_' . time() . '_' . uniqid() . '.mp3';
        $saveDir = storage_path('app/public/audio');

        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $savePath = $saveDir . '/' . $filename;

        // Endpoint Edge TTS Gratis tanpa API Key
        $url = "https://api.streamelements.com/kappa/v2/speech?voice={$voice}&text=" . urlencode($text);
        
        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            file_put_contents($savePath, $response->body());
            return asset('storage/audio/' . $filename);
        }

        throw new Exception("Gagal membuat suara Edge TTS.");
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