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
     * Memproses gambar dengan model Gemini Vision yang valid
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

        // Ambil daftar model vision yang disaring
        $visionModels = $this->fetchVisionModels($apiKey);

        if (empty($visionModels)) {
            Log::error("OCR Error: Tidak ada model Vision yang valid untuk API Key ini.");
            return [];
        }

        foreach ($visionModels as $modelName) {
            Log::info("DEBUG OCR: Mencoba mengeksekusi Model Vision: {$modelName}...");

            $url = "https://generativelanguage.googleapis.com/v1beta/{$modelName}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(25)
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
                        Log::info("SUKSES BERHASIL! Model Gemini '{$modelName}' BERHASIL (HTTP 200)!");
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
     * Ambil & Filter hanya model Gemini yang mendukung Input Gambar (Vision)
     */
    private function fetchVisionModels(string $apiKey): array
    {
        $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

        try {
            $response = Http::timeout(15)->get($listUrl);

            if ($response->successful()) {
                $modelsData = $response->json()['models'] ?? [];
                $filteredModels = [];

                foreach ($modelsData as $model) {
                    $name = $model['name'] ?? ''; // Format: models/gemini-...
                    $supportedMethods = $model['supportedGenerationMethods'] ?? [];
                    $inputModalities = $model['inputTokenLimit'] ?? 0; // indikator kapasitas model

                    // Abaikan model TTS (Text-to-Speech), embedding, dan Gemma
                    if (str_contains($name, '-tts') || str_contains($name, 'embedding') || str_contains($name, 'gemma')) {
                        continue;
                    }

                    // Hanya ambil model gemini yang mendukung generateContent
                    if (in_array('generateContent', $supportedMethods)) {
                        $filteredModels[] = $name;
                    }
                }

                // Urutkan agar model versi Flash standar/prioritas berada di paling awal
                usort($filteredModels, function ($a, $b) {
                    if (str_contains($a, 'flash') && !str_contains($b, 'flash')) return -1;
                    if (!str_contains($a, 'flash') && str_contains($b, 'flash')) return 1;
                    return 0;
                });

                Log::info("DEBUG OCR: Ditemukan " . count($filteredModels) . " model Vision yang valid.");
                return $filteredModels;
            }
        } catch (\Exception $e) {
            Log::error("DEBUG OCR: Exception saat ListModels: " . $e->getMessage());
        }

        // Daftar fallback spesifik untuk vision
        return [
            'models/gemini-1.5-flash',
            'models/gemini-1.5-pro',
            'models/gemini-1.0-pro-vision-latest'
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