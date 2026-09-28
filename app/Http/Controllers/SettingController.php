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
            // Data Sekolah & Logo
            'school_name'     => 'required|string|max:255',
            'provinsi_name'   => 'required|string',
            'logo_left'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo_left_st'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo_right'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo_right_st'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'app_favicon'     => 'nullable|image|mimes:jpeg,png,jpg,ico,webp|max:1024',
            'app_logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'ttd_pejabat'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'stempel'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

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
        ]);

        // 2. Daftar semua input berjenis File/Gambar/Logo
        $fileKeys = [
            'logo_left',
            'logo_left_st',
            'logo_right',
            'logo_right_st',
            'app_favicon',
            'app_logo',
            'signature_image',
            'ttd_pejabat',
            'stempel',
        ];

        // 3. Loop Upload File/Logo (Hapus file lama jika ada, lalu simpan file baru)
        foreach ($fileKeys as $key) {
            if ($request->hasFile($key)) {
                $oldFile = Setting::where('key', $key)->value('value');
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }

                $path = $request->file($key)->store('settings', 'public');
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $path]
                );
            }
        }

        // 4. Simpan Data Teks / Input Non-File
        $textData = $request->except(array_merge(['_token', '_method'], $fileKeys));

        foreach ($textData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan Lengkap Berhasil Disimpan!');
    }

    /**
     * Tampilkan Halaman Jam Operasional Absensi
     */
    public function settingAttendance()
    {
        $setting = AttendanceSetting::first();
        return view('admin.settings.attendance', compact('setting'));
    }

    /**
     * Proses Simpan / Update Jam Operasional Absensi
     */
    public function updateAttendance(Request $request)
    {
        $request->validate([
            'start_check_in_time'  => 'required',
            'late_limit_time'       => 'required|after:start_check_in_time',
            'early_departure_time' => 'required',
        ]);

        $data = $request->only([
            'start_check_in_time',
            'late_limit_time',
            'early_departure_time',
        ]);

        AttendanceSetting::updateOrCreate(['id' => 1], $data);

        return back()->with('success', 'Jam operasional absensi berhasil diperbarui!');
    }
}
