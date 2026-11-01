<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiAiService
{
    /**
     * Memanggil AI Teks dengan Auto-Discovery Endpoint Universal
     */
    public function askAi(string $prompt, ?string $customSystemInstruction = null): string
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi di Web Admin.";
        }

        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName = $daysMap[date('N')] ?? 'Senin';
        $currentDateStr = $todayName . ', ' . date('d F Y');

        $systemInstruction = $customSystemInstruction ?? 
            "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi.\n" .
            "Hari/Tanggal Server Saat Ini: {$currentDateStr}.\n\n" .
            "ATURAN MUTLAK:\n" .
            "1. Langsung berikan jawaban akhir yang ramah, sopan, dan singkat.\n" .
            "2. DILARANG KERAS menampilkan proses berpikir atau catatan internal.\n" .
            "3. Jawab dalam Bahasa Indonesia yang alami.";

        foreach ($apiKeys as $keyItem) {
            try {
                $provider = strtolower(trim($keyItem->provider));
                $pureKey  = $this->extractPureKey($keyItem->api_key);

                if (empty($pureKey)) continue;

                // ==========================================
                // 1. DYNAMIC DISCOVERY: GOOGLE GEMINI API
                // ==========================================
                if ($provider === 'gemini') {
                    $validEndpoints = $this->getOrDiscoverGeminiEndpoints($pureKey);

                    foreach ($validEndpoints as $url) {
                        $response = Http::withoutVerifying()
                            ->timeout(15)
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
                                    'temperature'     => 0.2,
                                ]
                            ]);

                        if ($response->successful()) {
                            $reply = $response->json('candidates.0.content.parts.0.text');
                            if (!empty($reply)) {
                                Log::info("SUKSES Gemini Text [{$keyItem->label_name}] | URL: {$url}");
                                return trim($reply);
                            }
                        }
                    }
                }

                // ==========================================
                // 2. DYNAMIC DISCOVERY: GROQ AI API
                // ==========================================
                elseif ($provider === 'groq') {
                    $validModels = $this->getOrDiscoverGroqModels($pureKey);

                    foreach ($validModels as $model) {
                        $response = Http::withoutVerifying()
                            ->timeout(15)
                            ->withToken($pureKey)
                            ->post('https://api.groq.com/openai/v1/chat/completions', [
                                'model' => $model,
                                'messages' => [
                                    ['role' => 'system', 'content' => $systemInstruction],
                                    ['role' => 'user', 'content' => $prompt]
                                ],
                                'temperature' => 0.2,
                                'max_tokens'  => 500,
                            ]);

                        if ($response->successful()) {
                            $reply = $response->json('choices.0.message.content');
                            if (!empty($reply)) {
                                Log::info("SUKSES Groq Text [{$keyItem->label_name}] | Model: {$model}");
                                return trim($reply);
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error("Exception AI [{$keyItem->label_name}]: " . $e->getMessage());
            }
        }

        return "🤖 *Asisten AI SISFO*\n\nMaaf, seluruh layanan AI sedang mengalami kendala/limit kuota. Silakan coba beberapa saat lagi.";
    }

    /**
     * 🔍 TAHAP 1: CARI ENDPOINT GEMINI RESMI YANG AKTIF DARI GOOGLE API
     */
    private function getOrDiscoverGeminiEndpoints(string $apiKey): array
    {
        $cacheKey = 'gemini_active_endpoints_' . md5($apiKey);

        return Cache::remember($cacheKey, 86400, function () use ($apiKey) { // Cache selama 24 jam
            $discovered = [];
            $apiVersions = ['v1beta', 'v1'];

            // Query langsung ke Google ModelService API untuk minta daftar model resmi
            foreach ($apiVersions as $ver) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(5)
                        ->get("https://generativelanguage.googleapis.com/{$ver}/models?key={$apiKey}");

                    if ($response->successful()) {
                        $models = $response->json('models', []);
                        foreach ($models as $m) {
                            $methods = $m['supportedGenerationMethods'] ?? [];
                            // Hanya ambil model yang mendukung teks 'generateContent'
                            if (in_array('generateContent', $methods)) {
                                $modelName = $m['name']; // Format: models/gemini-1.5-flash
                                $discovered[] = "https://generativelanguage.googleapis.com/{$ver}/{$modelName}:generateContent?key={$apiKey}";
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }

            // Fallback cadangan jika request discovery API gagal
            if (empty($discovered)) {
                $discovered = [
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}",
                    "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                ];
            }

            return array_unique($discovered);
        });
    }

    /**
     * 🔍 TAHAP 2: CARI MODEL GROQ RESMI YANG AKTIF DARI GROQ MODELS API
     */
    private function getOrDiscoverGroqModels(string $apiKey): array
    {
        $cacheKey = 'groq_active_models_' . md5($apiKey);

        return Cache::remember($cacheKey, 86400, function () use ($apiKey) {
            $discovered = [];

            try {
                $response = Http::withoutVerifying()
                    ->timeout(5)
                    ->withToken($apiKey)
                    ->get('https://api.groq.com/openai/v1/models');

                if ($response->successful()) {
                    $models = $response->json('data', []);
                    foreach ($models as $m) {
                        $id = $m['id'] ?? '';
                        // Ambil model text (abaikan audio whisper)
                        if (!empty($id) && !str_contains($id, 'whisper')) {
                            $discovered[] = $id;
                        }
                    }
                }
            } catch (\Exception $e) {}

            if (empty($discovered)) {
                $discovered = ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768'];
            }

            return array_unique($discovered);
        });
    }

    /**
     * Helper Ekstraksi Key Murni Plain-Text
     */
    private function extractPureKey(string $rawKey): string
    {
        $clean = trim(urldecode((string)$rawKey));

        if (str_contains($clean, 'key=')) {
            $parts = explode('key=', $clean);
            $clean = end($parts);
        }

        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $clean);

        if (preg_match('/(AIzaSy[a-zA-Z0-9_\-\.]{30,80}|AQ\.[a-zA-Z0-9_\-\.]{30,80}|AQAb[a-zA-Z0-9_\-\.]{30,80}|gsk_[a-zA-Z0-9_\-\.]{30,80})/', $clean, $matches)) {
            return $matches[1];
        }

        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);
    }

    /**
     * Parsing Presensi Guru (OCR Vision) dari Foto Rekap Presensi
     */
    public function parseAttendanceImage(string $imagePath): ?array
    {
        $fullPath = storage_path('app/public/' .$imagePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' .$imagePath);
        }

        if (!file_exists($fullPath)) {
            Log::error("AI Vision: File gambar tidak ditemukan di path: {$fullPath}");
            return null;
        }

        $base64Image = base64_encode(file_get_contents($fullPath));
        $mimeType    = mime_content_type($fullPath) ?: 'image/jpeg';

        $prompt = "Analisis foto rekap presensi ini secara teliti.\n" .
                  "Ekstrak data kehadiran siswa ke format JSON murni TANPA MARKDOWN / TANPA ```json.\n\n" .
                  "Format JSON:\n" .
                  "{\n" .
                  "  \"date\": \"YYYY-MM-DD\",\n" .
                  "  \"attendances\": [\n" .
                  "    {\"student_name\": \"Nama Lengkap Siswa\", \"nis\": \"12345\", \"status\": \"hadir/izin/sakit/alpha\"}\n" .
                  "  ]\n" .
                  "}";

        $apiKeys = ApiKey::where('is_active', true)->orderBy('priority', 'asc')->get();

        foreach ($apiKeys as $keyItem) {
            $provider = strtolower(trim($keyItem->provider));
            $pureKey  = $this->extractPureKey($keyItem->api_key);
            if (empty($pureKey)) continue;

            if ($provider === 'gemini') {
                // Endpoint Murni Tanpa Format Markdown/Kurung Siku
                $endpoints = [
                    "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=){$pureKey}",
                    "[https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=){$pureKey}",
                ];

                foreach ($endpoints as $url) {
                    try {
                        $response = Http::withoutVerifying()
                            ->timeout(20)
                            ->post($url, [
                                'contents' => [
                                    [
                                        'parts' => [
                                            ['text' => $prompt],
                                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]]
                                        ]
                                    ]
                                ],
                                'generationConfig' => ['temperature' => 0.1]
                            ]);

                        if ($response->successful()) {
                            $rawText = $response->json('candidates.0.content.parts.0.text');
                            $data = $this->cleanAndParseJson($rawText);
                            if ($data) {
                                Log::info("SUKSES Gemini Vision [{$keyItem->label_name}]");
                                return $data;
                            }
                        } else {
                            Log::warning("Gemini Vision Failed [{$keyItem->label_name}] | Status: " . $response->status());
                        }
                    } catch (\Exception $e) {
                        Log::error("Exception Gemini Vision [{$keyItem->label_name}]: " . $e->getMessage());
                    }
                }
            }
        }

        return null;
    }
}