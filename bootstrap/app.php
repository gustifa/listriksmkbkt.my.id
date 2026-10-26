<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureFaceRegistered; // <--- Impor class ini!
use App\Http\Middleware\UserActivity; // <--- Import ini di atas

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->append(\App\Http\Middleware\EnsureAppIsUpdated::class);
        // --- TAMBAHKAN BAGIAN INI ---
        // Masukkan ke grup 'web' agar Auth::check() berjalan dengan benar
        $middleware->web(append: [
            \App\Http\Middleware\UserActivity::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'ensure.face' => EnsureFaceRegistered::class,
            'check.license' => \App\Http\Middleware\CheckLicense::class,
        ]);
        // ----------------------------
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
