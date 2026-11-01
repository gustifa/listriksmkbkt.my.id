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
     * Memanggil AI Teks dengan Multi-Provider Fallback (Gemini & Groq)
     */
    public function askAi(string $prompt, ?string $customSystemInstruction = null): string
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi. Silakan hubungi Admin untuk memasukkan API Key.";
        }

        // Tanggal & Hari Real-Time dari Server
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName =$daysMap[date('N')] ?? 'Senin';
        $currentDateStr =$todayName . ', ' . date('d F Y');

        $systemInstruction =$customSystemInstruction ?? 
            "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi.\n" .
            "Hari/Tanggal Server Saat Ini: {$currentDateStr}.\n\n" .
            "ATURAN MUTLAK:\n" .
            "1. Langsung berikan jawaban akhir yang ramah, sopan, dan singkat.\n" .
            "2. DILARANG KERAS menampilkan proses berpikir, draft, evaluasi, persona, atau catatan internal.\n" .
            "3. Jawab dalam Bahasa Indonesia yang alami.";

        foreach ($apiKeys as$keyItem) {
            try {
                $provider = strtolower(trim($keyItem->provider));$pureKey  = $this->extractPureKey($keyItem->api_key);

                if (empty($pureKey)) {
                    continue;
                }

                // 1. PROVIDER GOOGLE GEMINI
                if ($provider === 'gemini') {$endpoints = [
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$pureKey}",
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$pureKey}",
                    ];

                    foreach ($endpoints as $url) {$response = Http::withoutVerifying()
                            ->timeout(12)
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
                            $reply =$response->json('candidates.0.content.parts.0.text');
                            if (!empty($reply)) {
                                return trim($reply);
                            }
                        } else {
                            Log::warning("Gemini Text Gagal [{$keyItem->label_name}] | Status: " . $response->status() . " \vert{} Body: " . $response->body());
                        }
                    }
                }

                // 2. PROVIDER GROQ AI
                elseif ($provider === 'groq') {$groqModels = ['llama-3.1-8b-instant', 'llama-3.3-70b-versatile', 'mixtral-8x7b-32768'];

                    foreach ($groqModels as $model) {$response = Http::withoutVerifying()
                            ->timeout(12)
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
                            $reply =$response->json('choices.0.message.content');
                            if (!empty($reply)) {
                                return trim($reply);
                            }
                        } else {
                            Log::warning("Groq Text Gagal [{$keyItem->label_name} - {$model}] | Status: " . $response->status() . " \vert{} Body: " . $response->body());
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error("Exception panggil AI '{$keyItem->label_name}': " . $e->getMessage());
            }
        }

        return "🤖 *Asisten AI SISFO*\n\nMaaf, seluruh kuota harian layanan AI sedang mencapai batas maksimal. Silakan coba beberapa saat lagi.";
    }

    /**
     * Memanggil AI dengan Injeksikan Context Pengguna
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
     * Menganalisis Foto Rekap Presensi Menggunakan Gemini / Groq Vision (Multi-Provider Fallback)
     */
    public function parseAttendanceImage(string $imagePath): ?array
    {
        $fullPath = storage_path('app/public/' .$imagePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' .$imagePath);
        }

        if (!file_exists($fullPath)) {
            Log::warning("Gemini Vision: File gambar tidak ditemukan di path: {$fullPath}");
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

        // -----------------------------------------------------------
        // TAHAP 1: EKSEKUSI GEMINI VISION API
        // -----------------------------------------------------------
        $geminiKeys = ApiKey::where('is_active', true)
            ->where('provider', 'gemini')
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($geminiKeys as $keyItem) {
            try {
                $pureKey = $this->extractPureKey($keyItem->api_key);
                if (empty($pureKey)) continue;

                // URL Endpoint Murni tanpa Markdown
                $endpointUrl = "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=){$pureKey}";

                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpointUrl, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data'      => $base64Image
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => ['temperature' => 0.1]
                    ]);

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text');
                    $data = $this->cleanAndParseJson($rawText);
                    if ($data) {
                        Log::info("Gemini Vision SUKSES [{$keyItem->label_name}]");
                        return $data;
                    }
                } else {
                    Log::warning("Gemini Vision Gagal [{$keyItem->label_name}] | Status: " . $response->status() . " | Body: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Gemini Vision Exception [{$keyItem->label_name}]: " . $e->getMessage());
            }
        }

        // -----------------------------------------------------------
        // TAHAP 2: FALLBACK KE GROQ VISION JIKA GEMINI LIMIT/GAGAL
        // -----------------------------------------------------------
        Log::warning("Gemini Vision gagal/limit. Mencoba Fallback ke Groq Vision...");

        $groqKeys = ApiKey::where('is_active', true)
            ->where('provider', 'groq')
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($groqKeys as $keyItem) {
            try {
                $pureKey = $this->extractPureKey($keyItem->api_key);
                if (empty($pureKey)) continue;

                $groqModels = ['llama-3.2-11b-vision-preview', 'llama-3.2-90b-vision-preview'];

                foreach ($groqModels as $modelName) {
                    $response = Http::withoutVerifying()
                        ->timeout(25)
                        ->withHeaders([
                            'Authorization' => "Bearer {$pureKey}",
                            'Content-Type'  => 'application/json',
                        ])
                        ->post('[https://api.groq.com/openai/v1/chat/completions](https://api.groq.com/openai/v1/chat/completions)', [
                            'model' => $modelName,
                            'messages' => [
                                [
                                    'role' => 'user',
                                    'content' => [
                                        ['type' => 'text', 'text' => $prompt],
                                        [
                                            'type' => 'image_url',
                                            'image_url' => [
                                                'url' => "data:{$mimeType};base64,{$base64Image}"
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'temperature' => 0.1
                        ]);

                    if ($response->successful()) {
                        $rawText = $response->json('choices.0.message.content');
                        $data = $this->cleanAndParseJson($rawText);
                        if ($data) {
                            Log::info("Groq Vision SUKSES [{$keyItem->label_name} - {$modelName}]");
                            return $data;
                        }
                    } else {
                        Log::warning("Groq Vision Gagal [{$keyItem->label_name} - {$modelName}] | Status: " . $response->status() . " | Body: " . $response->body());
                    }
                }
            } catch (\Exception $e) {
                Log::error("Groq Vision Exception [{$keyItem->label_name}]: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Helper Terpusat untuk Ekstraksi API Key Murni
     */
    private function extractPureKey(string $rawKey): string
    {
        if (is_string($rawKey) && str_starts_with($rawKey, 'eyJ')) {
            try {
                $rawKey = Crypt::decryptString($rawKey);
            } catch (\Exception $e) {}
        }

        $rawKey = urldecode((string)$rawKey);

        if (preg_match('/(AIzaSy[a-zA-Z0-9_\-]{33}|AQAb[a-zA-Z0-9_\-]{30,60}|gsk_[a-zA-Z0-9_\-]{30,60})/', $rawKey, $matches)) {
            return $matches[1];
        }

        $cleanString = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $rawKey);
        if (str_contains($cleanString, 'key=')) {
            $parts = explode('key=', $cleanString);
            $cleanString = end($parts);
        }

        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $cleanString);
    }

    /**
     * Helper Pembersih Respon JSON dari AI
     */
    private function cleanAndParseJson(?string $rawText): ?array
    {
        if (empty($rawText)) return null;

        $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
        $data = json_decode($cleanedJson, true);

        return is_array($data) ? $data : null;
    }
}