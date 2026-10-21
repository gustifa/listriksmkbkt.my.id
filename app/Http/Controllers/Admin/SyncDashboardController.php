<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DataSyncService;
use App\Models\SyncLog;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\StudentViolation;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SyncDashboardController extends Controller
{
    protected DataSyncService $syncService;

    public function __construct(DataSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index()
    {
        // Cek peran server dari .env ('online' atau 'local')
        $serverRole = env('SERVER_ROLE', 'local');

        // Ambil URL Server Online dan Token dari tabel settings
        $serverUrlSetting = Setting::where('key', 'sync_server_url')->first();
        $serverUrl = $serverUrlSetting ? $serverUrlSetting->value : config('services.online_server.url');

        $syncTokenSetting = Setting::where('key', 'sync_api_token')->first();
        $syncToken = $syncTokenSetting ? $syncTokenSetting->value : null;

        // Hitung data transaksi lokal yang belum disinkron
        $unsyncedDaily = DailyAttendance::where('is_synced', false)->count();
        $unsyncedAttendances = Attendance::where('is_synced', false)->count();
        $unsyncedViolations = StudentViolation::where('is_synced', false)->count();

        $totalUnsynced = $unsyncedDaily + $unsyncedAttendances + $unsyncedViolations;

        // Riwayat log sinkronisasi
        $logs = SyncLog::latest('synced_at')->paginate(10);

        return view('admin.sync.index', compact(
            'serverRole',
            'serverUrl',
            'syncToken',
            'unsyncedDaily',
            'unsyncedAttendances',
            'unsyncedViolations',
            'totalUnsynced',
            'logs'
        ));
    }

    public function runPush()
    {
        $result = $this->syncService->pushLocalData();
        return redirect()->back()->with($result['status'], $result['message']);
    }

    public function runPull()
    {
        $result = $this->syncService->pullMasterData();
        return redirect()->back()->with($result['status'], $result['message']);
    }

    /**
     * Khusus Server Online: Generate Token API Sanctum
     */
    public function generateToken(Request $request)
    {
        $user = auth()->user();
        
        // Hapus token lama dengan nama 'sync-token' jika ada
        $user->tokens()->where('name', 'sync-token')->delete();
        
        // Buat token Sanctum baru
        $newToken = $user->createToken('sync-token')->plainTextToken;

        return redirect()->back()->with([
            'success' => 'API Token berhasil di-generate dari Server Online!',
            'plain_token' => $newToken
        ]);
    }

    /**
     * Khusus Server Lokal: Simpan Input Token dari Server Online
     */
    public function updateToken(Request $request)
    {
        $request->validate([
            'api_token' => 'required|string',
        ]);

        Setting::updateOrCreate(
            ['key' => 'sync_api_token'],
            ['value' => trim($request->api_token)]
        );

        return redirect()->back()->with('success', 'API Token dari Server Online berhasil disimpan!');
    }

    /**
     * Simpan URL Server Online
     */
    public function updateServerUrl(Request $request)
    {
        $request->validate([
            'server_url' => 'required|url',
        ]);

        Setting::updateOrCreate(
            ['key' => 'sync_server_url'],
            ['value' => rtrim($request->server_url, '/')]
        );

        return redirect()->back()->with('success', 'URL Server Online berhasil diperbarui!');
    }

    
}