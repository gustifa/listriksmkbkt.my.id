<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

class DataSyncService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        // Ambil URL dari database settings, jika belum ada fallback ke config .env
        $urlSetting = Setting::where('key', 'sync_server_url')->value('value');
        $this->baseUrl = rtrim($urlSetting ?? config('services.online_server.url', ''), '/');
        
        // Ambil Token dari database settings
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

                // Tandai data lokal sebagai sudah disinkronkan
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
     * Eksekusi Pull (Online ke Lokal)
     */
    // public function pullMasterData(): array
    // {
    //     if (empty($this->baseUrl)) {
    //         return ['status' => 'error', 'message' => 'URL Server Online belum diatur. Silakan atur URL terlebih dahulu.'];
    //     }

    //     if (empty($this->token)) {
    //         return ['status' => 'error', 'message' => 'API Token belum diatur. Silakan masukkan token dari Server Online.'];
    //     }

    //     $lastPull = SyncLog::where('type', 'pull')
    //         ->where('status', 'success')
    //         ->latest('synced_at')
    //         ->value('synced_at');

    //     $lastSyncedAt = $lastPull ? $lastPull->toDateTimeString() : '1970-01-01 00:00:00';

    //     try {
    //         $response = Http::withToken($this->token)
    //             ->acceptJson()
    //             ->timeout(30)
    //             ->get("{$this->baseUrl}/api/v1/sync/pull", [
    //                 'last_synced_at' => $lastSyncedAt
    //             ]);

    //         if ($response->successful()) {
    //             $masterData = $response->json('data');
    //             $count = 0;

    //             DB::beginTransaction();

    //             foreach ($masterData['users'] ?? [] as $row) {
    //                 // Cari user berdasarkan email terlebih dahulu, atau ID
    //                 $existingUser = User::where('email', $row['email'])
    //                     ->orWhere('id', $row['id'])
    //                     ->first();

    //                 if (!isset($row['password']) || empty($row['password'])) {
    //                     if ($existingUser) {
    //                         // Pertahankan password lokal jika user sudah ada
    //                         unset($row['password']);
    //                     } else {
    //                         // Berikan password default jika user baru
    //                         $row['password'] = Hash::make('password123');
    //                     }
    //                 }

    //                 if ($existingUser) {
    //                     // Update user yang sudah ada berdasarkan ID/Email yang ditemukan
    //                     $existingUser->update($row);
    //                 } else {
    //                     // Buat user baru jika belum ada di lokal
    //                     User::create($row);
    //                 }

    //                 $count++;
    //             }

    //             // 2. Synchronize Classrooms
    //             foreach ($masterData['classrooms'] ?? [] as $row) {
    //                 Classroom::updateOrCreate(['id' => $row['id']], $row);
    //                 $count++;
    //             }

    //             // 3. Synchronize Teachers
    //             foreach ($masterData['teachers'] ?? [] as $row) {
    //                 Teacher::updateOrCreate(['id' => $row['id']], $row);
    //                 $count++;
    //             }

    //             // 4. Synchronize Students
    //             foreach ($masterData['students'] ?? [] as $row) {
    //                 Student::updateOrCreate(['id' => $row['id']], $row);
    //                 $count++;
    //             }

    //             DB::commit();

    //             SyncLog::create([
    //                 'type' => 'pull',
    //                 'table_name' => 'master_data',
    //                 'records_count' => $count,
    //                 'status' => 'success',
    //                 'synced_at' => now(),
    //             ]);

    //             return ['status' => 'success', 'message' => "Berhasil menarik {$count} data master terbaru."];
    //         }

    //         $statusCode = $response->status();
    //         $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data master dari online.';
    //         throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         SyncLog::create([
    //             'type' => 'pull',
    //             'table_name' => 'master_data',
    //             'records_count' => 0,
    //             'status' => 'failed',
    //             'error_message' => $e->getMessage(),
    //             'synced_at' => now(),
    //         ]);

    //         return ['status' => 'error', 'message' => 'Gagal Pull Data: ' . $e->getMessage()];
    //     }
    // }

    /**
 * Eksekusi Pull (Online ke Lokal)
 */
// public function pullMasterData(): array
// {
//     if (empty($this->baseUrl)) {
//         return ['status' => 'error', 'message' => 'URL Server Online belum diatur. Silakan atur URL terlebih dahulu.'];
//     }

//     if (empty($this->token)) {
//         return ['status' => 'error', 'message' => 'API Token belum diatur. Silakan masukkan token dari Server Online.'];
//     }

//     $lastPull = SyncLog::where('type', 'pull')
//         ->where('status', 'success')
//         ->latest('synced_at')
//         ->value('synced_at');

//     $lastSyncedAt = $lastPull ? $lastPull->toDateTimeString() : '1970-01-01 00:00:00';

