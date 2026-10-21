<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\StudentViolation;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\User;
use App\Models\SyncLog;
use App\Models\Setting;
use App\Models\PrayerAttendance;
use ZipArchive;

class DataSyncService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $urlSetting = Setting::where('key', 'sync_server_url')->value('value');
        $this->baseUrl = rtrim($urlSetting ?? config('services.online_server.url', ''), '/');
        
        $tokenSetting = Setting::where('key', 'sync_api_token')->value('value');
        $this->token = trim($tokenSetting ?? config('services.online_server.token', ''));
    }

    /**
     * Eksekusi Push (Lokal ke Online)
     */
    public function pushLocalData(): array
    {
        if (empty($this->baseUrl)) {
            return ['status' => 'error', 'message' => 'URL Server Online belum diatur. Silakan atur URL terlebih dahulu.'];
        }

        if (empty($this->token)) {
            return ['status' => 'error', 'message' => 'API Token belum diatur. Silakan masukkan token dari Server Online.'];
        }

        $dailyAttendances = DailyAttendance::where('is_synced', false)->limit(100)->get();
        $attendances = Attendance::where('is_synced', false)->limit(100)->get();
        $violations = StudentViolation::where('is_synced', false)->limit(100)->get();

        $totalRecords = $dailyAttendances->count() + $attendances->count() + $violations->count();

        if ($totalRecords === 0) {
            return ['status' => 'info', 'message' => 'Tidak ada data transaksi baru untuk dikirim.'];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->acceptJson()
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/sync/push", [
                    'daily_attendances' => $dailyAttendances->toArray(),
                    'attendances' => $attendances->toArray(),
                    'student_violations' => $violations->toArray(),
                ]);

            if ($response->successful()) {
                $processed = $response->json('processed_ids');

                if (!empty($processed['daily_attendances'])) {
                    DailyAttendance::whereIn('id', $processed['daily_attendances'])->update(['is_synced' => true]);
                }
                if (!empty($processed['attendances'])) {
                    Attendance::whereIn('id', $processed['attendances'])->update(['is_synced' => true]);
                }
                if (!empty($processed['student_violations'])) {
                    StudentViolation::whereIn('id', $processed['student_violations'])->update(['is_synced' => true]);
                }

                SyncLog::create([
                    'type' => 'push',
                    'table_name' => 'transaksi_gabungan',
                    'records_count' => $totalRecords,
                    'status' => 'success',
                    'synced_at' => now(),
                ]);

                return ['status' => 'success', 'message' => "Berhasil sinkron {$totalRecords} data ke online."];
            }

            $statusCode = $response->status();
            $errorMsg = $response->json('message') ?? $response->body() ?? 'Terjadi kesalahan pada server online.';
            throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

        } catch (\Exception $e) {
            SyncLog::create([
                'type' => 'push',
                'table_name' => 'transaksi_gabungan',
                'records_count' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => now(),
            ]);

            return ['status' => 'error', 'message' => 'Gagal Push Data: ' . $e->getMessage()];
        }
    }

    /**
     * Eksekusi Pull Data Master (Online ke Lokal)
     */
    public function pullMasterData(): array
    {
        set_time_limit(300);

        if (function_exists('activity')) {
            activity()->disableLogging();
        }

        if (empty($this->baseUrl) || empty($this->token)) {
            return ['status' => 'error', 'message' => 'URL Server atau API Token belum diatur.'];
        }

        $lastPull = SyncLog::where('type', 'pull')
            ->where('table_name', 'master_data')
            ->where('status', 'success')
            ->latest('synced_at')
            ->value('synced_at');

        $lastSyncedAt = $lastPull 
            ? \Carbon\Carbon::parse($lastPull)->format('Y-m-d H:i:s') 
            : '1970-01-01 00:00:00';

        $currentSyncTimestamp = now();

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->acceptJson()
                ->timeout(120)
                ->get("{$this->baseUrl}/api/v1/sync/pull", [
                    'last_synced_at' => $lastSyncedAt
                ]);

            if ($response->successful()) {
                $masterData = $response->json('data');
                
                $totalRecordsReceived = count($masterData['users'] ?? []) 
                    + count($masterData['teachers'] ?? [])
                    + count($masterData['classrooms'] ?? []) 
                    + count($masterData['students'] ?? []);

                if ($totalRecordsReceived === 0) {
                    SyncLog::create([
                        'type' => 'pull',
                        'table_name' => 'master_data',
                        'records_count' => 0,
                        'status' => 'success',
                        'synced_at' => $currentSyncTimestamp,
                    ]);

                    if (function_exists('activity')) {
                        activity()->enableLogging();
                    }

                    return ['status' => 'success', 'message' => 'Data master sudah yang terbaru (0 record ditarik).'];
                }

                $count = 0;
                DB::beginTransaction();

                // Matikan constraint PostgreSQL sementara selama sinkronisasi
                try {
                    DB::statement("SET session_replication_role = 'replica';");
                } catch (\Exception $ex) {
                    DB::statement('SET CONSTRAINTS ALL DEFERRED;');
                }

                // 1. Users
                foreach ($masterData['users'] ?? [] as $row) {
                    $existingUser = User::where('email', $row['email'])->orWhere('id', $row['id'])->first();
                    if (!isset($row['password']) || empty($row['password'])) {
                        if ($existingUser) { 
                            unset($row['password']); 
                        } else { 
                            $row['password'] = Hash::make('password123'); 
                        }
                    }
                    if ($existingUser) { 
                        $existingUser->update($row); 
                    } else { 
                        User::create($row); 
                    }
                    $count++;
                }

                // 2. Teachers (Pencegahan Not-Null Constraint user_id)
                foreach ($masterData['teachers'] ?? [] as $row) {
                    if (!empty($row['user_id'])) {
                        $userExists = User::where('id', $row['user_id'])->exists();
                        if (!$userExists) {
                            User::create([
                                'id' => $row['user_id'],
                                'name' => $row['name'] ?? 'Guru Sync',
                                'email' => $row['email'] ?? ('guru_' . Str::random(5) . '@school.id'),
                                'password' => Hash::make('password123'),
                            ]);
                        }
                    }

                    $query = Teacher::where('id', $row['id']);
                    if (!empty($row['nip'])) $query->orWhere('nip', $row['nip']);
                    if (!empty($row['email'])) $query->orWhere('email', $row['email']);
                    $existingTeacher = $query->first();

                    if ($existingTeacher) { 
                        $existingTeacher->update($row); 
                    } else { 
                        Teacher::create($row); 
                    }
                    $count++;
                }

                // 3. Classrooms
                foreach ($masterData['classrooms'] ?? [] as $row) {
                    $existingClassroom = Classroom::where('name', $row['name'])->orWhere('id', $row['id'])->first();
                    if ($existingClassroom) { 
                        $existingClassroom->update($row); 
                    } else { 
                        Classroom::create($row); 
                    }
                    $count++;
                }

                // 4. Students
                foreach ($masterData['students'] ?? [] as $row) {
                    $query = Student::where('id', $row['id']);
                    if (!empty($row['nisn'])) $query->orWhere('nisn', $row['nisn']);
                    if (!empty($row['nis'])) $query->orWhere('nis', $row['nis']);
                    $existingStudent = $query->first();

                    if ($existingStudent) { 
                        $existingStudent->update($row); 
                    } else { 
                        Student::create($row); 
                    }
                    $count++;
                }

                try {
                    DB::statement("SET session_replication_role = 'origin';");
                } catch (\Exception $ex) {}

                DB::commit();

                SyncLog::create([
                    'type' => 'pull',
                    'table_name' => 'master_data',
                    'records_count' => $count,
                    'status' => 'success',
                    'synced_at' => $currentSyncTimestamp,
                ]);

                if (function_exists('activity')) {
                    activity()->enableLogging();
                }

                return ['status' => 'success', 'message' => "Berhasil menarik {$count} data master terbaru."];
            }

            $statusCode = $response->status();
            $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data master dari online.';
            throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

        } catch (\Exception $e) {
            DB::rollBack();

            try {
                DB::statement("SET session_replication_role = 'origin';");
            } catch (\Exception $ex) {}

            if (function_exists('activity')) {
                activity()->enableLogging();
            }

            SyncLog::create([
                'type' => 'pull',
                'table_name' => 'master_data',
                'records_count' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => now(),
            ]);

            return ['status' => 'error', 'message' => 'Gagal Pull Data: ' . $e->getMessage()];
        }
    }

    /**
     * Eksekusi Pull Data Absensi (Online ke Lokal)
     */
    public function pullAttendanceData(): array
    {
        set_time_limit(300);

        if (function_exists('activity')) {
            activity()->disableLogging();
        }

        if (empty($this->baseUrl) || empty($this->token)) {
            return ['status' => 'error', 'message' => 'URL Server atau API Token belum diatur.'];
        }

        $lastPull = SyncLog::where('type', 'pull')
            ->where('table_name', 'attendance_data')
            ->where('status', 'success')
            ->latest('synced_at')
            ->value('synced_at');

        $lastSyncedAt = $lastPull 
            ? \Carbon\Carbon::parse($lastPull)->format('Y-m-d H:i:s') 
            : '1970-01-01 00:00:00';

        $currentSyncTimestamp = now();

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->acceptJson()
                ->timeout(120)
                ->get("{$this->baseUrl}/api/v1/sync/pull-attendances", [
                    'last_synced_at' => $lastSyncedAt
                ]);

            if ($response->successful()) {
                $data = $response->json('data');

                $totalRecordsReceived = count($data['daily_attendances'] ?? []) 
                    + count($data['attendances'] ?? []) 
                    + count($data['prayer_attendances'] ?? []);

                if ($totalRecordsReceived === 0) {
                    SyncLog::create([
                        'type' => 'pull',
                        'table_name' => 'attendance_data',
                        'records_count' => 0,
                        'status' => 'success',
                        'synced_at' => $currentSyncTimestamp,
                    ]);

                    if (function_exists('activity')) {
                        activity()->enableLogging();
                    }

                    return ['status' => 'success', 'message' => 'Data absensi sudah yang terbaru (0 record ditarik).'];
                }

                $count = 0;
                DB::beginTransaction();

                try {
                    DB::statement("SET session_replication_role = 'replica';");
                } catch (\Exception $ex) {
                    DB::statement('SET CONSTRAINTS ALL DEFERRED;');
                }

                foreach ($data['daily_attendances'] ?? [] as $row) {
                    $row['is_synced'] = true;
                    DailyAttendance::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }

                foreach ($data['attendances'] ?? [] as $row) {
                    $row['is_synced'] = true;
                    Attendance::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }

                foreach ($data['prayer_attendances'] ?? [] as $row) {
                    $row['is_synced'] = true;
                    PrayerAttendance::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }

                try {
                    DB::statement("SET session_replication_role = 'origin';");
                } catch (\Exception $ex) {}

                DB::commit();

                SyncLog::create([
                    'type' => 'pull',
                    'table_name' => 'attendance_data',
                    'records_count' => $count,
                    'status' => 'success',
                    'synced_at' => $currentSyncTimestamp,
                ]);

                if (function_exists('activity')) {
                    activity()->enableLogging();
                }

                return ['status' => 'success', 'message' => "Berhasil menarik {$count} data absensi dari online."];
            }

            $statusCode = $response->status();
            $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data absensi.';
            throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

        } catch (\Exception $e) {
            DB::rollBack();

            try {
                DB::statement("SET session_replication_role = 'origin';");
            } catch (\Exception $ex) {}

            if (function_exists('activity')) {
                activity()->enableLogging();
            }

            SyncLog::create([
                'type' => 'pull',
                'table_name' => 'attendance_data',
                'records_count' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => now(),
            ]);

            return ['status' => 'error', 'message' => 'Gagal Pull Absensi: ' . $e->getMessage()];
        }
    }

    /**
     * Penarikan File Foto Presensi dari Online ke Lokal
     * @param bool $deleteOnlineStorage (true = Hapus di Online, false = Simpan di Online)
     */
    // public function pullAttendancePhotos(bool $deleteOnlineStorage = false): array
    // {
    //     set_time_limit(600);

    //     if (empty($this->baseUrl) || empty($this->token)) {
    //         return ['status' => 'error', 'message' => 'URL Server atau API Token belum diatur.'];
    //     }

    //     try {
    //         $response = Http::withoutVerifying()
    //             ->withToken($this->token)
    //             ->timeout(300)
    //             ->get("{$this->baseUrl}/api/v1/sync/pull-photos", [
    //                 'delete_online_storage' => $deleteOnlineStorage ? '1' : '0'
    //             ]);

    //         if ($response->header('Content-Type') === 'application/json' || str_contains($response->header('Content-Type'), 'json')) {
    //             $resData = $response->json();
    //             return ['status' => 'success', 'message' => $resData['message'] ?? 'Tidak ada foto baru untuk ditarik.'];
    //         }

    //         if ($response->successful()) {
    //             $tempZipPath = storage_path('app/temp_photos_' . time() . '.zip');
    //             file_put_contents($tempZipPath, $response->body());

    //             $zip = new ZipArchive();
    //             if ($zip->open($tempZipPath) === true) {
    //                 $photoCount = $zip->numFiles; // Hitung jumlah file gambar di dalam ZIP
    //                 $zip->extractTo(storage_path('app/public'));
    //                 $zip->close();
    //                 @unlink($tempZipPath);

    //                 SyncLog::create([
    //                     'type' => 'pull',
    //                     'table_name' => 'attendance_photos',
    //                     'records_count' => $photoCount, // Catat jumlah foto riil
    //                     'status' => 'success',
    //                     'synced_at' => now(),
    //                 ]);

    //                 $msg = $deleteOnlineStorage 
    //                     ? 'Berhasil menarik foto presensi & mengosongkan storage hosting online.' 
    //                     : 'Berhasil menarik foto presensi (file di hosting tetap tersimpan).';

    //                 return ['status' => 'success', 'message' => $msg];
    //             }

    //             throw new \Exception('Gagal mengekstrak berkas foto ZIP.');
    //         }

    //         throw new \Exception('HTTP ' . $response->status() . ': Gagal mengunduh berkas foto.');

    //     } catch (\Exception $e) {
    //         return ['status' => 'error', 'message' => 'Gagal Pull Foto: ' . $e->getMessage()];
    //     }
    // }

    public function pullAttendancePhotos(bool $deleteOnlineStorage = false): array
{
    set_time_limit(600);

    if (empty($this->baseUrl) || empty($this->token)) {
        return ['status' => 'error', 'message' => 'URL Server atau API Token belum diatur.'];
    }

    try {
        $response = Http::withoutVerifying()
            ->withToken($this->token)
            ->timeout(300)
            ->get("{$this->baseUrl}/api/v1/sync/pull-photos", [
                'delete_online_storage' => $deleteOnlineStorage ? '1' : '0'
            ]);

        if ($response->header('Content-Type') === 'application/json' || str_contains($response->header('Content-Type'), 'json')) {
            $resData = $response->json();
            return ['status' => 'success', 'message' => $resData['message'] ?? 'Tidak ada foto baru untuk ditarik.'];
        }

        if ($response->successful()) {
            $tempZipPath = storage_path('app/temp_photos_' . time() . '.zip');
            file_put_contents($tempZipPath, $response->body());

            $zip = new ZipArchive();
            if ($zip->open($tempZipPath) === true) {
                $photoCount = $zip->numFiles;
                
                // Ekstrak langsung ke disk public (storage/app/public)
                $extractPath = Storage::disk('public')->path('');
                $zip->extractTo($extractPath);
                $zip->close();
                @unlink($tempZipPath);

                SyncLog::create([
                    'type' => 'pull',
                    'table_name' => 'attendance_photos',
                    'records_count' => $photoCount,
                    'status' => 'success',
                    'synced_at' => now(),
                ]);

                $msg = $deleteOnlineStorage 
                    ? "Berhasil menarik {$photoCount} foto presensi & mengosongkan storage hosting online." 
                    : "Berhasil menarik {$photoCount} foto presensi (file di hosting tetap tersimpan).";

                return ['status' => 'success', 'message' => $msg];
            }

            throw new \Exception('Gagal mengekstrak berkas foto ZIP.');
        }

        throw new \Exception('HTTP ' . $response->status() . ': Gagal mengunduh berkas foto.');

    } catch (\Exception $e) {
        return ['status' => 'error', 'message' => 'Gagal Pull Foto: ' . $e->getMessage()];
    }
}
}