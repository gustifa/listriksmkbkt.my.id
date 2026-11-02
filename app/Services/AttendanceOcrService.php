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
            Log::error("OCR Error: Kunci API aktif tidak ditemukan pada tabel api_keys.");
            return null;
        }

        return trim($config->api_key);
    }

    /**
     * Memproses gambar menggunakan Google Gemini REST API v1
     */
    public function processImage(string $imagePath): array
    {
        $apiKey = $this->getActiveApiKey();

        if (!$apiKey) {
            return [];
        }

        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            Log::error("OCR Error: Berkas gambar tidak ditemukan di path: {$fullPath}");
            return [];
        }

        $imageBytes = base64_encode(file_get_contents($fullPath));
        $mimeType   = mime_content_type($fullPath) ?: 'image/jpeg';
        $prompt     = 'Ekstrak seluruh daftar nama siswa dan NIS yang ada pada foto daftar hadir ini. Tuliskan hasilnya baris demi baris secara murni tanpa format markdown.';

        // Endpoint Resmi Gemini v1 REST (Sesuai untuk Kunci API Google Studio AQ.Ab8RN...)
        $endpoints = [
            'gemini-1.5-flash (v1)' => "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}",
            'gemini-1.5-pro (v1)'   => "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key={$apiKey}",
        ];

        foreach ($endpoints as $label => $url) {
            Log::info("DEBUG OCR: Menghubungi {$label}...");

            try {
                $response = Http::timeout(40)
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
                        Log::info("SUKSES! Endpoint {$label} BERHASIL (HTTP 200)!");
                        return $this->parseLines($text);
                    }
                } else {
                    Log::warning("Gagal {$label} [HTTP {$response->status()}]: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Exception {$label}: " . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * Memecah hasil ekstraksi teks AI menjadi baris terpisah
     */
    private function parseLines(string $fullText): array
    {
        $lines = explode("\n", $fullText);
        $results = [];

        foreach ($lines as $line) {
            $cleaned = trim($line);
            // Bersihkan nomor baris atau penanda list jika ada
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
     * Pencocokan Teks Hasil OCR ke Data Siswa di PostgreSQL
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
                // 1. Kemiripan NIS
                if (!empty($student->nis) && str_contains($rawText, $student->nis)) {
                    $bestMatch = $student;
                    $highestSimilarity = 100;
                    break;
                }

                // 2. Kemiripan Nama (sub-string)
                if (str_contains(strtolower($rawText), strtolower($student->name)) || 
                    str_contains(strtolower($student->name), strtolower($rawText))) {
                    $bestMatch = $student;
                    $highestSimilarity = 90;
                    break;
                }

                // 3. Kemiripan Ejaan (Fuzzy Matching)
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