<?php

namespace App\Http\Controllers;

use App\Models\PrayerAttendance;
use App\Models\PrayerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrayerAttendanceController extends Controller
{
    public function index()
    {
        $today = Carbon::now()->toDateString();
        $student = Auth::user()->student;

        // 1. Ambil Data Lokasi Mesjid Sekolah Utama (tanpa filter is_active agar tetap terbaca meski false)
        $schoolMosque = PrayerLocation::first();
        $masjidLat = $schoolMosque ? (float) $schoolMosque->latitude : -0.312221;
        $masjidLng = $schoolMosque ? (float) $schoolMosque->longitude : 100.383403;
        $radius    = $schoolMosque ? (int) $schoolMosque->radius_meters : 10;

        // Mode Kebijakan: true = Wajib dari Masjid, false = Boleh dari Rumah / PJJ
        $isMosqueMode = $schoolMosque ? (bool) $schoolMosque->is_active : true;

        // 2. Simulasi/Pengaturan Jadwal Sholat Hari Ini
        $schedule = [
            'subuh'   => '04:45',
            'dzuhur'  => '12:15',
            'ashar'   => '15:30',
            'maghrib' => '18:20',
            'isya'    => '19:30',
        ];

        // 3. Ambil Absensi Siswa Hari Ini
        $attendances = [];
        if ($student) {
            $records = PrayerAttendance::where('student_id', $student->id)
                ->where('date', $today)
                ->get();

            foreach ($records as $rec) {
                $attendances[$rec->prayer_name] = true;
            }
        }

        return view('prayer_attendances.index', compact(
            'today',
            'schedule',
            'attendances',
            'masjidLat',
            'masjidLng',
            'radius',
            'isMosqueMode'
        ));
    }

    public function create()
    {
        return view('prayer_attendances.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prayer_name'    => 'required|string',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'photo_evidence' => 'nullable|image|max:2048',
        ]);

        $student = Auth::user()->student;

        if (!$student) {
            return response()->json([
                'message' => 'Data profil siswa tidak ditemukan!'
            ], 422);
        }

        $today = Carbon::now()->toDateString();

        // 1. Cek Duplikasi Absensi Hari Ini
        $alreadyDone = PrayerAttendance::where('student_id', $student->id)
            ->where('date', $today)
            ->where('prayer_name', $validated['prayer_name'])
            ->exists();

        if ($alreadyDone) {
            return response()->json([
                'message' => "Anda sudah mencatat ibadah {$validated['prayer_name']} hari ini."
            ], 422);
        }

        $schoolMosque = PrayerLocation::first();
        $locationNote = 'Masjid Sekolah';

        // 2. Cek Mode Kebijakan Admin via is_active
        if ($schoolMosque && $schoolMosque->is_active) {
            // MODE MASJID: Wajib periksa radius GPS
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $schoolMosque->latitude,
                $schoolMosque->longitude
            );

            if ($distance > $schoolMosque->radius_meters) {
                return response()->json([
                    'message' => "Gagal! Mode Wajib dari Masjid Sekolah. Jarak Anda ({$distance}m) melebihi radius maksimal ({$schoolMosque->radius_meters}m)."
                ], 422);
            }
        } else {
            // MODE RUMAH / PJJ: Bebas radius GPS, wajib/dianjurkan upload foto bukti
            $locationNote = 'Sholat di Rumah / PJJ';

            if (!$request->hasFile('photo_evidence')) {
                return response()->json([
                    'message' => 'Wajib mengunggah foto bukti sholat jika dilaksanakan dari rumah.'
                ], 422);
            }
        }

        // Simpan Berkas Foto Bukti jika ada
        $photoPath = null;
        if ($request->hasFile('photo_evidence')) {
            $photoPath = $request->file('photo_evidence')->store('prayer_photos', 'public');
        }

        // 3. Simpan Transaksi Data Absensi
        PrayerAttendance::create([
            'student_id'     => $student->id,
            'date'           => $today,
            'prayer_name'    => $validated['prayer_name'],
            'check_in_time'  => Carbon::now()->toTimeString(),
            'status'         => 'hadir',
            'photo_evidence' => $photoPath,
            'notes'          => $locationNote,
            'latitude'       => $validated['latitude'],
            'longitude'      => $validated['longitude'],
        ]);

        return response()->json([
            'message' => "Berhasil mencatat ibadah {$validated['prayer_name']}!"
        ]);
    }

    /**
     * Rumus Haversine yang disamakan 100% dengan Leaflet JS Frontend
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // Radius bumi dalam meter

        // Konversi derajat ke radian
        $lat1Rad = deg2rad((float)$lat1);
        $lon1Rad = deg2rad((float)$lon1);
        $lat2Rad = deg2rad((float)$lat2);
        $lon2Rad = deg2rad((float)$lon2);

        $dLat = $lat2Rad - $lat1Rad;
        $dLon = $lon2Rad - $lon1Rad;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($R * $c);
    }
}