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
use ZipArchive;

class DatabaseController extends Controller
{
    public function index()
    {
        return view('settings.database');
    }

    /**
     * Proses Backup Database (Mendukung Opsi Database Saja ATAU Lengkap dengan Foto/File)
     */
    public function backup(Request $request)
    {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '1024M');

        $includeFiles = $request->input('include_files', '0') === '1';
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "backup-{$timestamp}";
        $connection = config('database.default');

        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $sqlPath = null;

        try {
            // -------------------------------------------------------------
            // 1. GENERATE FILE DATABASE (.sql / .sqlite)
            // -------------------------------------------------------------
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                $sqlPath = "{$backupDir}/{$filename}.sqlite";
                File::copy($dbPath, $sqlPath);
            } elseif ($connection === 'mysql') {
                $sqlPath = "{$backupDir}/{$filename}.sql";
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');

                $command = "\"mysqldump\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$sqlPath}\"";
                @exec($command);

                if (!file_exists($sqlPath) || filesize($sqlPath) === 0) {
                    $sqlDump = $this->generatePgsqlDumpNative();
                    File::put($sqlPath, $sqlDump);
                }
            } elseif ($connection === 'pgsql') {
                $sqlPath = "{$backupDir}/{$filename}.sql";
                $dbName = config('database.connections.pgsql.database');
                $dbUser = config('database.connections.pgsql.username');
                $dbPass = config('database.connections.pgsql.password');
                $dbHost = config('database.connections.pgsql.host');
                $dbPort = config('database.connections.pgsql.port', '5432');

                $pgDumpPath = env('PG_DUMP_PATH', 'pg_dump');
                $execEnabled = function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));

                if ($execEnabled) {
                    putenv("PGPASSWORD={$dbPass}");
                    $command = "\"{$pgDumpPath}\" -U {$dbUser} -h {$dbHost} -p {$dbPort} --inserts --column-inserts {$dbName} > \"{$sqlPath}\" 2>&1";
                    @exec($command);
                    putenv("PGPASSWORD=");
                }

                if (!file_exists($sqlPath) || filesize($sqlPath) < 100) {
                    $sqlDump = $this->generatePgsqlDumpNative();
                    File::put($sqlPath, $sqlDump);
                }
            }

            if (!$sqlPath || !file_exists($sqlPath)) {
                return back()->with('error', 'Gagal memproses file cadangan database.');
            }

            // -------------------------------------------------------------
            // 2. OPSI A: HANYA DATABASE (.sql / .sqlite)
            // -------------------------------------------------------------
            if (!$includeFiles) {
                return response()->download($sqlPath)->deleteFileAfterSend(true);
            }

            // -------------------------------------------------------------
            // 3. OPSI B: LENGKAP DENGAN GAMBAR & FILE (.ZIP)
            // -------------------------------------------------------------
            $zipFileName = "backup-full-{$timestamp}.zip";
            $zipPath = "{$backupDir}/{$zipFileName}";

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // A. Masukkan file DB ke dalam ZIP
                $zip->addFile($sqlPath, basename($sqlPath));

                // B. Pindai & Masukkan seluruh folder media dari public/
                $publicFolders = ['upload', 'images', 'file', 'storage', 'audio'];
                foreach ($publicFolders as $folder) {
                    $dirPath = public_path($folder);
                    if (File::exists($dirPath)) {
                        $files = File::allFiles($dirPath);
                        foreach ($files as $file) {
                            $relativePath = 'public/' . $folder . '/' . $file->getRelativePathname();
                            $zip->addFile($file->getRealPath(), $relativePath);
                        }
                    }
                }

                // C. Pindai & Masukkan dari storage/app/public jika ada
                $storageAppPublic = storage_path('app/public');
                if (File::exists($storageAppPublic)) {
                    $files = File::allFiles($storageAppPublic);
                    foreach ($files as $file) {
                        $relativePath = 'storage/' . $file->getRelativePathname();
                        $zip->addFile($file->getRealPath(), $relativePath);
                    }
                }

                $zip->close();
            }

            // Hapus file SQL temp setelah masuk ke ZIP
            if (file_exists($sqlPath)) {
                File::delete($sqlPath);
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            if ($sqlPath && file_exists($sqlPath)) File::delete($sqlPath);
            Log::error("Backup Error: " . $e->getMessage());
            return back()->with('error', 'Gagal backup: ' . $e->getMessage());
        }
    }

    /**
     * Proses Restore Database (Mendukung File .sql, .sqlite, dan .zip)
     */
    public function restore(Request $request)
    {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '1024M');

        $request->validate([
            'backup_file' => 'required|file|max:102400', // Maksimal 100MB
        ], [
            'backup_file.required' => 'Silakan pilih file backup terlebih dahulu.',
            'backup_file.file'     => 'File yang diunggah tidak valid.',
            'backup_file.uploaded' => 'Gagal mengunggah file. Ukuran file melampaui batas PHP/Nginx.',
            'backup_file.max'      => 'Ukuran file backup terlalu besar (Maksimal 100MB).',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $connection = config('database.default');

        if (!in_array($extension, ['sql', 'sqlite', 'zip'])) {
            return back()->with('error', 'Fitur ini hanya mendukung file .sql, .sqlite, atau .zip');
        }

        try {
            $storedPath = $file->storeAs('temp', 'restore_' . time() . '.' . $extension, 'local');
            $fullPath = Storage::disk('local')->path($storedPath);
            $extractPath = null;

            // 1. JIKA FILE ADALAH ZIP (Ekstrak Foto/File & Temukan File Database)
            if ($extension === 'zip') {
                $zip = new ZipArchive();
                $extractPath = storage_path('app/temp/extracted_' . time());

                if ($zip->open($fullPath) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();

                    // Restore Folder Media ke public/
                    $publicFolders = ['upload', 'images', 'file', 'storage', 'audio'];
                    foreach ($publicFolders as $folder) {
                        $extractedPublicDir = $extractPath . '/public/' . $folder;
                        if (File::exists($extractedPublicDir)) {
                            File::copyDirectory($extractedPublicDir, public_path($folder));
                        }
                    }

                    // Restore Folder storage/app/public jika ada
                    $extractedStorage = $extractPath . '/storage';
                    if (File::exists($extractedStorage)) {
                        File::copyDirectory($extractedStorage, storage_path('app/public'));
                    }

                    // Cari file SQL/SQLite di dalam hasil ekstraksi
                    $allFiles = File::allFiles($extractPath);
                    $sqlFileFound = null;
                    foreach ($allFiles as $extractedFile) {
                        $ext = strtolower($extractedFile->getExtension());
                        if (in_array($ext, ['sql', 'sqlite'])) {
                            $sqlFileFound = $extractedFile->getRealPath();
                            $extension = $ext;
                            break;
                        }
                    }

                    if (!$sqlFileFound) {
                        if (File::exists($extractPath)) File::deleteDirectory($extractPath);
                        Storage::disk('local')->delete($storedPath);
                        return back()->with('error', 'File ZIP tidak berisi file database (.sql / .sqlite).');
                    }

                    $fullPath = $sqlFileFound;
                }
            }

            // 2. RESTORE UNTUK SQLITE
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                File::copy($dbPath, $dbPath . '.bak');
                File::copy($fullPath, $dbPath);

                if (isset($storedPath)) Storage::disk('local')->delete($storedPath);
                if (isset($extractPath) && File::exists($extractPath)) File::deleteDirectory($extractPath);

                $request->session()->invalidate();
                return redirect()->route('login')->with('success', 'Database SQLite berhasil dipulihkan.');
            }

            /** @var \PDO $pdo */
            $pdo = DB::connection()->getPdo();

            // 3. PERSIAPAN DATABASE (PGSQL / MYSQL)
            if ($connection === 'pgsql') {
                try {
                    $pdo->exec("SET session_replication_role = 'replica';");
                } catch (\Exception $e) {
                    Log::notice("Cannot set session_replication_role: " . $e->getMessage());
                }

                $pdo->exec('DROP SCHEMA public CASCADE;');
                $pdo->exec('CREATE SCHEMA public;');
                $pdo->exec('GRANT ALL ON SCHEMA public TO public;');
            } elseif ($connection === 'mysql') {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            }

            // 4. BACA & EKSEKUSI FILE SQL
            $handle = fopen($fullPath, "r");
            if ($handle) {
                $query = '';
                $inCopyMode = false;

                while (($line = fgets($handle)) !== false) {
                    $trimmedLine = trim($line);

                    // Abaikan perintah meta CLI psql (Diawali \)
                    if (strpos($trimmedLine, '\\') === 0) {
                        if ($trimmedLine === '\.') $inCopyMode = false;
                        continue;
                    }

                    // Abaikan Mode COPY Postgres psql
                    if (preg_match('/^COPY\s+.*FROM\s+stdin/i', $trimmedLine)) {
                        $inCopyMode = true;
                        continue;
                    }
                    if ($inCopyMode) continue;

                    // Abaikan Komentar dan Baris Kosong
                    if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '/*') === 0) {
                        continue;
                    }

                    // Abaikan Perintah System/Privilege PGSQL
                    if (preg_match('/^(SET|SELECT pg_catalog|ALTER TABLE.*OWNER TO|GRANT|REVOKE|COMMENT ON)/i', $trimmedLine)) {
                        continue;
                    }

                    $query .= $line;

                    if (substr($trimmedLine, -1) === ';') {
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

            // 5. KEMBALIKAN KONFIGURASI DATABASE
            if ($connection === 'pgsql') {
                try {
                    $pdo->exec("SET session_replication_role = 'origin';");
                } catch (\Exception $e) {}
            } elseif ($connection === 'mysql') {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            }

            // Hapus file temporary
            if (isset($storedPath)) Storage::disk('local')->delete($storedPath);
            if (isset($extractPath) && File::exists($extractPath)) File::deleteDirectory($extractPath);

            DB::reconnect();

            // 6. PENYESUAIAN TABEL SESSIONS (Dukungan UUID user_id)
            if (!Schema::hasTable('sessions')) {
                Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->string('user_id', 36)->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            } else {
                try {
                    DB::statement('ALTER TABLE "sessions" ALTER COLUMN "user_id" TYPE VARCHAR(36) USING user_id::VARCHAR;');
                } catch (\Exception $ex) {}
            }

            // 7. PASTIKAN TABEL USERS TERSEDIA
            if (!Schema::hasTable('users')) {
                Artisan::call('migrate:fresh', ['--force' => true]);
                $request->session()->invalidate();
                return redirect()->route('login')->with('error', 'Gagal Restore: File SQL tidak memuat data tabel users.');
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Database & file media berhasil dipulihkan! Silakan login kembali.');

        } catch (\Exception $e) {
            if (isset($storedPath)) Storage::disk('local')->delete($storedPath);
            if (isset($extractPath) && File::exists($extractPath)) File::deleteDirectory($extractPath);
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