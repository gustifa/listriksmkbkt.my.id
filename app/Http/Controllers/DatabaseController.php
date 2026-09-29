<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan; // Tambahkan Facade Artisan
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    /**
     * Tampilkan Halaman Backup/Restore
     */
    public function index()
    {
        return view('settings.database');
    }

    /**
     * Proses Backup Database (Download SQL/SQLite)
     */
    public function backup()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d-H-i-s');
        $connection = config('database.default');

        // Pastikan folder penyimpanan ada
        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        if ($connection === 'sqlite') {
            // --- LOGIKA SQLITE ---
            $dbPath = config('database.connections.sqlite.database');
            $backupPath = storage_path("app/backups/{$filename}.sqlite");

            if (File::copy($dbPath, $backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Gagal backup file SQLite.');
            }

        } elseif ($connection === 'mysql') {
            // --- LOGIKA MYSQL ---
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            $filename = $filename . ".sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // Perintah mysqldump
            // Tambahkan path manual jika di windows (opsional, sesuaikan path XAMPP Anda)
            // $dumpPath = "C:/xampp/mysql/bin/mysqldump.exe"; 
            $dumpPath = "mysqldump"; // Default global path

            $command = "\"{$dumpPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$backupPath}\"";

            $returnVar = NULL;
            $output  = NULL;
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Gagal backup MySQL. Pastikan mysqldump terinstall dan ada di PATH.');
            }

        } elseif ($connection === 'pgsql') {
            // --- LOGIKA POSTGRESQL ---
            $dbName = config('database.connections.pgsql.database');
            $dbUser = config('database.connections.pgsql.username');
            $dbPass = config('database.connections.pgsql.password');
            $dbHost = config('database.connections.pgsql.host');
            $dbPort = config('database.connections.pgsql.port', '5432');

            $filename = $filename . ".sql";
            $backupPath = storage_path("app/backups/{$filename}");

            // 1. Definisikan Path pg_dump (Sesuaikan dengan instalasi Anda)
            // Jika di Windows/Laragon, ganti dengan path lengkap, misal: "C:/Program Files/PostgreSQL/15/bin/pg_dump.exe"
            $pgDumpPath = env('PG_DUMP_PATH', 'pg_dump'); 

            // 2. Set password environment variable
            putenv("PGPASSWORD={$dbPass}");

            // 3. Susun perintah (Tambahkan 2>&1 untuk menangkap error log)
            $command = "\"{$pgDumpPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} > \"{$backupPath}\" 2>&1";

            $returnVar = NULL;
            $output  = [];
            
            // Eksekusi
            exec($command, $output, $returnVar);

            // Bersihkan password dari memori
            putenv("PGPASSWORD=");

            if ($returnVar === 0 && file_exists($backupPath) && filesize($backupPath) > 0) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            } else {
                // Log Error Output untuk debugging
                Log::error("Backup PostgreSQL Gagal. Output: " . implode("\n", $output));
                
                return back()->with('error', 'Gagal backup PostgreSQL. Cek Log Laravel untuk detail error (kemungkinan path pg_dump salah).');
            }
        }

        return back()->with('error', 'Tipe database tidak didukung untuk fitur ini.');
    }

    /**
     * Proses Import / Restore Database
     */
    // public function restore(Request $request)
    // {
    //     // Tingkatkan limit untuk file besar
    //     ini_set('max_execution_time', 300); 
    //     ini_set('memory_limit', '512M');

    //     $request->validate([
    //         'backup_file' => 'required|file'
    //     ]);

    //     $file = $request->file('backup_file');
    //     $extension = $file->getClientOriginalExtension();
    //     $connection = config('database.default');

    //     try {
    //         if ($connection === 'sqlite' && $extension === 'sqlite') {
    //             // Restore SQLite
    //             $dbPath = config('database.connections.sqlite.database');
    //             File::copy($dbPath, $dbPath . '.bak'); // Buat backup file lama
    //             $file->move(dirname($dbPath), basename($dbPath));
    //             return back()->with('success', 'Database SQLite berhasil dipulihkan.');

    //         } elseif ($connection === 'mysql' && $extension === 'sql') {
    //             // Restore MySQL
    //             $dbName = config('database.connections.mysql.database');
    //             $dbUser = config('database.connections.mysql.username');
    //             $dbPass = config('database.connections.mysql.password');
    //             $dbHost = config('database.connections.mysql.host');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Tambahkan path mysql manual jika perlu
    //             $mysqlPath = "mysql"; 
                
    //             $command = "\"{$mysqlPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} < \"{$fullPath}\"";

    //             $returnVar = NULL;
    //             $output  = NULL;
    //             exec($command, $output, $returnVar);
    //             unlink($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database MySQL berhasil dipulihkan.');
    //             } else {
    //                 return back()->with('error', 'Gagal restore MySQL.');
    //             }

    //         } elseif ($connection === 'pgsql' && $extension === 'sql') {
    //             // --- LOGIKA RESTORE POSTGRESQL ---
    //             $dbName = config('database.connections.pgsql.database');
    //             $dbUser = config('database.connections.pgsql.username');
    //             $dbPass = config('database.connections.pgsql.password');
    //             $dbHost = config('database.connections.pgsql.host');
    //             $dbPort = config('database.connections.pgsql.port', '5432');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Path psql (bisa diset di .env PG_PSQL_PATH)
    //             $psqlPath = env('PG_PSQL_PATH', 'psql');

    //             putenv("PGPASSWORD={$dbPass}");

    //             // Perintah restore
    //             $command = "\"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} < \"{$fullPath}\" 2>&1";

    //             $returnVar = NULL;
    //             $output  = [];
    //             exec($command, $output, $returnVar);
                
    //             putenv("PGPASSWORD=");
    //             unlink($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database PostgreSQL berhasil dipulihkan.');
    //             } else {
    //                 Log::error("Restore PostgreSQL Gagal. Output: " . implode("\n", $output));
    //                 return back()->with('error', 'Gagal restore PostgreSQL. Cek Log Laravel.');
    //             }

    //         } else {
    //             return back()->with('error', 'Format file tidak cocok dengan database yang digunakan.');
    //         }

    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    /**
     * Proses Import / Restore Database
     */
    // public function restore(Request $request)
    // {
    //     // Tingkatkan limit untuk file besar
    //     ini_set('max_execution_time', 300); 
    //     ini_set('memory_limit', '512M');

    //     $request->validate([
    //         'backup_file' => 'required|file'
    //     ]);

    //     $file = $request->file('backup_file');
    //     $extension = $file->getClientOriginalExtension();
    //     $connection = config('database.default');

    //     try {
    //         if ($connection === 'sqlite' && $extension === 'sqlite') {
    //             // Restore SQLite
    //             $dbPath = config('database.connections.sqlite.database');
    //             File::copy($dbPath, $dbPath . '.bak'); // Buat backup file lama
    //             $file->move(dirname($dbPath), basename($dbPath));
    //             return back()->with('success', 'Database SQLite berhasil dipulihkan.');

    //         } elseif ($connection === 'mysql' && $extension === 'sql') {
    //             // Restore MySQL
    //             $dbName = config('database.connections.mysql.database');
    //             $dbUser = config('database.connections.mysql.username');
    //             $dbPass = config('database.connections.mysql.password');
    //             $dbHost = config('database.connections.mysql.host');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Tambahkan path mysql manual jika perlu
    //             $mysqlPath = "mysql"; 
                
    //             $command = "\"{$mysqlPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} < \"{$fullPath}\"";

    //             $returnVar = NULL;
    //             $output  = NULL;
    //             exec($command, $output, $returnVar);
                
    //             // Gunakan File::delete agar tidak error jika file sudah hilang/terkunci
    //             File::delete($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database MySQL berhasil dipulihkan.');
    //             } else {
    //                 return back()->with('error', 'Gagal restore MySQL.');
    //             }

    //         } elseif ($connection === 'pgsql' && $extension === 'sql') {
    //             // --- LOGIKA RESTORE POSTGRESQL ---
    //             $dbName = config('database.connections.pgsql.database');
    //             $dbUser = config('database.connections.pgsql.username');
    //             $dbPass = config('database.connections.pgsql.password');
    //             $dbHost = config('database.connections.pgsql.host');
    //             $dbPort = config('database.connections.pgsql.port', '5432');

    //             $path = $file->storeAs('temp', 'restore.sql');
    //             $fullPath = storage_path('app/' . $path);

    //             // Path psql (bisa diset di .env PG_PSQL_PATH)
    //             $psqlPath = env('PG_PSQL_PATH', 'psql');

    //             putenv("PGPASSWORD={$dbPass}");

    //             // Perintah restore
    //             $command = "\"{$psqlPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} {$dbName} < \"{$fullPath}\" 2>&1";

    //             $returnVar = NULL;
    //             $output  = [];
    //             exec($command, $output, $returnVar);
                
    //             putenv("PGPASSWORD=");
                
    //             // Gunakan File::delete agar lebih aman
    //             File::delete($fullPath);

    //             if ($returnVar === 0) {
    //                 return back()->with('success', 'Database PostgreSQL berhasil dipulihkan.');
    //             } else {
    //                 Log::error("Restore PostgreSQL Gagal. Output: " . implode("\n", $output));
    //                 return back()->with('error', 'Gagal restore PostgreSQL. Cek Log Laravel.');
    //             }

    //         } else {
    //             return back()->with('error', 'Format file tidak cocok dengan database yang digunakan.');
    //         }

    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

     /**
     * Proses Import / Restore Database
     */
     /**
     * Proses Import / Restore Database
     */
    
    public function restore(Request $request)
{
    ini_set('max_execution_time', 600);
    ini_set('memory_limit', '1024M');

    $request->validate(['backup_file' => 'required|file']);

    $file = $request->file('backup_file');
    $extension = strtolower($file->getClientOriginalExtension());

    if ($extension !== 'sql') {
        return back()->with('error', 'Fitur ini hanya mendukung file .sql');
    }

    $dbConfig = config('database.connections.pgsql');
    $psqlPath = env('PG_PSQL_PATH', 'psql');
    $storedPath = null;

    try {
        // 1. Simpan File Temp
        $filename = 'restore_' . time() . '.sql';
        $storedPath = $file->storeAs('temp', $filename, 'local'); 
        $fullPath = Storage::disk('local')->path($storedPath);

        if (!file_exists($fullPath) || filesize($fullPath) < 50) {
            if ($storedPath) Storage::disk('local')->delete($storedPath);
            return back()->with('error', 'File backup tidak valid atau kosong.');
        }

        // 2. WIPE TOTAL SCHEMA PUBLIC (Hapus Semua Tabel & Sequence)
        // Menggunakan CASCADE agar tidak terkendala foreign key
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        DB::statement('GRANT ALL ON SCHEMA public TO public');

        // 3. Eksekusi Restore via CLI psql
        $output = [];
        $returnVar = 0;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmdPath = str_replace('/', '\\', $fullPath);
            $command = "set PGPASSWORD={$dbConfig['password']} && \"{$psqlPath}\" -U {$dbConfig['username']} -h {$dbConfig['host']} -p {$dbConfig['port']} -d {$dbConfig['database']} -f \"{$cmdPath}\" 2>&1";
        } else {
            putenv("PGPASSWORD={$dbConfig['password']}");
            $command = "\"{$psqlPath}\" -U {$dbConfig['username']} -h {$dbConfig['host']} -p {$dbConfig['port']} -d {$dbConfig['database']} -f \"{$fullPath}\" 2>&1";
        }

        exec($command, $output, $returnVar);

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            putenv("PGPASSWORD=");
        }

        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }

        // 4. Verifikasi Hasil Restore
        if (!Schema::hasTable('users')) {
            Log::error("Restore psql gagal. Output: " . implode("\n", array_slice($output, -10)));
            Artisan::call('migrate:fresh', ['--force' => true]); 
            return redirect()->route('login')->with('error', 'Gagal Restore: File SQL tidak dapat dipulihkan. Database di-reset ke default.');
        }

        // 5. Invalidate / Flush Session Pengguna & Reconnect
        $request->session()->flush();
        $request->session()->regenerate();
        DB::reconnect();

        return redirect()->route('login')->with('success', 'Database berhasil dipulihkan! Silakan login kembali.');

    } catch (\Exception $e) {
        if ($storedPath) {
            Storage::disk('local')->delete($storedPath);
        }

        if (!Schema::hasTable('users')) {
            Artisan::call('migrate:fresh', ['--force' => true]);
        }

        Log::error("Restore Exception: " . $e->getMessage());
        return redirect()->route('login')->with('error', 'Gagal Restore Database: ' . $e->getMessage());
    }
}

}