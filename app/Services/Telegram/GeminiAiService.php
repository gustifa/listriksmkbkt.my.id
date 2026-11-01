<?php

namespace App\Services\Telegram;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    /**
     * Memanggil AI dengan Multi-API Fallback membaca dari Database
     */
    public function askAi(string $prompt): string
    {
        // Ambil semua API Key aktif dari database terurut berdasarkan prioritas
        $apiKeys = ApiKey::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($apiKeys->isEmpty()) {
            return "⚠️ *Asisten AI SISFO*\n\nLayanan AI belum dikonfigurasi. Silakan hubungi Admin untuk memasukkan API Key.";
        }

        $systemInstruction = "Anda adalah Asisten Virtual cerdas & ramah untuk SISFO SMKN 1 Bukittinggi. " .
                             "Jawablah pertanyaan pengguna secara sopan, jelas, singkat, dan relevan dengan lingkungan sekolah/pendidikan.";

        foreach ($apiKeys as $keyItem) {
            try {
                $provider = strtolower(trim($keyItem->provider));
                $apiKey   = trim($keyItem->api_key); // Hapus karakter spasi tak terlihat jika ada

                if (empty($apiKey)) {
                    continue;
                }

                // 1. PROVIDER GOOGLE GEMINI
                if ($provider === 'gemini') {
                    // Gunakan endpoint resmi v1beta gemini-1.5-flash
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

                    $response = Http::timeout(12)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, [
                            'system_instruction' => [
                                'parts' => [['text' => $systemInstruction]]
                            ],
                            'contents' => [
                                ['parts' => [['text' => $prompt]]]
                            ],
                            'generationConfig' => [
                                'maxOutputTokens' => 500,
                                'temperature'     => 0.7,
                            ]
                        ]);

                    if ($response->successful()) {
                        $reply = $response->json('candidates.0.content.parts.0.text');
                        if (!empty($reply)) {
                            return trim($reply);
                        }
                    }

                    // Log detail response untuk kemudahan debugging
                    Log::warning("Gemini Key '{$keyItem->label_name}' gagal/limit. Status: " . $response->status() . " | Body: " . $response->body());
                }

                // 2. PROVIDER GROQ (JIKA ADA PROVIDER CADANGAN)
                elseif ($provider === 'groq') {
                    $response = Http::timeout(12)
                        ->withToken($apiKey)
                        ->post('https://api.groq.com/openai/v1/chat/completions', [
                            'model' => 'llama-3.1-8b-instant',
                            'messages' => [
                                ['role' => 'system', 'content' => $systemInstruction],
                                ['role' => 'user', 'content' => $prompt]
                            ],
                            'max_tokens' => 500,
                        ]);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content');
                        if (!empty($reply)) {
                            return trim($reply);
                        }
                    }

                    Log::warning("Groq Key '{$keyItem->label_name}' gagal/limit. Status: " . $response->status());
                }

            } catch (\Exception $e) {
                Log::error("Exception panggil AI '{$keyItem->label_name}': " . $e->getMessage());
            }
        }

        return "🤖 *Asisten AI SISFO*\n\nMaaf, seluruh kuota harian layanan AI sedang mencapai batas maksimal. Silakan coba beberapa saat lagi.";
    }
}