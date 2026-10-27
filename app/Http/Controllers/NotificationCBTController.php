<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationCBTController extends Controller
{
    /**
     * Tandai semua notifikasi pengguna yang belum dibaca menjadi dibaca.
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();

            if ($user) {
                $user->unreadNotifications->markAsRead();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Semua notifikasi berhasil ditandai dibaca.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tandai satu notifikasi spesifik sebagai dibaca berdasarkan ID.
     */
    public function markAsRead($id)
    {
        try {
            $notification = Auth::user()->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Notifikasi berhasil dibaca.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membaca notifikasi.'
            ], 500);
        }
    }
}