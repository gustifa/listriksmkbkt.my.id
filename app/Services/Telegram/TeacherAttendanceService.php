<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeacherAttendanceService
{
    /**
     * Memproses Foto Rekap Presensi Khusus Guru (Vision OCR)
     */
    public function processAttendancePhoto(string $imagePath): ?array
    {
        // 1. Cek keberadaan file fisik gambar
        $fullPath = storage_path('app/public/' .$imagePath);
        
        if (!file_exists($fullPath)) {
            $fullPath = public_path('storage/' .$imagePath);
        }
        
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' .$imagePath);
        }

        if (!file_exists($fullPath)) {
            Log::error("Teacher OCR: File tidak ditemukan di path: {$fullPath}");
            return null;
        }

        $base64Image = base64_encode(file_get_contents($fullPath));
        $mimeType    = mime_content_type($fullPath) ?: 'image/jpeg';

        $prompt = "Analisis foto rekap presensi kelas ini secara teliti.\n" .
                  "Ekstrak data kehadiran siswa ke format JSON murni TANPA MARKDOWN / TANPA ```json.\n\n" .
                  "Format JSON:\n" .
                  "{\n" .
                  "  \"date\": \"YYYY-MM-DD\",\n" .
                  "  \"attendances\": [\n" .
                  "    {\"student_name\": \"Nama Lengkap\", \"nis\": \"12345\", \"status\": \"hadir/izin/sakit/alpha\"}\n" .
                  "  ]\n" .
                  "}";

        $apiKeys = ApiKey::where('is_active', true)->orderBy('priority', 'asc')->get();

        foreach ($apiKeys as $keyItem) {
            $provider = strtolower(trim($keyItem->provider));
            
            // Mengambil langsung nilai API Key murni dari database
            $pureKey = trim((string) $keyItem->api_key);

            if (empty($pureKey)) continue;

            if ($provider === 'gemini') {
                $endpoints = [
                    '[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=)' . $pureKey,
                    '[https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=)' . $pureKey,
                ];

                foreach ($endpoints as $url) {
                    try {
                        $response = Http::withoutVerifying()
                            ->timeout(30)
                            ->retry(2, 500)
                            ->post($url, [
                                'contents' => [
                                    [
                                        'parts' => [
                                            ['text' => $prompt],
                                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]]
                                        ]
                                    ]
                                ],
                                'generationConfig' => ['temperature' => 0.1]
                            ]);

                        if ($response->successful()) {
                            $rawText = $response->json('candidates.0.content.parts.0.text');
                            $cleanedJson = trim(str_replace(['```json', '```'], '', $rawText));
                            $data = json_decode($cleanedJson, true);

                            if (is_array($data)) {
                                Log::info("SUKSES OCR Presensi Guru [{$keyItem->label_name}]");
                                return $data;
                            }
                        } else {
                            Log::warning("Gemini Vision HTTP Failed [{$keyItem->label_name}] | Status: " . $response->status());
                        }
                    } catch (\Exception $e) {
                        Log::error("Exception OCR Guru [{$keyItem->label_name}]: " . $e->getMessage());
                    }
                }
            }
        }

        return null;
    }
}