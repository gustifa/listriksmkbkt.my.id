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
    public function askAi(string $prompt, ?string $customSystemInstruction = null): string
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi. Silakan hubungi Admin untuk memasukkan API Key.";
        }

        // Tanggal & Hari Real-Time dari Server Laravel
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName = $daysMap[date('N')] ?? 'Senin';
        $currentDateStr = $todayName . ', ' . date('d F Y');

        // System Instruction Default yang Ketat
        $systemInstruction = $customSystemInstruction ?? 
            "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi.\n" .
            "Hari/Tanggal Server Saat Ini: {$currentDateStr}.\n\n" .
            "ATURAN MUTLAK:\n" .
            "1. Langsung berikan jawaban akhir yang ramah, sopan, dan singkat.\n" .
            "2. DILARANG KERAS menampilkan proses berpikir, draft, evaluasi, persona, atau catatan internal dalam bentuk apa pun.\n" .
            "3. Jawab dalam Bahasa Indonesia yang alami.";

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
                                    'maxOutputTokens' => 300,
                                    'temperature'     => 0.2,
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
                                'temperature' => 0.2,
                                'max_tokens'  => 300,
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
     * Memanggil AI dengan Injeksikan Basic Context
     */
    public function askAiWithContext(string $prompt, ?\App\Models\TelegramSubscriber $subscriber = null): string
    {
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName = $daysMap[date('N')] ?? 'Senin';
        $currentDateStr = $todayName . ', ' . date('d F Y');

        $userDataContext = "Pengguna: Pengunjung Umum.\n";

        if ($subscriber) {
            if ($subscriber->teacher_id && $subscriber->teacher) {
                $userDataContext = "Identitas Pengguna: Guru bernama " . $subscriber->teacher->name . "\n";
            } elseif ($subscriber->student_id && $subscriber->student) {
                $userDataContext = "Identitas Pengguna: Siswa bernama " . $subscriber->student->name . " (Kelas " . object_get($subscriber->student, 'classroom.name', '-') . ")\n";
            }
        }

        $systemInstruction = "Anda adalah Asisten Virtual Resmi SISFO SMKN 1 Bukittinggi.\n" .
                             "Hari/Tanggal Server Saat Ini: {$currentDateStr}.\n" .
                             $userDataContext . "\n" .
                             "ATURAN MUTLAK:\n" .
                             "1. LANGSUNG BERIKAN JAWABAN AKHIR kepada pengguna. DILARANG MENAMPILKAN PROSES BERPIKIR, DRAFT, ATAU EVALUASI PERSONA.\n" .
                             "2. Jawab secara singkat, jelas, dan ramah dalam Bahasa Indonesia.\n" .
                             "3. Jika ditanya tanggal/hari ini, jawablah berdasarkan Hari/Tanggal Server Saat Ini di atas.";

        return $this->askAi($prompt, $systemInstruction);
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
                    'llama-3.1-8b-instant',
                    'llama-3.3-70b-versatile',
                    'mixtral-8x7b-32768',
                    'gemma2-9b-it',
                ];
            }

            return $validModels;
        });
    }

    /**
     * Menganalisis Foto Rekap Presensi menggunakan Gemini Vision API
     */
    // public function parseAttendanceImage(string $imagePath): ?array
    // {
    //     $apiKeys = ApiKey::where('is_active', true)
    //         ->where('provider', 'gemini')
    //         ->orderBy('priority', 'asc')
    //         ->get();

    //     if ($apiKeys->isEmpty()) {
    //         return null;
    //     }

    //     $fullPath = storage_path('app/public/' .$photoPath);
    //     if (!file_exists($fullPath)) {
    //         $fullPath = storage_path('app/' .$imagePath);
    //     }

    //     if (!file_exists($fullPath)) {
    //         return null;
    //     }

    //     $base64Image = base64_encode(file_get_contents($fullPath));
    //     $mimeType    = mime_content_type($fullPath) ?: 'image/jpeg';

    //     $prompt = "Analisis foto rekap kehadiran siswa ini. Ekstrak data kehadiran ke dalam format JSON murni TANPA MARKDOWN (tanpa ```json).\n" .
    //               "Format JSON yang wajib dikembalikan:\n" .
    //               "{\n" .
    //               "  \"date\": \"YYYY-MM-DD\", // tanggal pada dokumen, jika tidak ada gunakan null\n" .
    //               "  \"attendances\": [\n" .
    //               "    {\"student_name\": \"Nama Siswa 1\", \"status\": \"HADIR/IZIN/SAKIT/ALPHA\"},\n" .
    //               "    {\"student_name\": \"Nama Siswa 2\", \"status\": \"HADIR/IZIN/SAKIT/ALPHA\"}\n" .
    //               "  ]\n" .
    //               "}";

    //     foreach ($apiKeys as $keyItem) {
    //         try {
    //             $apiKey = trim($keyItem->api_key);
    //             $url = "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=){$apiKey}";

    //             $response = Http::withoutVerifying()
    //                 ->timeout(15)
    //                 ->withHeaders(['Content-Type' => 'application/json'])
    //                 ->post($url, [
    //                     'contents' => [
    //                         [
    //                             'parts' => [
    //                                 ['text' => $prompt],
    //                                 [
    //                                     'inline_data' => [
    //                                         'mime_type' => $mimeType,
    //                                         'data'      => $base64Image
    //                                     ]
    //                                 ]
    //                             ]
    //                         ]
    //                     ],
    //                     'generationConfig' => [
    //                         'temperature' => 0.1, // Respons sangat ketat & terstruktur
    //                     ]
    //                 ]);

    //             if ($response->successful()) {
    //                 $rawText = $response->json('candidates.0.content.parts.0.text');
    //                 $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
    //                 $data = json_decode($cleanedJson, true);

    //                 if (is_array($data)) {
    //                     return $data;
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("Gemini Vision Parsing Exception: " . $e->getMessage());
    //         }
    //     }

    //     return null;
    // }
    /**
     * Menganalisis Foto Rekap Presensi menggunakan Gemini Vision API
     */
    // public function parseAttendanceImage(string $imagePath): ?array
    // {
    //     $apiKeys = ApiKey::where('is_active', true)
    //         ->where('provider', 'gemini')
    //         ->orderBy('priority', 'asc')
    //         ->get();

    //     if ($apiKeys->isEmpty()) {
    //         return null;
    //     }

    //     // PERBAIKAN: Gunakan $imagePath secara konsisten
    //     $fullPath = storage_path('app/public/' .$imagePath);
    //     if (!file_exists($fullPath)) {
    //         $fullPath = storage_path('app/' .$imagePath);
    //     }

    //     if (!file_exists($fullPath)) {
    //         Log::warning("Gemini Vision: File gambar tidak ditemukan di path: {$fullPath}");
    //         return null;
    //     }

    //     $base64Image = base64_encode(file_get_contents($fullPath));
    //     $mimeType    = mime_content_type($fullPath) ?: 'image/jpeg';

    //     $prompt = "Analisis foto rekap kehadiran siswa ini. Ekstrak data kehadiran ke dalam format JSON murni TANPA MARKDOWN (tanpa ```json).\n" .
    //               "Format JSON yang wajib dikembalikan:\n" .
    //               "{\n" .
    //               "  \"date\": \"YYYY-MM-DD\", // tanggal pada dokumen, jika tidak ada gunakan null\n" .
    //               "  \"attendances\": [\n" .
    //               "    {\"student_name\": \"Nama Siswa 1\", \"status\": \"HADIR/IZIN/SAKIT/ALPHA\"},\n" .
    //               "    {\"student_name\": \"Nama Siswa 2\", \"status\": \"HADIR/IZIN/SAKIT/ALPHA\"}\n" .
    //               "  ]\n" .
    //               "}";

    //     foreach ($apiKeys as $keyItem) {
    //         try {
    //             $apiKey = trim($keyItem->api_key);
    //             $url = "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=){$apiKey}";

    //             $response = Http::withoutVerifying()
    //                 ->timeout(15)
    //                 ->withHeaders(['Content-Type' => 'application/json'])
    //                 ->post($url, [
    //                     'contents' => [
    //                         [
    //                             'parts' => [
    //                                 ['text' => $prompt],
    //                                 [
    //                                     'inline_data' => [
    //                                         'mime_type' => $mimeType,
    //                                         'data'      => $base64Image
    //                                     ]
    //                                 ]
    //                             ]
    //                         ]
    //                     ],
    //                     'generationConfig' => [
    //                         'temperature' => 0.1,
    //                     ]
    //                 ]);

    //             if ($response->successful()) {
    //                 $rawText = $response->json('candidates.0.content.parts.0.text');
    //                 $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
    //                 $data = json_decode($cleanedJson, true);

    //                 if (is_array($data)) {
    //                     return $data;
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("Gemini Vision Parsing Exception: " . $e->getMessage());
    //         }
    //     }

    //     return null;
    // }

    /**
     * Menganalisis Foto Rekap Presensi menggunakan Gemini Vision API (Hardened Key Isolation)
     */
    public function parseAttendanceImage(string $imagePath): ?array
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->where('provider', 'gemini')
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            Log::warning("Gemini Vision: Tidak ada API Key aktif untuk provider 'gemini'.");
            return null;
        }

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

        $prompt = "Analisis foto rekap kehadiran siswa ini secara teliti.\n" .
                  "Ekstrak data kehadiran ke dalam format JSON murni TANPA MARKDOWN / TANPA TANDA BINTANG / TANPA ```json.\n\n" .
                  "Aturan JSON:\n" .
                  "{\n" .
                  "  \"date\": \"YYYY-MM-DD\", // tanggal pada dokumen/foto, jika tidak ada gunakan null\n" .
                  "  \"attendances\": [\n" .
                  "    {\"student_name\": \"Nama Lengkap Siswa\", \"nis\": \"12345\", \"status\": \"hadir/izin/sakit/alpha\"}\n" .
                  "  ]\n" .
                  "}";

        foreach ($apiKeys as $keyItem) {
            try {
                // 1. Ambil data mentah dari DB
                $rawKey = $keyItem->api_key;

                // 2. Dekripsi jika berupa encrypted string Laravel (eyJ...)
                if (is_string($rawKey) && str_starts_with($rawKey, 'eyJ')) {
                    try {
                        $rawKey = \Illuminate\Support\Facades\Crypt::decryptString($rawKey);
                    } catch (\Exception $eCrypt) {
                        Log::warning("Gemini Vision: Gagal dekripsi key '{$keyItem->label_name}': " . $eCrypt->getMessage());
                    }
                }

                $rawKey = urldecode((string)$rawKey);

                // 🌟 3. EKSTRAKSI MANDIRI KETAT: Isolasi string Kunci API murni saja
                if (preg_match('/(AIzaSy[a-zA-Z0-9_\-]{33}|AQAb[a-zA-Z0-9_\-]{30,60})/', $rawKey, $matches)) {
                    $apiKey = $matches[1];
                } else {
                    // Fallback pembersihan jika karakter depan bukan AIzaSy/AQAb
                    $cleanString = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $rawKey);
                    if (str_contains($cleanString, 'key=')) {
                        $parts = explode('key=', $cleanString);
                        $cleanString = end($parts);
                    }
                    if (str_contains($cleanString, 'http')) {
                        $slashParts = explode('/', $cleanString);
                        $cleanString = end($slashParts);
                    }
                    $apiKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $cleanString);
                }

                if (empty($apiKey)) {
                    Log::warning("Gemini Vision: API Key kosong setelah ekstraksi dari label '{$keyItem->label_name}'.");
                    continue;
                }

                // 4. Murni tempelkan Kunci API tanpa menyertakan URL luar dari DB
                $endpointUrl = "[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=)" . trim($apiKey);

                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                            CURLOPT_SSL_VERIFYPEER => false,
                        ]
                    ])
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
                        'generationConfig' => [
                            'temperature' => 0.1,
                        ]
                    ]);

                if ($response->successful()) {
                    $rawText = $response->json('candidates.0.content.parts.0.text');
                    Log::info("Gemini Vision RAW Output: " . $rawText);

                    $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
                    $data = json_decode($cleanedJson, true);

                    if (is_array($data)) {
                        return $data;
                    }
                } else {
                    Log::warning("Gemini Vision HTTP Gagal [{$keyItem->label_name}]. Status: " . $response->status() . " Response: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Gemini Vision Parsing Exception [{$keyItem->label_name}]: " . $e->getMessage());
            }
        }

        return null;
    }
}