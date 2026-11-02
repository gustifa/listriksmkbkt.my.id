<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceOcrService
{
    /**
     * Mengambil API Key Google Vision aktif dari tabel api_keys
     */
    protected function getActiveApiKey(): ?string
    {
        $config = ApiKey::where('provider', 'google_vision')
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->first();

        if (!$config) {
            Log::error("OCR Error: Kunci API aktif untuk 'google_vision' tidak ditemukan di tabel api_keys.");
            return null;
        }

        return $config->api_key;
    }

    /**
     * Memproses gambar lembar absensi dengan Google Cloud Vision API
     */
    public function processImage(string $imagePath): array
    {
        $apiKey = $this->getActiveApiKey();

        if (!$apiKey) {
            return [];
        }

        $fullPath = storage_path('app/public/' . $imagePath);

        if (!file_exists($fullPath)) {
            Log::error("OCR Error: Berkas gambar tidak ditemukan pada lokasi: {$fullPath}");
            return [];
        }

        $url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";

        try {
            // Encode gambar ke format Base64
            $imageBytes = base64_encode(file_get_contents($fullPath));

            // Request payload menggunakan DOCUMENT_TEXT_DETECTION (optimasi untuk tabel & tulisan cetak/tangan)
            $response = Http::timeout(45)->post($url, [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageBytes
                        ],
                        'features' => [
                            [
                                'type' => 'DOCUMENT_TEXT_DETECTION'
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                return $this->parseVisionResponse($json);
            } else {
                Log::error("Google Vision API Response Error [Status {$response->status()}]: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("OCR Exception: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Urai teks hasil deteksi dari Google Cloud Vision API
     */
    private function parseVisionResponse(array $json): array
    {
        $responses = $json['responses'][0] ?? [];

        if (isset($responses['error'])) {
            Log::error("Google Vision API Error Payload: " . json_encode($responses['error']));
            return [];
        }

        $textAnnotations = $responses['textAnnotations'] ?? [];

        if (empty($textAnnotations)) {
            Log::warning("OCR Warning: Google Vision API tidak menemukan teks pada gambar.");
            return [];
        }

        // Teks indeks ke-0 menyimpan gabungan seluruh baris teks yang terdeteksi
        $fullText = $textAnnotations[0]['description'] ?? '';
        $lines = explode("\n", $fullText);

        $results = [];
        foreach ($lines as $line) {
            $cleanedLine = trim($line);

            // Filter baris pendek atau header umum
            if (strlen($cleanedLine) >= 3) {
                $results[] = [
                    'raw_text' => $cleanedLine,
                    'status'   => 'present',
                ];
            }
        }

        return $results;
    }

    /**
     * Pencocokan baris teks OCR dengan data siswa di database
     */
    public function matchStudents(array $ocrResults): array
    {
        if (empty($ocrResults)) {
            return [];
        }

        // Ambil data acuan siswa
        $students = Student::select('id', 'nis', 'name')->get();
        $matchedResults = [];
        $addedStudentIds = [];

        foreach ($ocrResults as $item) {
            $rawText = trim($item['raw_text']);
            $bestMatch = null;
            $highestSimilarity = 0;

            foreach ($students as $student) {
                // 1. Pencocokan Presisi via NIS
                if (!empty($student->nis) && str_contains($rawText, $student->nis)) {
                    $bestMatch = $student;
                    $highestSimilarity = 100;
                    break;
                }

                // 2. Pencocokan Nama via String Similarity
                similar_text(strtolower($rawText), strtolower($student->name), $percent);
                if ($percent > $highestSimilarity) {
                    $highestSimilarity = $percent;
                    $bestMatch = $student;
                }
            }

            // Ambang batas kemiripan minimal 35% agar tabel terisi data hasil ekstraksi
            if ($bestMatch && $highestSimilarity >= 35 && !in_array($bestMatch->id, $addedStudentIds)) {
                $matchedResults[] = [
                    'raw_text'             => $rawText,
                    'matched_student_id'   => $bestMatch->id,
                    'matched_student_name' => $bestMatch->name,
                    'similarity_score'     => round($highestSimilarity, 2),
                    'status'               => $item['status'] ?? 'present',
                    'is_verified'          => $highestSimilarity >= 70,
                ];

                $addedStudentIds[] = $bestMatch->id;
            }
        }

        return $matchedResults;
    }
}