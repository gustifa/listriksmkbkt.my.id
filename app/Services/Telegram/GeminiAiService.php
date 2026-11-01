<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class GeminiAiService
{
    /**
     * Memanggil AI Teks dengan Auto-Discovery Endpoint & Multi-Provider Fallback
     */

    /**
     * Memanggil AI Teks secara langsung (Tanpa Enkripsi & Tanpa Format Markdown pada URL)
     */
    public function askAi(string $prompt, ?string $customSystemInstruction = null): string
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi.";
        }

        $systemInstruction = $customSystemInstruction ?? "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi. Jawab dengan ramah, singkat, dan jelas.";

        foreach ($apiKeys as $keyItem) {
            try {
                $provider = strtolower(trim($keyItem->provider));
                // Ambil kunci murni tanpa dekripsi
                $pureKey  = $this->extractPureKey($keyItem->api_key);

                if (empty($pureKey)) continue;

                if ($provider === 'gemini') {
                    // Endpoint resmi langsung tanpa kurung siku / format markdown
                    $endpoints = [
                        "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$pureKey}",
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$pureKey}",
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$pureKey}",
                    ];

                    foreach ($endpoints as $url) {
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
                                Log::info("SUKSES Gemini Text [{$keyItem->label_name}]");
                                return trim($reply);
                            }
                        } else {
                            Log::warning("Gemini Try Failed [{$keyItem->label_name}] | Status: " . $response->status());
                        }
                    }
                } elseif ($provider === 'groq') {
                    $response = Http::withoutVerifying()
                        ->timeout(15)
                        ->withToken($pureKey)
                        ->post('https://api.groq.com/openai/v1/chat/completions', [
                            'model' => 'llama-3.3-70b-versatile',
                            'messages' => [
                                ['role' => 'system', 'content' => $systemInstruction],
                                ['role' => 'user', 'content' => $prompt]
                            ],
                            'temperature' => 0.2
                        ]);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content');
                        if (!empty($reply)) {
                            Log::info("SUKSES Groq Text [{$keyItem->label_name}]");
                            return trim($reply);
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error("Exception AI [{$keyItem->label_name}]: " . $e->getMessage());
            }
        }

        return "🤖 *Asisten AI SISFO*\n\nMaaf, seluruh layanan AI sedang mengalami kendala jaringan. Silakan coba beberapa saat lagi.";
    }

    /**
     * Helper Ekstraksi Key Murni (Plain-Text)
     */
    private function extractPureKey(string $rawKey): string
    {
        $clean = trim(urldecode((string)$rawKey));

        if (str_contains($clean, 'key=')) {
            $parts = explode('key=', $clean);
            $clean = end($parts);
        }

        // Buang seluruh karakter Markdown, kurung siku, dan spasi
        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $clean);

        if (preg_match('/(AIzaSy[a-zA-Z0-9_\-\.]{30,80}|AQ\.[a-zA-Z0-9_\-\.]{30,80}|AQAb[a-zA-Z0-9_\-\.]{30,80}|gsk_[a-zA-Z0-9_\-\.]{30,80})/', $clean, $matches)) {
            return $matches[1];
        }

        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);
    }

    /**
     * Memanggil AI Teks dengan Context Identitas Pengguna
     */
    public function askAiWithContext(string $prompt, ?\App\Models\TelegramSubscriber$subscriber = null): string
    {
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName =$daysMap[date('N')] ?? 'Senin';
        $currentDateStr =$todayName . ', ' . date('d F Y');

        $userDataContext = "Pengguna: Pengunjung Umum.\n";

        if ($subscriber) {
            if ($subscriber->teacher_id && $subscriber->teacher) {$userDataContext = "Identitas Pengguna: Guru bernama " . $subscriber->teacher->name . "\n";
            } elseif ($subscriber->student_id && $subscriber->student) {$userDataContext = "Identitas Pengguna: Siswa bernama " . $subscriber->student->name . " (Kelas " . object_get($subscriber->student, 'classroom.name', '-') . ")\n";
            }
        }

        $systemInstruction = "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi.\n" .
                             "Hari/Tanggal Server Saat Ini: {$currentDateStr}.\n" .
                             $userDataContext . "\n" .
                             "ATURAN MUTLAK:\n" .
                             "1. LANGSUNG BERIKAN JAWABAN AKHIR kepada pengguna. DILARANG MENAMPILKAN PROSES BERPIKIR ATAU DRAFT.\n" .
                             "2. Jawab secara singkat, jelas, dan ramah dalam Bahasa Indonesia.\n" .
                             "3. Jika ditanya tanggal/hari ini, jawablah berdasarkan Hari/Tanggal Server Saat Ini di atas.";

        return $this->askAi($prompt,$systemInstruction);
    }

    /**
     * Parsing Presensi Guru (OCR Vision) via Auto-Discovered Endpoints
     */
    public function parseAttendanceImage(string $imagePath): ?array
    {
        $fullPath = storage_path('app/public/' .$imagePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' .$imagePath);
        }

        if (!file_exists($fullPath)) {
            Log::warning("AI Vision: File gambar tidak ditemukan di path: {$fullPath}");
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

            // 1. VISION VIA GEMINI
            if ($provider === 'gemini') {
                $endpoints = $this->discoverGeminiEndpoints($pureKey);
                foreach ($endpoints as $url) {
                    try {
                        $response = Http::withoutVerifying()->timeout(25)->post($url, [
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
                            $data = $this->cleanAndParseJson($response->json('candidates.0.content.parts.0.text'));
                            if ($data) return $data;
                        }
                    } catch (\Exception $e) {}
                }
            }
            // 2. VISION VIA GROQ
            elseif ($provider === 'groq') {
                $groqVisionModels = ['llama-3.2-11b-vision-preview', 'llama-3.2-90b-vision-preview'];
                foreach ($groqVisionModels as $model) {
                    try {
                        $response = Http::withoutVerifying()->timeout(25)->withToken($pureKey)
                            ->post('[https://api.groq.com/openai/v1/chat/completions](https://api.groq.com/openai/v1/chat/completions)', [
                                'model' => $model,
                                'messages' => [
                                    [
                                        'role' => 'user',
                                        'content' => [
                                            ['type' => 'text', 'text' => $prompt],
                                            ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"]]
                                        ]
                                    ]
                                ],
                                'temperature' => 0.1
                            ]);

                        if ($response->successful()) {
                            $data = $this->cleanAndParseJson($response->json('choices.0.message.content'));
                            if ($data) return $data;
                        }
                    } catch (\Exception $e) {}
                }
            }
        }

        return null;
    }

    /**
     * 🔍 AUTO-DISCOVERY 1: Menangkap Seluruh Endpoint Gemini Aktif dari Google ModelService API
     */
    private function discoverGeminiEndpoints(string $apiKey): array
    {
        $cacheKey = 'gemini_discovered_endpoints_' . md5($apiKey);

        return Cache::remember($cacheKey, 7200, function () use ($apiKey) {
            $discoveredEndpoints = [];

            // Panggil API ModelService v1 & v1beta milik Google untuk ambil daftar model resmi
            $versions = ['v1', 'v1beta'];
            foreach ($versions as $ver) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(6)
                        ->get("[https://generativelanguage.googleapis.com/](https://generativelanguage.googleapis.com/){$ver}/models?key={$apiKey}");

                    if ($response->successful()) {
                        $models = $response->json('models', []);
                        foreach ($models as $m) {
                            $methods = $m['supportedGenerationMethods'] ?? [];
                            if (in_array('generateContent', $methods)) {
                                $modelName = $m['name']; // contoh: models/gemini-1.5-flash
                                $discoveredEndpoints[] = "[https://generativelanguage.googleapis.com/](https://generativelanguage.googleapis.com/){$ver}/{$modelName}:generateContent?key={$apiKey}";
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }

            // Fallback manual jika discovery API timeout/tersendat
            if (empty($discoveredEndpoints)) {
                $discoveredEndpoints = [
                    "[https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=){$apiKey}",
                    "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=){$apiKey}",
                    "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=){$apiKey}",
                    "[https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-exp:generateContent?key=](https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-exp:generateContent?key=){$apiKey}",
                ];
            }

            return array_unique($discoveredEndpoints);
        });
    }

    /**
     * 🔍 AUTO-DISCOVERY 2: Menangkap Seluruh Model Groq Aktif dari API Groq Models
     */
    private function discoverGroqModels(string $apiKey): array
    {
        $cacheKey = 'groq_discovered_models_' . md5($apiKey);

        return Cache::remember($cacheKey, 7200, function () use ($apiKey) {
            $discoveredModels = [];

            try {
                $response = Http::withoutVerifying()
                    ->timeout(6)
                    ->withToken($apiKey)
                    ->get('[https://api.groq.com/openai/v1/models](https://api.groq.com/openai/v1/models)');

                if ($response->successful()) {
                    $models = $response->json('data', []);
                    foreach ($models as $m) {
                        if (isset($m['id']) && !str_contains($m['id'], 'whisper')) {
                            $discoveredModels[] = $m['id'];
                        }
                    }
                }
            } catch (\Exception $e) {}

            if (empty($discoveredModels)) {
                $discoveredModels = [
                    'llama-3.1-8b-instant',
                    'llama-3.3-70b-versatile',
                    'mixtral-8x7b-32768',
                    'gemma2-9b-it'
                ];
            }

            return array_unique($discoveredModels);
        });
    }


    /**
     * Helper Pembersih JSON
     */
    private function cleanAndParseJson(?string $rawText): ?array
    {
        if (empty($rawText)) return null;
        $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
        $data = json_decode($cleanedJson, true);
        return is_array($data) ? $data : null;
    }
}