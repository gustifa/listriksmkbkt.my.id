<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class Pm2Controller extends Controller
{
    // Mengambil path PM2 dari .env (default ke 'pm2' biasa jika kosong)
    private $pm2_path;

    // Sangat penting di Windows: Arahkan ke folder .pm2 milik User Administrator
    // Agar proses yang berjalan di web terbaca juga saat kamu buka CMD manual
    private $pm2_home = 'C:\Users\ASUS\.pm2';

    public function __construct()
    {
        $this->pm2_path = env('PM2_PATH', 'pm2');
    }
    /**
     * Helper: Mendapatkan path PM2 (Command Utama)
     */
    private function getPm2Command()
    {
        $envPath = env('PM2_PATH');
        if ($envPath) {
            $envPath = trim($envPath, '"\'');
            return "\"{$envPath}\"";
        }

        $paths = [
            getenv('APPDATA') . '\npm\pm2.cmd',
            getenv('ProgramFiles') . '\nodejs\pm2.cmd'
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) return "\"{$path}\"";
        }

        return 'pm2';
    }

    /**
     * Helper: Mendapatkan path PM2-STARTUP (Untuk Service Windows)
     */
    private function getPm2StartupCommand()
    {
        // 1. Cek jika ada override di .env (Prioritas Tertinggi)
        $envPath = env('PM2_STARTUP_PATH');
        if ($envPath) {
            $envPath = trim($envPath, '"\'');
            return "\"{$envPath}\"";
        }

        // 2. Cek lokasi standar npm global di Windows (AppData Roaming)
        $appData = getenv('APPDATA');
        if ($appData) {
            $npmPath = $appData . '\npm\pm2-startup.cmd';
            if (file_exists($npmPath)) {
                return "\"{$npmPath}\"";
            }
        }

        // 3. Fallback ke command global
        return 'pm2-startup';
    }

    /**
     * Helper Eksekusi Command dengan Environment Fix
     */
    private function executeCommand($command)
    {
        // FIX: Tambahkan Environment Variable HOMEPATH agar PM2 jalan di Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (!getenv('HOMEPATH')) {
                $home = env('WINDOWS_USER_HOME', '\Users\Public');
                $drive = env('WINDOWS_USER_DRIVE', 'C:');

                putenv("HOMEDRIVE=$drive");
                putenv("HOMEPATH=$home");
                putenv("PM2_HOME=$drive$home\.pm2");
            }
        }

        return shell_exec($command . " 2>&1");
    }

    // public function index()
    // {
    //     chdir(base_path());
    //     $pm2 = $this->getPm2Command();

    //     $status = $this->executeCommand("$pm2 status");

    //     if (empty($status) || str_contains($status, 'is not recognized')) {
    //          $status .= "\n[SYSTEM INFO] PM2 tidak ditemukan.\nSolusi: Set PM2_PATH di .env";
    //     }

    //     return view('admin.pm2_control', compact('status'));
    // }

    // Contoh Fungsi untuk mendapatkan Status (Tampil di Terminal Hitam)
    public function index()
    {
        $result = Process::env(['PM2_HOME' => $this->pm2_home])
            ->run("{$this->pm2_path} list --no-color"); // --no-color agar output rapi di HTML

        $status = $result->successful() ? $result->output() : null;

        return view('admin.pm2_control', compact('status'));
    }

    // Contoh Fungsi untuk Start Ecosystem
    public function start()
    {
        // Jalankan perintah di folder project (base_path)
        $result = Process::path(base_path())
            ->env(['PM2_HOME' => $this->pm2_home])
            ->run("{$this->pm2_path} start ecosystem.config.cjs");

        if ($result->successful()) {
            return back()->with('success', 'Ecosystem berhasil dijalankan.');
        }

        return back()->with('error', "Gagal menjalankan PM2:\n" . $result->errorOutput());
    }

    // public function start()
    // {
    //     chdir(base_path());
    //     $pm2 = $this->getPm2Command();

    //     $configFile = 'ecosystem.config.js';
    //     if (file_exists(base_path('ecosystem.config.cjs'))) {
    //         $configFile = 'ecosystem.config.cjs';
    //     }

    //     $output = $this->executeCommand("$pm2 start $configFile");

    //     if (str_contains($output, 'module is not defined') || str_contains($output, 'malformated')) {
    //         return redirect()->back()->with('error', "Gagal Start: Format file config salah.\n\nSOLUSI: Ubah nama file 'ecosystem.config.js' menjadi 'ecosystem.config.cjs'.");
    //     }

    //     return redirect()->back()->with('success', 'Start Output: ' . $output);
    // }

    public function restart()
    {
        chdir(base_path());
        $pm2 = $this->getPm2Command();
        $output = $this->executeCommand("$pm2 restart all");
        return redirect()->back()->with('success', 'Restart Output: ' . $output);
    }

    public function stop()
    {
        chdir(base_path());
        $pm2 = $this->getPm2Command();
        $output = $this->executeCommand("$pm2 stop all");
        return redirect()->back()->with('success', 'Stop Output: ' . $output);
    }

    public function delete()
    {
        chdir(base_path());
        $pm2 = $this->getPm2Command();
        $output = $this->executeCommand("$pm2 delete all");
        return redirect()->back()->with('success', 'Delete Output: ' . $output);
    }

    public function save()
    {
        chdir(base_path());
        $pm2 = $this->getPm2Command();
        $output = $this->executeCommand("$pm2 save");

        if (str_contains(strtolower($output), 'cannot find the path') || str_contains(strtolower($output), 'not recognized')) {
             return redirect()->back()->with('error', "Gagal Save. PM2 tidak ditemukan. Output: $output");
        }

        return redirect()->back()->with('success', 'PM2 Save Berhasil. Output: ' . $output);
    }

    public function installService(Request $request)
    {
        chdir(base_path());

        // Gunakan path absolut yang dideteksi otomatis
        $pm2Startup = $this->getPm2StartupCommand();

        $output = $this->executeCommand("$pm2Startup install");

        // Cek error jika command tidak dikenali atau path salah
        if (str_contains(strtolower($output), 'not recognized') ||
            str_contains(strtolower($output), 'bukan perintah') ||
            str_contains(strtolower($output), 'cannot find the path')) {

            return redirect()->back()->with('error',
                "GAGAL: Sistem tidak dapat menemukan file '$pm2Startup'.\n\n" .
                "Output: $output\n\n" .
                "SOLUSI MANUAL:\n" .
                "1. Buka CMD di komputer server.\n" .
                "2. Ketik perintah: where pm2-startup\n" .
                "3. Copy path yang muncul\n" .
                "4. Buka file .env di Laravel dan tambahkan baris:\n" .
                "PM2_STARTUP_PATH=\"PATH_YANG_ANDA_COPY\"\n" .
                "5. Jalankan: php artisan config:clear lalu coba lagi tombol ini."
            );
        }

        // Cek jika gagal karena permission (Access Denied)
        if (str_contains(strtolower($output), 'access is denied') || str_contains(strtolower($output), 'eacces')) {
            return redirect()->back()->with('error',
                "GAGAL INSTALL SERVICE: Akses Ditolak.\n\n" .
                "PENTING: PHP/Terminal harus dijalankan sebagai ADMINISTRATOR untuk menginstal Service Windows."
            );
        }

        // 2. Rename Service Display Name (Fitur Baru)
        if ($request->filled('service_name')) {
            // Tunggu sebentar agar service terdaftar di Windows Registry sebelum direname (Fix error 1060)
            sleep(3); // Increased sleep time

            // Bersihkan nama dari karakter aneh
            $safeName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $request->service_name);

            // Coba rename service 'PM2' (Default)
            $renameCmd = "sc config PM2 DisplayName= \"{$safeName}\"";
            $renameOutput = shell_exec($renameCmd . " 2>&1");

            // Jika gagal dengan error 1060 (Service not found), coba lowercase 'pm2'
            if (str_contains($renameOutput, '1060')) {
                 sleep(1); // Wait a bit more before retrying
                 $renameCmd = "sc config pm2 DisplayName= \"{$safeName}\"";
                 $renameOutput = shell_exec($renameCmd . " 2>&1");
            }

            $output .= "\n\n[RENAME SERVICE] $renameOutput";
        }

        return redirect()->back()->with('success', 'Windows Service Berhasil Diinstal! Aplikasi akan otomatis jalan saat Windows restart. Output: ' . $output);
    }

    public function uninstallService()
    {
        chdir(base_path());

        $pm2Startup = $this->getPm2StartupCommand();
        $output = $this->executeCommand("$pm2Startup uninstall");

        return redirect()->back()->with('success', 'Windows Service Dihapus. Output: ' . $output);
    }
}
