<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GithubVersionChecker;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsUpdated
{
    protected $checker;

    public function __construct(GithubVersionChecker $checker)
    {
        $this->checker = $checker;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Jangan cek jika sedang mengakses halaman update-required itu sendiri agar tidak looping
        if ($request->routeIs('app.update_required')) {
            return $next($request);
        }

        // Cek Versi
        if ($this->checker->isOutdated()) {
            // Jika Outdated, lempar ke halaman peringatan
            return redirect()->route('app.update_required');
        }

        return $next($request);
    }
}