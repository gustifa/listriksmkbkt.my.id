<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage; // Needed for saving images

class PrayerServerSyncController extends Controller
{
    /**
     * Endpoint untuk memberikan data ke server lain
     */
    // public function exportData(Request $request)
    // {
    //     // 1. Validasi Security Sederhana
    //     $serverKey = Setting::value('server_sync_key');

    //     // Jika belum disetting di database, tolak
    //     if (!$serverKey) {
    //         return response()->json(['status' => 'error', 'message' => 'Server Sync Key not configured on source server.'], 403);
    //     }

    //     // Cek apakah key yang dikirim client sesuai
    //     if ($request->header('X-Server-Key') !== $serverKey && $request->input('key') !== $serverKey) {
    //         return response()->json(['status' => 'error', 'message' => 'Unauthorized. Invalid Key.'], 401);
    //     }

    //     // 2. Ambil Parameter Tanggal
    //     $startDate = $request->input('start_date', date('Y-m-01'));
    //     $endDate   = $request->input('end_date', date('Y-m-t'));

    //     // 3. Ambil Data dengan Relasi Siswa (PENTING: Kita butuh NIS/NISN untuk mencocokkan siswa antar server)
    //     $attendances = PrayerAttendance::with('student:id,nis,name')
    //         ->whereBetween('date', [$startDate, $endDate])
    //         ->get()
    //         ->map(function($item) {
    //             return [
    //                 'nis'           => $item->student->nis ?? null, // Kunci pencocokan
    //                 'student_name'  => $item->student->name ?? null,
    //                 'date'          => $item->date->format('Y-m-d'),
    //                 'prayer_name'   => $item->prayer_name,
    //                 'check_in_time' => $item->check_in_time,
    //                 'status'        => $item->status,
    //                 'latitude'      => $item->latitude,
    //                 'longitude'     => $item->longitude,
    //             ];
    //         });

    //     return response()->json([
    //         'status' => 'success',
    //         'count' => $attendances->count(),
    //         'data' => $attendances
    //     ]);
    // }

     /**
     * Export data absensi untuk ditarik oleh Server B
     */
    // public function exportData(Request $request)
    // {
    //     // 1. Ambil Key yang valid dari database Server A
    //     $validKey = Setting::where('key', 'server_sync_key')->value('value');

    //     // 2. Ambil Key dari Request (Bisa dari Header atau Query Parameter)
    //     $providedKey = $request->header('X-Server-Key') ?? $request->query('key');

    //     // 3. Validasi Key
    //     if (!$validKey || $providedKey !== $validKey) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Unauthorized: API Key tidak valid atau belum diatur di Server A.'
    //         ], 401);
    //     }

    //     // 4. Validasi Parameter Tanggal
    //     $startDate = $request->query('start_date');
    //     $endDate   = $request->query('end_date');

    //     if (!$startDate || !$endDate) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Parameter start_date dan end_date diperlukan.'
    //         ], 400);
    //     }

    //     try {
    //         // 5. Ambil data absensi dengan Join ke tabel Students untuk mendapatkan NIS
    //         // NIS digunakan sebagai identifier unik antar server
    //         $data = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances::student_id', '=', 'students.id')
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'total'  => $data->count(),
    //             'data'   => $data
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Internal Server Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function exportData(Request $request)
    // {
    //     // Validasi API Key sederhana untuk keamanan antar server
    //     $apiKey = $request->header('X-Sync-Key') ?? $request->query('key');
    //     if ($apiKey !== config('app.sync_token', 'token_rahasia_anda')) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $startDate = $request->query('start_date');
    //     $endDate = $request->query('end_date');

    //     // Mengambil data berdasarkan rentang tanggal
    //     $query = PrayerAttendance::query();

    //     if ($startDate && $endDate) {
    //         $query->whereBetween('created_at', [$startDate, $endDate]);
    //     }

    //     $data = $query->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'server_time' => now()->toDateTimeString(),
    //         'total_records' => $data->count(),
    //         'data' => $data
    //     ], 200);
    // }

