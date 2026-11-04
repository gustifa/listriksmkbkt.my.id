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
     * Fetch daftar model resmi Gemini (Filter khusus seri 'gemini-' yang cepat)
     */
    public function getAvailableModels($apiKey = null)
    {
        $apiKey = $apiKey ?? $this->getActiveApiKey();

        return Cache::remember('gemini_available_models_v2_' . md5($apiKey), now()->addHours(12), function () use ($apiKey) {
            // Tambahkan timeout 15 detik untuk fetching daftar model
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            $response = Http::timeout(15)->get($url);

            if ($response->successful()) {
                $data = $response->json('models') ?? [];
                
                // Filter HANYA model Gemini yang mendukung 'generateContent' dan hindari model Gemma/Vision berat
                $validModels = array_filter($data, function ($model) {
                    $name = $model['name'] ?? '';
                    $methods = $model['supportedGenerationMethods'] ?? [];
                    
                    return in_array('generateContent', $methods) && 
                           str_contains($name, 'gemini') && 
                           !str_contains($name, 'gemma'); // Abaikan Gemma agar tidak timeout
                });

                return array_map(function ($model) {
                    return str_replace('models/', '', $model['name']);
                }, array_values($validModels));
            }

            return ['gemini-2.5-flash', 'gemini-1.5-flash', 'gemini-1.5-pro'];
        });
    }

    /**
     * Menentukan model tercepat (Memprioritaskan 2.5-flash & 1.5-flash)
     */
    public function getBestModel($apiKey = null)
    {
        $models = $this->getAvailableModels($apiKey);

        // Prioritas 1: Gemini 2.5 Flash
        foreach ($models as $m) {
            if (str_contains($m, '2.5-flash')) return $m;
        }

        // Prioritas 2: Gemini 1.5 Flash
        foreach ($models as $m) {
            if (str_contains($m, '1.5-flash')) return $m;
        }

        return $models[0] ?? 'gemini-1.5-flash';
    }

    /**
     * Kirim prompt ke Gemini API dengan Timeout 120 detik & Fallback Loop
     */
    public function generateContent(string $userPrompt, string $systemInstruction = null)
    {
        $apiKey = $this->getActiveApiKey();
        $availableModels = $this->getAvailableModels($apiKey);

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

        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

            try {
                // KUNCI UTAMA: Tambahkan timeout(120) agar Laravel menunggu hingga 2 menit
                $response = Http::timeout(120)
                    ->connectTimeout(15)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    if (!empty($text)) {
                        return $text;
                    }
                }

                $lastExceptionMessage = "Model {$modelName} HTTP Error: " . $response->body();
            } catch (Exception $e) {
                $lastExceptionMessage = "Model {$modelName} Catch Error: " . $e->getMessage();
            }
        }

        throw new Exception("Seluruh model Gemini gagal merespon. Detail: " . $lastExceptionMessage);
    }
}