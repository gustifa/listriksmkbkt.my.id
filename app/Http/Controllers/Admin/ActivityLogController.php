<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20);

        return view('admin.activity_logs.index', compact('logs'));
    }

    public function clearLog()
{
    try {
        // Menghapus seluruh data log aktivitas
        Activity::truncate(); // atau Activity::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seluruh riwayat log aktivitas berhasil dikosongkan.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengosongkan log: ' . $e->getMessage()
        ], 500);
    }
}
}