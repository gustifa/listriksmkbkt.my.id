<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrayerAttendance;
use App\Models\Student;
use App\Models\Setting;
use App\Models\PrayerSchedule; // Pastikan model ini diimport
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class PrayerStudentController extends Controller
{
    /**
     * Halaman Jadwal & Absen Sholat
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        // Cek Siswa & Redirect jika bukan siswa
        if (!$student) {
            if (Route::has('dashboard')) {
                return redirect()->route('dashboard')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
            }
            return redirect('/')->with('error', 'Fitur Absensi Sholat khusus untuk akun Siswa.');
        }

        $dateObj = Carbon::now('Asia/Jakarta');
        $today = $dateObj->format('Y-m-d');
        $schedule = [];
        //dd($today);

        // 1. CEK DATABASE LOKAL (Prioritas Utama)
        // Jika Admin sudah melakukan sync, data akan diambil dari sini agar cepat
        $localSchedule = PrayerSchedule::where('date', $today)->first();

        if ($localSchedule) {
            // Gunakan data dari database
            $schedule = [
                'subuh'   => $localSchedule->subuh,
                'dhuha'   => $localSchedule->dhuha,
                'dzuhur'  => $localSchedule->dzuhur,
                'ashar'   => $localSchedule->ashar,
                'maghrib' => $localSchedule->maghrib,
                'isya'    => $localSchedule->isya,
                'date'    => $localSchedule->date->format('Y-m-d'),
                'source'  => 'database' // Marker debug
            ];
        } else {
            // 2. FALLBACK KE API (Jika data lokal kosong)
            // Mengambil ID Kota dari Setting atau default Bukittinggi
            $cityId = Setting::value('prayer_city_id', '0306');
            $year = $dateObj->format('Y');
            $month = $dateObj->format('m');
            $day = $dateObj->format('d');

            try {
                $url = "https://api.myquran.com/v2/sholat/jadwal/$cityId/$year/$month/$day";

                // Menggunakan cURL Native untuk stabilitas
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

                $responseBody = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200 && $responseBody) {
                    $data = json_decode($responseBody, true);
                    if (is_array($data) && isset($data['data']['jadwal'])) {
                        $schedule = $data['data']['jadwal'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Prayer API Fallback Error: " . $e->getMessage());
            }

            // 3. FALLBACK TERAKHIR (Jika API & DB Gagal, gunakan data statis)
            if (empty($schedule)) {
                $schedule = [
                    'subuh'   => '05:00',
                    'dzuhur'  => '12:30',
                    'ashar'   => '15:45',
                    'maghrib' => '18:30',
                    'isya'    => '19:45',
                    'dhuha'   => '07:00',
                ];
            }
        }

        // Cek Status Absensi Hari Ini
        $attendances = PrayerAttendance::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->pluck('check_in_time', 'prayer_name')
                        ->toArray();

        // Ambil Setting Lokasi dari Database (Dinamis)
        $masjidLat = (float) Setting::value('masjid_lat', -0.305123);
        $masjidLng = (float) Setting::value('masjid_lng', 100.369456);
        $radius    = (int) Setting::value('masjid_radius', 50);

        return view('siswa.prayer.index', compact('schedule', 'attendances', 'today', 'student', 'masjidLat', 'masjidLng', 'radius'));
    }

    /**
     * Proses Simpan Absen Sholat dengan Validasi GEOFENCING DINAMIS
     */
    public function store(Request $request)
    {
        // // 1. Validasi Input Dasar
        // $request->validate([
        //     'prayer_name' => 'required|in:subuh,dzuhur,ashar,maghrib,isya,dhuha',
        //     'latitude'    => 'required|numeric',
        //     'longitude'   => 'required|numeric',
        // ], [
        //     'latitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS aktif.',
        //     'longitude.required' => 'Lokasi tidak terdeteksi. Pastikan GPS aktif.',
        // ]);

        // Daftar Sholat Valid (Fardhu, Sunnah, Rawatib)
        $validPrayers = [
            // Fardhu
            'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya', 'jumat',
            // Sunnah Utama
            'dhuha', 'tahajjud', 'tarawih', 'witir',
            // Rawatib (Qobliyah/Ba'diyah)
            'qobliyah_subuh', 
            'qobliyah_dzuhur', 'badiyah_dzuhur', 
            'qobliyah_ashar', 
            'qobliyah_maghrib', 'badiyah_maghrib',
            'qobliyah_isya', 'badiyah_isya'
        ];

        $request->validate([
            'prayer_name' => 'required|in:' . implode(',', $validPrayers),
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
        ]);

        // 2. Security Check: Pastikan koordinat tidak kosong
        if (empty($request->latitude) || empty($request->longitude)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi gagal! GPS Wajib aktif.'
            ], 422);
        }

        $user = Auth::user();
        try {
            $student = Student::where('user_id', $user->id)->firstOrFail();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        // --- FITUR GEOFENCING DINAMIS ---
        // Ambil Setting dari DB (sama dengan index) agar validasi konsisten
        $masjidLat = (float) Setting::value('masjid_lat', -0.305123);
        $masjidLng = (float) Setting::value('masjid_lng', 100.369456);
        $maxRadius = (int) Setting::value('masjid_radius', 50);

        // Hitung jarak siswa ke masjid
        $distance = $this->calculateDistance($request->latitude, $request->longitude, $masjidLat, $masjidLng);

        // Jika jarak lebih besar dari radius yang ditentukan, tolak absensi
        if ($distance > $maxRadius) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal! Anda berada di luar area masjid (' . round($distance) . ' meter). Harap masuk ke area masjid (< ' . $maxRadius . 'm).'
            ], 422);
        }
        // ------------------------

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
                'message' => 'Alhamdulillah, absen sholat berhasil dicatat.'
            ]);

        } catch (\Exception $e) {
            Log::error("Prayer Store Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan absensi.'
            ], 500);
        }
    }

    /**
     * Hitung Jarak antara 2 titik koordinat (Haversine Formula)
     * Return: Jarak dalam Meter
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
