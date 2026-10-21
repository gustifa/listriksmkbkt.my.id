<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\StudentViolation;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\User;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class DataSyncService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        // Ambil URL dari database settings, jika belum ada fallback ke config .env
        $this->baseUrl = rtrim(
            Setting::where('key', 'sync_server_url')->value('value') 
            ?? config('services.online_server.url', ''), 
            '/'
        );
        
        // Ambil Token dari database settings
        $this->token = Setting::where('key', 'sync_api_token')->value('value') 
                    ?? config('services.online_server.token', '');
    }

    /**
     * Eksekusi Push (Lokal ke Online)
     */
    public function pushLocalData(): array
    {
        $dailyAttendances = DailyAttendance::where('is_synced', false)->limit(100)->get();
        $attendances = Attendance::where('is_synced', false)->limit(100)->get();
        $violations = StudentViolation::where('is_synced', false)->limit(100)->get();

        $totalRecords = $dailyAttendances->count() + $attendances->count() + $violations->count();

        if ($totalRecords === 0) {
            return ['status' => 'info', 'message' => 'Tidak ada data transaksi baru untuk dikirim.'];
        }

        try {
            $response = Http::withToken($this->token)
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

            throw new \Exception($response->json('message') ?? 'Terjadi kesalahan pada server online.');

        } catch (\Exception $e) {
            SyncLog::create([
                'type' => 'push',
                'table_name' => 'transaksi_gabungan',
                'records_count' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'synced_at' => now(),
            ]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Eksekusi Pull (Online ke Lokal)
     */
    public function pullMasterData(): array
    {
        $lastPull = SyncLog::where('type', 'pull')
            ->where('status', 'success')
            ->latest('synced_at')
            ->value('synced_at');

        $lastSyncedAt = $lastPull ? $lastPull->toDateTimeString() : '1970-01-01 00:00:00';

        try {
            $response = Http::withToken($this->token)
                ->timeout(30)
                ->get("{$this->baseUrl}/api/v1/sync/pull", [
                    'last_synced_at' => $lastSyncedAt
                ]);

            if ($response->successful()) {
                $masterData = $response->json('data');
                $count = 0;

                DB::beginTransaction();

                foreach ($masterData['users'] ?? [] as $row) {
                    User::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }
                foreach ($masterData['classrooms'] ?? [] as $row) {
                    Classroom::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }
                foreach ($masterData['teachers'] ?? [] as $row) {
                    Teacher::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }
                foreach ($masterData['students'] ?? [] as $row) {
                    Student::updateOrCreate(['id' => $row['id']], $row);
                    $count++;
                }

                DB::commit();

                SyncLog::create([
                    'type' => 'pull',
                    'table_name' => 'master_data',
                    'records_count' => $count,
                    'status' => 'success',
                    'synced_at' => now(),
                ]);

                return ['status' => 'success', 'message' => "Berhasil menarik {$count} data master terbaru."];
            }

            throw new \Exception('Gagal mengambil data master dari online.');

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

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}