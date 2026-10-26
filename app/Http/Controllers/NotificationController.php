<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Ambil semua notifikasi milik user (15 data per halaman)
        $notifications = Auth::user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menandai semua notifikasi milik user sebagai telah dibaca
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function getNotificationsAjax()
{
    // Ambil 20 notifikasi terbaru milik user
    $notifications = Auth::user()->notifications()->take(20)->get()->map(function ($notification) {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? 'Pemberitahuan',
            'message' => $notification->data['message'] ?? '',
            'icon' => $notification->data['icon'] ?? 'bx-bell',
            'url' => $notification->data['url'] ?? '#',
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at->diffForHumans(),
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $notifications
    ]);
}

public function markAllAsReadAjax()
{
    Auth::user()->unreadNotifications->markAsRead();
    return response()->json(['status' => 'success']);
}
}
