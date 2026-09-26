<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScannerController;
use App\Http\Controllers\Api\WhatsappWebhookController;
use App\Http\Controllers\Api\PrayerServerSyncController;
use App\Http\Controllers\Api\PrayerApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- AUTHENTICATION ROUTES ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Group untuk Device Scanner
Route::prefix('device')->group(function () {
    Route::post('/register', [ScannerController::class, 'registerDevice']);
    Route::post('/scan', [ScannerController::class, 'deviceScan']);
});

Route::get('/students', [AttendanceApiController::class, 'getStudents']);
Route::get('/staff', [AttendanceApiController::class, 'getStaff']);
Route::post('/attendance', [AttendanceApiController::class, 'storeAttendance']);


// --- WHATSAPP WEBHOOK (CHATBOT) ---
// Endpoint ini dipanggil oleh Node.js Service saat ada pesan masuk
// Route::post('/whatsapp/webhook', [WhatsappWebhookController::class, 'handle']);

// Endpoint untuk sinkronisasi antar server
Route::get('/prayer/sync-export', [PrayerServerSyncController::class, 'exportData']);
Route::get('/sync/export-all', [PrayerServerSyncController::class, 'exportAll']);

