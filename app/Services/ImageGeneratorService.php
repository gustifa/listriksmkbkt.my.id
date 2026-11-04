<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ImageGeneratorService
{
    /**
     * Generate gambar ilustrasi/skema berdasarkan TP via AI
     */
    public function generateIllustration(string $tpText)
    {
        // Contoh menggunakan OpenAI DALL-E 3 (atau bisa diganti API Stable Diffusion / Pollinations AI gratis)
        // Alternatif 100% gratis tanpa API key berbayar: menggunakan Pollinations.ai API
        $prompt = "Educational technical illustration, electrical engineering vocational school style, clean vector diagram of: " . $tpText;
        $encodedPrompt = urlencode($prompt);
        
        // URL Endpoint Pollinations.ai (Gratis & Langsung jadi URL Gambar)
        $imageUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1024&height=768&nologo=true";

        return [
            'status' => 'success',
            'image_url' => $imageUrl,
            'message' => 'Ilustrasi gambar berhasil digenerate!'
        ];
    }
}