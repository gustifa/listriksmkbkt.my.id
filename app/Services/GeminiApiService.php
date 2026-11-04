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
     * Fetch daftar model resmi Gemini (Filter khusus seri 'gemini-' yang mendukung generateContent)
     */
    public function getAvailableModels($apiKey = null)
    {
        $apiKey = $apiKey ?? $this->getActiveApiKey();

        return Cache::remember('gemini_available_models_v3_' . md5($apiKey), now()->addHours(12), function () use ($apiKey) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            $response = Http::timeout(15)->get($url);

            if ($response->successful()) {$data = $response->json('models') ?? [];$validModels = array_filter($data, function ($model) {
                    $name =$model['name'] ?? '';
                    $methods =$model['supportedGenerationMethods'] ?? [];
                    
                    return in_array('generateContent', $methods) && 
                           str_contains($name, 'gemini') && 
                           !str_contains($name, 'gemma');
                });

                return array_map(function ($model) {
                    return str_replace('models/', '', $model['name']);
                }, array_values($validModels));
            }

            return ['gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-2.0-flash-exp'];
        });
    }

    /**
     * Menentukan model terbaik (Memprioritaskan 1.5-pro / 2.0-flash untuk penalaran kurikulum mendalam)
     */
    public function getBestModel($apiKey = null)
    {
        $models = $this->getAvailableModels($apiKey);

        // Prioritas 1: Gemini 1.5 Pro (Sangat cerdas untuk menyusun dokumen panjang)
        foreach ($models as$m) {
            if (str_contains($m, '1.5-pro')) return$m;
        }

        // Prioritas 2: Gemini 2.0 / 1.5 Flash
        foreach ($models as$m) {
            if (str_contains($m, '2.0-flash') || str_contains($m, '1.5-flash')) return$m;
        }

        return $models[0] ?? 'gemini-1.5-flash';
    }

    /**
     * Kirim prompt ke Gemini API dengan Konfigurasi Parameter AI Pintar & Fallback Loop
     */
    public function generateContent(string $userPrompt, string$systemInstruction = null)
    {
        $apiKey = $this->getActiveApiKey();$availableModels = $this->getAvailableModels($apiKey);

        $bestModel =$this->getBestModel($apiKey);$modelsToTry = array_unique(array_merge([$bestModel],$availableModels));

        // Setup Payload dengan Parameter Pintar (Temperature 0.3 untuk fokus dan presisi kurikulum)
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'topP' => 0.8,
                'maxOutputTokens' => 8192,
            ]
        ];

        if ($systemInstruction) {$payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        $lastExceptionMessage = '';

        foreach ($modelsToTry as $modelName) {$url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(180)
                    ->connectTimeout(15)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url,$payload);

                if ($response->successful()) {
                    $text =$response->json('candidates.0.content.parts.0.text');
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