<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiAiService
{
    /**
     * Memanggil AI dengan Auto-Discovery Endpoint & Multi-Provider Fallback
     */
    public function askAi(string $prompt): string
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi. Silakan hubungi Admin untuk memasukkan API Key.";
        }

        $systemInstruction = "Anda adalah Asisten Virtual cerdas & ramah untuk SISFO SMKN 1 Bukittinggi. " .
                             "Jawablah pertanyaan pengguna secara sopan, jelas, singkat, dan relevan dengan lingkungan sekolah/pendidikan.";

        foreach ($apiKeys as $keyItem) {
            try {
                $provider = strtolower(trim($keyItem->provider));
                $apiKey   = trim($keyItem->api_key);

                if (empty($apiKey)) {
                    continue;
                }

                // 1. PROVIDER GOOGLE GEMINI (DENGAN AUTO-DISCOVERY ENDPOINT)
                if ($provider === 'gemini') {
                    $availableEndpoints = $this->getValidGeminiEndpoints($apiKey);

                    foreach ($availableEndpoints as $url) {
                        $response = Http::withoutVerifying()
                            ->timeout(10)
                            ->withOptions([
                                'curl' => [
                                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                                    CURLOPT_SSL_VERIFYPEER => false,
                                ]
                            ])
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->post($url, [
                                'system_instruction' => [
                                    'parts' => [['text' => $systemInstruction]]
                                ],
                                'contents' => [
                                    ['parts' => [['text' => $prompt]]]
                                ],
                                'generationConfig' => [
                                    'maxOutputTokens' => 500,
                                    'temperature'     => 0.7,
                                ]
                            ]);

                        if ($response->successful()) {
                            $reply = $response->json('candidates.0.content.parts.0.text');
                            if (!empty($reply)) {
                                return trim($reply);
                            }
                        }
                    }

                    Log::warning("Gemini Key '{$keyItem->label_name}' tidak ada endpoint yang merespon.");
                }

                // 2. PROVIDER GROQ (DENGAN AUTO-DISCOVERY MODEL)
                elseif ($provider === 'groq') {
                    $availableModels = $this->getValidGroqModels($apiKey);

                    foreach ($availableModels as $model) {
                        $response = Http::withoutVerifying()
                            ->timeout(10)
                            ->withOptions([
                                'curl' => [
                                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                                ]
                            ])
                            ->withToken($apiKey)
                            ->post('https://api.groq.com/openai/v1/chat/completions', [
                                'model' => $model,
                                'messages' => [
                                    ['role' => 'system', 'content' => $systemInstruction],
                                    ['role' => 'user', 'content' => $prompt]
                                ],
                                'max_tokens' => 500,
                            ]);

                        if ($response->successful()) {
                            $reply = $response->json('choices.0.message.content');
                            if (!empty($reply)) {
                                return trim($reply);
                            }
                        }
                    }

                    Log::warning("Groq Key '{$keyItem->label_name}' tidak ada model yang merespon.");
                }

            } catch (\Exception $e) {
                Log::error("Exception panggil AI '{$keyItem->label_name}': " . $e->getMessage());
            }
        }

        return "🤖 *Asisten AI SISFO*\n\nMaaf, seluruh kuota harian layanan AI sedang mencapai batas maksimal. Silakan coba beberapa saat lagi.";
    }

    /**
     * Menangkap seluruh endpoint Gemini yang didukung oleh API Key
     */
    private function getValidGeminiEndpoints(string $apiKey): array
    {
        $cacheKey = 'gemini_endpoints_' . md5($apiKey);

        return Cache::remember($cacheKey, 3600, function () use ($apiKey) {
            $endpoints = [];
            
            // Panggil API ModelService Google untuk menangkap daftar model aktif
            $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            
            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                            CURLOPT_SSL_VERIFYPEER => false,
                        ]
                    ])
                    ->get($listUrl);

                if ($response->successful()) {
                    $models = $response->json('models', []);
                    foreach ($models as $modelData) {
                        $methods = $modelData['supportedGenerationMethods'] ?? [];
                        // Hanya ambil model yang mendukung generateContent
                        if (in_array('generateContent', $methods)) {
                            $name = $modelData['name']; // Format: models/gemini-xxx
                            $endpoints[] = "https://generativelanguage.googleapis.com/v1beta/{$name}:generateContent?key={$apiKey}";
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal mendapatkan daftar model Gemini: " . $e->getMessage());
            }

            // Fallback default jika API ListModels gagal/timeout di hosting
            if (empty($endpoints)) {
                $endpoints = [
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}",
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}",
                ];
            }

            return $endpoints;
        });
    }

    /**
     * Menangkap seluruh model Groq yang didukung oleh API Key
     */
    private function getValidGroqModels(string $apiKey): array
    {
        $cacheKey = 'groq_models_' . md5($apiKey);

        return Cache::remember($cacheKey, 3600, function () use ($apiKey) {
            $validModels = [];

            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        ]
                    ])
                    ->withToken($apiKey)
                    ->get('https://api.groq.com/openai/v1/models');

                if ($response->successful()) {
                    $models = $response->json('data', []);
                    foreach ($models as $m) {
                        if (isset($m['id'])) {
                            $validModels[] = $m['id'];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal mendapatkan daftar model Groq: " . $e->getMessage());
            }

            // Fallback default jika API ListModels Groq gagal
            if (empty($validModels)) {
                $validModels = [
                    'llama-3.3-70b-versatile',
                    'llama-3.1-8b-instant',
                    'mixtral-8x7b-32768',
                    'gemma2-9b-it',
                ];
            }

            return $validModels;
        });
    }
}