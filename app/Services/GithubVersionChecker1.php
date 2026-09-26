<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GithubVersionChecker
{
    public function isOutdated()
    {
        // Cache hasil pengecekan selama 10 menit agar tidak menembak API GitHub terus menerus (Rate Limit)
        // Ganti 600 (detik) sesuai kebutuhan.
        return Cache::remember('app_is_outdated', 600, function () {
            
            // 1. Ambil Hash Commit Lokal
            $localHash = $this->getLocalCommitHash();
            
            // 2. Ambil Hash Commit Remote (GitHub)
            $remoteHash = $this->getRemoteCommitHash();

            // Jika gagal ambil salah satu, anggap aplikasi aman (false) agar tidak error fatal
            if (!$localHash || !$remoteHash) {
                return false;
            }

            // Jika Hash Beda, berarti Outdated
            return $localHash !== $remoteHash;
        });
    }

    private function getLocalCommitHash()
    {
        try {
            // Menggunakan perintah git untuk mengambil hash terakhir
            // Pastikan fungsi exec() aktif di server
            $hash = exec('git rev-parse HEAD');
            return trim($hash);
        } catch (\Exception $e) {
            Log::error("Gagal membaca git lokal: " . $e->getMessage());
            return null;
        }
    }

    private function getRemoteCommitHash()
    {
        $user = env('GITHUB_USERNAME');
        $repo = env('GITHUB_REPO');
        $branch = env('GITHUB_BRANCH', 'main');
        $token = env('GITHUB_TOKEN');

        $url = "https://api.github.com/repos/{$user}/{$repo}/commits/{$branch}";

        try {
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
            ];

            if ($token) {
                $headers['Authorization'] = 'token ' . $token;
            }

            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                return $response->json()['sha'] ?? null;
            }

            Log::error("Gagal koneksi ke GitHub API: " . $response->status());
            return null;
        } catch (\Exception $e) {
            Log::error("Exception GitHub API: " . $e->getMessage());
            return null;
        }
    }
}