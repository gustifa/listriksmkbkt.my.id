<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\User;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\StudentViolation;
use App\Models\PrayerAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ZipArchive;

class SyncController extends Controller
{
    /**
     * ONLINE -> LOKAL: Mengambil Data Master Terbaru
     */
    public function pullMasterData(Request $request)
    {
        $lastSyncedAtRaw = $request->get('last_synced_at');

        $lastSyncedAt = null;
        if ($lastSyncedAtRaw && $lastSyncedAtRaw !== '1970-01-01 00:00:00') {
            try {
                $lastSyncedAt = Carbon::parse($lastSyncedAtRaw)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $lastSyncedAt = null;
            }
        }

        if ($lastSyncedAt) {
            $users = User::where('updated_at', '>', $lastSyncedAt)->get()->makeVisible(['password']);
            $teachers = Teacher::where('updated_at', '>', $lastSyncedAt)->get();
            $classrooms = Classroom::where('updated_at', '>', $lastSyncedAt)->get();
            $students = Student::where('updated_at', '>', $lastSyncedAt)->get();
        } else {
            $users = User::all()->makeVisible(['password']);
            $teachers = Teacher::all();
            $classrooms = Classroom::all();
            $students = Student::all();
        }

        // Urutan array disesuaikan: Users -> Teachers -> Classrooms -> Students
        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'teachers' => $teachers,
                'classrooms' => $classrooms,
                'students' => $students,
            ]
        ]);
    }

    /**
     * LOKAL -> ONLINE: Menerima Data Transaksi dari Server Lokal
     */
    public function pushTransactionData(Request $request)
    {
        $request->validate([
            'daily_attendances' => 'nullable|array',
            'attendances' => 'nullable|array',
            'student_violations' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $processedIds = [
                'daily_attendances' => [],
                'attendances' => [],
                'student_violations' => [],
            ];

            // 1. Sync Daily Attendances
            if ($request->has('daily_attendances')) {
                foreach ($request->daily_attendances as $row) {
                    unset($row['is_synced']);
                    DailyAttendance::updateOrCreate(['id' => $row['id']], $row);
                    $processedIds['daily_attendances'][] = $row['id'];
                }
            }

            // 2. Sync Attendances
            if ($request->has('attendances')) {
                foreach ($request->attendances as $row) {
                    unset($row['is_synced']);
                    Attendance::updateOrCreate(['id' => $row['id']], $row);
                    $processedIds['attendances'][] = $row['id'];
                }
            }

            // 3. Sync Violations
            if ($request->has('student_violations')) {
                foreach ($request->student_violations as $row) {
                    unset($row['is_synced']);
                    StudentViolation::updateOrCreate(['id' => $row['id']], $row);
                    $processedIds['student_violations'][] = $row['id'];
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disinkronkan ke server online',
                'processed_ids' => $processedIds,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Khusus Pull Data Absensi dari Online ke Lokal
     */
    public function pullAttendanceData(Request $request)
    {
        set_time_limit(300);

        $lastSyncedAtRaw = $request->get('last_synced_at');
        $lastSyncedAt = null;

        if ($lastSyncedAtRaw && $lastSyncedAtRaw !== '1970-01-01 00:00:00') {
            try {
                $lastSyncedAt = Carbon::parse($lastSyncedAtRaw)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $lastSyncedAt = null;
            }
        }

        if ($lastSyncedAt) {
            $dailyAttendances = DailyAttendance::where('updated_at', '>', $lastSyncedAt)->get();
            $attendances = Attendance::where('updated_at', '>', $lastSyncedAt)->get();
            $prayerAttendances = PrayerAttendance::where('updated_at', '>', $lastSyncedAt)->get();
        } else {
            $dailyAttendances = DailyAttendance::all();
            $attendances = Attendance::all();
            $prayerAttendances = PrayerAttendance::all();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'daily_attendances' => $dailyAttendances,
                'attendances' => $attendances,
                'prayer_attendances' => $prayerAttendances,
            ]
        ]);
    }

    /**
     * ONLINE -> LOKAL: Penarikan File Foto Presensi & Auto-Delete Storage Hosting
     */
    public function pullAttendancePhotos(Request $request)
    {
        set_time_limit(600);

        $dailyPhotos = DailyAttendance::whereNotNull('photo_in')
            ->orWhereNotNull('photo_out')
            ->limit(100)
            ->get(['photo_in', 'photo_out']);

        $prayerPhotos = PrayerAttendance::whereNotNull('photo_evidence')
            ->limit(100)
            ->get(['photo_evidence']);

        $filesToZip = [];

        foreach ($dailyPhotos as $daily) {
            if ($daily->photo_in && Storage::disk('public')->exists($daily->photo_in)) {
                $filesToZip[] = $daily->photo_in;
            }
            if ($daily->photo_out && Storage::disk('public')->exists($daily->photo_out)) {
                $filesToZip[] = $daily->photo_out;
            }
        }

        foreach ($prayerPhotos as $prayer) {
            if ($prayer->photo_evidence && Storage::disk('public')->exists($prayer->photo_evidence)) {
                $filesToZip[] = $prayer->photo_evidence;
            }
        }

        if (empty($filesToZip)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada file foto baru untuk ditarik.',
                'photos_count' => 0
            ]);
        }

        $zipFileName = 'photos_sync_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($filesToZip as $file) {
                $fullPath = storage_path('app/public/' . $file);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $file);
                }
            }
            $zip->close();
        }

        // Hapus file foto dari storage online jika diminta oleh request lokal
        if ($request->get('delete_online_storage') == true) {
            foreach ($filesToZip as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function generateToken()
    {
        $newToken = 'sk_live_' . Str::random(52);

        $setting = DB::table('settings')->where('key', 'sync_api_token')->first();

        if ($setting) {
            DB::table('settings')
                ->where('key', 'sync_api_token')
                ->update([
                    'value'      => $newToken,
                    'updated_at' => now(),
                ]);
        } else {
            $maxId = DB::table('settings')->max('id') ?? 0;

            DB::table('settings')->insert([
                'id'         => $maxId + 1,
                'key'        => 'sync_api_token',
                'value'      => $newToken,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'API Token Sinkronisasi berhasil diperbarui!');
    }

    public function index()
    {
        $syncToken = DB::table('settings')->where('key', 'sync_api_token')->value('value');
        $syncLogs = DB::table('sync_logs')->latest()->take(10)->get();
        $pendingGateAttendance = DB::table('gate_attendances')->where('is_synced', false)->count();
        $pendingSubjectAttendance = DB::table('subject_attendances')->where('is_synced', false)->count();
        $pendingViolations = DB::table('student_violations')->where('is_synced', false)->count();
        $totalPendingPush = $pendingGateAttendance + $pendingSubjectAttendance + $pendingViolations;

        return view('admin.sync.index', compact(
            'syncToken',
            'syncLogs',
            'pendingGateAttendance',
            'pendingSubjectAttendance',
            'pendingViolations',
            'totalPendingPush'
        ));
    }
}