//     try {
//         $response = Http::withoutVerifying() // <-- Tambahkan ini
//             ->withToken($this->token)
//             ->acceptJson()
//             ->timeout(30)
//             ->get("{$this->baseUrl}/api/v1/sync/pull", [
//                 'last_synced_at' => $lastSyncedAt
//             ]);

//         if ($response->successful()) {
//             $masterData = $response->json('data');
//             $count = 0;

//             DB::beginTransaction();

//             // 1. Synchronize Users (Update ID Lokal agar Sama dengan ID Online)
//             foreach ($masterData['users'] ?? [] as $row) {
//                 $existingUser = User::where('email', $row['email'])
//                     ->orWhere('id', $row['id'])
//                     ->first();

//                 if (!isset($row['password']) || empty($row['password'])) {
//                     if ($existingUser) {
//                         unset($row['password']);
//                     } else {
//                         $row['password'] = Hash::make('password123');
//                     }
//                 }

//                 if ($existingUser) {
//                     $existingUser->update($row);
//                 } else {
//                     User::create($row);
//                 }
//                 $count++;
//             }

//             // 2. Synchronize Classrooms
//             foreach ($masterData['classrooms'] ?? [] as $row) {
//                 $existingClassroom = Classroom::where('name', $row['name'])
//                     ->orWhere('id', $row['id'])
//                     ->first();

//                 if ($existingClassroom) {
//                     $existingClassroom->update($row);
//                 } else {
//                     Classroom::create($row);
//                 }
//                 $count++;
//             }

//             // 3. Synchronize Teachers (Validasi user_id)
//             foreach ($masterData['teachers'] ?? [] as $row) {
//                 if (!empty($row['user_id'])) {
//                     $userExists = User::where('id', $row['user_id'])->exists();
//                     if (!$userExists) {
//                         $row['user_id'] = null;
//                     }
//                 }

//                 $query = Teacher::where('id', $row['id']);
//                 if (!empty($row['nip'])) {
//                     $query->orWhere('nip', $row['nip']);
//                 }
//                 if (!empty($row['email'])) {
//                     $query->orWhere('email', $row['email']);
//                 }
                
//                 $existingTeacher = $query->first();

//                 if ($existingTeacher) {
//                     $existingTeacher->update($row);
//                 } else {
//                     Teacher::create($row);
//                 }
//                 $count++;
//             }

//             // 4. Synchronize Students (Validasi classroom_id dan user_id)
//             foreach ($masterData['students'] ?? [] as $row) {
//                 // Check Foreign Key classroom_id
//                 if (!empty($row['classroom_id'])) {
//                     $classroomExists = Classroom::where('id', $row['classroom_id'])->exists();
//                     if (!$classroomExists) {
//                         $row['classroom_id'] = null;
//                     }
//                 }

//                 // Check Foreign Key user_id
//                 if (!empty($row['user_id'])) {
//                     $userExists = User::where('id', $row['user_id'])->exists();
//                     if (!$userExists) {
//                         $row['user_id'] = null;
//                     }
//                 }

//                 $query = Student::where('id', $row['id']);
//                 if (!empty($row['nisn'])) {
//                     $query->orWhere('nisn', $row['nisn']);
//                 }
//                 if (!empty($row['nis'])) {
//                     $query->orWhere('nis', $row['nis']);
//                 }

//                 $existingStudent = $query->first();

//                 if ($existingStudent) {
//                     $existingStudent->update($row);
//                 } else {
//                     Student::create($row);
//                 }
//                 $count++;
//             }

//             DB::commit();

//             SyncLog::create([
//                 'type' => 'pull',
//                 'table_name' => 'master_data',
//                 'records_count' => $count,
//                 'status' => 'success',
//                 'synced_at' => now(),
//             ]);

//             return ['status' => 'success', 'message' => "Berhasil menarik {$count} data master terbaru."];
//         }

//         $statusCode = $response->status();
//         $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data master dari online.';
//         throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

//     } catch (\Exception $e) {
//         DB::rollBack();

//         SyncLog::create([
//             'type' => 'pull',
//             'table_name' => 'master_data',
//             'records_count' => 0,
//             'status' => 'failed',
//             'error_message' => $e->getMessage(),
//             'synced_at' => now(),
//         ]);

//         return ['status' => 'error', 'message' => 'Gagal Pull Data: ' . $e->getMessage()];
//     }
// }

