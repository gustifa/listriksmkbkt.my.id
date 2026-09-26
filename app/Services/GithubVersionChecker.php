<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GithubVersionChecker
{
    public function isOutdated()
    {
        return Cache::remember('app_is_outdated', 600, function () {
            $localHash = $this->getLocalCommitHash();
            $remoteHash = $this->getRemoteCommitHash();

            if (!$localHash || !$remoteHash) {
                return false;
            }

            return $localHash !== $remoteHash;
        });
    }

    private function getLocalCommitHash()
    {
        try {
            // Mengambil hash commit terakhir dari git lokal
            $hash = exec('git rev-parse HEAD');
            return $hash ? trim($hash) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getRemoteCommitHash()
    {
        $user = env('GITHUB_USERNAME');
        $repo = env('GITHUB_REPO');
        $branch = env('GITHUB_BRANCH', 'main');
        $token = env('GITHUB_TOKEN');

        if (!$user || !$repo) {
            return null; 
        }

        $url = "https://api.github.com/repos/{$user}/{$repo}/commits/{$branch}";

        try {
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
            ];

            if ($token) {
                $headers['Authorization'] = 'token ' . $token;
            }

            // PERBAIKAN: Menambahkan withoutVerifying() untuk bypass error SSL di localhost
            $response = Http::withoutVerifying()->withHeaders($headers)->get($url);

            if ($response->successful()) {
                return $response->json()['sha'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Exception GitHub API: " . $e->getMessage());
            return null;
        }
    }
}