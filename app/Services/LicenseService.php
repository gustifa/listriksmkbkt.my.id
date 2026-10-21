<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LicenseService
{
    /**
     * Mendapatkan Unik Server Signature (Machine ID)
     */
    public function getServerSignature(): string
    {
        $hostname = gethostname() ?: 'unknown_host';
        $ip = gethostbyname($hostname) ?: '127.0.0.1';
        
        // Mengambil MAC Address pada OS Linux/Windows
        $mac = '00:00:00:00:00:00';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @exec('getmac', $output);
            $mac = $output[0] ?? $mac;
        } else {
            @exec('netstat -i', $output);
            $mac = $output[0] ?? $mac;
        }

        return strtoupper(md5($hostname . '|' . $ip . '|' . $mac));
    }

    /**
     * Verifikasi Masa Berlaku & Identitas Lisensi
     */
    public function verifyLicense(): array
    {
        $licensePath = storage_path('app/license.lic');

        if (!file_exists($licensePath)) {
            return ['status' => false, 'message' => 'File lisensi (license.lic) tidak ditemukan.'];
        }

        $licenseContent = file_get_contents($licensePath);
        $data = json_decode(base64_decode($licenseContent), true);

        if (!$data || !isset($data['signature'], $data['expires_at'], $data['client_name'])) {
            return ['status' => false, 'message' => 'Format file lisensi tidak valid.'];
        }

        // Cek apakah lisensi cocok dengan Server Signature saat ini
        if ($data['signature'] !== $this->getServerSignature()) {
            return ['status' => false, 'message' => 'Lisensi ini tidak terdaftar untuk server/komputer ini.'];
        }

        // Cek masa berlaku
        if (now()->greaterThan($data['expires_at'])) {
            return ['status' => false, 'message' => 'Masa berlaku lisensi Anda telah kadaluarsa pada ' . $data['expires_at']];
        }

        return [
            'status' => true,
            'client_name' => $data['client_name'],
            'expires_at' => $data['expires_at'],
            'message' => 'Lisensi Aktif'
        ];
    }
}