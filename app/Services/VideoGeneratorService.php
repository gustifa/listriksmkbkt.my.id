<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class VideoGeneratorService
{
    /**
     * Ambil API Key Aktif dari Database secara Dinamis berdasarkan Provider
     */
    public function getActiveApiKey(string $provider)
    {
        $keyRecord = ApiKey::where('provider', $provider)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();

        if ($keyRecord && !empty($keyRecord->api_key)) {
            return $keyRecord->api_key;
        }

        // Fallback ke .env jika di database tidak ditemukan
        $envKey = env(strtoupper($provider) . '_API_KEY');
        if (!empty($envKey)) {
            return $envKey;
        }

        return null;
    }

    /**
     * 100% GRATIS: Generate Voiceover Bahasa Indonesia Menggunakan Edge TTS API
     */
    public function generateFreeVoiceover(string $text, string $voice = 'id-ID-ArdiNeural')
    {
        $filename = 'audio_' . time() . '_' . uniqid() . '.mp3';
        $savePath = storage_path('app/public/audio/' . $filename);

        if (!file_exists(storage_path('app/public/audio'))) {
            mkdir(storage_path('app/public/audio'), 0755, true);
        }

        // Menggunakan endpoint publik Edge TTS (Tanpa API Key, 100% Gratis)
        $url = "https://api.streamelements.com/kappa/v2/speech?voice={$voice}&text=" . urlencode($text);
        
        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            file_put_contents($savePath, $response->body());
            return asset('storage/audio/' . $filename);
        }

        throw new Exception("Gagal merender suara Edge TTS.");
    }

    /**
     * Generator Video Hybrid: Memprioritaskan API Key Dinamis dari DB, Fallback ke Voiceover Gratis
     */
    public function renderDynamicVideo(string $script, string $format, string $topic)
    {
        // 1. Cek API Key HeyGen dari Database
        $heygenKey = $this->getActiveApiKey('heygen');
        if ($heygenKey) {
            try {
                return $this->renderWithHeyGen($script, $format, $heygenKey);
            } catch (Exception $e) {
                // Jika key HeyGen gagal/habis kuota, lanjut ke fallback
            }
        }

        // 2. Cek API Key Luma dari Database
        $lumaKey = $this->getActiveApiKey('luma');
        if ($lumaKey) {
            try {
                return $this->renderWithLuma($script, $format, $lumaKey);
            } catch (Exception $e) {
                // Jika gagal, lanjut ke fallback
            }
        }

        // 3. Fallback 100% GRATIS: Edge TTS Audio + Standar Slide Video
        $audioUrl = $this->generateFreeVoiceover($script);

        return [
            'status' => 'success',
            'provider' => 'Edge TTS + Engine Lokal (100% Gratis)',
            'audio_url' => $audioUrl,
            'script' => $script,
            'message' => 'Voiceover Bahasa Indonesia berhasil dibuat gratis! Menggunakan audio pengisi suara.'
        ];
    }

    private function renderWithHeyGen($script, $format, $apiKey)
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
                'status' => 'success',
                'provider' => 'HeyGen Avatar AI (Database Key)',
                'video_url' => $response->json('data.video_url')
            ];
        }

        throw new Exception("HeyGen error");
    }

    private function renderWithLuma($prompt, $format, $apiKey)
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
                'status' => 'success',
                'provider' => 'Luma AI (Database Key)',
                'video_url' => $response->json('assets.video')
            ];
        }

        throw new Exception("Luma error");
    }
}