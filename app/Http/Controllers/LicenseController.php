<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseService;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Tampilan Halaman Aktivasi Lisensi
     */
    public function index()
    {
        $serverSignature = $this->licenseService->getServerSignature();
        $check = $this->licenseService->verifyLicense();

        return view('license.index', compact('serverSignature', 'check'));
    }

    /**
     * Proses Upload File Lisensi (.lic)
     */
    public function upload(Request $request)
{
    $request->validate([
        'license_file' => 'required|file',
    ]);

    $file = $request->file('license_file');

    // Pastikan ekstensi file adalah .lic
    if (strtolower($file->getClientOriginalExtension()) !== 'lic') {
        return redirect()->back()->with('error', 'Format file harus berupa berkas .lic');
    }

    // Pindahkan file langsung ke storage_path('app/license.lic')
    $file->move(storage_path('app'), 'license.lic');

    // Verifikasi lisensi setelah disimpan
    $check = $this->licenseService->verifyLicense();

    if ($check['status']) {
        return redirect()->route('dashboard')->with('success', 'Aplikasi berhasil diaktivasi! Selamat menggunakan.');
    }

    return redirect()->back()->with('error', $check['message']);
}
}