<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PrayerSchedule;
use App\Models\PrayerAttendance; // Tambahan untuk pullAttendance
use App\Models\Student;          // Tambahan untuk pullAttendance
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage; // Needed for saving images
// use Illuminate\Support\Facades\Http; // Dihapus karena diganti cURL Native

class PrayerSettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Lokasi Masjid & Sinkronisasi
     */
    public function index()
    {
        // Ambil data setting atau gunakan default
        $lat    = Setting::value('masjid_lat', -0.305123);
        $lng    = Setting::value('masjid_lng', 100.369456);
        $radius = Setting::value('masjid_radius', 50);
        $cityId = Setting::value('prayer_city_id', '0306'); // Default Bukittinggi

        // Setting Sinkronisasi Antar Server (Baru)
        $myServerKey    = Setting::value('server_sync_key', \Illuminate\Support\Str::random(32)); // Key lokal
        $targetUrl      = Setting::value('target_sync_url'); // URL Server Sumber
        $targetKey      = Setting::value('target_sync_key'); // Key Server Sumber

        // Data untuk dropdown bulan/tahun sinkronisasi
        $years = range(Carbon::now()->year, Carbon::now()->year + 1);
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.prayer.settings', compact('lat', 'lng', 'radius', 'cityId', 'years', 'months', 'myServerKey', 'targetUrl', 'targetKey'));
    }

    /**
     * Simpan Pengaturan Lokasi & ID Kota & Server Sync
     */
    public function update(Request $request)
    {
        $request->validate([
            'masjid_lat'    => 'required|numeric',
            'masjid_lng'    => 'required|numeric',
            'masjid_radius' => 'required|numeric|min:5|max:1000',
            'prayer_city_id'=> 'required|string',
        ]);

        // Simpan ke tabel settings
        Setting::updateOrCreate(['key' => 'masjid_lat'], ['value' => $request->masjid_lat]);
        Setting::updateOrCreate(['key' => 'masjid_lng'], ['value' => $request->masjid_lng]);
        Setting::updateOrCreate(['key' => 'masjid_radius'], ['value' => $request->masjid_radius]);
        Setting::updateOrCreate(['key' => 'prayer_city_id'], ['value' => $request->prayer_city_id]);

        // Simpan Konfigurasi Sync Server (Baru)
        if ($request->has('server_sync_key')) {
            Setting::updateOrCreate(['key' => 'server_sync_key'], ['value' => $request->server_sync_key]);
        }
        if ($request->has('target_sync_url')) {
            Setting::updateOrCreate(['key' => 'target_sync_url'], ['value' => $request->target_sync_url]);
        }
        if ($request->has('target_sync_key')) {
            Setting::updateOrCreate(['key' => 'target_sync_key'], ['value' => $request->target_sync_key]);
        }

        return redirect()->back()->with('success', 'Semua pengaturan berhasil diperbarui!');
    }

    /**
     * Fitur Sinkronisasi Jadwal ke Database (MyQuran)
     */
    public function sync(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'month' => 'required|numeric|min:1|max:12',
        ]);

        $cityId = Setting::value('prayer_city_id', '0306');
        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

        try {
            $url = "https://api.myquran.com/v2/sholat/jadwal/$cityId/$year/$month";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode == 200 && $responseBody) {
                $data = json_decode($responseBody, true);

                if (isset($data['data']['jadwal'])) {
                    $schedules = $data['data']['jadwal'];
                    $count = 0;

                    foreach ($schedules as $sched) {
                        PrayerSchedule::updateOrCreate(
                            ['date' => $sched['date']],
                            [
                                'subuh'   => $sched['subuh'],
                                'dhuha'   => $sched['dhuha'],
                                'dzuhur'  => $sched['dzuhur'],
                                'ashar'   => $sched['ashar'],
                                'maghrib' => $sched['maghrib'],
                                'isya'    => $sched['isya'],
                            ]
                        );
                        $count++;
                    }

                    return redirect()->back()->with('success', "Berhasil sinkronisasi $count data jadwal sholat untuk periode $month/$year.");
                } else {
                    return redirect()->back()->with('error', 'Format data API MyQuran tidak sesuai.');
                }
            } else {
                return redirect()->back()->with('error', "Gagal menghubungi API MyQuran. Status: $httpCode");
            }

        } catch (\Exception $e) {
            Log::error("Sync Prayer Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * BARU: Tarik Data Absensi dari Server Lain
     * MENGGUNAKAN CURL NATIVE (Untuk menghindari error 'Undefined method successful')
     */
    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $targetUrl = Setting::value('target_sync_url');
    //     $targetKey = Setting::value('target_sync_key');

    //     if (!$targetUrl || !$targetKey) {
    //         return redirect()->back()->with('error', 'URL atau Key Server Sumber belum dikonfigurasi. Silakan isi di form pengaturan.');
    //     }

    //     try {
    //         // Pastikan URL valid dan arahkan ke endpoint export
    //         // Hapus trailing slash jika ada, lalu tambah path api
    //         $endpoint = rtrim($targetUrl, '/') . '/api/prayer/sync-export';

    //         // Bangun query string
    //         $queryParams = http_build_query([
    //             'start_date' => $request->start_date,
    //             'end_date'   => $request->end_date,
    //             'key'        => $targetKey // Fallback jika header tidak terbaca di beberapa server
    //         ]);

    //         $urlWithParams = $endpoint . '?' . $queryParams;

    //         // Inisialisasi cURL
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    //         curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout lebih lama untuk data banyak

    //         // Bypass SSL untuk menghindari error sertifikat
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    //         // Set Headers
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //             'X-Server-Key: ' . $targetKey,
    //             'Accept: application/json'
    //         ]);

    //         // User Agent
    //         curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel App Sync Client');

    //         // Eksekusi
    //         $responseBody = curl_exec($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         $curlError = curl_error($ch);
    //         curl_close($ch);

    //         // Proses Response
    //         if ($httpCode == 200 && $responseBody) {
    //             $result = json_decode($responseBody, true);

    //             if (isset($result['status']) && $result['status'] === 'success') {
    //                 $attendances = $result['data'];
    //                 $importedCount = 0;
    //                 $skippedCount = 0;

    //                 foreach ($attendances as $row) {
    //                     // Cari siswa lokal berdasarkan NIS (Pencocokan Data)
    //                     // Pastikan table students punya kolom 'nis'
    //                     $localStudent = Student::where('nis', $row['nis'])->first();

    //                     if ($localStudent) {
    //                         // Update atau Buat Data Absensi
    //                         PrayerAttendance::updateOrCreate(
    //                             [
    //                                 'student_id'  => $localStudent->id,
    //                                 'date'        => $row['date'],
    //                                 'prayer_name' => $row['prayer_name']
    //                             ],
    //                             [
    //                                 'check_in_time' => $row['check_in_time'],
    //                                 'status'        => $row['status'],
    //                                 'latitude'      => $row['latitude'],
    //                                 'longitude'     => $row['longitude'],
    //                                 // Timestamp bisa diupdate atau biarkan default
    //                             ]
    //                         );
    //                         $importedCount++;
    //                     } else {
    //                         $skippedCount++; // Siswa tidak ditemukan di database lokal
    //                     }
    //                 }

    //                 $msg = "Berhasil menarik $importedCount data absensi.";
    //                 if ($skippedCount > 0) $msg .= " ($skippedCount data dilewati karena NIS siswa tidak ditemukan di server ini).";

    //                 return redirect()->back()->with('success', $msg);
    //             } else {
    //                 $errorMsg = $result['message'] ?? 'Unknown Error from Source Server';
    //                 return redirect()->back()->with('error', 'Server Sumber menolak: ' . $errorMsg);
    //             }
    //         } else {
    //             return redirect()->back()->with('error', "Gagal koneksi ke server sumber. HTTP Status: $httpCode. Error: $curlError");
    //         }

    //     } catch (\Exception $e) {
    //         Log::error("Server Pull Error: " . $e->getMessage());
    //         return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menarik data: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate', // Validasi tipe
    //     ]);

    //     $syncType = $request->input('type', 'all'); // Default 'all' jika tidak ada input

    //     // Ambil config dari table settings
    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         // Memanggil HTTP Client Laravel
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying() // <--- FIX: Bypass SSL Verification (cURL Error 60)
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120) // Timeout 2 menit
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];
    //         $processCount = 0;

    //         DB::beginTransaction();

    //         // 1. Sinkronisasi Sholat
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     DB::table('prayer_attendances')->updateOrInsert(
    //                         ['student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name']],
    //                         [
    //                             'check_in_time' => $p['check_in_time'],
    //                             'status' => $p['status'],
    //                             'latitude' => $p['latitude'],
    //                             'longitude' => $p['longitude'],
    //                             'updated_at' => now()
    //                         ]
    //                     );
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // 2. Sinkronisasi Gerbang
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     DB::table('daily_attendances')->updateOrInsert(
    //                         ['student_id' => $studentId, 'date' => $g['date']],
    //                         [
    //                             'id' => (string) Str::uuid(),
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]
    //                     );
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // 3. Sinkronisasi Pembelajaran
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     DB::table('attendances')->updateOrInsert(
    //                         ['student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date']],
    //                         [
    //                             'id' => (string) Str::uuid(),
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]
    //                     );
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data',
    //             'prayer' => 'Absensi Sholat',
    //             'gate' => 'Absensi Gerbang',
    //             'learning' => 'Absensi Pembelajaran'
    //         ];

    //         return back()->with('success', "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data') . " berhasil ($processCount data diproses).");

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate', // Validasi tipe
    //     ]);

    //     $syncType = $request->input('type', 'all'); // Default 'all' jika tidak ada input

    //     // Ambil config dari table settings
    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         // Memanggil HTTP Client Laravel
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying() // FIX: Bypass SSL Verification (cURL Error 60)
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120) // Timeout 2 menit
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         // --- DIAGNOSA: CEK APAKAH ADA DATA DARI SERVER? ---
    //         $totalRemote = ($results['prayer']['total'] ?? 0) + ($results['gate']['total'] ?? 0) + ($results['learning']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi ke Server Sumber BERHASIL, tetapi TIDAK ADA DATA absensi pada rentang tanggal tersebut.");
    //         }
    //         // ---------------------------------------------------

    //         $processCount = 0;
    //         $skippedCount = 0; // Menghitung data yang dilewati karena NIS tidak ketemu

    //         DB::beginTransaction();

    //         // 1. Sinkronisasi Sholat (LOGIKA DIPERBAIKI: Generate UUID manual)
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek Manual apakah data sudah ada
    //                     $exists = DB::table('prayer_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $p['date'])
    //                                 ->where('prayer_name', $p['prayer_name'])
    //                                 ->first();

    //                     if ($exists) {
    //                         // UPDATE
    //                         DB::table('prayer_attendances')
    //                             ->where('id', $exists->id)
    //                             ->update([
    //                                 'check_in_time' => $p['check_in_time'],
    //                                 'status' => $p['status'],
    //                                 'latitude' => $p['latitude'],
    //                                 'longitude' => $p['longitude'],
    //                                 'updated_at' => now()
    //                             ]);
    //                     } else {
    //                         // INSERT BARU (Generate UUID)
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), // <--- FIX ERROR NULL ID
    //                             'student_id' => $studentId,
    //                             'date' => $p['date'],
    //                             'prayer_name' => $p['prayer_name'],
    //                             'check_in_time' => $p['check_in_time'],
    //                             'status' => $p['status'],
    //                             'latitude' => $p['latitude'],
    //                             'longitude' => $p['longitude'],
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 2. Sinkronisasi Gerbang (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek eksistensi manual karena UUID tidak boleh di-update sembarangan
    //                     $exists = DB::table('daily_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $g['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'date' => $g['date'],
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 3. Sinkronisasi Pembelajaran (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');

    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('schedule_id', $l['schedule_id'])
    //                                 ->where('date', $l['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'schedule_id' => $l['schedule_id'],
    //                             'date' => $l['date'],
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data',
    //             'prayer' => 'Absensi Sholat',
    //             'gate' => 'Absensi Gerbang',
    //             'learning' => 'Absensi Pembelajaran'
    //         ];

    //         // LOGIKA PESAN HASIL
    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 // Jika ada sukses tapi ada juga yang gagal
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan di database lokal.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             // Jika 0 processed
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL MEMPROSES DATA. $skippedCount data ditemukan tetapi NIS Siswa tidak ada yang cocok di database lokal ini.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    //terbaru
    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate', // Validasi tipe
    //     ]);

    //     $syncType = $request->input('type', 'all'); // Default 'all' jika tidak ada input

    //     // Ambil config dari table settings
    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         // Memanggil HTTP Client Laravel
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying() // FIX: Bypass SSL Verification (cURL Error 60)
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120) // Timeout 2 menit
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         // --- DIAGNOSA: CEK APAKAH ADA DATA DARI SERVER? ---
    //         $totalRemote = ($results['prayer']['total'] ?? 0) + ($results['gate']['total'] ?? 0) + ($results['learning']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi ke Server Sumber BERHASIL, tetapi TIDAK ADA DATA absensi pada rentang tanggal tersebut.");
    //         }
    //         // ---------------------------------------------------

    //         $processCount = 0;
    //         $skippedCount = 0; // Menghitung data yang dilewati karena NIS tidak ketemu

    //         DB::beginTransaction();

    //         // 1. Sinkronisasi Sholat (LOGIKA DIPERBAIKI: Generate UUID manual)
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek Manual apakah data sudah ada
    //                     $exists = DB::table('prayer_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $p['date'])
    //                                 ->where('prayer_name', $p['prayer_name'])
    //                                 ->first();

    //                     if ($exists) {
    //                         // UPDATE
    //                         DB::table('prayer_attendances')
    //                             ->where('id', $exists->id)
    //                             ->update([
    //                                 'check_in_time' => $p['check_in_time'],
    //                                 'status' => $p['status'],
    //                                 'latitude' => $p['latitude'],
    //                                 'longitude' => $p['longitude'],
    //                                 'updated_at' => now()
    //                             ]);
    //                     } else {
    //                         // INSERT BARU (Generate UUID)
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), // <--- FIX ERROR NULL ID
    //                             'student_id' => $studentId,
    //                             'date' => $p['date'],
    //                             'prayer_name' => $p['prayer_name'],
    //                             'check_in_time' => $p['check_in_time'],
    //                             'status' => $p['status'],
    //                             'latitude' => $p['latitude'],
    //                             'longitude' => $p['longitude'],
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 2. Sinkronisasi Gerbang (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek eksistensi manual karena UUID tidak boleh di-update sembarangan
    //                     $exists = DB::table('daily_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $g['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'date' => $g['date'],
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 3. Sinkronisasi Pembelajaran (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');

    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('schedule_id', $l['schedule_id'])
    //                                 ->where('date', $l['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'schedule_id' => $l['schedule_id'],
    //                             'date' => $l['date'],
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data',
    //             'prayer' => 'Absensi Sholat',
    //             'gate' => 'Absensi Gerbang',
    //             'learning' => 'Absensi Pembelajaran'
    //         ];

    //         // LOGIKA PESAN HASIL
    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 // Jika ada sukses tapi ada juga yang gagal
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan di database lokal.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             // Jika 0 processed
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL MEMPROSES DATA. $skippedCount data ditemukan tetapi NIS Siswa tidak ada yang cocok di database lokal ini.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal', // Validasi tipe ditambah journal
    //     ]);

    //     $syncType = $request->input('type', 'all'); // Default 'all' jika tidak ada input

    //     // Ambil config dari table settings
    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         // Memanggil HTTP Client Laravel
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying() // FIX: Bypass SSL Verification (cURL Error 60)
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120) // Timeout 2 menit
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         // --- DIAGNOSA: CEK APAKAH ADA DATA DARI SERVER? ---
    //         $totalRemote = ($results['prayer']['total'] ?? 0) +
    //                        ($results['gate']['total'] ?? 0) +
    //                        ($results['learning']['total'] ?? 0) +
    //                        ($results['journal']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi ke Server Sumber BERHASIL, tetapi TIDAK ADA DATA absensi/jurnal pada rentang tanggal tersebut.");
    //         }
    //         // ---------------------------------------------------

    //         $processCount = 0;
    //         $skippedCount = 0; // Menghitung data yang dilewati karena NIS tidak ketemu

    //         DB::beginTransaction();

    //         // 1. Sinkronisasi Sholat (LOGIKA DIPERBAIKI: Generate UUID manual)
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek Manual apakah data sudah ada
    //                     $exists = DB::table('prayer_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $p['date'])
    //                                 ->where('prayer_name', $p['prayer_name'])
    //                                 ->first();

    //                     if ($exists) {
    //                         // UPDATE
    //                         DB::table('prayer_attendances')
    //                             ->where('id', $exists->id)
    //                             ->update([
    //                                 'check_in_time' => $p['check_in_time'],
    //                                 'status' => $p['status'],
    //                                 'latitude' => $p['latitude'],
    //                                 'longitude' => $p['longitude'],
    //                                 'updated_at' => now()
    //                             ]);
    //                     } else {
    //                         // INSERT BARU (Generate UUID)
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), // <--- FIX ERROR NULL ID
    //                             'student_id' => $studentId,
    //                             'date' => $p['date'],
    //                             'prayer_name' => $p['prayer_name'],
    //                             'check_in_time' => $p['check_in_time'],
    //                             'status' => $p['status'],
    //                             'latitude' => $p['latitude'],
    //                             'longitude' => $p['longitude'],
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 2. Sinkronisasi Gerbang (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek eksistensi manual karena UUID tidak boleh di-update sembarangan
    //                     $exists = DB::table('daily_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $g['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'date' => $g['date'],
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 3. Sinkronisasi Pembelajaran (Menggunakan UUID Manual Check)
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');

    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('schedule_id', $l['schedule_id'])
    //                                 ->where('date', $l['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'schedule_id' => $l['schedule_id'],
    //                             'date' => $l['date'],
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 4. Sinkronisasi Jurnal (Journal)
    //         if (($syncType === 'all' || $syncType === 'teaching_journals') && isset($results['teaching_journals']['data'])) {
    //             foreach ($results['teaching_journals']['data'] as $j) {
    //                 // Cek eksistensi jurnal berdasarkan Jadwal dan Tanggal
    //                 $exists = DB::table('teaching_journals')
    //                             ->where('schedule_id', $j['schedule_id'])
    //                             ->where('date', $j['date'])
    //                             ->first();

    //                 // Pastikan format JSON valid untuk kolom attendance_summary
    //                 $attendanceSummary = is_array($j['attendance_summary'])
    //                                     ? json_encode($j['attendance_summary'])
    //                                     : $j['attendance_summary'];

    //                 $journalData = [
    //                     'topic' => $j['topic'],
    //                     'activity' => $j['activity'],
    //                     'attendance_summary' => $attendanceSummary,
    //                     'absent_details' => $j['absent_details'],
    //                     'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('teaching_journals')->where('id', $exists->id)->update($journalData);
    //                 } else {
    //                     $journalData['id'] = (string) Str::uuid();
    //                     $journalData['schedule_id'] = $j['schedule_id'];
    //                     $journalData['date'] = $j['date'];
    //                     $journalData['created_at'] = now();

    //                     DB::table('teaching_journals')->insert($journalData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data',
    //             'prayer' => 'Absensi Sholat',
    //             'gate' => 'Absensi Gerbang',
    //             'learning' => 'Absensi Pembelajaran',
    //             'journal' => 'Jurnal Guru'
    //         ];

    //         // LOGIKA PESAN HASIL
    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 // Jika ada sukses tapi ada juga yang gagal
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan di database lokal.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             // Jika 0 processed
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL MEMPROSES DATA. $skippedCount data ditemukan tetapi NIS Siswa tidak ada yang cocok di database lokal ini.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal', // Validasi tipe ditambah journal
    //     ]);

    //     $syncType = $request->input('type', 'all'); // Default 'all' jika tidak ada input

    //     // Ambil config dari table settings
    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         // Memanggil HTTP Client Laravel
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying() // FIX: Bypass SSL Verification (cURL Error 60)
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120) // Timeout 2 menit
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         // --- DIAGNOSA: CEK APAKAH ADA DATA DARI SERVER? ---
    //         $totalRemote = ($results['prayer']['total'] ?? 0) +
    //                        ($results['gate']['total'] ?? 0) +
    //                        ($results['learning']['total'] ?? 0) +
    //                        ($results['journal']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi ke Server Sumber BERHASIL, tetapi TIDAK ADA DATA absensi/jurnal pada rentang tanggal tersebut.");
    //         }
    //         // ---------------------------------------------------

    //         $processCount = 0;
    //         $skippedCount = 0; // Menghitung data yang dilewati karena NIS tidak ketemu

    //         DB::beginTransaction();

    //         // 1. Sinkronisasi Sholat
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');

    //                 if ($studentId) {
    //                     // Cek Manual apakah data sudah ada
    //                     $exists = DB::table('prayer_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $p['date'])
    //                                 ->where('prayer_name', $p['prayer_name'])
    //                                 ->first();

    //                     if ($exists) {
    //                         // UPDATE
    //                         DB::table('prayer_attendances')
    //                             ->where('id', $exists->id)
    //                             ->update([
    //                                 'check_in_time' => $p['check_in_time'],
    //                                 'status' => $p['status'],
    //                                 'latitude' => $p['latitude'],
    //                                 'longitude' => $p['longitude'],
    //                                 'updated_at' => now()
    //                             ]);
    //                     } else {
    //                         // INSERT BARU (Generate UUID)
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'date' => $p['date'],
    //                             'prayer_name' => $p['prayer_name'],
    //                             'check_in_time' => $p['check_in_time'],
    //                             'status' => $p['status'],
    //                             'latitude' => $p['latitude'],
    //                             'longitude' => $p['longitude'],
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 2. Sinkronisasi Gerbang
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');

    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('date', $g['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'date' => $g['date'],
    //                             'arrival_time' => $g['arrival_time'],
    //                             'departure_time' => $g['departure_time'],
    //                             'status' => $g['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 3. Sinkronisasi Pembelajaran
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');

    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')
    //                                 ->where('student_id', $studentId)
    //                                 ->where('schedule_id', $l['schedule_id'])
    //                                 ->where('date', $l['date'])
    //                                 ->first();

    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(),
    //                             'student_id' => $studentId,
    //                             'schedule_id' => $l['schedule_id'],
    //                             'date' => $l['date'],
    //                             'subject_id' => $l['subject_id'],
    //                             'check_in_time' => $l['check_in_time'],
    //                             'status' => $l['status'],
    //                             'recorded_by' => 'Sync System',
    //                             'created_at' => now(),
    //                             'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // 4. Sinkronisasi Jurnal (teaching_journals)
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($results['journal']['data'])) {
    //             foreach ($results['journal']['data'] as $j) {
    //                 // Cek eksistensi jurnal berdasarkan Jadwal dan Tanggal
    //                 // Asumsi: schedule_id sinkron antar server
    //                 $exists = DB::table('teaching_journals')
    //                             ->where('schedule_id', $j['schedule_id'])
    //                             ->where('date', $j['date'])
    //                             ->first();

    //                 // Pastikan format JSON valid untuk kolom attendance_summary
    //                 // Data dari API (JSON) otomatis di-decode jadi array oleh HTTP client Laravel
    //                 $attendanceSummary = is_array($j['attendance_summary'])
    //                                     ? json_encode($j['attendance_summary'])
    //                                     : $j['attendance_summary'];

    //                 $journalData = [
    //                     'topic' => $j['topic'],
    //                     'activity' => $j['activity'],
    //                     'attendance_summary' => $attendanceSummary,
    //                     'absent_details' => $j['absent_details'],
    //                     'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('teaching_journals')->where('id', $exists->id)->update($journalData);
    //                 } else {
    //                     $journalData['id'] = (string) Str::uuid();
    //                     $journalData['schedule_id'] = $j['schedule_id'];
    //                     $journalData['date'] = $j['date'];
    //                     $journalData['created_at'] = now();

    //                     DB::table('teaching_journals')->insert($journalData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data',
    //             'prayer' => 'Absensi Sholat',
    //             'gate' => 'Absensi Gerbang',
    //             'learning' => 'Absensi Pembelajaran',
    //             'journal' => 'Jurnal Guru'
    //         ];

    //         // LOGIKA PESAN HASIL
    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan di database lokal.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL MEMPROSES DATA. $skippedCount data ditemukan tetapi NIS Siswa tidak ada yang cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal,student', // Tambah tipe student
    //     ]);

    //     $syncType = $request->input('type', 'all');

    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120)
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         $totalRemote = ($results['prayer']['total'] ?? 0) +
    //                        ($results['gate']['total'] ?? 0) +
    //                        ($results['learning']['total'] ?? 0) +
    //                        ($results['journal']['total'] ?? 0) +
    //                        ($results['student']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
    //         }

    //         $processCount = 0;
    //         $skippedCount = 0;

    //         DB::beginTransaction();

    //         // 1. SINKRONISASI SISWA (MASTER DATA) - Jalankan Dulu Agar Foreign Key Aman
    //         if (($syncType === 'all' || $syncType === 'student') && isset($results['student']['data'])) {
    //             foreach ($results['student']['data'] as $s) {

    //                 // Cari Classroom ID lokal berdasarkan Nama Kelas dari server pusat
    //                 $classId = null;
    //                 if (!empty($s['classroom_name'])) {
    //                     // Mencari kelas berdasarkan nama (case-insensitive jika memungkinkan, di sini exact match)
    //                     $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
    //                     if ($cls) {
    //                         $classId = $cls->id;
    //                     }
    //                 }

    //                 // Cek Siswa berdasarkan NIS
    //                 $exists = DB::table('students')->where('nis', $s['nis'])->first();

    //                 $studentData = [
    //                     'name' => $s['name'],
    //                     'face_descriptor' => $s['face_descriptor'],
    //                     'phone' => $s['phone'],
    //                     'address' => $s['address'],
    //                     'classroom_id' => $classId, // Update kelas jika ditemukan
    //                     'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('students')->where('id', $exists->id)->update($studentData);
    //                 } else {
    //                     // Generate ID Baru untuk Siswa Baru
    //                     $studentData['id'] = (string) Str::uuid();
    //                     $studentData['nis'] = $s['nis'];
    //                     $studentData['user_id'] = null; // Default null karena user sync kompleks
    //                     $studentData['created_at'] = now();

    //                     DB::table('students')->insert($studentData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // 2. Sholat
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('prayer_attendances')->where('student_id', $studentId)->where('date', $p['date'])->where('prayer_name', $p['prayer_name'])->first();
    //                     if ($exists) {
    //                         DB::table('prayer_attendances')->where('id', $exists->id)->update([
    //                             'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name'], 'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 3. Gerbang
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 4. Pembelajaran
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 5. Jurnal Guru (teaching_journals)
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($results['journal']['data'])) {
    //             foreach ($results['journal']['data'] as $j) {
    //                 $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();

    //                 $attendanceSummary = is_array($j['attendance_summary'])
    //                                     ? json_encode($j['attendance_summary'])
    //                                     : $j['attendance_summary'];

    //                 $journalData = [
    //                     'topic' => $j['topic'], 'activity' => $j['activity'], 'attendance_summary' => $attendanceSummary, 'absent_details' => $j['absent_details'], 'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('teaching_journals')->where('id', $exists->id)->update($journalData);
    //                 } else {
    //                     $journalData['id'] = (string) Str::uuid();
    //                     $journalData['schedule_id'] = $j['schedule_id'];
    //                     $journalData['date'] = $j['date'];
    //                     $journalData['created_at'] = now();
    //                     DB::table('teaching_journals')->insert($journalData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 'journal' => 'Jurnal Guru', 'student' => 'Data Siswa'
    //         ];

    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data absensi DILEWATI karena NIS Siswa tidak ditemukan (Disarankan Sync Data Siswa dulu).");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal,student', // Tambah tipe student
    //     ]);

    //     $syncType = $request->input('type', 'all');

    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');

    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120)
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         $totalRemote = ($results['prayer']['total'] ?? 0) +
    //                        ($results['gate']['total'] ?? 0) +
    //                        ($results['learning']['total'] ?? 0) +
    //                        ($results['journal']['total'] ?? 0) +
    //                        ($results['student']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
    //         }

    //         $processCount = 0;
    //         $skippedCount = 0;

    //         DB::beginTransaction();

    //         // 1. SINKRONISASI SISWA (MASTER DATA) - Jalankan Dulu Agar Foreign Key Aman
    //         if (($syncType === 'all' || $syncType === 'student') && isset($results['student']['data'])) {
    //             foreach ($results['student']['data'] as $s) {

    //                 // Cari Classroom ID lokal berdasarkan Nama Kelas dari server pusat
    //                 $classId = null;
    //                 if (!empty($s['classroom_name'])) {
    //                     // Mencari kelas berdasarkan nama (case-insensitive jika memungkinkan, di sini exact match)
    //                     $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
    //                     if ($cls) {
    //                         $classId = $cls->id;
    //                     }
    //                 }

    //                 // Cek Siswa berdasarkan NIS
    //                 $exists = DB::table('students')->where('nis', $s['nis'])->first();

    //                 $studentData = [
    //                     'name' => $s['name'],
    //                     'face_descriptor' => $s['face_descriptor'],
    //                     'phone' => $s['phone'],
    //                     'address' => $s['address'],
    //                     'classroom_id' => $classId, // Update kelas jika ditemukan
    //                     'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('students')->where('id', $exists->id)->update($studentData);
    //                 } else {
    //                     // Generate ID Baru untuk Siswa Baru
    //                     $studentData['id'] = (string) Str::uuid();
    //                     $studentData['nis'] = $s['nis'];
    //                     $studentData['user_id'] = null; // Default null karena user sync kompleks
    //                     $studentData['created_at'] = now();

    //                     DB::table('students')->insert($studentData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // 2. Sholat
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('prayer_attendances')->where('student_id', $studentId)->where('date', $p['date'])->where('prayer_name', $p['prayer_name'])->first();
    //                     if ($exists) {
    //                         DB::table('prayer_attendances')->where('id', $exists->id)->update([
    //                             'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name'], 'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 3. Gerbang
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 4. Pembelajaran
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 5. Jurnal Guru (teaching_journals)
    //         // UPDATE: Mapping disesuaikan dengan schema lokal (notes, photo_evidence)
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($results['journal']['data'])) {
    //             foreach ($results['journal']['data'] as $j) {
    //                 $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();

    //                 // Siapkan data dasar
    //                 $journalData = [
    //                     'topic' => $j['topic'],
    //                     'activity' => $j['activity'],
    //                     'updated_at' => now()
    //                 ];

    //                 // Mapping 'notes' (Catatan)
    //                 // Jika source kirim 'notes', pakai itu.
    //                 // Jika source kirim 'absent_details' (tapi lokal tidak punya kolom absent_details),
    //                 // masukkan ke 'notes' sebagai alternatif agar data tidak hilang.
    //                 if (isset($j['notes'])) {
    //                     $journalData['notes'] = $j['notes'];
    //                 } elseif (isset($j['absent_details'])) {
    //                     $journalData['notes'] = $j['absent_details'];
    //                 }

    //                 // Mapping 'photo_evidence' (Bukti Foto)
    //                 if (isset($j['photo_evidence'])) {
    //                     $journalData['photo_evidence'] = $j['photo_evidence'];
    //                 }

    //                 // PENTING: Jangan masukkan 'attendance_summary' atau 'absent_details'
    //                 // secara langsung jika kolom tersebut tidak ada di database lokal.

    //                 if ($exists) {
    //                     DB::table('teaching_journals')->where('id', $exists->id)->update($journalData);
    //                 } else {
    //                     $journalData['id'] = (string) Str::uuid();
    //                     $journalData['schedule_id'] = $j['schedule_id'];
    //                     $journalData['date'] = $j['date'];
    //                     $journalData['created_at'] = now();
    //                     DB::table('teaching_journals')->insert($journalData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 'journal' => 'Jurnal Guru', 'student' => 'Data Siswa'
    //         ];

    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data absensi DILEWATI karena NIS Siswa tidak ditemukan (Disarankan Sync Data Siswa dulu).");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal,student', // Tambah tipe student
    //     ]);

    //     $syncType = $request->input('type', 'all');

    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(120)
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $results = $result['results'];

    //         $totalRemote = ($results['prayer']['total'] ?? 0) +
    //                        ($results['gate']['total'] ?? 0) +
    //                        ($results['learning']['total'] ?? 0) +
    //                        ($results['journal']['total'] ?? 0) +
    //                        ($results['student']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
    //         }

    //         $processCount = 0;
    //         $skippedCount = 0;

    //         DB::beginTransaction();

    //         // 1. SINKRONISASI SISWA (MASTER DATA) - Jalankan Dulu Agar Foreign Key Aman
    //         if (($syncType === 'all' || $syncType === 'student') && isset($results['student']['data'])) {
    //             foreach ($results['student']['data'] as $s) {

    //                 // Cari Classroom ID lokal berdasarkan Nama Kelas dari server pusat
    //                 $classId = null;
    //                 if (!empty($s['classroom_name'])) {
    //                     // Mencari kelas berdasarkan nama (case-insensitive jika memungkinkan, di sini exact match)
    //                     $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
    //                     if ($cls) {
    //                         $classId = $cls->id;
    //                     }
    //                 }

    //                 // Cek Siswa berdasarkan NIS
    //                 $exists = DB::table('students')->where('nis', $s['nis'])->first();

    //                 $studentData = [
    //                     'name' => $s['name'],
    //                     'face_descriptor' => $s['face_descriptor'],
    //                     'phone' => $s['phone'],
    //                     'address' => $s['address'],
    //                     'classroom_id' => $classId, // Update kelas jika ditemukan
    //                     'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('students')->where('id', $exists->id)->update($studentData);
    //                 } else {
    //                     // Generate ID Baru untuk Siswa Baru
    //                     $studentData['id'] = (string) Str::uuid();
    //                     $studentData['nis'] = $s['nis'];
    //                     $studentData['user_id'] = null; // Default null karena user sync kompleks
    //                     $studentData['created_at'] = now();

    //                     DB::table('students')->insert($studentData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // 2. Sholat
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($results['prayer']['data'])) {
    //             foreach ($results['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('prayer_attendances')->where('student_id', $studentId)->where('date', $p['date'])->where('prayer_name', $p['prayer_name'])->first();
    //                     if ($exists) {
    //                         DB::table('prayer_attendances')->where('id', $exists->id)->update([
    //                             'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('prayer_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name'], 'check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 3. Gerbang
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($results['gate']['data'])) {
    //             foreach ($results['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update([
    //                             'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 4. Pembelajaran
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($results['learning']['data'])) {
    //             foreach ($results['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update([
    //                             'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'updated_at' => now()
    //                         ]);
    //                     } else {
    //                         DB::table('attendances')->insert([
    //                             'id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'recorded_by' => 'Sync System', 'created_at' => now(), 'updated_at' => now()
    //                         ]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // 5. Jurnal Guru (teaching_journals)
    //         // UPDATE: Mapping disesuaikan dengan schema lokal (notes, photo_evidence)
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($results['journal']['data'])) {
    //             foreach ($results['journal']['data'] as $j) {
    //                 $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();

    //                 // Siapkan data dasar
    //                 $journalData = [
    //                     'topic' => $j['topic'],
    //                     'activity' => $j['activity'],
    //                     'updated_at' => now()
    //                 ];

    //                 // Mapping 'notes' (Catatan)
    //                 if (isset($j['notes'])) {
    //                     $journalData['notes'] = $j['notes'];
    //                 } elseif (isset($j['absent_details'])) {
    //                     $journalData['notes'] = $j['absent_details'];
    //                 }

    //                 // Mapping 'photo_evidence' (Bukti Foto)
    //                 if (isset($j['photo_evidence'])) {
    //                     $journalData['photo_evidence'] = $j['photo_evidence'];
    //                 }

    //                 if ($exists) {
    //                     DB::table('teaching_journals')->where('id', $exists->id)->update($journalData);
    //                 } else {
    //                     // FIX: Jangan generate UUID manual untuk kolom 'id' jika tabel menggunakan BigInt (Auto Increment).
    //                     // Hapus baris: $journalData['id'] = (string) Str::uuid();

    //                     $journalData['schedule_id'] = $j['schedule_id'];
    //                     $journalData['date'] = $j['date'];
    //                     $journalData['created_at'] = now();
    //                     DB::table('teaching_journals')->insert($journalData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();

    //         $typeLabels = [
    //             'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 'journal' => 'Jurnal Guru', 'student' => 'Data Siswa'
    //         ];

    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');

    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data absensi DILEWATI karena NIS Siswa tidak ditemukan (Disarankan Sync Data Siswa dulu).");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         // Tambahkan tipe 'permit' dan tipe lainnya
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal,student,master,schedule,mbg,permit', 
    //     ]);

    //     $syncType = $request->input('type', 'all');

    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(180) // Timeout diperpanjang
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $res = $result['results'];
            
    //         // Hitung Total Data
    //         $totalRemote = ($res['prayer']['total'] ?? 0) + 
    //                        ($res['gate']['total'] ?? 0) + 
    //                        ($res['learning']['total'] ?? 0) +
    //                        ($res['journal']['total'] ?? 0) +
    //                        ($res['student']['total'] ?? 0) +
    //                        ($res['teacher']['total'] ?? 0) + // Master Guru
    //                        ($res['schedule']['total'] ?? 0) + // Jadwal
    //                        ($res['mbg']['total'] ?? 0) +      // MBG
    //                        ($res['permit']['total'] ?? 0);    // Permit
            
    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
    //         }

    //         $processCount = 0;
    //         $skippedCount = 0;
            
    //         DB::beginTransaction();

    //         // === 1. SYNC MASTER DATA (JURUSAN, RUANGAN, MAPEL, GURU, KELAS) ===
    //         if ($syncType === 'all' || $syncType === 'master') {
    //             // Majors
    //             if(isset($res['major']['data'])) {
    //                 foreach($res['major']['data'] as $m) {
    //                     DB::table('majors')->updateOrInsert(['id' => $m['id']], ['name' => $m['name'], 'code' => $m['code'] ?? null, 'updated_at' => now()]);
    //                 }
    //             }
    //             // Rooms
    //             if(isset($res['room']['data'])) {
    //                 foreach($res['room']['data'] as $r) {
    //                     DB::table('rooms')->updateOrInsert(['id' => $r['id']], ['name' => $r['name'], 'capacity' => $r['capacity'] ?? 0, 'updated_at' => now()]);
    //                 }
    //             }
    //             // Subjects
    //             if(isset($res['subject']['data'])) {
    //                 foreach($res['subject']['data'] as $sub) {
    //                     DB::table('subjects')->updateOrInsert(['id' => $sub['id']], ['name' => $sub['name'], 'code' => $sub['code'] ?? null, 'updated_at' => now()]);
    //                 }
    //             }
    //             // Teachers (User & Teacher)
    //             if(isset($res['teacher']['data'])) {
    //                 foreach($res['teacher']['data'] as $t) {
    //                     $user = DB::table('users')->where('email', $t['email'])->first();
    //                     $userId = $user ? $user->id : (string) Str::uuid();
    //                     if(!$user) {
    //                         DB::table('users')->insert(['id' => $userId, 'name' => $t['user_name'], 'email' => $t['email'], 'username' => $t['username'] ?? explode('@', $t['email'])[0], 'password' => Hash::make('12345678'), 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()]);
    //                     }
    //                     DB::table('teachers')->updateOrInsert(['id' => $t['id']], ['user_id' => $userId, 'name' => $t['name'], 'nip' => $t['nip'], 'gender' => $t['gender'], 'phone' => $t['phone'], 'place_of_birth' => $t['place_of_birth'], 'date_of_birth' => $t['date_of_birth'], 'address' => $t['address'], 'updated_at' => now()]);
    //                     $processCount++;
    //                 }
    //             }
    //             // Classrooms
    //             if(isset($res['classroom']['data'])) {
    //                 foreach($res['classroom']['data'] as $c) {
    //                     DB::table('classrooms')->updateOrInsert(['id' => $c['id']], ['name' => $c['name'], 'homeroom_teacher_id' => $c['homeroom_teacher_id'], 'counseling_teacher_id' => $c['counseling_teacher_id'], 'class_leader_id' => $c['class_leader_id'], 'updated_at' => now()]);
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 2. SYNC SISWA ===
    //         if (($syncType === 'all' || $syncType === 'student') && isset($res['student']['data'])) {
    //             foreach ($res['student']['data'] as $s) {
    //                 $classId = $s['classroom_id'];
    //                 // Fallback cari kelas by Name jika UUID beda
    //                 if(!DB::table('classrooms')->where('id', $s['classroom_id'])->exists() && !empty($s['classroom_name'])) {
    //                     $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
    //                     if($cls) $classId = $cls->id;
    //                 }

    //                 $exists = DB::table('students')->where('nis', $s['nis'])->first();
    //                 $studentData = [
    //                     'name' => $s['name'], 'face_descriptor' => $s['face_descriptor'],
    //                     'phone' => $s['phone'], 'address' => $s['address'],
    //                     'classroom_id' => $classId, 'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('students')->where('id', $exists->id)->update($studentData);
    //                 } else {
    //                     $studentData['id'] = $s['id']; // Gunakan ID sumber jika belum ada
    //                     $studentData['nis'] = $s['nis'];
    //                     $studentData['created_at'] = now();
    //                     DB::table('students')->insert($studentData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // === 3. SYNC JADWAL ===
    //         if (($syncType === 'all' || $syncType === 'schedule') && isset($res['schedule']['data'])) {
    //             foreach($res['schedule']['data'] as $sch) {
    //                 $teacherExists = DB::table('teachers')->where('id', $sch['teacher_id'])->exists();
    //                 $classExists = DB::table('classrooms')->where('id', $sch['classroom_id'])->exists();
    //                 $subjectExists = DB::table('subjects')->where('id', $sch['subject_id'])->exists();

    //                 if($teacherExists && $classExists && $subjectExists) {
    //                     DB::table('schedules')->updateOrInsert(['id' => $sch['id']], [
    //                         'teacher_id' => $sch['teacher_id'], 'classroom_id' => $sch['classroom_id'],
    //                         'subject_id' => $sch['subject_id'], 'room_id' => $sch['room_id'] ?? null,
    //                         'day' => $sch['day'], 'start_time' => $sch['start_time'], 'end_time' => $sch['end_time'],
    //                         'updated_at' => now()
    //                     ]);
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 4. Izin Siswa (Permit) - BARU ===
    //         if (($syncType === 'all' || $syncType === 'permit') && isset($res['permit']['data'])) {
    //             foreach ($res['permit']['data'] as $perm) {
    //                 $studentId = DB::table('students')->where('nis', $perm['nis'])->value('id');
                    
    //                 if ($studentId) {
    //                     // Cek eksistensi berdasarkan ID Asli dari server sumber
    //                     $exists = DB::table('student_permits')->where('id', $perm['id'])->first();

    //                     $permitData = [
    //                         'student_id' => $studentId,
    //                         'date' => $perm['date'],
    //                         'time_out' => $perm['time_out'],
    //                         'time_in' => $perm['time_in'],
    //                         'reason' => $perm['reason'],
    //                         'description' => $perm['description'],
    //                         'status' => $perm['status'],
    //                         'method' => $perm['method'],
    //                         'image_evidence' => $perm['image_evidence'],
    //                         'recorded_by' => 'Sync System',
    //                         'updated_at' => now()
    //                     ];

    //                     if ($exists) {
    //                         DB::table('student_permits')->where('id', $perm['id'])->update($permitData);
    //                     } else {
    //                         $permitData['id'] = $perm['id']; // Gunakan ID sumber
    //                         $permitData['created_at'] = now();
    //                         DB::table('student_permits')->insert($permitData);
    //                     }
    //                     $processCount++;
    //                 } else {
    //                     $skippedCount++;
    //                 }
    //             }
    //         }

    //         // === 5. MBG (Makan Bergizi Gratis) ===
    //         if (($syncType === 'all' || $syncType === 'mbg') && isset($res['mbg']['data'])) {
    //             foreach ($res['mbg']['data'] as $m) {
    //                 $studentId = DB::table('students')->where('nis', $m['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('mbg_attendances')->where('student_id', $studentId)->where('date', $m['date'])->first();
    //                     $mbgData = [
    //                         'check_in_time' => $m['check_in_time'], 'status' => $m['status'],
    //                         'method' => $m['method'] ?? 'barcode', 'image_evidence' => $m['image_evidence'] ?? null,
    //                         'recorded_by' => 'Sync System', 'updated_at' => now()
    //                     ];
    //                     if ($exists) { DB::table('mbg_attendances')->where('id', $exists->id)->update($mbgData); }
    //                     else { 
    //                         $mbgData['id'] = (string) Str::uuid(); $mbgData['student_id'] = $studentId; $mbgData['date'] = $m['date']; $mbgData['created_at'] = now(); 
    //                         DB::table('mbg_attendances')->insert($mbgData); 
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // === 6. Absensi Sholat ===
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($res['prayer']['data'])) {
    //             foreach ($res['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     DB::table('prayer_attendances')->updateOrInsert(
    //                         ['student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name']],
    //                         ['check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()]
    //                     );
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // === 7. Absensi Gerbang ===
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($res['gate']['data'])) {
    //             foreach ($res['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update(['arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'created_at' => now()]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // === 8. Absensi Pembelajaran ===
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($res['learning']['data'])) {
    //             foreach ($res['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update(['subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'created_at' => now()]);
    //                     }
    //                     $processCount++;
    //                 } else { $skippedCount++; }
    //             }
    //         }

    //         // === 9. Jurnal Guru ===
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($res['journal']['data'])) {
    //             foreach ($res['journal']['data'] as $j) {
    //                 $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();
    //                 $journalData = ['topic' => $j['topic'], 'activity' => $j['activity'], 'updated_at' => now()];
    //                 if (isset($j['notes'])) $journalData['notes'] = $j['notes'];
    //                 elseif (isset($j['absent_details'])) $journalData['notes'] = $j['absent_details'];
    //                 if (isset($j['photo_evidence'])) $journalData['photo_evidence'] = $j['photo_evidence'];
                    
    //                 if ($exists) { DB::table('teaching_journals')->where('id', $exists->id)->update($journalData); }
    //                 else { 
    //                     // Auto-increment ID assumed if not UUID
    //                     $journalData['schedule_id'] = $j['schedule_id']; $journalData['date'] = $j['date']; $journalData['created_at'] = now(); 
    //                     DB::table('teaching_journals')->insert($journalData); 
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         DB::commit();
            
    //         $typeLabels = [
    //             'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 
    //             'journal' => 'Jurnal Guru', 'student' => 'Data Siswa', 'mbg' => 'Absensi MBG', 'permit' => 'Izin Siswa'
    //         ];

    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');
            
    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data telah disimpan/diupdate.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }

    /**
     * Helper: Simpan gambar Base64 ke Storage lokal
     * Mengembalikan path file yang disimpan
     */
    // private function saveBase64Image($item, $field, $defaultPath = null)
    // {
    //     $base64Key = $field . '_base64';

    //     // Cek jika ada data base64 dikirim
    //     if (isset($item[$base64Key]) && !empty($item[$base64Key])) {
    //         try {
    //             // Decode
    //             $imageContent = base64_decode($item[$base64Key]);

    //             // Gunakan path asli dari server sumber jika memungkinkan
    //             $path = $item[$field];

    //             // Jika path kosong/null di data asli tapi ada base64 (case jarang), generate nama baru
    //             if (!$path) {
    //                 $path = 'synced_images/' . uniqid() . '.jpg';
    //             }

    //             // Agar folder otomatis terbuat
    //             $directory = dirname($path);
    //             if (!Storage::disk('public')->exists($directory)) {
    //                 Storage::disk('public')->makeDirectory($directory);
    //             }

    //             Storage::disk('public')->put($path, $imageContent);

    //             return $path; // Kembalikan path untuk disimpan di DB
    //         } catch (\Exception $e) {
    //             Log::error("Gagal simpan gambar sync ($path): " . $e->getMessage());
    //         }
    //     }

    //     // Jika tidak ada data base64 baru, kembalikan path lama (atau null)
    //     return $item[$field] ?? $defaultPath;
    // }

    // public function pullAttendance(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'type' => 'nullable|in:all,prayer,learning,gate,journal,student,master,schedule,mbg,permit',
    //     ]);

    //     $syncType = $request->input('type', 'all');

    //     $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
    //     $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

    //     if (!$targetUrl || !$targetKey) {
    //         return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
    //     }

    //     try {
    //         $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'X-Api-Key' => $targetKey,
    //                 'Accept' => 'application/json'
    //             ])
    //             ->timeout(300) // Timeout diperpanjang karena kirim gambar
    //             ->get($apiUrl, [
    //                 'start_date' => $request->start_date,
    //                 'end_date' => $request->end_date,
    //                 'key' => $targetKey
    //             ]);

    //         if ($response->failed()) {
    //             Log::error("Sync Failed: " . $response->body());
    //             return back()->with('error', "Server Target Error (Status: " . $response->status() . ")");
    //         }

    //         $result = $response->json();

    //         if (!isset($result['status']) || $result['status'] !== 'success') {
    //             return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
    //         }

    //         $res = $result['results'];

    //         // Hitung Total Data
    //         $totalRemote = ($res['prayer']['total'] ?? 0) +
    //                        ($res['gate']['total'] ?? 0) +
    //                        ($res['learning']['total'] ?? 0) +
    //                        ($res['journal']['total'] ?? 0) +
    //                        ($res['student']['total'] ?? 0) +
    //                        ($res['teacher']['total'] ?? 0) +
    //                        ($res['schedule']['total'] ?? 0) +
    //                        ($res['mbg']['total'] ?? 0) +
    //                        ($res['permit']['total'] ?? 0);

    //         if ($totalRemote === 0) {
    //             return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
    //         }

    //         $processCount = 0;
    //         $skippedCount = 0;

    //         DB::beginTransaction();

    //         // === 1. SYNC MASTER DATA (JURUSAN, RUANGAN, MAPEL, GURU, KELAS) ===
    //         if ($syncType === 'all' || $syncType === 'master') {
    //             if(isset($res['major']['data'])) {
    //                 foreach($res['major']['data'] as $m) {
    //                     DB::table('majors')->updateOrInsert(['id' => $m['id']], ['name' => $m['name'], 'code' => $m['code'] ?? null, 'updated_at' => now()]);
    //                 }
    //             }
    //             if(isset($res['room']['data'])) {
    //                 foreach($res['room']['data'] as $r) {
    //                     DB::table('rooms')->updateOrInsert(['id' => $r['id']], ['name' => $r['name'], 'capacity' => $r['capacity'] ?? 0, 'updated_at' => now()]);
    //                 }
    //             }
    //             if(isset($res['subject']['data'])) {
    //                 foreach($res['subject']['data'] as $sub) {
    //                     DB::table('subjects')->updateOrInsert(['id' => $sub['id']], ['name' => $sub['name'], 'code' => $sub['code'] ?? null, 'updated_at' => now()]);
    //                 }
    //             }
    //             if(isset($res['teacher']['data'])) {
    //                 foreach($res['teacher']['data'] as $t) {
    //                     $user = DB::table('users')->where('email', $t['email'])->first();
    //                     $userId = $user ? $user->id : (string) Str::uuid();
    //                     if(!$user) {
    //                         DB::table('users')->insert(['id' => $userId, 'name' => $t['user_name'], 'email' => $t['email'], 'username' => $t['username'] ?? explode('@', $t['email'])[0], 'password' => Hash::make('12345678'), 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()]);
    //                     }
    //                     DB::table('teachers')->updateOrInsert(['id' => $t['id']], ['user_id' => $userId, 'name' => $t['name'], 'nip' => $t['nip'], 'gender' => $t['gender'], 'phone' => $t['phone'], 'place_of_birth' => $t['place_of_birth'], 'date_of_birth' => $t['date_of_birth'], 'address' => $t['address'], 'updated_at' => now()]);
    //                 }
    //             }
    //             if(isset($res['classroom']['data'])) {
    //                 foreach($res['classroom']['data'] as $c) {
    //                     DB::table('classrooms')->updateOrInsert(['id' => $c['id']], ['name' => $c['name'], 'homeroom_teacher_id' => $c['homeroom_teacher_id'], 'counseling_teacher_id' => $c['counseling_teacher_id'], 'class_leader_id' => $c['class_leader_id'], 'updated_at' => now()]);
    //                 }
    //             }
    //         }

    //         // === 2. SYNC SISWA ===
    //         if (($syncType === 'all' || $syncType === 'student') && isset($res['student']['data'])) {
    //             foreach ($res['student']['data'] as $s) {
    //                 $classId = $s['classroom_id'];
    //                 if(!DB::table('classrooms')->where('id', $s['classroom_id'])->exists() && !empty($s['classroom_name'])) {
    //                     $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
    //                     if($cls) $classId = $cls->id;
    //                 }

    //                 $exists = DB::table('students')->where('nis', $s['nis'])->first();
    //                 $studentData = [
    //                     'name' => $s['name'], 'face_descriptor' => $s['face_descriptor'],
    //                     'phone' => $s['phone'], 'address' => $s['address'],
    //                     'classroom_id' => $classId, 'updated_at' => now()
    //                 ];

    //                 if ($exists) {
    //                     DB::table('students')->where('id', $exists->id)->update($studentData);
    //                 } else {
    //                     $studentData['id'] = $s['id'];
    //                     $studentData['nis'] = $s['nis'];
    //                     $studentData['created_at'] = now();
    //                     DB::table('students')->insert($studentData);
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // === 3. SYNC JADWAL ===
    //         if (($syncType === 'all' || $syncType === 'schedule') && isset($res['schedule']['data'])) {
    //             foreach($res['schedule']['data'] as $sch) {
    //                 $teacherExists = DB::table('teachers')->where('id', $sch['teacher_id'])->exists();
    //                 $classExists = DB::table('classrooms')->where('id', $sch['classroom_id'])->exists();
    //                 $subjectExists = DB::table('subjects')->where('id', $sch['subject_id'])->exists();

    //                 if($teacherExists && $classExists && $subjectExists) {
    //                     DB::table('schedules')->updateOrInsert(['id' => $sch['id']], [
    //                         'teacher_id' => $sch['teacher_id'], 'classroom_id' => $sch['classroom_id'],
    //                         'subject_id' => $sch['subject_id'], 'room_id' => $sch['room_id'] ?? null,
    //                         'day' => $sch['day'], 'start_time' => $sch['start_time'], 'end_time' => $sch['end_time'],
    //                         'updated_at' => now()
    //                     ]);
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //          // === 4. Izin Siswa (Permit) ===
    //          if (($syncType === 'all' || $syncType === 'permit') && isset($res['permit']['data'])) {
    //             foreach ($res['permit']['data'] as $perm) {
    //                 $studentId = DB::table('students')->where('nis', $perm['nis'])->value('id');
                    
    //                 if ($studentId) {
    //                     // Proses Gambar Evidence
    //                     $imagePath = $this->saveBase64Image($perm, 'image_evidence');

    //                     $permitData = [
    //                         'student_id' => $studentId,
    //                         'date' => $perm['date'],
    //                         'time_out' => $perm['time_out'],
    //                         'time_in' => $perm['time_in'],
    //                         'reason' => $perm['reason'],
    //                         'description' => $perm['description'],
    //                         'status' => $perm['status'],
    //                         'method' => $perm['method'],
    //                         'image_evidence' => $imagePath, // Path lokal yang baru disimpan
    //                         'recorded_by' => 'Sync System',
    //                         'updated_at' => now()
    //                     ];

    //                     DB::table('student_permits')->updateOrInsert(['id' => $perm['id']], $permitData);
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 5. MBG (Makan Bergizi Gratis) ===
    //         if (($syncType === 'all' || $syncType === 'mbg') && isset($res['mbg']['data'])) {
    //             foreach ($res['mbg']['data'] as $m) {
    //                 $studentId = DB::table('students')->where('nis', $m['nis'])->value('id');
    //                 if ($studentId) {
    //                     // Proses Gambar-gambar MBG
    //                     $imgEvidence = $this->saveBase64Image($m, 'image_evidence');
    //                     $imgTaken = $this->saveBase64Image($m, 'taken_image');
    //                     $imgReturned = $this->saveBase64Image($m, 'returned_image');

    //                     $mbgData = [
    //                         'student_id' => $studentId,
    //                         'date' => $m['date'],
    //                         'check_in_time' => $m['check_in_time'], 
    //                         'status' => $m['status'],
    //                         'method' => $m['method'] ?? 'barcode',
    //                         'taken_at' => $m['taken_at'] ?? null,
    //                         'taken_method' => $m['taken_method'] ?? null,
    //                         'returned_at' => $m['returned_at'] ?? null,
    //                         'returned_method' => $m['returned_method'] ?? null,
    //                         'image_evidence' => $imgEvidence,
    //                         'taken_image' => $imgTaken,
    //                         'returned_image' => $imgReturned,
    //                         'recorded_by' => 'Sync System', 
    //                         'updated_at' => now()
    //                     ];
                        
    //                     // Fallback Logic ID
    //                     if(isset($m['id'])) {
    //                          DB::table('mbg_attendances')->updateOrInsert(['id' => $m['id']], $mbgData);
    //                     } else {
    //                          $exists = DB::table('mbg_attendances')->where('student_id', $studentId)->where('date', $m['date'])->first();
    //                          if($exists) DB::table('mbg_attendances')->where('id', $exists->id)->update($mbgData);
    //                          else { $mbgData['id'] = (string) Str::uuid(); DB::table('mbg_attendances')->insert($mbgData); }
    //                     }
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 6. Jurnal Guru ===
    //         if (($syncType === 'all' || $syncType === 'journal') && isset($res['journal']['data'])) {
    //             foreach ($res['journal']['data'] as $j) {
    //                 // Proses Gambar Jurnal
    //                 $photoPath = $this->saveBase64Image($j, 'photo_evidence');

    //                 $journalData = [
    //                     'topic' => $j['topic'], 
    //                     'activity' => $j['activity'], 
    //                     'photo_evidence' => $photoPath,
    //                     'updated_at' => now()
    //                 ];
    //                 if (isset($j['notes'])) $journalData['notes'] = $j['notes'];
                    
    //                 if(isset($j['id']) && Str::isUuid($j['id'])) {
    //                     DB::table('teaching_journals')->updateOrInsert(['id' => $j['id']], array_merge($journalData, ['schedule_id' => $j['schedule_id'], 'date' => $j['date']]));
    //                 } else {
    //                     $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();
    //                     if ($exists) { DB::table('teaching_journals')->where('id', $exists->id)->update($journalData); }
    //                     else { 
    //                         $journalData['schedule_id'] = $j['schedule_id']; $journalData['date'] = $j['date']; $journalData['created_at'] = now(); 
    //                         $journalData['id'] = (string) Str::uuid();
    //                         DB::table('teaching_journals')->insert($journalData);
    //                     }
    //                 }
    //                 $processCount++;
    //             }
    //         }

    //         // === 7. Absensi Sholat ===
    //         if (($syncType === 'all' || $syncType === 'prayer') && isset($res['prayer']['data'])) {
    //             foreach ($res['prayer']['data'] as $p) {
    //                 $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
    //                 if ($studentId) {
    //                     DB::table('prayer_attendances')->updateOrInsert(
    //                         ['student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name']],
    //                         ['check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()]
    //                     );
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 8. Absensi Gerbang ===
    //         if (($syncType === 'all' || $syncType === 'gate') && isset($res['gate']['data'])) {
    //             foreach ($res['gate']['data'] as $g) {
    //                 $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
    //                     if ($exists) {
    //                         DB::table('daily_attendances')->where('id', $exists->id)->update(['arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('daily_attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'created_at' => now()]);
    //                     }
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         // === 9. Absensi Pembelajaran ===
    //         if (($syncType === 'all' || $syncType === 'learning') && isset($res['learning']['data'])) {
    //             foreach ($res['learning']['data'] as $l) {
    //                 $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
    //                 if ($studentId) {
    //                     $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
    //                     if ($exists) {
    //                         DB::table('attendances')->where('id', $exists->id)->update(['subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'created_at' => now()]);
    //                     }
    //                     $processCount++;
    //                 }
    //             }
    //         }

    //         DB::commit();
            
    //         $typeLabels = [
    //             'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 
    //             'journal' => 'Jurnal Guru', 'student' => 'Data Siswa', 'mbg' => 'Absensi MBG', 'permit' => 'Izin Siswa'
    //         ];

    //         $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');
            
    //         if ($processCount > 0) {
    //             $message = "$msgTitle BERHASIL. $processCount data (termasuk gambar) telah diproses.";
    //             if ($skippedCount > 0) {
    //                 return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan.");
    //             }
    //             return back()->with('success', $message);
    //         } else {
    //             if ($skippedCount > 0) {
    //                 return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
    //             } else {
    //                 return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Pull Attendance Error: " . $e->getMessage());
    //         return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
    //     }
    // }


    /**
     * Helper: Simpan gambar Base64 ke Storage lokal
     * Mengembalikan path file yang disimpan
     */
    private function saveBase64Image($item, $field, $defaultPath = null)
    {
        $base64Key = $field . '_base64';

        // Cek jika ada data base64 dikirim
        if (isset($item[$base64Key]) && !empty($item[$base64Key])) {
            try {
                // Decode
                $imageContent = base64_decode($item[$base64Key]);
                if ($imageContent === false) {
                    throw new \Exception("Gagal decode base64");
                }

                // Gunakan path asli dari server sumber jika memungkinkan
                $path = $item[$field];

                // Jika path kosong/null di data asli tapi ada base64 (case jarang), generate nama baru
                if (!$path) {
                    $path = 'synced_images/' . uniqid() . '.jpg';
                }

                // Agar folder otomatis terbuat
                $directory = dirname($path);
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                Storage::disk('public')->put($path, $imageContent);

                return $path; // Kembalikan path untuk disimpan di DB
            } catch (\Exception $e) {
                Log::error("Gagal simpan gambar sync ($field): " . $e->getMessage());
            }
        }

        // Jika tidak ada data base64 baru, kembalikan path lama (atau null)
        return $item[$field] ?? $defaultPath;
    }

    public function pullAttendance(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'nullable|in:all,prayer,learning,gate,journal,student,master,schedule,mbg,permit',
        ]);

        $syncType = $request->input('type', 'all');

        $targetUrl = DB::table('settings')->where('key', 'target_sync_url')->value('value');
        $targetKey = DB::table('settings')->where('key', 'target_sync_key')->value('value');

        if (!$targetUrl || !$targetKey) {
            return back()->with('error', 'Konfigurasi URL atau API Key target belum diatur di sistem.');
        }

        try {
            $apiUrl = rtrim($targetUrl, '/') . '/api/sync/export-all';

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'X-Api-Key' => $targetKey,
                    'Accept' => 'application/json'
                ])
                ->timeout(300) // Timeout diperpanjang karena kirim gambar
                ->get($apiUrl, [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'key' => $targetKey
                ]);

            if ($response->failed()) {
                Log::error("Sync Failed Body: " . $response->body());
                
                // Ambil pesan error spesifik dari Server Target (Server A)
                $serverMessage = "Server Target Error (Status: " . $response->status() . ")";
                
                // Cek jika response berupa JSON (biasanya exception Laravel API)
                $json = $response->json();
                if ($json && isset($json['message'])) {
                    $serverMessage .= " - " . $json['message'];
                } else {
                    // Cek jika response HTML (Whoops page error), coba ambil title atau h1
                    $body = $response->body();
                    if (preg_match('/<title>(.*?)<\/title>/', $body, $matches)) {
                        $serverMessage .= " - " . $matches[1];
                    }
                }
                
                return back()->with('error', $serverMessage);
            }

            $result = $response->json();

            if (!isset($result['status']) || $result['status'] !== 'success') {
                return back()->with('error', 'Format respon server tidak valid atau kunci salah.');
            }

            $res = $result['results'];

            // Hitung Total Data
            $totalRemote = ($res['prayer']['total'] ?? 0) +
                           ($res['gate']['total'] ?? 0) +
                           ($res['learning']['total'] ?? 0) +
                           ($res['journal']['total'] ?? 0) +
                           ($res['student']['total'] ?? 0) +
                           ($res['teacher']['total'] ?? 0) +
                           ($res['schedule']['total'] ?? 0) +
                           ($res['mbg']['total'] ?? 0) +
                           ($res['permit']['total'] ?? 0);

            if ($totalRemote === 0) {
                return back()->with('warning', "Koneksi BERHASIL, tetapi TIDAK ADA DATA pada rentang tanggal tersebut.");
            }

            $processCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();

            // === 1. SYNC MASTER DATA (JURUSAN, RUANGAN, MAPEL, GURU, KELAS) ===
            if ($syncType === 'all' || $syncType === 'master') {
                if(isset($res['major']['data'])) {
                    foreach($res['major']['data'] as $m) {
                        DB::table('majors')->updateOrInsert(['id' => $m['id']], ['name' => $m['name'], 'code' => $m['code'] ?? null, 'updated_at' => now()]);
                    }
                }
                if(isset($res['room']['data'])) {
                    foreach($res['room']['data'] as $r) {
                        DB::table('rooms')->updateOrInsert(['id' => $r['id']], ['name' => $r['name'], 'capacity' => $r['capacity'] ?? 0, 'updated_at' => now()]);
                    }
                }
                if(isset($res['subject']['data'])) {
                    foreach($res['subject']['data'] as $sub) {
                        DB::table('subjects')->updateOrInsert(['id' => $sub['id']], ['name' => $sub['name'], 'code' => $sub['code'] ?? null, 'updated_at' => now()]);
                    }
                }
                if(isset($res['teacher']['data'])) {
                    foreach($res['teacher']['data'] as $t) {
                        $user = DB::table('users')->where('email', $t['email'])->first();
                        $userId = $user ? $user->id : (string) Str::uuid();
                        if(!$user) {
                            DB::table('users')->insert(['id' => $userId, 'name' => $t['user_name'], 'email' => $t['email'], 'username' => $t['username'] ?? explode('@', $t['email'])[0], 'password' => Hash::make('12345678'), 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()]);
                        }
                        DB::table('teachers')->updateOrInsert(['id' => $t['id']], ['user_id' => $userId, 'name' => $t['name'], 'nip' => $t['nip'], 'gender' => $t['gender'], 'phone' => $t['phone'], 'place_of_birth' => $t['place_of_birth'], 'date_of_birth' => $t['date_of_birth'], 'address' => $t['address'], 'updated_at' => now()]);
                    }
                }
                if(isset($res['classroom']['data'])) {
                    foreach($res['classroom']['data'] as $c) {
                        DB::table('classrooms')->updateOrInsert(['id' => $c['id']], ['name' => $c['name'], 'homeroom_teacher_id' => $c['homeroom_teacher_id'], 'counseling_teacher_id' => $c['counseling_teacher_id'], 'class_leader_id' => $c['class_leader_id'], 'updated_at' => now()]);
                    }
                }
            }

            // === 2. SYNC SISWA ===
            if (($syncType === 'all' || $syncType === 'student') && isset($res['student']['data'])) {
                foreach ($res['student']['data'] as $s) {
                    $classId = $s['classroom_id'];
                    if(!DB::table('classrooms')->where('id', $s['classroom_id'])->exists() && !empty($s['classroom_name'])) {
                        $cls = DB::table('classrooms')->where('name', $s['classroom_name'])->first();
                        if($cls) $classId = $cls->id;
                    }

                    $exists = DB::table('students')->where('nis', $s['nis'])->first();
                    $studentData = [
                        'name' => $s['name'], 'face_descriptor' => $s['face_descriptor'],
                        'phone' => $s['phone'], 'address' => $s['address'],
                        'classroom_id' => $classId, 'updated_at' => now()
                    ];

                    if ($exists) {
                        DB::table('students')->where('id', $exists->id)->update($studentData);
                    } else {
                        $studentData['id'] = $s['id'];
                        $studentData['nis'] = $s['nis'];
                        $studentData['created_at'] = now();
                        DB::table('students')->insert($studentData);
                    }
                    $processCount++;
                }
            }

            // === 3. SYNC JADWAL ===
            if (($syncType === 'all' || $syncType === 'schedule') && isset($res['schedule']['data'])) {
                foreach($res['schedule']['data'] as $sch) {
                    $teacherExists = DB::table('teachers')->where('id', $sch['teacher_id'])->exists();
                    $classExists = DB::table('classrooms')->where('id', $sch['classroom_id'])->exists();
                    $subjectExists = DB::table('subjects')->where('id', $sch['subject_id'])->exists();

                    if($teacherExists && $classExists && $subjectExists) {
                        DB::table('schedules')->updateOrInsert(['id' => $sch['id']], [
                            'teacher_id' => $sch['teacher_id'], 'classroom_id' => $sch['classroom_id'],
                            'subject_id' => $sch['subject_id'], 'room_id' => $sch['room_id'] ?? null,
                            'day' => $sch['day'], 'start_time' => $sch['start_time'], 'end_time' => $sch['end_time'],
                            'updated_at' => now()
                        ]);
                        $processCount++;
                    }
                }
            }

             // === 4. Izin Siswa (Permit) ===
             if (($syncType === 'all' || $syncType === 'permit') && isset($res['permit']['data'])) {
                foreach ($res['permit']['data'] as $perm) {
                    $studentId = DB::table('students')->where('nis', $perm['nis'])->value('id');
                    
                    if ($studentId) {
                        // Proses Gambar Evidence
                        $imagePath = $this->saveBase64Image($perm, 'image_evidence');

                        $permitData = [
                            'student_id' => $studentId,
                            'date' => $perm['date'],
                            'time_out' => $perm['time_out'],
                            'time_in' => $perm['time_in'],
                            'reason' => $perm['reason'],
                            'description' => $perm['description'],
                            'status' => $perm['status'],
                            'method' => $perm['method'],
                            'image_evidence' => $imagePath, // Path lokal yang baru disimpan
                            'recorded_by' => 'Sync System',
                            'updated_at' => now()
                        ];

                        DB::table('student_permits')->updateOrInsert(['id' => $perm['id']], $permitData);
                        $processCount++;
                    }
                }
            }

            // === 5. MBG (Makan Bergizi Gratis) ===
            if (($syncType === 'all' || $syncType === 'mbg') && isset($res['mbg']['data'])) {
                foreach ($res['mbg']['data'] as $m) {
                    $studentId = DB::table('students')->where('nis', $m['nis'])->value('id');
                    if ($studentId) {
                        // Proses Gambar-gambar MBG
                        $imgEvidence = $this->saveBase64Image($m, 'image_evidence');
                        $imgTaken = $this->saveBase64Image($m, 'taken_image');
                        $imgReturned = $this->saveBase64Image($m, 'returned_image');

                        $mbgData = [
                            'student_id' => $studentId,
                            'date' => $m['date'],
                            'check_in_time' => $m['check_in_time'], 
                            'status' => $m['status'],
                            'method' => $m['method'] ?? 'barcode',
                            'taken_at' => $m['taken_at'] ?? null,
                            'taken_method' => $m['taken_method'] ?? null,
                            'returned_at' => $m['returned_at'] ?? null,
                            'returned_method' => $m['returned_method'] ?? null,
                            'image_evidence' => $imgEvidence,
                            'taken_image' => $imgTaken,
                            'returned_image' => $imgReturned,
                            'recorded_by' => 'Sync System', 
                            'updated_at' => now()
                        ];
                        
                        // Fallback Logic ID
                        if(isset($m['id'])) {
                             DB::table('mbg_attendances')->updateOrInsert(['id' => $m['id']], $mbgData);
                        } else {
                             $exists = DB::table('mbg_attendances')->where('student_id', $studentId)->where('date', $m['date'])->first();
                             if($exists) DB::table('mbg_attendances')->where('id', $exists->id)->update($mbgData);
                             else { $mbgData['id'] = (string) Str::uuid(); DB::table('mbg_attendances')->insert($mbgData); }
                        }
                        $processCount++;
                    }
                }
            }

            // === 6. Jurnal Guru ===
            if (($syncType === 'all' || $syncType === 'journal') && isset($res['journal']['data'])) {
                foreach ($res['journal']['data'] as $j) {
                    // Proses Gambar Jurnal
                    $photoPath = $this->saveBase64Image($j, 'photo_evidence');

                    $journalData = [
                        'topic' => $j['topic'], 
                        'activity' => $j['activity'], 
                        'photo_evidence' => $photoPath,
                        'updated_at' => now()
                    ];
                    if (isset($j['notes'])) $journalData['notes'] = $j['notes'];
                    
                    if(isset($j['id']) && Str::isUuid($j['id'])) {
                        DB::table('teaching_journals')->updateOrInsert(['id' => $j['id']], array_merge($journalData, ['schedule_id' => $j['schedule_id'], 'date' => $j['date']]));
                    } else {
                        $exists = DB::table('teaching_journals')->where('schedule_id', $j['schedule_id'])->where('date', $j['date'])->first();
                        if ($exists) { DB::table('teaching_journals')->where('id', $exists->id)->update($journalData); }
                        else { 
                            $journalData['schedule_id'] = $j['schedule_id']; $journalData['date'] = $j['date']; $journalData['created_at'] = now(); 
                            $journalData['id'] = (string) Str::uuid();
                            DB::table('teaching_journals')->insert($journalData);
                        }
                    }
                    $processCount++;
                }
            }

            // === 7. Absensi Sholat ===
            if (($syncType === 'all' || $syncType === 'prayer') && isset($res['prayer']['data'])) {
                foreach ($res['prayer']['data'] as $p) {
                    $studentId = DB::table('students')->where('nis', $p['nis'])->value('id');
                    if ($studentId) {
                        DB::table('prayer_attendances')->updateOrInsert(
                            ['student_id' => $studentId, 'date' => $p['date'], 'prayer_name' => $p['prayer_name']],
                            ['check_in_time' => $p['check_in_time'], 'status' => $p['status'], 'latitude' => $p['latitude'], 'longitude' => $p['longitude'], 'updated_at' => now()]
                        );
                        $processCount++;
                    }
                }
            }

            // === 8. Absensi Gerbang ===
            if (($syncType === 'all' || $syncType === 'gate') && isset($res['gate']['data'])) {
                foreach ($res['gate']['data'] as $g) {
                    $studentId = DB::table('students')->where('nis', $g['nis'])->value('id');
                    if ($studentId) {
                        $exists = DB::table('daily_attendances')->where('student_id', $studentId)->where('date', $g['date'])->first();
                        if ($exists) {
                            DB::table('daily_attendances')->where('id', $exists->id)->update(['arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'updated_at' => now()]);
                        } else {
                            DB::table('daily_attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'date' => $g['date'], 'arrival_time' => $g['arrival_time'], 'departure_time' => $g['departure_time'], 'status' => $g['status'], 'created_at' => now()]);
                        }
                        $processCount++;
                    }
                }
            }

            // === 9. Absensi Pembelajaran ===
            if (($syncType === 'all' || $syncType === 'learning') && isset($res['learning']['data'])) {
                foreach ($res['learning']['data'] as $l) {
                    $studentId = DB::table('students')->where('nis', $l['nis'])->value('id');
                    if ($studentId) {
                        $exists = DB::table('attendances')->where('student_id', $studentId)->where('schedule_id', $l['schedule_id'])->where('date', $l['date'])->first();
                        if ($exists) {
                            DB::table('attendances')->where('id', $exists->id)->update(['subject_id' => $l['subject_id'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'updated_at' => now()]);
                        } else {
                            DB::table('attendances')->insert(['id' => (string) Str::uuid(), 'student_id' => $studentId, 'schedule_id' => $l['schedule_id'], 'date' => $l['date'], 'check_in_time' => $l['check_in_time'], 'status' => $l['status'], 'created_at' => now()]);
                        }
                        $processCount++;
                    }
                }
            }

            DB::commit();
            
            $typeLabels = [
                'all' => 'Semua Data', 'prayer' => 'Absensi Sholat', 'gate' => 'Absensi Gerbang', 'learning' => 'Absensi Pembelajaran', 
                'journal' => 'Jurnal Guru', 'student' => 'Data Siswa', 'mbg' => 'Absensi MBG', 'permit' => 'Izin Siswa'
            ];

            $msgTitle = "Sinkronisasi " . ($typeLabels[$syncType] ?? 'Data');
            
            if ($processCount > 0) {
                $message = "$msgTitle BERHASIL. $processCount data (termasuk gambar) telah diproses.";
                if ($skippedCount > 0) {
                    return back()->with('warning', "$message Namun, ada $skippedCount data DILEWATI karena NIS Siswa tidak ditemukan.");
                }
                return back()->with('success', $message);
            } else {
                if ($skippedCount > 0) {
                    return back()->with('error', "$msgTitle GAGAL. Data ditemukan tetapi NIS Siswa tidak cocok.");
                } else {
                    return back()->with('warning', "Koneksi sukses, tetapi tidak ada data baru yang perlu diproses.");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Pull Attendance Error: " . $e->getMessage());
            return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }
}
