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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SyncController extends Controller
{
    /**
     * ONLINE -> LOKAL: Mengambil Data Master Terbaru
     */
    // public function pullMasterData(Request $request)
    // {
    //     $lastSyncedAt = $request->query('last_synced_at', '1970-01-01 00:00:00');

    //     return response()->json([
    //         'status' => 'success',
    //         'timestamp' => now()->toDateTimeString(),
    //         'data' => [
    //             'users' => User::where('updated_at', '>', $lastSyncedAt)->get(),
    //             'classrooms' => Classroom::where('updated_at', '>', $lastSyncedAt)->get(),
    //             'teachers' => Teacher::where('updated_at', '>', $lastSyncedAt)->get(),
    //             'students' => Student::where('updated_at', '>', $lastSyncedAt)->get(),
    //         ]
    //     ]);
    // }

    // public function pullMasterData(Request $request)
    // {
    //     // Ambil data users dan buat password ikut terkirim dalam JSON
    //     $users = User::all()->makeVisible(['password']);

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => [
    //             'users' => $users,
    //             'classrooms' => Classroom::all(),
    //             'teachers' => Teacher::all(),
    //             'students' => Student::all(),
    //         ]
    //     ]);
    // }

    public function pullMasterData(Request $request)
{
    $lastSyncedAtRaw = $request->get('last_synced_at');

    // Parse tanggal agar diformat dengan presisi Y-m-d H:i:s
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
        $classrooms = Classroom::where('updated_at', '>', $lastSyncedAt)->get();
        $teachers = Teacher::where('updated_at', '>', $lastSyncedAt)->get();
        $students = Student::where('updated_at', '>', $lastSyncedAt)->get();
    } else {
        // Jika belum pernah tarik data / pertama kali
        $users = User::all()->makeVisible(['password']);
        $classrooms = Classroom::all();
        $teachers = Teacher::all();
        $students = Student::all();
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'users' => $users,
            'classrooms' => $classrooms,
            'teachers' => $teachers,
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

    // public function generateToken()
    // {
    //     // Generate token acak yang aman (64 karakter)
    //     $newToken = 'sk_live_' . Str::random(56);

    //     // Simpan ke setting sekolah / database
    //     $setting = SchoolSetting::first();
    //     if ($setting) {
    //         $setting->update(['sync_api_token' => $newToken]);
    //     }

    //     return back()->with('success', 'API Token baru berhasil dibuat!');
    // }

    // public function index()
    // {
    //     // Ambil token dari tabel settings
    //     $syncToken = DB::table('settings')->where('key', 'sync_api_token')->value('value');

    //     return view('admin.sync.index', compact('syncToken'));
    // }

    public function index()
    {
        // Ambil token dari tabel settings berdasarkan key 'sync_api_token'
        $syncToken = DB::table('settings')->where('key', 'sync_api_token')->value('value');

        // Data riwayat dan counter pending push
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
    
    public function generateToken()
    {
        $newToken = 'sk_live_' . Str::random(52);

        // Cek apakah key 'sync_api_token' sudah ada
        $setting = DB::table('settings')->where('key', 'sync_api_token')->first();

        if ($setting) {
            // Update jika key sudah ada di tabel
            DB::table('settings')
                ->where('key', 'sync_api_token')
                ->update([
                    'value'      => $newToken,
                    'updated_at' => now(),
                ]);
        } else {
            // Jika belum ada, ambil MAX(id) + 1 untuk menghindari konflik PostgreSQL sequence
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

    
}