public function pullMasterData(): array
{
    set_time_limit(300);

    if (empty($this->baseUrl) || empty($this->token)) {
        return ['status' => 'error', 'message' => 'URL Server atau API Token belum diatur.'];
    }

    // Ambil log sukses terakhir
    $lastPull = SyncLog::where('type', 'pull')
        ->where('status', 'success')
        ->latest('synced_at')
        ->value('synced_at');

    // Format eksplisit Y-m-d H:i:s
    $lastSyncedAt = $lastPull 
        ? \Carbon\Carbon::parse($lastPull)->format('Y-m-d H:i:s') 
        : '1970-01-01 00:00:00';

    // Catat timestamp penarikan saat ini sebelum proses loop
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
            
            // Cek jika seluruh array data kosong (0 data)
            $totalRecordsReceived = count($masterData['users'] ?? []) 
                + count($masterData['classrooms'] ?? []) 
                + count($masterData['teachers'] ?? []) 
                + count($masterData['students'] ?? []);

            if ($totalRecordsReceived === 0) {
                // Catat log sukses dengan 0 record
                SyncLog::create([
                    'type' => 'pull',
                    'table_name' => 'master_data',
                    'records_count' => 0,
                    'status' => 'success',
                    'synced_at' => $currentSyncTimestamp,
                ]);

                return ['status' => 'success', 'message' => 'Data master sudah yang terbaru (0 record ditarik).'];
            }

            $count = 0;
            DB::beginTransaction();

            // 1. Users
            foreach ($masterData['users'] ?? [] as $row) {
                $existingUser = User::where('email', $row['email'])->orWhere('id', $row['id'])->first();
                if (!isset($row['password']) || empty($row['password'])) {
                    if ($existingUser) { unset($row['password']); } 
                    else { $row['password'] = Hash::make('password123'); }
                }
                if ($existingUser) { $existingUser->update($row); } 
                else { User::create($row); }
                $count++;
            }

            // 2. Classrooms
            foreach ($masterData['classrooms'] ?? [] as $row) {
                $existingClassroom = Classroom::where('name', $row['name'])->orWhere('id', $row['id'])->first();
                if ($existingClassroom) { $existingClassroom->update($row); } 
                else { Classroom::create($row); }
                $count++;
            }

            // 3. Teachers
            foreach ($masterData['teachers'] ?? [] as $row) {
                if (!empty($row['user_id']) && !User::where('id', $row['user_id'])->exists()) {
                    $row['user_id'] = null;
                }
                $query = Teacher::where('id', $row['id']);
                if (!empty($row['nip'])) $query->orWhere('nip', $row['nip']);
                if (!empty($row['email'])) $query->orWhere('email', $row['email']);
                $existingTeacher = $query->first();

                if ($existingTeacher) { $existingTeacher->update($row); } 
                else { Teacher::create($row); }
                $count++;
            }

            // 4. Students
            foreach ($masterData['students'] ?? [] as $row) {
                if (!empty($row['classroom_id']) && !Classroom::where('id', $row['classroom_id'])->exists()) {
                    $row['classroom_id'] = null;
                }
                if (!empty($row['user_id']) && !User::where('id', $row['user_id'])->exists()) {
                    $row['user_id'] = null;
                }
                $query = Student::where('id', $row['id']);
                if (!empty($row['nisn'])) $query->orWhere('nisn', $row['nisn']);
                if (!empty($row['nis'])) $query->orWhere('nis', $row['nis']);
                $existingStudent = $query->first();

                if ($existingStudent) { $existingStudent->update($row); } 
                else { Student::create($row); }
                $count++;
            }

            DB::commit();

            SyncLog::create([
                'type' => 'pull',
                'table_name' => 'master_data',
                'records_count' => $count,
                'status' => 'success',
                'synced_at' => $currentSyncTimestamp,
            ]);

            return ['status' => 'success', 'message' => "Berhasil menarik {$count} data master terbaru."];
        }

        $statusCode = $response->status();
        $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data master dari online.';
        throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

    } catch (\Exception $e) {
        DB::rollBack();

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

                return ['status' => 'success', 'message' => 'Data absensi sudah yang terbaru (0 record ditarik).'];
            }

            $count = 0;
            DB::beginTransaction();

            // 1. Daily Attendances (Absensi Gerbang)
            foreach ($data['daily_attendances'] ?? [] as $row) {
                $row['is_synced'] = true; // Tandai sudah sinkron
                DailyAttendance::updateOrCreate(['id' => $row['id']], $row);
                $count++;
            }

            // 2. Attendances (Absensi Pembelajaran)
            foreach ($data['attendances'] ?? [] as $row) {
                $row['is_synced'] = true;
                Attendance::updateOrCreate(['id' => $row['id']], $row);
                $count++;
            }

            // 3. Prayer Attendances (Absensi Sholat)
            foreach ($data['prayer_attendances'] ?? [] as $row) {
                $row['is_synced'] = true;
                PrayerAttendance::updateOrCreate(['id' => $row['id']], $row);
                $count++;
            }

            DB::commit();

            SyncLog::create([
                'type' => 'pull',
                'table_name' => 'attendance_data',
                'records_count' => $count,
                'status' => 'success',
                'synced_at' => $currentSyncTimestamp,
            ]);

            return ['status' => 'success', 'message' => "Berhasil menarik {$count} data absensi dari online."];
        }

        $statusCode = $response->status();
        $errorMsg = $response->json('message') ?? $response->body() ?? 'Gagal mengambil data absensi.';
        throw new \Exception("HTTP {$statusCode}: {$errorMsg}");

    } catch (\Exception $e) {
        DB::rollBack();

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



}