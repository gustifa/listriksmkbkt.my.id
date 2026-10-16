<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\User;

class DeviceSettingController extends Controller
{
    /**
     * Menampilkan halaman utama pengaturan & daftar perangkat siswa.
     */
    public function index(Request $request)
    {
        // Ambil pengaturan sistem berbasis UUID
        $settings = SystemSetting::pluck('value', 'key');

        // Query filter khusus pengguna dengan role 'siswa'
        $query = User::role('siswa');

        // Filter pencarian berdasarkan nama atau email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination data siswa
        $students = $query->orderBy('name', 'asc')->paginate(15);

        return view('admin.device_settings.index', compact('settings', 'students'));
    }


    public function updateSettings(Request $request)
    {
        $request->validate([
            'enable_device_lock' => 'required|in:0,1',
            'max_allowed_devices' => 'required|in:1,2',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'enable_device_lock'],
            ['value' => $request->enable_device_lock]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'max_allowed_devices'],
            ['value' => $request->max_allowed_devices]
        );

        if (function_exists('activity')) {
            activity('Pengaturan Sistem')
                ->causedBy(auth()->user())
                ->log("Mengubah pengaturan kunci perangkat siswa (Status: {$request->enable_device_lock}, Max: {$request->max_allowed_devices} HP)");
        }

        return redirect()->back()->with('success', 'Pengaturan kunci perangkat berhasil diperbarui.');
    }

    public function resetDevice(Request $request, $id)
    {
        $student = User::findOrFail($id);
        
        // Reset kedua slot perangkat
        $student->forceFill([
            'device_id_1' => null,
            'device_id_2' => null,
        ])->save();

        // Catat ke Activitylog jika ada
        if (function_exists('activity')) {
            activity('Kelola Perangkat')
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->log("Mereset penguncian HP siswa {$student->name}");
        }

        return response()->json([
            'success' => true,
            'message' => "Tautan perangkat milik {$student->name} berhasil di-reset.",
        ]);
    }
}
