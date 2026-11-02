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
            ->whereIn('provider', ['google_vision', 'gemini', 'google'])
            ->orderBy('priority', 'asc')
            ->first();
    }

    /**
     * Memproses gambar dengan mencoba seluruh Endpoint API yang sesuai
     */
    public function processImage(string $imagePath): array
    {
        $keyRecord = $this->getActiveApiKeyRecord();

        if (!$keyRecord) {
            Log::error("OCR Error: Kunci API aktif untuk 'google_vision' atau 'gemini' tidak ditemukan di tabel api_keys.");
            return [];
        }

        $apiKey = trim($keyRecord->api_key);
        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            Log::error("OCR Error: Berkas gambar tidak ditemukan pada: {$fullPath}");
            return [];
        }

        $imageBytes = base64_encode(file_get_contents($fullPath));
        $mimeType   = mime_content_type($fullPath) ?: 'image/jpeg';

        // --- ENDPOINT 1: Google Cloud Vision API (DOCUMENT_TEXT_DETECTION) ---
        Log::info("DEBUG OCR: Mencoba Endpoint [1] Google Cloud Vision API...");
        $visionUrl = "https://vision.googleapis.com/v1/images:annotate?key=" . $apiKey;

        try {
            $response = Http::timeout(25)->post($visionUrl, [
                'requests' => [
                    [
                        'image' => ['content' => $imageBytes],
                        'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $textAnnotations = $json['responses'][0]['textAnnotations'] ?? [];

                if (!empty($textAnnotations)) {
                    Log::info("DEBUG OCR: Endpoint [1] Google Vision BERHASIL (HTTP 200)!");
                    $fullText = $textAnnotations[0]['description'] ?? '';
                    return $this->parseLines($fullText);
                }
            } else {
                Log::warning("DEBUG OCR: Endpoint [1] Vision Gagal [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::warning("DEBUG OCR: Endpoint [1] Exception: " . $e->getMessage());
        }

        // --- ENDPOINT 2: Google Gemini 1.5 Flash API ---
        Log::info("DEBUG OCR: Mencoba Endpoint [2] Gemini 1.5 Flash API...");
        $gemini15Url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::timeout(25)->post($gemini15Url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Ekstrak seluruh daftar nama siswa dan NIS dari foto rekap absensi ini baris demi baris.'],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageBytes]]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($text)) {
                    Log::info("DEBUG OCR: Endpoint [2] Gemini 1.5 Flash BERHASIL (HTTP 200)!");
                    return $this->parseLines($text);
                }
            } else {
                Log::warning("DEBUG OCR: Endpoint [2] Gemini 1.5 Gagal [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::warning("DEBUG OCR: Endpoint [2] Exception: " . $e->getMessage());
        }

        // --- ENDPOINT 3: Google Gemini 2.0 Flash API ---
        Log::info("DEBUG OCR: Mencoba Endpoint [3] Gemini 2.0 Flash API...");
        $gemini20Url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        try {
            $response = Http::timeout(25)->post($gemini20Url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Ekstrak seluruh daftar nama siswa dari foto rekap absensi ini.'],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageBytes]]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($text)) {
                    Log::info("DEBUG OCR: Endpoint [3] Gemini 2.0 Flash BERHASIL (HTTP 200)!");
                    return $this->parseLines($text);
                }
            } else {
                Log::error("DEBUG OCR: Endpoint [3] Gemini 2.0 Gagal [HTTP {$response->status()}]: " . substr($response->body(), 0, 150));
            }
        } catch (\Exception $e) {
            Log::error("DEBUG OCR: Endpoint [3] Exception: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Memecah string teks menjadi array baris
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
     * Pencocokan Fuzzy Match Teks ke Database Siswa
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