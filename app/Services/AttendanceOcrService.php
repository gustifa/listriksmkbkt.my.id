<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceOcrService
{
    /**
     * Mengambil Kunci API aktif dari tabel api_keys
     */
    protected function getActiveApiKey(): ?string
    {
        $config = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();

        if (!$config) {
            Log::error("OCR Error: Kunci API aktif tidak ditemukan di tabel api_keys.");
            return null;
        }

        return trim($config->api_key);
    }

    /**
     * Memproses gambar dengan mengambil daftar model aktif secara dinamis
     */
    public function processImage(string $imagePath): array
    {
        $apiKey = $this->getActiveApiKey();

        if (!$apiKey) {
            return [];
        }

        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            Log::error("OCR Error: File gambar tidak ditemukan pada: {$fullPath}");
            return [];
        }

        $imageBytes = base64_encode(file_get_contents($fullPath));
        $mimeType   = mime_content_type($fullPath) ?: 'image/jpeg';
        $prompt     = 'Tolong baca foto lembar absensi ini. Ekstrak dan tuliskan seluruh nama siswa dan NIS yang terdeteksi baris demi baris secara murni.';

        // 1. Ambil daftar model yang tersedia untuk API Key ini
        $availableModels = $this->fetchAvailableGeminiModels($apiKey);

        if (empty($availableModels)) {
            Log::error("OCR Error: Tidak ada model Gemini yang dapat diakses dengan API Key tersebut.");
            return [];
        }

        // 2. Coba setiap model yang ditemukan
        foreach ($availableModels as $modelName) {
            Log::info("DEBUG OCR: Mencoba mengeksekusi Model Gemini: {$modelName}...");

            $url = "https://generativelanguage.googleapis.com/v1beta/{$modelName}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(35)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data'      => $imageBytes
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    if (!empty($text)) {
                        Log::info("SUKSES! Model Gemini '{$modelName}' BERHASIL (HTTP 200)!");
                        return $this->parseLines($text);
                    }
                } else {
                    Log::warning("Gagal pada model '{$modelName}' [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
                }
            } catch (\Exception $e) {
                Log::error("Exception pada model '{$modelName}': " . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * Mengambil daftar nama model Gemini yang aktif via Google ModelService API
     */
    private function fetchAvailableGeminiModels(string $apiKey): array
    {
        $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

        try {
            $response = Http::timeout(15)->get($listUrl);

            if ($response->successful()) {
                $modelsData = $response->json()['models'] ?? [];
                $validModels = [];

                foreach ($modelsData as $model) {
                    $name = $model['name'] ?? ''; // Format: models/gemini-...
                    $supportedMethods = $model['supportedGenerationMethods'] ?? [];

                    // Filter model yang mendukung generateContent
                    if (in_array('generateContent', $supportedMethods)) {
                        $validModels[] = $name;
                    }
                }

                Log::info("DEBUG OCR: Berhasil menemukan " . count($validModels) . " model aktif.");
                return $validModels;
            } else {
                Log::warning("DEBUG OCR: Gagal mendapatkan ListModels [HTTP {$response->status()}]: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("DEBUG OCR: Exception saat ListModels: " . $e->getMessage());
        }

        // Fallback default jika ListModels dibatasi
        return [
            'models/gemini-1.5-flash',
            'models/gemini-1.5-pro',
            'models/gemini-2.0-flash-exp'
        ];
    }

    /**
     * Memecah hasil teks baris demi baris
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
     * Match Teks Hasil OCR ke Data Siswa di PostgreSQL
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
                if (!empty($student->nis) && str_contains($rawText, $student->nis)) {
                    $bestMatch = $student;
                    $highestSimilarity = 100;
                    break;
                }

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