<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator; // <--- 1. Tambahkan ini

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
    }
}
