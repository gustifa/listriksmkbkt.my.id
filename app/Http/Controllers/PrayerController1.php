<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class PrayerController extends Controller
{
    /**
     * Halaman Jadwal & Absen Sholat
     */
    // public function index()
    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");
    //         if ($response->successful()) {
    //             $schedule = $response->json()['data']['jadwal'];
    //         } else {
    //             throw new \Exception('Gagal ambil data API');
    //         }
    //     } catch (\Exception $e) {
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }

    /**
     * Halaman Jadwal & Absen Sholat
     */
    public function index()
    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");

    //         // FIX: Validasi response JSON agar tidak error saat mengakses array
    //         $data = $response->json();

    //         if ($response->successful() && isset($data['data']['jadwal'])) {
    //             $schedule = $data['data']['jadwal'];
    //         } else {
    //             throw new \Exception('Gagal ambil data API atau format berubah');
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback jadwal statis jika API error
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }

    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");

    //         // FIX: Validasi response JSON agar tidak error saat mengakses array
    //         // Menggunakan status() dan json_decode() manual untuk kompatibilitas jika method helper tidak tersedia
    //         if ($response->status() == 200) {
    //             // Gunakan json_decode pada body response
    //             $data = json_decode($response->body(), true);

    //             // Pastikan $data valid dan memiliki key yang dibutuhkan
    //             if (is_array($data) && isset($data['data']['jadwal'])) {
    //                 $schedule = $data['data']['jadwal'];
    //             } else {
    //                 // Log respon jika format tidak sesuai
    //                 Log::warning("Format API Sholat tidak sesuai: " . substr($response->body(), 0, 200));
    //                 throw new \Exception('Format data API berubah atau tidak valid');
    //             }
    //         } else {
    //             throw new \Exception('Request API gagal. Status: ' . $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback jadwal statis jika API error
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }

    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");

    //         // FIX: Gunakan method PSR-7 (getStatusCode) dan casting body ke string
    //         // Ini menangani kasus dimana method helper Laravel (status, body) tidak dikenali
    //         if ($response->getStatusCode() == 200) {
    //             // Decode body stream ke string lalu ke array
    //             $responseBody = (string) $response->getBody();
    //             $data = json_decode($responseBody, true);

    //             // Pastikan $data valid dan memiliki key yang dibutuhkan
    //             if (is_array($data) && isset($data['data']['jadwal'])) {
    //                 $schedule = $data['data']['jadwal'];
    //             } else {
    //                 // Log respon jika format tidak sesuai
    //                 Log::warning("Format API Sholat tidak sesuai: " . substr($responseBody, 0, 200));
    //                 throw new \Exception('Format data API berubah atau tidak valid');
    //             }
    //         } else {
    //             throw new \Exception('Request API gagal. Status: ' . $response->getStatusCode());
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback jadwal statis jika API error
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }

    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         $response = Http::timeout(3)->get("https://equran.id/api/v2/shalat/jadwal/$cityId/$today");

    //         // FIX: Gunakan method PSR-7 (getStatusCode) dan json_decode manual
    //         // Ini adalah cara paling aman untuk menghindari error 'Undefined method successful/json'
    //         if ($response->getStatusCode() == 200) {
    //             // Konversi body response ke string lalu decode JSON
    //             $responseBody = (string) $response->getBody();
    //             $data = json_decode($responseBody, true);

    //             // Validasi struktur data
    //             if (is_array($data) && isset($data['data']['jadwal'])) {
    //                 $schedule = $data['data']['jadwal'];
    //             } else {
    //                 Log::warning("Format API Sholat tidak sesuai: " . substr($responseBody, 0, 200));
    //                 throw new \Exception('Format data API berubah atau tidak valid');
    //             }
    //         } else {
    //             throw new \Exception('Request API gagal. Status: ' . $response->getStatusCode());
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback ke jadwal statis jika API error/timeout
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }
    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         // FIX FINAL: Menggunakan cURL native PHP
    //         // Ini menghindari semua masalah "Undefined method" pada wrapper HTTP Laravel
    //         $url = "https://equran.id/api/v2/shalat/jadwal/$cityId/$today";

    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Timeout koneksi
    //         curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout total

    //         $responseBody = curl_exec($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         $curlError = curl_error($ch);

    //         curl_close($ch);

    //         if ($httpCode == 200 && $responseBody) {
    //             $data = json_decode($responseBody, true);

    //             // Validasi struktur data
    //             if (is_array($data) && isset($data['data']['jadwal'])) {
    //                 $schedule = $data['data']['jadwal'];
    //             } else {
    //                 Log::warning("Format API Sholat tidak sesuai: " . substr($responseBody, 0, 200));
    //                 throw new \Exception('Format data API berubah atau tidak valid');
    //             }
    //         } else {
    //             throw new \Exception("Request API gagal. Status: $httpCode. Error: $curlError");
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback ke jadwal statis jika API error/timeout
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }
    // {
    //     $user = Auth::user();

    //     // Cek data siswa berdasarkan user login
    //     $student = Student::where('user_id', $user->id)->first();

    //     // --- FIX ERR_TOO_MANY_REDIRECTS ---
    //     if (!$student) {
    //         if (Route::has('dashboard')) {
    //             return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //         }
    //         return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
    //     }

    //     // 1. Ambil Jadwal Sholat dari API equran.id
    //     $cityId = '0306'; // Bukittinggi
    //     $today = Carbon::now()->format('Y-m-d');

    //     $schedule = [];
    //     try {
    //         // FIX FINAL: Menggunakan cURL native PHP
    //         // Ini menghindari semua masalah "Undefined method" pada wrapper HTTP Laravel
    //         $url = "https://equran.id/api/v2/shalat/jadwal/$cityId/$today";

    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Timeout koneksi
    //         curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout total

    //         $responseBody = curl_exec($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         $curlError = curl_error($ch);

    //         // curl_close($ch); // Removed as it has no effect in PHP 8.0+ (CurlHandle automatically closes)

    //         if ($httpCode == 200 && $responseBody) {
    //             $data = json_decode($responseBody, true);

    //             // Validasi struktur data
    //             if (is_array($data) && isset($data['data']['jadwal'])) {
    //                 $schedule = $data['data']['jadwal'];
    //             } else {
    //                 Log::warning("Format API Sholat tidak sesuai: " . substr($responseBody, 0, 200));
    //                 throw new \Exception('Format data API berubah atau tidak valid');
    //             }
    //         } else {
    //             throw new \Exception("Request API gagal. Status: $httpCode. Error: $curlError");
    //         }
    //     } catch (\Exception $e) {
    //         // Fallback ke jadwal statis jika API error/timeout
    //         Log::error("Prayer API Error: " . $e->getMessage());
    //         $schedule = [
    //             'subuh' => '04:55', 'dzuhur' => '12:25', 'ashar' => '15:40',
    //             'maghrib' => '18:25', 'isya' => '19:35', 'dhuha' => '07:00'
    //         ];
    //     }

    //     // 2. Cek Status Absensi Hari Ini
    //     $attendances = PrayerAttendance::where('student_id', $student->id)
    //                     ->whereDate('date', $today)
    //                     ->pluck('check_in_time', 'prayer_name')
    //                     ->toArray();

    //     return view('prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    // }
{
        $user = Auth::user();

        // Cek data siswa berdasarkan user login
        $student = Student::where('user_id', $user->id)->first();

        // --- FIX ERR_TOO_MANY_REDIRECTS ---
        if (!$student) {
            if (Route::has('dashboard')) {
                return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
            }
            return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
        }

        // 1. Ambil Jadwal Sholat (API MYQURAN.COM)
        $cityId = '0306'; // ID Kota Bukittinggi

        // FIX: Pastikan Timezone Asia/Jakarta agar tanggal request benar
        $dateObj = Carbon::now('Asia/Jakarta');
        $year = $dateObj->format('Y');
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');
        $today = $dateObj->format('Y-m-d');

        $schedule = [];

        try {
            $url = "https://api.myquran.com/v2/sholat/jadwal/$cityId/$year/$month/$day";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Naikkan timeout jadi 10 detik
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // FIX UTAMA: Bypass SSL Verification (Sering error di localhost/hosting shared)
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect jika ada

            // User Agent
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            // curl_close($ch); // Auto close PHP 8+

            if ($httpCode == 200 && $responseBody) {
                $data = json_decode($responseBody, true);

                // Validasi struktur data MyQuran
                if (is_array($data) && isset($data['data']['jadwal'])) {
                    $schedule = $data['data']['jadwal'];
                } else {
                    Log::warning("Format API Sholat MyQuran tidak sesuai atau limit tercapai: " . substr($responseBody, 0, 200));
                    throw new \Exception('Format data API tidak valid');
                }
            } else {
                throw new \Exception("Request API MyQuran gagal. Status: $httpCode. Error: $curlError");
            }

        } catch (\Exception $e) {
            // Fallback ke jadwal statis jika API error
            Log::error("Prayer API Error: " . $e->getMessage());

            // Data Dummy (WIB)
            $schedule = [
                'subuh'   => '05:00',
                'dzuhur'  => '12:30',
                'ashar'   => '15:45',
                'maghrib' => '18:30',
                'isya'    => '19:45',
                'dhuha'   => '07:00',
                'imsak'   => '04:50',
                'tanggal' => $dateObj->translatedFormat('l, d F Y') . ' (Data Offline)'
            ];
        }

        // 2. Cek Status Absensi Hari Ini
        $attendances = PrayerAttendance::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->pluck('check_in_time', 'prayer_name')
                        ->toArray();

        return view('siswa.prayer.index', compact('schedule', 'attendances', 'today', 'student'));
    }




    /**
     * Proses Simpan Absen Sholat dengan Validasi GPS Wajib
     */
    // public function store(Request $request)
    // {
    //     // 1. Validasi Input Dasar
    //     $request->validate([
    //         'prayer_name' => 'required|in:subuh,dzuhur,ashar,maghrib,isya,dhuha',
    //         'latitude'    => 'required|numeric', // Diubah menjadi required
    //         'longitude'   => 'required|numeric', // Diubah menjadi required
    //     ], [
    //         'latitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
    //         'longitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
    //     ]);

    //     // 2. Security Check: Pastikan koordinat tidak nol atau kosong
    //     if (empty($request->latitude) || empty($request->longitude)) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Absensi gagal! Anda wajib mengaktifkan GPS/Layanan Lokasi pada perangkat Anda.'
    //         ], 422);
    //     }

    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->firstOrFail();
    //     $now = Carbon::now();

    //     // 3. Cek Duplikasi Absen
    //     $exists = PrayerAttendance::where('student_id', $student->id)
    //                 ->whereDate('date', $now->format('Y-m-d'))
    //                 ->where('prayer_name', $request->prayer_name)
    //                 ->exists();

    //     if ($exists) {
    //         return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absen untuk sholat ini!'], 400);
    //     }

    //     // 4. Simpan Data Absensi
    //     try {
    //         PrayerAttendance::create([
    //             'student_id'    => $student->id,
    //             'date'          => $now->format('Y-m-d'),
    //             'prayer_name'   => $request->prayer_name,
    //             'check_in_time' => $now->format('H:i:s'),
    //             'status'        => 'hadir',
    //             'latitude'      => $request->latitude,
    //             'longitude'     => $request->longitude,
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Alhamdulillah, absen sholat berhasil dicatat dengan lokasi GPS.'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan sistem saat menyimpan absensi.'
    //         ], 500);
    //     }
    // }
     /**
     * Proses Simpan Absen Sholat dengan Validasi GPS Wajib
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'prayer_name' => 'required|in:subuh,dzuhur,ashar,maghrib,isya,dhuha',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
        ], [
            'latitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
            'longitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS/Layanan Lokasi Anda aktif.',
        ]);

        // 2. Security Check: Pastikan koordinat tidak nol atau kosong
        if (empty($request->latitude) || empty($request->longitude)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi gagal! Anda wajib mengaktifkan GPS/Layanan Lokasi pada perangkat Anda.'
            ], 422);
        }

        $user = Auth::user();
        try {
            $student = Student::where('user_id', $user->id)->firstOrFail();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $now = Carbon::now();

        // 3. Cek Duplikasi Absen
        $exists = PrayerAttendance::where('student_id', $student->id)
                    ->whereDate('date', $now->format('Y-m-d'))
                    ->where('prayer_name', $request->prayer_name)
                    ->exists();

        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absen untuk sholat ini!'], 400);
        }

        // 4. Simpan Data Absensi
        try {
            PrayerAttendance::create([
                'student_id'    => $student->id,
                'date'          => $now->format('Y-m-d'),
                'prayer_name'   => $request->prayer_name,
                'check_in_time' => $now->format('H:i:s'),
                'status'        => 'hadir',
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Alhamdulillah, absen sholat berhasil dicatat dengan lokasi GPS.'
            ]); // Status 200 OK

        } catch (\Exception $e) {
            Log::error("Prayer Store Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan absensi.'
            ], 500);
        }
    }
}
