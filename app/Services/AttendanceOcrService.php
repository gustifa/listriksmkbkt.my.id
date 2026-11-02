<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceOcrService
{
    /**
     * Mengambil API Key aktif dari database PostgreSQL (tabel api_keys)
     */
    protected function getActiveApiKeyRecord()
    {
        return ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();
    }

    /**
     * Memproses gambar dengan mencoba seluruh Endpoint AI / OCR secara otomatis
     */
    public function processImage(string $imagePath): array
    {
        $keyRecord = $this->getActiveApiKeyRecord();

        if (!$keyRecord) {
            Log::error("OCR Error: Tidak ada Kunci API aktif ditemukan pada tabel api_keys.");
            return [];
        }

        $apiKey = trim($keyRecord->api_key);
        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            Log::error("OCR Error: Berkas gambar tidak ditemukan di path: {$fullPath}");
            return [];
        }

        $imageBytes = base64_encode(file_get_contents($fullPath));
        $mimeType   = mime_content_type($fullPath) ?: 'image/jpeg';
        $prompt     = 'Tolong baca foto lembar absensi ini. Ekstrak dan tuliskan seluruh nama siswa dan NIS yang terdeteksi baris demi baris, pisahkan dengan baris baru.';

        // ==========================================
        // 1. ENDPOINT GEMINI (Google AI Studio)
        // ==========================================
        $geminiEndpoints = [
            'gemini-1.5-flash'  => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
            'gemini-1.5-pro'    => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$apiKey}",
            'gemini-2.0-flash-exp' => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}",
            'gemini-pro-vision' => "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent?key={$apiKey}",
        ];

        foreach ($geminiEndpoints as $modelName => $url) {
            Log::info("DEBUG OCR: Mencoba Endpoint Gemini ({$modelName})...");

            try {
                $response = Http::timeout(20)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageBytes]]
                                ]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty($text)) {
                        Log::info("SUKSES! Endpoint Gemini ({$modelName}) BERHASIL (HTTP 200)!");
                        return $this->parseLines($text);
                    }
                } else {
                    Log::warning("Gagal Gemini ({$modelName}) [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
                }
            } catch (\Exception $e) {
                Log::warning("Exception Gemini ({$modelName}): " . $e->getMessage());
            }
        }

        // ==========================================
        // 2. ENDPOINT GROK (xAI API)
        // ==========================================
        Log::info("DEBUG OCR: Mencoba Endpoint Grok Vision (xAI)...");
        try {
            $response = Http::timeout(20)
                ->withToken($apiKey)
                ->post('https://api.x.ai/v1/chat/completions', [
                    'model' => 'grok-vision-beta',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageBytes}"]]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $text = $response->json()['choices'][0]['message']['content'] ?? '';
                if (!empty($text)) {
                    Log::info("SUKSES! Endpoint Grok Vision BERHASIL (HTTP 200)!");
                    return $this->parseLines($text);
                }
            } else {
                Log::warning("Gagal Grok Vision [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::warning("Exception Grok: " . $e->getMessage());
        }

        // ==========================================
        // 3. ENDPOINT OPENAI (GPT-4o / GPT-4 Vision)
        // ==========================================
        Log::info("DEBUG OCR: Mencoba Endpoint OpenAI (GPT-4o Vision)...");
        try {
            $response = Http::timeout(20)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageBytes}"]]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $text = $response->json()['choices'][0]['message']['content'] ?? '';
                if (!empty($text)) {
                    Log::info("SUKSES! Endpoint OpenAI GPT-4o BERHASIL (HTTP 200)!");
                    return $this->parseLines($text);
                }
            } else {
                Log::warning("Gagal OpenAI [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::warning("Exception OpenAI: " . $e->getMessage());
        }

        // ==========================================
        // 4. ENDPOINT GOOGLE CLOUD VISION REST API
        // ==========================================
        Log::info("DEBUG OCR: Mencoba Endpoint Google Cloud Vision REST...");
        try {
            $response = Http::timeout(20)
                ->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
                    'requests' => [
                        [
                            'image' => ['content' => $imageBytes],
                            'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $textAnnotations = $response->json()['responses'][0]['textAnnotations'] ?? [];
                if (!empty($textAnnotations)) {
                    Log::info("SUKSES! Endpoint Google Cloud Vision REST BERHASIL (HTTP 200)!");
                    return $this->parseLines($textAnnotations[0]['description'] ?? '');
                }
            } else {
                Log::warning("Gagal Google Cloud Vision [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::warning("Exception Cloud Vision: " . $e->getMessage());
        }

        Log::error("OCR Error: Seluruh endpoint AI/OCR telah dicoba namun tidak ada yang berhasil dengan API Key tersebut.");
        return [];
    }

    /**
     * Memecah teks output AI menjadi baris terpisah
     */
    private function parseLines(string $fullText): array
    {
        $lines = explode("\n", $fullText);
        $results = [];

        foreach ($lines as $line) {
            $cleaned = trim($line);
            $cleaned = preg_replace('/^[\*\-\d\.\s]+/', '', $cleaned);

            if (strlen($cleaned) >= 3) {
                $results[] = [
                    'raw_text' => $cleaned,
                    'status'   => 'present'
                ];
            }
        }

        return $results;
    }

    /**
     * Match Teks Hasil OCR ke Tabel Students di PostgreSQL
     */
    public function matchStudents(array $ocrResults): array
    {
        if (empty($ocrResults)) {
            return [];
        }

        $students = Student::select('id', 'nis', 'name')->get();
        $matchedResults = [];
        $usedStudentIds = [];

        foreach ($ocrResults as $item) {
            $rawText = trim($item['raw_text']);
            $bestMatch = null;
            $highestSimilarity = 0;

            foreach ($students as $student) {
                // 1. Cek Kemiripan NIS
                if (!empty($student->nis) && str_contains($rawText, $student->nis)) {
                    $bestMatch = $student;
                    $highestSimilarity = 100;
                    break;
                }

                // 2. Cek Kemiripan Nama (sub-string & fuzzy match)
                if (str_contains(strtolower($rawText), strtolower($student->name)) || 
                    str_contains(strtolower($student->name), strtolower($rawText))) {
                    $bestMatch = $student;
                    $highestSimilarity = 90;
                    break;
                }

                similar_text(strtolower($rawText), strtolower($student->name), $percent);
                if ($percent > $highestSimilarity) {
                    $highestSimilarity = $percent;
                    $bestMatch = $student;
                }
            }

            if ($bestMatch && $highestSimilarity >= 25 && !in_array($bestMatch->id, $usedStudentIds)) {
                $matchedResults[] = [
                    'raw_text'             => $rawText,
                    'matched_student_id'   => $bestMatch->id,
                    'matched_student_name' => $bestMatch->name,
                    'similarity_score'     => round($highestSimilarity, 2),
                    'status'               => $item['status'] ?? 'present',
                    'is_verified'          => $highestSimilarity >= 70,
                ];

                $usedStudentIds[] = $bestMatch->id;
            }
        }

        return $matchedResults;
    }
}