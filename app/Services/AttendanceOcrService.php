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
     * Memproses gambar langsung menembak model terbukti valid (gemini-flash-lite-latest)
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

        // Prioritaskan model gemini-flash-lite-latest yang terbukti HTTP 200 di server Anda
        $priorityModels = [
            'models/gemini-flash-lite-latest',
            'models/gemini-2.0-flash-lite-preview-02-05',
            'models/gemini-1.5-flash-8b'
        ];

        foreach ($priorityModels as $modelName) {
            Log::info("DEBUG OCR: Mengeksekusi Model Vision: {$modelName}...");

            $url = "https://generativelanguage.googleapis.com/v1beta/{$modelName}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(20)
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
                        Log::info("SUKSES BERHASIL! Model '{$modelName}' mengembalikan respons HTTP 200.");
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
    // public function matchStudents(array $ocrResults): array
    // {
    //     if (empty($ocrResults)) {
    //         return [];
    //     }

    //     $students = Student::select('id', 'nis', 'name')->get();
    //     $matchedResults = [];
    //     $usedStudentIds = [];

    //     foreach ($ocrResults as $item) {
    //         $rawText = trim($item['raw_text']);
    //         $bestMatch = null;
    //         $highestSimilarity = 0;

    //         foreach ($students as $student) {
    //             if (!empty($student->nis) && str_contains($rawText, $student->nis)) {
    //                 $bestMatch = $student;
    //                 $highestSimilarity = 100;
    //                 break;
    //             }

    //             if (str_contains(strtolower($rawText), strtolower($student->name)) || 
    //                 str_contains(strtolower($student->name), strtolower($rawText))) {
    //                 $bestMatch = $student;
    //                 $highestSimilarity = 90;
    //                 break;
    //             }

    //             similar_text(strtolower($rawText), strtolower($student->name), $percent);
    //             if ($percent > $highestSimilarity) {
    //                 $highestSimilarity = $percent;
    //                 $bestMatch = $student;
    //             }
    //         }

    //         if ($bestMatch && $highestSimilarity >= 25 && !in_array($bestMatch->id, $usedStudentIds)) {
    //             $matchedResults[] = [
    //                 'raw_text'             => $rawText,
    //                 'matched_student_id'   => $bestMatch->id,
    //                 'matched_student_name' => $bestMatch->name,
    //                 'similarity_score'     => round($highestSimilarity, 2),
    //                 'status'               => $item['status'] ?? 'present',
    //                 'is_verified'          => $highestSimilarity >= 70,
    //             ];

    //             $usedStudentIds[] = $bestMatch->id;
    //         }
    //     }

    //     return $matchedResults;
    // }

    /**
 * Match Teks Hasil OCR ke Data Siswa berdasarkan Kelas Spesifik (classroom_id)
 */
public function matchStudents(array $ocrResults, ?string $classroomId = null): array
{
    if (empty($ocrResults)) {
        return [];
    }

    // Query ke tabel students menggunakan kolom classroom_id
    $query = Student::select('id', 'nis', 'name', 'classroom_id');

    // Filter siswa berdasarkan classroom_id jika dikirim
    if ($classroomId) {
        $query->where('classroom_id', $classroomId);
    }

    $students = $query->get();
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

            // 2. Cek Sub-string Nama
            if (str_contains(strtolower($rawText), strtolower($student->name)) || 
                str_contains(strtolower($student->name), strtolower($rawText))) {
                $bestMatch = $student;
                $highestSimilarity = 90;
                break;
            }

            // 3. Similarity Ejaan (Fuzzy Matching)
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
                'status'               => $item['status'] ?? 'hadir',
                'is_verified'          => $highestSimilarity >= 70,
            ];

            $usedStudentIds[] = $bestMatch->id;
        }
    }

    return $matchedResults;
}
}