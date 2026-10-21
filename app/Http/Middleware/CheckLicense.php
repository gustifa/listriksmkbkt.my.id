<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;

class CheckLicense
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Pengecualian agar halaman input lisensi tetap bisa dibuka
        if ($request->is('license*')) {
            return $next($request);
        }

        $check = $this->licenseService->verifyLicense();

        if (!$check['status']) {
            return redirect()->route('license.index')->with('error', $check['message']);
        }

        return $next($request);
    }
}