<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeacherAttendanceService
{
    /**
     * Memproses Foto Dokumen Rekap Presensi Guru (Vision OCR)
     */
    public function processAttendancePhoto(string $imagePath): ?array
    {
        $fullPath = storage_path('app/public/' .$imagePath);
        
        if (!file_exists($fullPath)) {
            $fullPath = public_path('storage/' .$imagePath);
        }
        
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' .$imagePath);
        }

        if (!file_exists($fullPath)) {
            Log::error("Teacher Attendance OCR: File dokumen foto tidak ditemukan di path: {$fullPath}");
            return null;
        }

        $base64Image = base64_encode(file_get_contents($fullPath));
        $mimeType    = mime_content_type($fullPath) ?: 'image/jpeg';

        $prompt = "Analisis foto dokumen rekap presensi guru/kelas ini secara teliti.\n" .
                  "Ekstrak data kehadiran siswa ke format JSON murni TANPA MARKDOWN / TANPA ```json.\n\n" .
                  "Format JSON yang wajib dikembalikan:\n" .
                  "{\n" .
                  "  \"date\": \"YYYY-MM-DD\",\n" .
                  "  \"attendances\": [\n" .
                  "    {\"student_name\": \"Nama Lengkap Siswa\", \"nis\": \"Nomor Induk\", \"status\": \"hadir/izin/sakit/alpha\"}\n" .
                  "  ]\n" .
                  "}";

        $apiKeys = ApiKey::where('is_active', true)
            ->where(function($q) {
                $q->where('provider', 'ILIKE', '%gemini%')
                  ->orWhere('provider', 'gemini');
            })
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            Log::error("Teacher Attendance OCR: Tidak ada Kunci API Gemini aktif di database.");
            return null;
        }

        foreach ($apiKeys as $keyItem) {
            $raw = (string) $keyItem->api_key;
            
            // Pembersihan agresif untuk menghilangkan sisa Markdown link, kurung, dan spasi
            $clean = preg_replace('/\[.*?\]\(.*?\)/', '', $raw);
            $clean = preg_replace('/https?:\/\/[^\s]+/', '', $clean);
            $pureKey = trim(str_replace([
                ']', ')', '[', '(', ' ', '"', "'", 
                '%5B', '%5D', '%5b', '%5d', '%28', '%29'
            ], '', $clean));

            if (str_contains($pureKey, 'key=')) {
                $parts = explode('key=', $pureKey);
                $pureKey = trim(end($parts));
            }

            if (empty($pureKey)) continue;

            // Membangun URL secara eksplisit dan terisolasi dari variabel luar
            $endpoints = [
                '[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=)' . $pureKey,
                '[https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=)' . $pureKey,
            ];

            foreach ($endpoints as $url) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(45)
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
                            Log::info("SUKSES OCR Dokumen Presensi Guru [Key ID: {$keyItem->id}]");
                            return $data;
                        }
                    } else {
                        Log::warning("Gemini Vision HTTP Failed [Key ID: {$keyItem->id}] | Status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    Log::error("Exception OCR Dokumen Guru [Key ID: {$keyItem->id}]: " . $e->getMessage());
                }
            }
        }

        return null;
    }
}