    public function exportData(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Gunakan Query Builder untuk kontrol join yang lebih presisi
            $query = DB::table('prayer_attendances')
                ->join('students', 'prayer_attendances.student_id', '=', 'students.id') // Perbaikan: gunakan titik (.)
                ->select(
                    'students.nis',
                    'prayer_attendances.date',
                    'prayer_attendances.prayer_name',
                    'prayer_attendances.check_in_time',
                    'prayer_attendances.status',
                    'prayer_attendances.latitude',
                    'prayer_attendances.longitude'
                );

            // Filter tanggal jika tersedia
            if ($startDate && $endDate) {
                $query->whereBetween('prayer_attendances.date', [$startDate, $endDate]);
            }

            $data = $query->get();

            return response()->json([
                'status' => 'success',
                'total_records' => $data->count(),
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            // Log error untuk memudahkan debugging di sisi server
            Log::error('Export Sync Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

     /**
     * Export Terpadu: Sholat, Gerbang, dan Pembelajaran
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang (daily_attendances)
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran (attendances)
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             // Sertakan UUID schedule dan subject untuk referensi di server tujuan
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => [
    //                     'total' => $prayerData->count(),
    //                     'data' => $prayerData
    //                 ],
    //                 'gate' => [
    //                     'total' => $gateData->count(),
    //                     'data' => $gateData
    //                 ],
    //                 'learning' => [
    //                     'total' => $learningData->count(),
    //                     'data' => $learningData
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang (daily_attendances)
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran (attendances)
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             // Sertakan UUID schedule dan subject untuk referensi di server tujuan
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (journals)
    //         // Jurnal tidak perlu join ke students, tapi bergantung pada schedule_id
    //         $journalData = DB::table('teaching_journals')
    //             ->select(
    //                 'teaching_journals.schedule_id',
    //                 'teaching_journals.date',
    //                 'teaching_journals.topic',
    //                 'teaching_journals.activity',
    //                 'teaching_journals.attendance_summary',
    //                 'teaching_journals.absent_details'
    //             )
    //             ->whereBetween('teaching_journals.date', [$startDate, $endDate])
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => [
    //                     'total' => $prayerData->count(),
    //                     'data' => $prayerData
    //                 ],
    //                 'gate' => [
    //                     'total' => $gateData->count(),
    //                     'data' => $gateData
    //                 ],
    //                 'learning' => [
    //                     'total' => $learningData->count(),
    //                     'data' => $learningData
    //                 ],
    //                 'journal' => [
    //                     'total' => $journalData->count(),
    //                     'data' => $journalData
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

     /**
     * Export Terpadu: Sholat, Gerbang, Pembelajaran, dan Jurnal (teaching_journals)
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang (daily_attendances)
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran (attendances)
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             // Sertakan UUID schedule dan subject untuk referensi di server tujuan
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (teaching_journals)
    //         // Menggunakan collect([]) default agar tidak error jika tabel tidak ada
    //         $journalData = collect([]);

    //         // Cek ketersediaan tabel 'teaching_journals' sebelum query
    //         if (Schema::hasTable('teaching_journals')) {
    //             $journalData = DB::table('teaching_journals')
    //                 ->select(
    //                     'teaching_journals.schedule_id',
    //                     'teaching_journals.date',
    //                     'teaching_journals.topic',
    //                     'teaching_journals.activity',
    //                     'teaching_journals.attendance_summary',
    //                     'teaching_journals.absent_details'
    //                 )
    //                 ->whereBetween('teaching_journals.date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => [
    //                     'total' => $prayerData->count(),
    //                     'data' => $prayerData
    //                 ],
    //                 'gate' => [
    //                     'total' => $gateData->count(),
    //                     'data' => $gateData
    //                 ],
    //                 'learning' => [
    //                     'total' => $learningData->count(),
    //                     'data' => $learningData
    //                 ],
    //                 'journal' => [
    //                     'total' => $journalData->count(),
    //                     'data' => $journalData
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Export Terpadu: Sholat, Gerbang, Pembelajaran, Jurnal, dan Siswa
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang (daily_attendances)
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran (attendances)
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (teaching_journals)
    //         $journalData = collect([]);
    //         if (Schema::hasTable('teaching_journals')) {
    //             $journalData = DB::table('teaching_journals')
    //                 ->select(
    //                     'teaching_journals.schedule_id',
    //                     'teaching_journals.date',
    //                     'teaching_journals.topic',
    //                     'teaching_journals.activity',
    //                     'teaching_journals.attendance_summary',
    //                     'teaching_journals.absent_details'
    //                 )
    //                 ->whereBetween('teaching_journals.date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         // 5. Ambil Data Master Siswa (students)
    //         // Mengambil semua siswa (tidak difilter tanggal) agar data master selalu sinkron
    //         // Menggunakan leftJoin classrooms untuk mengambil nama kelas (memudahkan mapping di server tujuan)
    //         $studentData = DB::table('students')
    //             ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //             ->select(
    //                 'students.nis',
    //                 'students.name',
    //                 'students.face_descriptor',
    //                 'students.phone',
    //                 'students.address',
    //                 'classrooms.name as classroom_name'
    //             )
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => [
    //                     'total' => $prayerData->count(),
    //                     'data' => $prayerData
    //                 ],
    //                 'gate' => [
    //                     'total' => $gateData->count(),
    //                     'data' => $gateData
    //                 ],
    //                 'learning' => [
    //                     'total' => $learningData->count(),
    //                     'data' => $learningData
    //                 ],
    //                 'journal' => [
    //                     'total' => $journalData->count(),
    //                     'data' => $journalData
    //                 ],
    //                 'student' => [
    //                     'total' => $studentData->count(),
    //                     'data' => $studentData
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (teaching_journals)
    //         // FIX: Cek kolom terlebih dahulu untuk menghindari error "Column not found"
    //         $journalData = collect([]);
    //         if (Schema::hasTable('teaching_journals')) {
    //             $query = DB::table('teaching_journals');

    //             // Daftar kolom dasar yang sesuai dengan tabel Anda
    //             $selects = ['schedule_id', 'date', 'topic', 'activity'];

    //             // Tambahkan kolom yang tersedia di tabel Anda (notes & photo_evidence)
    //             if (Schema::hasColumn('teaching_journals', 'notes')) {
    //                 $selects[] = 'notes';
    //             }
    //             if (Schema::hasColumn('teaching_journals', 'photo_evidence')) {
    //                 $selects[] = 'photo_evidence';
    //             }

    //             // Handle kolom 'attendance_summary' (Server B butuh ini, kirim NULL jika tidak ada)
    //             if (Schema::hasColumn('teaching_journals', 'attendance_summary')) {
    //                 $selects[] = 'attendance_summary';
    //             } else {
    //                 $selects[] = DB::raw('NULL as attendance_summary');
    //             }

    //             // Handle kolom 'absent_details' (Server B butuh ini)
    //             if (Schema::hasColumn('teaching_journals', 'absent_details')) {
    //                 $selects[] = 'absent_details';
    //             } else {
    //                 // OPSI: Jika 'notes' ada, kita bisa mengirimnya sebagai 'absent_details' alias
    //                 // agar data catatan tidak hilang di server tujuan.
    //                 if (Schema::hasColumn('teaching_journals', 'notes')) {
    //                     $selects[] = DB::raw('notes as absent_details');
    //                 } else {
    //                     $selects[] = DB::raw('NULL as absent_details');
    //                 }
    //             }

    //             $journalData = $query->select($selects)
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         // 5. Ambil Data Master Siswa
    //         $studentData = DB::table('students')
    //             ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //             ->select('students.*', 'classrooms.name as classroom_name')
    //             ->get();

    //         // 6. MASTER DATA (Guru, Mapel, Ruangan, Jurusan)
    //         // Join users untuk ambil data login guru
    //         $teacherData = DB::table('teachers')
    //             ->join('users', 'teachers.user_id', '=', 'users.id')
    //             ->select('teachers.*', 'users.email', 'users.name as user_name', 'users.username')
    //             ->get();

    //         $subjectData = Schema::hasTable('subjects') ? DB::table('subjects')->get() : collect([]);
    //         $roomData = Schema::hasTable('rooms') ? DB::table('rooms')->get() : collect([]);
    //         $majorData = Schema::hasTable('majors') ? DB::table('majors')->get() : collect([]);

    //         // 7. Data Kelas & Jadwal
    //         $classroomData = Schema::hasTable('classrooms') ? DB::table('classrooms')->get() : collect([]);
    //         $scheduleData = Schema::hasTable('schedules') ? DB::table('schedules')->get() : collect([]);

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => ['total' => $prayerData->count(), 'data' => $prayerData],
    //                 'gate' => ['total' => $gateData->count(), 'data' => $gateData],
    //                 'learning' => ['total' => $learningData->count(), 'data' => $learningData],
    //                 'journal' => ['total' => $journalData->count(), 'data' => $journalData],
    //                 'student' => ['total' => $studentData->count(), 'data' => $studentData],
    //                 // Data Master Baru
    //                 'teacher' => ['total' => $teacherData->count(), 'data' => $teacherData],
    //                 'subject' => ['total' => $subjectData->count(), 'data' => $subjectData],
    //                 'room' => ['total' => $roomData->count(), 'data' => $roomData],
    //                 'major' => ['total' => $majorData->count(), 'data' => $majorData],
    //                 'classroom' => ['total' => $classroomData->count(), 'data' => $classroomData],
    //                 'schedule' => ['total' => $scheduleData->count(), 'data' => $scheduleData],
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Export Terpadu: Absensi, Jurnal, Siswa, Guru, Kelas, Mapel, Jadwal
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (teaching_journals)
    //         $journalData = collect([]);
    //         if (Schema::hasTable('teaching_journals')) {
    //             $query = DB::table('teaching_journals');

    //             // Daftar kolom dasar yang sesuai dengan tabel Anda
    //             $selects = ['schedule_id', 'date', 'topic', 'activity'];

    //             // Tambahkan kolom yang tersedia di tabel Anda (notes & photo_evidence)
    //             if (Schema::hasColumn('teaching_journals', 'notes')) {
    //                 $selects[] = 'notes';
    //             }
    //             if (Schema::hasColumn('teaching_journals', 'photo_evidence')) {
    //                 $selects[] = 'photo_evidence';
    //             }

    //             // Handle kolom 'attendance_summary' (Server B butuh ini, kirim NULL jika tidak ada)
    //             if (Schema::hasColumn('teaching_journals', 'attendance_summary')) {
    //                 $selects[] = 'attendance_summary';
    //             } else {
    //                 $selects[] = DB::raw('NULL as attendance_summary');
    //             }

    //             // Handle kolom 'absent_details' (Server B butuh ini)
    //             if (Schema::hasColumn('teaching_journals', 'absent_details')) {
    //                 $selects[] = 'absent_details';
    //             } else {
    //                 // OPSI: Jika 'notes' ada, kita bisa mengirimnya sebagai 'absent_details' alias
    //                 // agar data catatan tidak hilang di server tujuan.
    //                 if (Schema::hasColumn('teaching_journals', 'notes')) {
    //                     $selects[] = DB::raw('notes as absent_details');
    //                 } else {
    //                     $selects[] = DB::raw('NULL as absent_details');
    //                 }
    //             }

    //             $journalData = $query->select($selects)
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         // 5. Ambil Data Master Siswa
    //         $studentData = DB::table('students')
    //             ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //             ->select('students.*', 'classrooms.name as classroom_name')
    //             ->get();

    //         // 6. MASTER DATA (Guru, Mapel, Ruangan, Jurusan)
    //         // Join users untuk ambil data login guru
    //         $teacherData = DB::table('teachers')
    //             ->join('users', 'teachers.user_id', '=', 'users.id')
    //             ->select('teachers.*', 'users.email', 'users.name as user_name', 'users.username')
    //             ->get();

    //         $subjectData = Schema::hasTable('subjects') ? DB::table('subjects')->get() : collect([]);
    //         $roomData = Schema::hasTable('rooms') ? DB::table('rooms')->get() : collect([]);
    //         $majorData = Schema::hasTable('majors') ? DB::table('majors')->get() : collect([]);

    //         // 7. Data Kelas & Jadwal
    //         $classroomData = Schema::hasTable('classrooms') ? DB::table('classrooms')->get() : collect([]);
    //         $scheduleData = Schema::hasTable('schedules') ? DB::table('schedules')->get() : collect([]);

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => ['total' => $prayerData->count(), 'data' => $prayerData],
    //                 'gate' => ['total' => $gateData->count(), 'data' => $gateData],
    //                 'learning' => ['total' => $learningData->count(), 'data' => $learningData],
    //                 'journal' => ['total' => $journalData->count(), 'data' => $journalData],
    //                 'student' => ['total' => $studentData->count(), 'data' => $studentData],
    //                 // Data Master Baru
    //                 'teacher' => ['total' => $teacherData->count(), 'data' => $teacherData],
    //                 'subject' => ['total' => $subjectData->count(), 'data' => $subjectData],
    //                 'room' => ['total' => $roomData->count(), 'data' => $roomData],
    //                 'major' => ['total' => $majorData->count(), 'data' => $majorData],
    //                 'classroom' => ['total' => $classroomData->count(), 'data' => $classroomData],
    //                 'schedule' => ['total' => $scheduleData->count(), 'data' => $scheduleData],
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

     /**
     * Export Terpadu: Absensi, Jurnal, Siswa, Guru, Kelas, Mapel, Jadwal, MBG, & Izin
     */
    // public function exportAll(Request $request)
    // {
    //     try {
    //         $startDate = $request->query('start_date');
    //         $endDate = $request->query('end_date');

    //         if (!$startDate || !$endDate) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
    //             ], 400);
    //         }

    //         // 1. Ambil Data Absensi Sholat
    //         $prayerData = DB::table('prayer_attendances')
    //             ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'prayer_attendances.date',
    //                 'prayer_attendances.prayer_name',
    //                 'prayer_attendances.check_in_time',
    //                 'prayer_attendances.status',
    //                 'prayer_attendances.latitude',
    //                 'prayer_attendances.longitude'
    //             )
    //             ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 2. Ambil Data Absensi Gerbang
    //         $gateData = DB::table('daily_attendances')
    //             ->join('students', 'daily_attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'daily_attendances.date',
    //                 'daily_attendances.arrival_time',
    //                 'daily_attendances.departure_time',
    //                 'daily_attendances.status',
    //                 'daily_attendances.recorded_by'
    //             )
    //             ->whereBetween('daily_attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 3. Ambil Data Absensi Pembelajaran
    //         $learningData = DB::table('attendances')
    //             ->join('students', 'attendances.student_id', '=', 'students.id')
    //             ->select(
    //                 'students.nis',
    //                 'attendances.schedule_id',
    //                 'attendances.subject_id',
    //                 'attendances.date',
    //                 'attendances.check_in_time',
    //                 'attendances.status',
    //                 'attendances.recorded_by'
    //             )
    //             ->whereBetween('attendances.date', [$startDate, $endDate])
    //             ->get();

    //         // 4. Ambil Data Jurnal Guru (teaching_journals)
    //         $journalData = collect([]);
    //         if (Schema::hasTable('teaching_journals')) {
    //             $query = DB::table('teaching_journals');
    //             $selects = ['schedule_id', 'date', 'topic', 'activity'];
                
    //             if (Schema::hasColumn('teaching_journals', 'notes')) $selects[] = 'notes';
    //             if (Schema::hasColumn('teaching_journals', 'photo_evidence')) $selects[] = 'photo_evidence';
                
    //             // Handle kolom opsional/mapping
    //             $selects[] = Schema::hasColumn('teaching_journals', 'attendance_summary') ? 'attendance_summary' : DB::raw('NULL as attendance_summary');
    //             $selects[] = Schema::hasColumn('teaching_journals', 'absent_details') ? 'absent_details' : DB::raw('NULL as absent_details');

    //             $journalData = $query->select($selects)
    //                 ->whereBetween('date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         // 5. Ambil Data Master Siswa
    //         $studentData = DB::table('students')
    //             ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
    //             ->select('students.*', 'classrooms.name as classroom_name')
    //             ->get();

    //         // 6. MASTER DATA (Guru, Mapel, Ruangan, Jurusan)
    //         $teacherData = DB::table('teachers')
    //             ->join('users', 'teachers.user_id', '=', 'users.id')
    //             ->select('teachers.*', 'users.email', 'users.name as user_name', 'users.username')
    //             ->get();

    //         $subjectData = Schema::hasTable('subjects') ? DB::table('subjects')->get() : collect([]);
    //         $roomData = Schema::hasTable('rooms') ? DB::table('rooms')->get() : collect([]);
    //         $majorData = Schema::hasTable('majors') ? DB::table('majors')->get() : collect([]);
            
    //         // 7. Data Kelas & Jadwal
    //         $classroomData = Schema::hasTable('classrooms') ? DB::table('classrooms')->get() : collect([]);
    //         $scheduleData = Schema::hasTable('schedules') ? DB::table('schedules')->get() : collect([]);

    //         // 8. Data Absensi MBG (Makan Bergizi Gratis)
    //         $mbgData = collect([]);
    //         if (Schema::hasTable('mbg_attendances')) {
    //             $mbgData = DB::table('mbg_attendances')
    //                 ->join('students', 'mbg_attendances.student_id', '=', 'students.id')
    //                 ->select(
    //                     'students.nis',
    //                     'mbg_attendances.date',
    //                     'mbg_attendances.check_in_time',
    //                     'mbg_attendances.status',
    //                     'mbg_attendances.method',
    //                     'mbg_attendances.image_evidence'
    //                 )
    //                 ->whereBetween('mbg_attendances.date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         // 9. Data Izin Siswa (Student Permits) - BARU
    //         $permitData = collect([]);
    //         if (Schema::hasTable('student_permits')) {
    //             $permitData = DB::table('student_permits')
    //                 ->join('students', 'student_permits.student_id', '=', 'students.id')
    //                 ->select(
    //                     'students.nis',
    //                     'student_permits.id', // Kirim ID asli agar sync update berjalan tepat
    //                     'student_permits.date',
    //                     'student_permits.time_out',
    //                     'student_permits.time_in',
    //                     'student_permits.reason',
    //                     'student_permits.description',
    //                     'student_permits.status',
    //                     'student_permits.method',
    //                     'student_permits.image_evidence',
    //                     'student_permits.recorded_by'
    //                 )
    //                 ->whereBetween('student_permits.date', [$startDate, $endDate])
    //                 ->get();
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'filter' => [
    //                 'start' => $startDate,
    //                 'end' => $endDate
    //             ],
    //             'results' => [
    //                 'prayer' => ['total' => $prayerData->count(), 'data' => $prayerData],
    //                 'gate' => ['total' => $gateData->count(), 'data' => $gateData],
    //                 'learning' => ['total' => $learningData->count(), 'data' => $learningData],
    //                 'journal' => ['total' => $journalData->count(), 'data' => $journalData],
    //                 'student' => ['total' => $studentData->count(), 'data' => $studentData],
    //                 'teacher' => ['total' => $teacherData->count(), 'data' => $teacherData],
    //                 'subject' => ['total' => $subjectData->count(), 'data' => $subjectData],
    //                 'room' => ['total' => $roomData->count(), 'data' => $roomData],
    //                 'major' => ['total' => $majorData->count(), 'data' => $majorData],
    //                 'classroom' => ['total' => $classroomData->count(), 'data' => $classroomData],
    //                 'schedule' => ['total' => $scheduleData->count(), 'data' => $scheduleData],
    //                 'mbg' => ['total' => $mbgData->count(), 'data' => $mbgData],
    //                 'permit' => ['total' => $permitData->count(), 'data' => $permitData], // Tambahan Baru
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Full Sync Export Error: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Helper untuk mengubah file gambar fisik menjadi Base64 string.
     * * @param \Illuminate\Support\Collection $collection
     * @param array $fields Daftar nama kolom yang berisi path gambar
     * @return \Illuminate\Support\Collection
     */
    private function attachBase64Images($collection, $fields)
    {
        return $collection->map(function ($item) use ($fields) {
            foreach ($fields as $field) {
                // Cek apakah kolom ada dan memiliki path gambar
                if (isset($item->$field) && !empty($item->$field)) {
                    $path = $item->$field;
                    // Cek apakah file fisik ada di storage public
                    if (Storage::disk('public')->exists($path)) {
                        try {
                            $fileContent = Storage::disk('public')->get($path);
                            // Buat key baru dengan akhiran '_base64' (contoh: photo_evidence_base64)
                            $key = $field . '_base64';
                            $item->$key = base64_encode($fileContent);
                        } catch (\Exception $e) {
                            Log::error("Gagal encode gambar $path: " . $e->getMessage());
                        }
                    }
                }
            }
            return $item;
        });
    }

    /**
     * Export Terpadu: Absensi, Jurnal, Siswa, Guru, Kelas, Mapel, Jadwal, MBG, & Izin
     */
    public function exportAll(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if (!$startDate || !$endDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter start_date dan end_date wajib diisi (YYYY-MM-DD).'
                ], 400);
            }

            // --- 1. DATA MASTER & BASIC ---

            // Absensi Sholat (Tanpa Gambar)
            $prayerData = DB::table('prayer_attendances')
                ->join('students', 'prayer_attendances.student_id', '=', 'students.id')
                ->select(
                    'students.nis',
                    'prayer_attendances.date',
                    'prayer_attendances.prayer_name',
                    'prayer_attendances.check_in_time',
                    'prayer_attendances.status',
                    'prayer_attendances.latitude',
                    'prayer_attendances.longitude'
                )
                ->whereBetween('prayer_attendances.date', [$startDate, $endDate])
                ->get();

            // Absensi Gerbang
            $gateData = DB::table('daily_attendances')
                ->join('students', 'daily_attendances.student_id', '=', 'students.id')
                ->select(
                    'students.nis',
                    'daily_attendances.date',
                    'daily_attendances.arrival_time',
                    'daily_attendances.departure_time',
                    'daily_attendances.status',
                    'daily_attendances.recorded_by'
                )
                ->whereBetween('daily_attendances.date', [$startDate, $endDate])
                ->get();

            // Absensi Pembelajaran
            $learningData = DB::table('attendances')
                ->join('students', 'attendances.student_id', '=', 'students.id')
                ->select(
                    'students.nis',
                    'attendances.schedule_id',
                    'attendances.subject_id',
                    'attendances.date',
                    'attendances.check_in_time',
                    'attendances.status',
                    'attendances.recorded_by'
                )
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->get();

            // Data Master Siswa
            $studentData = DB::table('students')
                ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->select('students.*', 'classrooms.name as classroom_name')
                ->get();

            // Data Master Guru
            $teacherData = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->select('teachers.*', 'users.email', 'users.name as user_name', 'users.username')
                ->get();

            // Data Master Lainnya
            $subjectData = Schema::hasTable('subjects') ? DB::table('subjects')->get() : collect([]);
            $roomData = Schema::hasTable('rooms') ? DB::table('rooms')->get() : collect([]);
            $majorData = Schema::hasTable('majors') ? DB::table('majors')->get() : collect([]);
            $classroomData = Schema::hasTable('classrooms') ? DB::table('classrooms')->get() : collect([]);
            $scheduleData = Schema::hasTable('schedules') ? DB::table('schedules')->get() : collect([]);


            // --- 2. DATA DENGAN GAMBAR (JURNAL, MBG, PERMIT) ---

            // A. Jurnal Guru (Ada Foto: photo_evidence)
            $journalData = collect([]);
            if (Schema::hasTable('teaching_journals')) {
                $query = DB::table('teaching_journals')->whereBetween('date', [$startDate, $endDate]);
                
                // Ambil semua kolom
                $journalData = $query->get();
                
                // Encode Gambar Jurnal jika kolomnya ada
                if (Schema::hasColumn('teaching_journals', 'photo_evidence')) {
                    $journalData = $this->attachBase64Images($journalData, ['photo_evidence']);
                }
            }

            // B. MBG Attendance (Ada Foto: image_evidence, taken_image, returned_image)
            $mbgData = collect([]);
            if (Schema::hasTable('mbg_attendances')) {
                $mbgData = DB::table('mbg_attendances')
                    ->join('students', 'mbg_attendances.student_id', '=', 'students.id')
                    ->select('students.nis', 'mbg_attendances.*') // Select * agar ID dan semua kolom terbawa
                    ->whereBetween('mbg_attendances.date', [$startDate, $endDate])
                    ->get();

                // Daftar kolom gambar yang mungkin ada
                $mbgImageFields = [];
                if (Schema::hasColumn('mbg_attendances', 'image_evidence')) $mbgImageFields[] = 'image_evidence';
                if (Schema::hasColumn('mbg_attendances', 'taken_image')) $mbgImageFields[] = 'taken_image';
                if (Schema::hasColumn('mbg_attendances', 'returned_image')) $mbgImageFields[] = 'returned_image';
                
                if(!empty($mbgImageFields)) {
                    $mbgData = $this->attachBase64Images($mbgData, $mbgImageFields);
                }
            }

            // C. Izin Siswa / Student Permits (Ada Foto: image_evidence)
            $permitData = collect([]);
            if (Schema::hasTable('student_permits')) {
                $permitData = DB::table('student_permits')
                    ->join('students', 'student_permits.student_id', '=', 'students.id')
                    ->select('students.nis', 'student_permits.*') // Select * agar ID terbawa untuk sync update
                    ->whereBetween('student_permits.date', [$startDate, $endDate])
                    ->get();
                
                // Encode Gambar Permit
                if (Schema::hasColumn('student_permits', 'image_evidence')) {
                    $permitData = $this->attachBase64Images($permitData, ['image_evidence']);
                }
            }

            return response()->json([
                'status' => 'success',
                'filter' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'results' => [
                    'prayer' => ['total' => $prayerData->count(), 'data' => $prayerData],
                    'gate' => ['total' => $gateData->count(), 'data' => $gateData],
                    'learning' => ['total' => $learningData->count(), 'data' => $learningData],
                    'student' => ['total' => $studentData->count(), 'data' => $studentData],
                    'teacher' => ['total' => $teacherData->count(), 'data' => $teacherData],
                    'subject' => ['total' => $subjectData->count(), 'data' => $subjectData],
                    'room' => ['total' => $roomData->count(), 'data' => $roomData],
                    'major' => ['total' => $majorData->count(), 'data' => $majorData],
                    'classroom' => ['total' => $classroomData->count(), 'data' => $classroomData],
                    'schedule' => ['total' => $scheduleData->count(), 'data' => $scheduleData],
                    // Data dengan Gambar Base64
                    'journal' => ['total' => $journalData->count(), 'data' => $journalData],
                    'mbg' => ['total' => $mbgData->count(), 'data' => $mbgData],
                    'permit' => ['total' => $permitData->count(), 'data' => $permitData],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Full Sync Export Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}
