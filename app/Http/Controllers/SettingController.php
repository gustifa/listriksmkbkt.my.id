<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan
     */
    public function index()
    {
        // Mengambil semua data settings menjadi array key => value
        // Contoh: ['school_name' => 'SMK 1', 'logo_left' => 'settings/logo.png']
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Proses Simpan / Update Pengaturan
     */
    public function update(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            // Data Sekolah
            'school_name' => 'required|string|max:255',
            'logo_left'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'logo_right'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Pengaturan Kertas
            'paper_size'        => 'required|in:a4,letter,f4',
            'paper_orientation' => 'required|in:portrait,landscape',
            'margin_top'        => 'required|numeric|min:0',
            'margin_right'      => 'required|numeric|min:0',
            'margin_bottom'     => 'required|numeric|min:0',
            'margin_left'       => 'required|numeric|min:0',

            // Tanda Tangan
            'signature_city'  => 'required|string',
            'signature_title' => 'required|string',
            'signature_name'  => 'required|string',
            'signature_nip'   => 'nullable|string',
            'info_aplikasi'   => 'nullable|string',
            'provinsi_name'  => 'required|string',
        ]);

        // 2. Daftar File yang akan diproses
        $filesToUpload = [
            'logo_left',      // Logo Kop Surat Kiri (Lama)
            'logo_left_st',      // Logo Kop Surat Kiri (Lama)
            'logo_right',     // Logo Kop Surat Kanan (Lama)
            'logo_right_st',     // Logo Kop Surat Kanan (Lama)
            'app_favicon',    // Favicon Browser (Baru)
            'app_logo',       // Logo Sidebar/Navbar (Baru)
            'signature_image', // Scan Tanda Tangan (Baru)
            'ttd_pejabat',
            'stempel'
        ];

       // 2. Handle Upload Logo (Kiri & Kanan) - Kode lama tetap
        // foreach (['logo_left', 'logo_right'] as $logoKey) {
        //     if ($request->hasFile($logoKey)) {
        //         $oldLogo = Setting::value($logoKey);
        //         if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
        //             Storage::disk('public')->delete($oldLogo);
        //         }
        //         $path = $request->file($logoKey)->store('settings', 'public');
        //         Setting::updateOrCreate(['key' => $logoKey], ['value' => $path]);
        //     }
        // }

        // 3. Simpan Data Teks Lainnya (Termasuk margin, kertas, ttd)

        // 3. Loop Proses Upload File
        foreach ($filesToUpload as $key) {
            if ($request->hasFile($key)) {
                // Hapus file lama
                $oldFile = Setting::value($key);
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }

                // Upload baru
                $path = $request->file($key)->store('settings', 'public');
                Setting::updateOrCreate(['key' => $key], ['value' => $path]);
            }
        }
        $data = $request->except(['_token', '_method', 'logo_left', 'logo_left_st', 'logo_right', 'logo_right_st', 'app_favicon', 'app_logo', 'signature_image', 'ttd_pejabat', 'stempel']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }


        return redirect()->back()->with('success', 'Pengaturan Lengkap Berhasil Disimpan!');
    }

    public function settingAttendance()
{
    $setting = AttendanceSetting::first();
    return view('admin.settings.attendance', compact('setting'));
}

    // public function updateAttendance(Request $request)
    // {
    //     $request->validate([
    //         'late_limit_time' => 'required',
    //         'early_departure_time' => 'required',
    //     ]);

    //     $setting = AttendanceSetting::first();

    //     // Jika belum ada data, buat baru. Jika ada, update.
    //     if(!$setting) {
    //         AttendanceSetting::create($request->all());
    //     } else {
    //         $setting->update([
    //             'late_limit_time' => $request->late_limit_time,
    //             'early_departure_time' => $request->early_departure_time
    //         ]);
    //     }

    //     return back()->with('success', 'Jam operasional absensi berhasil diperbarui!');
    // }

    public function updateAttendance(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'start_check_in_time' => 'required', // Wajib diisi
            'late_limit_time' => 'required|after:start_check_in_time', // Harus setelah jam mulai
            'early_departure_time' => 'required',
        ]);

        // 2. Ambil Data Setting yang ada
        $setting = AttendanceSetting::first();

        // Data yang akan disimpan
        $data = [
            'start_check_in_time' => $request->start_check_in_time,
            'late_limit_time' => $request->late_limit_time,
            'early_departure_time' => $request->early_departure_time,
        ];

        // 3. Simpan atau Update
        if (!$setting) {
            AttendanceSetting::create($data);
        } else {
            $setting->update($data);
        }

        return back()->with('success', 'Jam operasional absensi berhasil diperbarui!');
    }
}
