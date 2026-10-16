<?php

namespace App\Listeners;

use App\Models\SystemSetting;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BindStudentDeviceOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Pengecekan khusus untuk role siswa
        if ($user->hasRole('siswa')) {
            $settings = SystemSetting::pluck('value', 'key');
            $enableLock = $settings['enable_device_lock'] ?? '0';
            $maxDevices = (int)($settings['max_allowed_devices'] ?? '1');

            if ($enableLock === '1') {
                // Ambil cookie UUID perangkat dari request
                $deviceToken = request()->cookie('student_device_token');

                if (!$deviceToken) {
                    $deviceToken = (string) Str::uuid();
                    // Simpan cookie di browser siswa selama 1 tahun (525.600 menit)
                    Cookie::queue('student_device_token', $deviceToken, 525600);
                }

                // Cek apakah UUID perangkat ini sudah terdaftar di slot 1 atau 2
                $isRegistered = ($user->device_id_1 === $deviceToken) || ($user->device_id_2 === $deviceToken);

                if (!$isRegistered) {
                    // Daftarkan ke slot 1 jika masih kosong
                    if (is_null($user->device_id_1)) {
                        $user->forceFill(['device_id_1' => $deviceToken])->save();
                    } 
                    // Daftarkan ke slot 2 jika slot 1 terisi & max_devices == 2
                    elseif ($maxDevices === 2 && is_null($user->device_id_2)) {
                        $user->forceFill(['device_id_2' => $deviceToken])->save();
                    } 
                    // Slot penuh -> Logout-kan pengguna & Lempar Exception
                    else {
                        auth()->logout();
                        session()->invalidate();
                        session()->regenerateToken();

                        $errorMsg = ($maxDevices === 1)
                            ? 'Akun Anda terkunci pada HP lain! Silakan hubungi Admin untuk reset HP.'
                            : 'Batas maksimal 2 HP telah tercapai! Silakan hubungi Admin untuk reset HP.';

                        // Simpan pesan error ke session agar bisa dibaca SweetAlert2 / View
                        session()->flash('device_error', $errorMsg);

                        // Lempar ValidationException agar Livewire/Fortify menangani error dengan aman
                        throw ValidationException::withMessages([
                            'email' => [$errorMsg],
                        ]);
                    }
                }
            }
        }
    }
}