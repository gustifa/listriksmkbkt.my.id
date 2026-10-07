<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseController extends Controller
{
    public function index()
    {
        return view('settings.database');
    }

    /**
     * Proses Backup Database (Support Native PHP Fallback untuk PGSQL)
     */
    public function backup()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d-H-i-s');
        $connection = config('database.default');

        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        // --- BACKUP SQLITE ---
        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            $backupPath = storage_path("app/backups/{$filename}.sqlite");

            if (File::copy($dbPath, $backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Gagal backup file SQLite.');
        }

        // --- BACKUP MYSQL ---
        if ($connection === 'mysql') {
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            $filename .= ".sql";
            $backupPath = storage_path("app/backups/{$filename}");
            $dumpPath = "mysqldump";

            $command = "\"{$dumpPath}\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$backupPath}\"";

            $returnVar = NULL;
            @exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($backupPath)) {
                return response()->download($backupPath)->deleteFileAfterSend(true);
            }
            return back()->with('error', 'Gagal backup MySQL.');
        }

        // --- BACKUP POSTGRESQL ---
        if ($connection === 'pgsql') {
            $dbName = config('database.connections.pgsql.database');
            $dbUser = config('database.connections.pgsql.username');
            $dbPass = config('database.connections.pgsql.password');
            $dbHost = config('database.connections.pgsql.host');
            $dbPort = config('database.connections.pgsql.port', '5432');

            $filename .= ".sql";
            $backupPath = storage_path("app/backups/{$filename}");

            $pgDumpPath = env('PG_DUMP_PATH', 'pg_dump'); 
            $execEnabled = function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));

            // Metode 1: Coba via CLI pg_dump
            if ($execEnabled) {
                putenv("PGPASSWORD={$dbPass}");
                $command = "\"{$pgDumpPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} --inserts --column-inserts {$dbName} > \"{$backupPath}\" 2>&1";

                $returnVar = NULL;
                $output = [];
                @exec($command, $output, $returnVar);
                putenv("PGPASSWORD=");

                if ($returnVar === 0 && file_exists($backupPath) && filesize($backupPath) > 100) {
                    return response()->download($backupPath)->deleteFileAfterSend(true);
                }
            }

            // Metode 2: Native PHP Backup Generator (Fallback tanpa CLI pg_dump)
            try {
                $sqlDump = $this->generatePgsqlDumpNative();
                File::put($backupPath, $sqlDump);

                if (file_exists($backupPath) && filesize($backupPath) > 0) {
                    return response()->download($backupPath)->deleteFileAfterSend(true);
                }
            } catch (\Exception $e) {
                Log::error("Native PGSQL Backup Error: " . $e->getMessage());
            }

            return back()->with('error', 'Gagal backup PostgreSQL. Cek Log Laravel.');
        }

        return back()->with('error', 'Tipe database tidak didukung.');
    }

    /**
     * Proses Restore Database
     */
    public function restore(Request $request)
    {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '1024M');

        $request->validate(['backup_file' => 'required|file']);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $connection = config('database.default');

        if (!in_array($extension, ['sql', 'sqlite'])) {
            return back()->with('error', 'Fitur ini hanya mendukung file .sql atau .sqlite');
        }

        try {
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                File::copy($dbPath, $dbPath . '.bak');
                $file->move(dirname($dbPath), basename($dbPath));

                $request->session()->invalidate();
                return redirect()->route('login')->with('success', 'Database SQLite berhasil dipulihkan.');
            }

            $filename = 'restore_' . time() . '.sql';
            $storedPath = $file->storeAs('temp', $filename, 'local'); 
            $fullPath = Storage::disk('local')->path($storedPath);

            if (!file_exists($fullPath) || filesize($fullPath) < 10) {
                if ($storedPath) Storage::disk('local')->delete($storedPath);
                return back()->with('error', 'File backup tidak valid.');
            }

            $pdo = DB::connection()->getPdo();

            if ($connection === 'pgsql') {
                $pdo->exec("SET session_replication_role = 'replica';");
                $pdo->exec('DROP SCHEMA public CASCADE;');
                $pdo->exec('CREATE SCHEMA public;');
                $pdo->exec('GRANT ALL ON SCHEMA public TO public;');
            } elseif ($connection === 'mysql') {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            }

            $handle = fopen($fullPath, "r");
            if ($handle) {
                $query = '';
                $inCopyMode = false;

                while (($line = fgets($handle)) !== false) {
                    $trimmedLine = trim($line);

                    if (preg_match('/^COPY\s+.*FROM\s+stdin/i', $trimmedLine)) {
                        $inCopyMode = true;
                        continue;
                    }
                    if ($inCopyMode) {
                        if ($trimmedLine === '\.') $inCopyMode = false;
                        continue;
                    }

                    if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '/*') === 0) {
                        continue;
                    }

                    if (preg_match('/^(SET|SELECT pg_catalog|ALTER TABLE.*OWNER TO|GRANT|REVOKE)/i', $trimmedLine)) {
                        continue;
                    }

                    $query .= $line;

                    if (substr(trim($query), -1) === ';') {
                        try {
                            $pdo->exec($query);
                        } catch (\Exception $e) {
                            Log::warning("Restore SQL Warning: " . $e->getMessage());
                        }
                        $query = '';
                    }
                }
                fclose($handle);
            }

            if ($connection === 'pgsql') {
                $pdo->exec("SET session_replication_role = 'origin';");
            } elseif ($connection === 'mysql') {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            }

            if ($storedPath) Storage::disk('local')->delete($storedPath);

            DB::reconnect();

            // Pastikan tabel sessions & users ada
            if (!Schema::hasTable('sessions')) {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/0001_01_01_000000_create_users_table.php',
                    '--force' => true
                ]);
            }

            if (!Schema::hasTable('users')) {
                Artisan::call('migrate:fresh', ['--force' => true]);
                $request->session()->invalidate();
                return redirect()->route('login')->with('error', 'Gagal Restore: File SQL tidak valid.');
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Database berhasil dipulihkan! Silakan login kembali.');

        } catch (\Exception $e) {
            if (isset($storedPath)) Storage::disk('local')->delete($storedPath);
            Log::error("Restore Exception: " . $e->getMessage());

            if (!Schema::hasTable('users') || !Schema::hasTable('sessions')) {
                try { Artisan::call('migrate:fresh', ['--force' => true]); } catch (\Exception $ex) {}
            }

            try { $request->session()->invalidate(); } catch (\Exception $ex) {}
            return redirect()->route('login')->with('error', 'Gagal Restore Database: ' . $e->getMessage());
        }
    }

    /**
     * Native Backup Engine untuk PostgreSQL menggunakan Pure PHP PDO
     */
    private function generatePgsqlDumpNative(): string
    {
        $pdo = DB::connection()->getPdo();
        $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);

        $out = "-- PostgreSQL Native Dump PHP\n";
        $out .= "-- Created at: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            // Get Create Table Statement Structure
            $columns = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema='public' AND table_name='{$table}' ORDER BY ordinal_position")->fetchAll(\PDO::FETCH_ASSOC);

            $out .= "DROP TABLE IF EXISTS \"{$table}\" CASCADE;\n";
            $out .= "CREATE TABLE \"{$table}\" (\n";
            $colDefs = [];

            foreach ($columns as $col) {
                $def = "  \"{$col['column_name']}\" " . strtoupper($col['data_type']);
                if ($col['is_nullable'] === 'NO') $def .= " NOT NULL";
                if ($col['column_default'] !== null) $def .= " DEFAULT " . $col['column_default'];
                $colDefs[] = $def;
            }

            $out .= implode(",\n", $colDefs);
            $out .= "\n);\n\n";

            // Get Rows
            $rows = $pdo->query("SELECT * FROM \"{$table}\"")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $vals = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, array_values($row));

                $colNames = implode('", "', $cols);
                $valValues = implode(', ', $vals);

                $out .= "INSERT INTO \"{$table}\" (\"{$colNames}\") VALUES ({$valValues});\n";
            }
            $out .= "\n";
        }

        return $out;
    }
}