<?php
namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class GeminiApiService
{
    /**
     * Ambil API Key Gemini aktif dari database api_keys
     */
    private function getActiveApiKey()
    {
        $keyRecord = ApiKey::where('provider', 'gemini')
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();

        if ($keyRecord && !empty($keyRecord->api_key)) {
            return $keyRecord->api_key;
        }

        $envKey = env('GEMINI_API_KEY');
        if (!empty($envKey)) {
            return $envKey;
        }

        throw new Exception("Tidak ada API Key Gemini yang aktif di database api_keys atau .env.");
    }

    /**
     * Fetch daftar seluruh model yang didukung oleh API Key dari Google
     * Hasil disimpan di Cache selama 12 jam agar performa tetap cepat.
     */
    public function getAvailableModels($apiKey = null)
    {
        $apiKey = $apiKey ?? $this->getActiveApiKey();

        return Cache::remember('gemini_available_models_' . md5($apiKey), now()->addHours(12), function () use ($apiKey) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json('models') ?? [];
                
                // Filter hanya model yang mendukung metode 'generateContent'
                $validModels = array_filter($data, function ($model) {
                    $methods = $model['supportedGenerationMethods'] ?? [];
                    return in_array('generateContent', $methods);
                });

                // Ekstrak nama model (misal: "models/gemini-1.5-flash" -> "gemini-1.5-flash")
                return array_map(function ($model) {
                    return str_replace('models/', '', $model['name']);
                }, array_values($validModels));
            }

            // Fallback default jika gagal fetching daftar model
            return ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash-exp'];
        });
    }

    /**
     * Menentukan model terbaik yang tersedia (Memprioritaskan varian flash)
     */
    public function getBestModel($apiKey = null)
    {
        $models = $this->getAvailableModels($apiKey);

        // Prioritas pencarian model
        foreach ($models as $model) {
            if (str_contains($model, '1.5-flash') || str_contains($model, '2.0-flash')) {
                return $model;
            }
        }

        // Jika tidak ada varian flash, ambil model pertama yang tersedia
        return $models[0] ?? 'gemini-1.5-flash';
    }

    /**
     * Kirim prompt ke Gemini API dengan fitur Auto-Match Model & Fallback Loop
     */
    public function generateContent(string $userPrompt, string $systemInstruction = null)
    {
        $apiKey = $this->getActiveApiKey();
        $availableModels = $this->getAvailableModels($apiKey);

        // Susun urutan model (prioritaskan model terbaik di paling depan)
        $bestModel = $this->getBestModel($apiKey);
        $modelsToTry = array_unique(array_merge([$bestModel], $availableModels));

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

        $lastExceptionMessage = '';

        // Looping mecoba tiap endpoint model sampai ada yang berhasil
        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (!empty($text)) {
                    return $text; // Sukses, kembalikan teks hasil AI
                }
            }

            $lastExceptionMessage = "Model {$modelName} gagal: " . $response->body();
        }

        throw new Exception("Seluruh model Gemini gagal merespon. Detail terakhir: " . $lastExceptionMessage);
    }
}