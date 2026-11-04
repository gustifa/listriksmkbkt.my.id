<?php
namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiApiService
{
    /**
     * Ambil API Key Gemini aktif dari database api_keys
     */
    private function getActiveApiKey()
{
    // 1. Coba ambil dari database PostgreSQL terlebih dahulu
    $keyRecord = ApiKey::where('provider', 'gemini')
        ->where('is_active', true)
        ->orderBy('priority', 'asc')
        ->first();

    if ($keyRecord && !empty($keyRecord->api_key)) {
        return $keyRecord->api_key;
    }

    // 2. Fallback: Ambil dari file .env jika di database tidak ditemukan
    $envKey = env('GEMINI_API_KEY');
    if (!empty($envKey)) {
        return $envKey;
    }

    throw new Exception("Tidak ada API Key Gemini yang aktif di database api_keys maupun di file .env.");
}

    /**
     * Kirim prompt ke Gemini API
     */
    public function generateContent(string $userPrompt, string $systemInstruction = null)
    {
        $apiKey = $this->getActiveApiKey();

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userPrompt]
                    ]
                ]
            ]
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            throw new Exception("Gagal menghubungi Gemini API: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }
}