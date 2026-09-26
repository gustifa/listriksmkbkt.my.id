<?php

namespace App\Http\Controllers;

use App\Models\ScannerDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScannerDeviceController extends Controller
{
    /**
     * Menampilkan daftar perangkat scanner / CCTV.
     * Mendukung filter ?filter=online untuk melihat yang sedang aktif.
     */
    public function index(Request $request)
    {
        $query = ScannerDevice::latest();

        // Fitur Filter: Tampilkan hanya yang sedang aktif (Online < 5 menit)
        if ($request->input('filter') === 'online') {
            $query->where('last_active_at', '>=', now()->subMinutes(5));
        }

        $devices = $query->get();

        return view('scanner_devices.index', compact('devices'));
    }

    /**
     * Menampilkan Halaman Kiosk (Scan Camera).
     * Digunakan untuk route('/scan-camera')
     */
    public function scan()
    {
        // Mengembalikan view yang memuat Aplikasi React (biasanya 'welcome' atau 'app')
        return view('daily_attendance.scan');
    }

    /**
     * Menyimpan perangkat baru (Manual Add via Admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
            'mode'        => 'required|in:qr,face,hybrid',
            'rtsp_url'    => 'nullable|string', // Validasi untuk CCTV
        ]);

        // Generate Token Manual (Format: ADM-RANDOM-TIMESTAMP)
        $token = 'ADM-' . strtoupper(Str::random(6)) . '-' . time();

        ScannerDevice::create([
            'device_name'  => $request->device_name,
            'device_token' => $token,
            'mode'         => $request->mode,
            'rtsp_url'     => $request->rtsp_url,
            'description'  => $request->description,
            'status'       => 'active', // Default aktif
            'last_active_at' => null // Belum pernah connect
        ]);

        return redirect()->back()->with('success', 'Perangkat CCTV/Scanner berhasil ditambahkan.');
    }

    /**
     * Menyimpan perubahan konfigurasi (RTSP / Mode).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
            'rtsp_url'    => 'nullable|string',
            'mode'        => 'required|in:qr,face,hybrid',
        ]);

        $device = ScannerDevice::findOrFail($id);

        $device->update([
            'device_name' => $request->device_name,
            'rtsp_url'    => $request->rtsp_url,
            'mode'        => $request->mode,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Konfigurasi perangkat berhasil diperbarui.');
    }

    /**
     * Menghapus perangkat (Revoke akses).
     */
    public function destroy($id)
    {
        $device = ScannerDevice::findOrFail($id);
        $device->delete();

        return redirect()->back()->with('success', 'Perangkat dihapus dan akses dicabut.');
    }
}
