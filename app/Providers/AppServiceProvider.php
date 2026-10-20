<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator; // <--- 1. Tambahkan ini
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout; // <-- 2. Tambahkan ini
use Illuminate\Auth\Events\Logout; // <-- 1. Tambahkan ini
use App\Listeners\BindStudentDeviceOnLogin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tambahkan baris ini
        if (file_exists(app_path('Helpers/SchoolHelper.php'))) {
            require_once app_path('Helpers/SchoolHelper.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
        return $user->hasRole('admin') ? true : null;
        });

        // Cek jika aplikasi berjalan di lingkungan lokal (local environment)
        // DAN Ngrok sedang digunakan (dideteksi dari host)
        if (env('APP_ENV') === 'local' && str_contains(request()->header('host'), '.ngrok-free.app')) {

            // Memaksa Laravel untuk menghasilkan semua URL menggunakan skema HTTPS
            URL::forceScheme('https');
        }

        // 2. Tambahkan ini agar semua pagination pakai Bootstrap 5
        Paginator::useBootstrapFive();

        // --- TAMBAHKAN KODE EVENT LISTENER LOGIN DI SINI ---
        // Event::listen(
        //     Login::class,
        //     LogSuccessfulLogin::class
        // );
        // Event::listen(
        //     Logout::class,
        //     LogSuccessfulLogout::class
        // );

        Event::listen(
        \Illuminate\Auth\Events\Login::class,
        \App\Listeners\BindStudentDeviceOnLogin::class
        
    );

        
    }
}
