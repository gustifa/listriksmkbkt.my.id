<?php

namespace App\Http\Controllers;

use App\Models\TelegramNotification;
use App\Models\TelegramSubscriber;
use Illuminate\Http\Request;

class TelegramMonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Metric Summary
        $totalSubscribers = TelegramSubscriber::where('is_active', true)->count();
        $totalSentSuccess = TelegramNotification::where('status', 'success')->count();
        $totalSentFailed  = TelegramNotification::where('status', 'failed')->count();
        $todaySentCount   = TelegramNotification::whereDate('created_at', today())->count();

        // Query Log Notifikasi
        $logsQuery = TelegramNotification::with('subscriber')->latest();

        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $logsQuery->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $logsQuery->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('chat_id', 'like', "%{$search}%");
            });
        }

        $logs = $logsQuery->paginate(15, ['*'], 'logs_page');

        // Query Subscriber (Orang Tua)
        $subscribersQuery = TelegramSubscriber::with('student')->latest();

        if ($request->filled('sub_search')) {
            $subSearch = $request->sub_search;
            $subscribersQuery->where(function ($q) use ($subSearch) {
                $q->where('parent_name', 'like', "%{$subSearch}%")
                  ->orWhere('chat_id', 'like', "%{$subSearch}%")
                  ->orWhere('phone_number', 'like', "%{$subSearch}%");
            });
        }

        $subscribers = $subscribersQuery->paginate(15, ['*'], 'sub_page');

        return view('monitoring.telegram', compact(
            'totalSubscribers',
            'totalSentSuccess',
            'totalSentFailed',
            'todaySentCount',
            'logs',
            'subscribers'
        ));
    }
}