<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\DataSyncService;
use App\Models\AttendanceSetting;

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

// Ambil jam eksekusi otomatisasi dari setting database (default '13:00' jika kosong)
$autoAbsentTime = AttendanceSetting::value('auto_absent_time') ?? '13:00';
Schedule::command('attendance:record-absent-violations')
    ->dailyAt($autoAbsentTime)
    ->weekdays();
// Schedule::command('attendance:record-absent-violations')
//     ->dailyAt('13:00')
//     ->weekdays();
