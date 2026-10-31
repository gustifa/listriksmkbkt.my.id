<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GithubVersionChecker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureAppIsUpdated
{
    protected $checker;

    public function __construct(GithubVersionChecker $checker)
    {
        $this->checker = $checker;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. KECUALIKAN RUTE KRUSIAL (Webhook Telegram, Livewire, API, & Halaman Peringatan Update)
        if (
            $request->routeIs('app.update_required') ||
            $request->is('api/*', 'telegram/*', 'livewire/*', 'assets/*') ||
            $request->expectsJson()
        ) {
            return $next($request);
        }

        // 2. CEK VERSI DENGAN PROTEKSI TRY-CATCH (Mencegah aplikasi crash jika GitHub API Error/Timeout)
        try {
            if ($this->checker->isOutdated()) {
                return redirect()->route('app.update_required');
            }
        } catch (\Throwable $e) {
            // Catat log jika gagal terhubung ke GitHub, tapi biarkan aplikasi tetap berjalan aman
            Log::warning("GithubVersionChecker Exception: " . $e->getMessage());
        }

        return $next($request);
    }
}