<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

class UpdateController extends Controller
{
    public function index()
    {
        $detected = $this->autoDetectPaths();
        $paths = $this->getExecPaths();
        $currentHash = trim(@shell_exec("{$paths['git']} rev-parse --short HEAD") ?? 'Unknown');

        return view('settings.update', compact('currentHash', 'detected'));
    }

    /**
     * Otomatis Mendeteksi Path PHP & Git di Sistem Server/Local
     */
    private function autoDetectPaths(): array
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Ambil dari .env atau gunakan deteksi otomatis
        $php = env('SYSTEM_PHP_PATH');
        $git = env('SYSTEM_GIT_PATH');

        if (!$php) {
            if ($isWindows) {
                $php = PHP_BINARY ? str_replace('\\', '/', PHP_BINARY) : 'php';
            } else {
                $php = trim(@shell_exec('which php') ?? 'php');
            }
        }

        if (!$git) {
            if ($isWindows) {
                // Cek tempat umum instalasi Git di Windows
                $commonPaths = [
                    'C:/Program Files/Git/cmd/git.exe',
                    'C:/Program Files (x86)/Git/cmd/git.exe',
                    'D:/nginx/pgsql/bin/git.exe',
                ];
                $git = 'git';
                foreach ($commonPaths as $path) {
                    if (file_exists($path)) {
                        $git = $path;
                        break;
                    }
                }
            } else {
                $git = trim(@shell_exec('which git') ?? 'git');
            }
        }

        return [
            'php' => $php,
            'git' => $git,
            'is_windows' => $isWindows
        ];
    }

    /**
     * Simpan Path Executable ke File .env
     */
    public function savePaths(Request $request)
    {
        $request->validate([
            'system_php_path' => 'nullable|string',
            'system_git_path' => 'nullable|string',
        ]);

        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);

            $this->setEnvKey($envContent, 'SYSTEM_PHP_PATH', $request->system_php_path);
            $this->setEnvKey($envContent, 'SYSTEM_GIT_PATH', $request->system_git_path);

            File::put($envPath, $envContent);
        }

        return back()->with('success', 'Konfigurasi System Path berhasil disimpan!');
    }

    private function setEnvKey(&$envContent, $key, $value)
    {
        $value = '"' . trim(str_replace('"', '', $value)) . '"';
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}";
        }
    }

    private function getExecPaths(): array
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $detected = $this->autoDetectPaths();

        $php = env('SYSTEM_PHP_PATH', $detected['php']);
        $git = env('SYSTEM_GIT_PATH', $detected['git']);
        $composer = env('SYSTEM_COMPOSER_PATH', 'composer');

        if ($isWindows) {
            return [
                'php' => "\"{$php}\"",
                'git' => "\"{$git}\"",
                'composer' => "\"{$composer}\"",
                'is_win' => true
            ];
        }

        return [
            'php' => $php ?: 'php',
            'git' => $git ?: 'git',
            'composer' => $composer ?: 'composer',
            'is_win' => false
        ];
    }

    /**
     * Process Auto Update Dual Environment
     */
    public function doUpdate(Request $request)
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        $paths = $this->getExecPaths();
        $php = $paths['php'];
        $git = $paths['git'];
        $composer = $paths['composer'];

        $branch = config('app.git_branch', 'main');
        $basePath = base_path();
        $projectPath = str_replace('\\', '/', $basePath);

        // Hapus cache bootstrap manual
        $cacheFiles = [
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
            base_path('bootstrap/cache/routes-v7.php'),
        ];
        foreach ($cacheFiles as $file) {
            if (file_exists($file)) @unlink($file);
        }

        $cdCommand = $paths['is_win'] ? "cd /d \"{$basePath}\"" : "cd \"{$basePath}\"";

        // Mencegah error '$HOME not set' di Windows Web Server / Nginx
        $envVars = [];
        if ($paths['is_win']) {
            $userProfile = getenv('USERPROFILE') ?: 'C:\\Users\\Public';
            putenv("HOME={$userProfile}");
            putenv("USERPROFILE={$userProfile}");
            $envVars = [
                'HOME' => $userProfile,
                'USERPROFILE' => $userProfile,
            ];
        }

        $commands = [
            // Gunakan --system atau safe.directory lokasi spesifik
            "{$git} config --system --add safe.directory \"{$projectPath}\"",
            "{$git} fetch --all",
            "{$git} reset --hard origin/{$branch}",
            "{$composer} install --no-interaction --prefer-dist --optimize-autoloader --no-dev",
            "{$php} artisan optimize:clear",
            "{$php} artisan migrate --force",
            "{$php} artisan config:cache",
            "{$php} artisan route:cache",
            "{$php} artisan view:clear"
        ];

        $outputLog = [];

        try {
            foreach ($commands as $cmd) {
                // Pass $envVars ke Process agar Git mengenali folder HOME
                $process = Process::fromShellCommandline("{$cdCommand} && {$cmd}", null, $envVars);
                $process->setTimeout(300);
                $process->run();

                $outputLog[] = "$ " . $cmd;
                $outputLog[] = $process->getOutput() ?: $process->getErrorOutput();

                // Abaikan jika git config --system mengeluarkan warning minor
                if (!$process->isSuccessful() && !str_contains($cmd, 'git config')) {
                    throw new \Exception("Gagal pada perintah: {$cmd}\n" . $process->getErrorOutput());
                }
            }

            return back()->with('success', 'Aplikasi berhasil diupdate!')->with('log', $outputLog);

        } catch (\Exception $e) {
            return back()->with('error', 'Update Gagal: ' . $e->getMessage())->with('log', $outputLog);
        }
    }

    public function executeTerminal(Request $request)
    {
        $request->validate(['command' => 'required|string']);

        $command = trim($request->command);
        $basePath = base_path();
        $paths = $this->getExecPaths();
        $cdCommand = $paths['is_win'] ? "cd /d \"{$basePath}\"" : "cd \"{$basePath}\"";

        if (str_starts_with($command, 'php artisan')) {
            $command = str_replace('php artisan', "{$paths['php']} artisan", $command);
        } elseif (str_starts_with($command, 'git')) {
            $command = substr_replace($command, $paths['git'], 0, 3);
        } elseif (str_starts_with($command, 'composer')) {
            $command = substr_replace($command, $paths['composer'], 0, 8);
        }

        try {
            $process = Process::fromShellCommandline("{$cdCommand} && {$command}");
            $process->setTimeout(300);
            $process->run();

            $output = $process->getOutput() ?: $process->getErrorOutput();

            return response()->json([
                'status' => 'success',
                'command' => $request->command,
                'output' => $output ?: 'Perintah berhasil dieksekusi tanpa output.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'output' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}