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
            ->whereIn('provider', ['google_vision', 'gemini', 'google'])
            ->orderBy('priority', 'asc')
            ->first();

        if (!$config) {
            Log::error("OCR Error: Kunci API aktif tidak ditemukan pada tabel api_keys.");
            return null;
        }

        return trim($config->api_key);
    }

    /**
     * Memproses gambar dengan Google Gemini API Endpoint Resmi (gemini-1.5-flash-latest / gemini-2.5-flash)
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

        // Gunakan Model Stable gemini-1.5-flash-latest
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $apiKey;

        try {
            $response = Http::timeout(45)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Tolong baca foto lembar absensi ini. Ekstrak dan tuliskan seluruh nama siswa dan NIS yang terdeteksi baris demi baris, pisahkan dengan baris baru.'
                                ],
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
                $extractedText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                Log::info("DEBUG OCR: Panggilan Gemini API BERHASIL (HTTP 200)!");
                return $this->parseLines($extractedText);
            } else {
                Log::error("Gemini API Error Status [{$response->status()}]: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("OCR Exception: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Parse hasil teks baris demi baris
     */
    private function parseLines(string $fullText): array
    {
        $lines = explode("\n", $fullText);
        $results = [];

        foreach ($lines as $line) {
            $cleaned = trim($line);
            // Hapus karakter nomor / bullet points markdown
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

                // 2. Cek Kemiripan Nama
                if (str_contains(strtolower($rawText), strtolower($student->name)) || 
                    str_contains(strtolower($student->name), strtolower($rawText))) {
                    $bestMatch = $student;
                    $highestSimilarity = 90;
                    break;
                }

                // 3. Fallback similarity
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