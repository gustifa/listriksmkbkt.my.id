<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UpdateController extends Controller
{
    public function index()
    {
        // Opsional: Cek versi git saat ini
        $currentHash = trim(shell_exec('git rev-parse --short HEAD'));
        return view('settings.update', compact('currentHash'));
    }

    public function doUpdate()
    {
        // Konfigurasi Repo
        $repoUrl = 'https://github.com/gustifafauzan1986/listrik.git';
        $branch = 'main'; // Sesuaikan jika branch utama anda 'master'

        // 1. Ambil path folder project dan ubah backslash (\) jadi forward slash (/) agar Git Windows paham
        $projectPath = str_replace('\\', '/', base_path());

        // 1. HARD RESET CACHE (PENTING)
        // Kita hapus file cache secara manual agar tidak perlu boot artisan
        $filesToDelete = [
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php')
        ];

        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                @unlink($file); // Hapus file cache
            }
        }

        // Daftar perintah yang akan dijalankan
        $commands = [
            // 1. Setup Git Permission
            "git config --global --add safe.directory \"{$projectPath}\"",
            
            // 2. Tarik kode
            "git pull $repoUrl $branch", 
            
            // 3. Update Vendor (Downgradeable)
            "composer update --no-interaction --prefer-dist --optimize-autoloader",
            
            // 4. PENTING: Hapus Cache DULU sebelum Migrate
            // Agar Laravel membaca .env yang benar (MySQL) dan bukan default (SQLite)
            "php artisan optimize:clear",
            "php artisan config:clear",
            "php artisan cache:clear",
            
            // 5. Baru jalankan Migrate
            "php artisan migrate --force",
            
            // 6. Rapikan view
            "php artisan view:clear"
        ];

        $outputLog = [];
        $basePath = base_path();

        try {
            foreach ($commands as $cmd) {
                // Menjalankan command
                $process = Process::fromShellCommandline("cd {$basePath} && $cmd");
                
                // Set timeout agar proses tidak mati jika internet lambat (default 60s, kita set 300s)
                $process->setTimeout(300);
                
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                $outputLog[] = "SUCCESS: " . $cmd;
                $outputLog[] = $process->getOutput();
            }

            return back()->with('success', 'Aplikasi berhasil diupdate!')->with('log', $outputLog);

        } catch (\Exception $e) {
            // Tampilkan error lengkap
            return back()->with('error', 'Update Gagal: ' . $e->getMessage());
        }
    }
}