<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\DataSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan pengecekan setiap menit
Schedule::command('schedule:notify-teachers')->everyMinute();


// Jalankan PUSH data transaksi setiap 5 menit
Schedule::call(function () {
    app(DataSyncService::class)->pushLocalData();
})->everyFiveMinutes();

// Jalankan PULL data master setiap jam
Schedule::call(function () {
    app(DataSyncService::class)->pullMasterData();
})->hourly();
