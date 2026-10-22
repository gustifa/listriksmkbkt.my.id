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

        // 1. Ambil Data Lokasi Mesjid Sekolah Utama
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

        // 3. Ambil Absensi Siswa Hari Ini beserta Statusnya
        $attendances = [];
        if ($student) {
            $records = PrayerAttendance::where('student_id', $student->id)
                ->where('date', $today)
                ->get();

            foreach ($records as $rec) {
                // Menyimpan status absensi ('hadir' atau 'berhalangan')
                $attendances[$rec->prayer_name] = $rec->status;
            }
        }

        return view('prayer_attendances.index', compact(
            'today',
            'schedule',
            'attendances',
            'masjidLat',
            'masjidLng',
            'radius',
            'isMosqueMode',
            'student'
        ));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return response()->json([
                'message' => 'Data profil siswa tidak ditemukan!'
            ], 422);
        }

        $today = Carbon::now()->toDateString();
        $status = $request->input('status', 'hadir');

        // ------------------------------------------------------------------
        // LOGIKA KHUSUS SISWA PEREMPUAN BERHALANGAN (UDZUR SYAR'I)
        // ------------------------------------------------------------------
        if ($status === 'berhalangan') {
            // Validasi: Hanya siswa perempuan (gender = 'P') yang diizinkan
            if (strtoupper($student->gender) !== 'P') {
                return response()->json([
                    'message' => 'Status berhalangan hanya berlaku untuk siswa perempuan.'
                ], 403);
            }

            // Cek Duplikasi Absensi Hari Ini
            $alreadyDone = PrayerAttendance::where('student_id', $student->id)
                ->where('date', $today)
                ->where('prayer_name', $request->prayer_name)
                ->exists();

            if ($alreadyDone) {
                return response()->json([
                    'message' => "Anda sudah mencatat status ibadah {$request->prayer_name} hari ini."
                ], 422);
            }

            // Simpan transaksi status berhalangan (tanpa wajib foto & radius GPS)
            PrayerAttendance::create([
                'student_id'     => $student->id,
                'date'           => $today,
                'prayer_name'    => $request->prayer_name,
                'check_in_time'  => Carbon::now()->toTimeString(),
                'status'         => 'berhalangan',
                'photo_evidence' => null,
                'notes'          => 'Berhalangan (Halangan Syar\'i)',
                'latitude'       => $request->input('latitude'),
                'longitude'      => $request->input('longitude'),
                'location_type'  => 'rumah',
            ]);

            return response()->json([
                'message' => "Status berhalangan untuk sholat {$request->prayer_name} berhasil dicatat."
            ]);
        }

        // ------------------------------------------------------------------
        // LOGIKA STANDAR ABSENSI SHOLAT (STATUS: HADIR)
        // ------------------------------------------------------------------
        $validated = $request->validate([
            'prayer_name'    => 'required|string',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'photo_evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'prayer_name.required' => 'Nama sholat wajib dipilih.',
            'latitude.required'    => 'Koordinat GPS (Latitude) tidak terdeteksi.',
            'longitude.required'   => 'Koordinat GPS (Longitude) tidak terdeteksi.',
            'photo_evidence.image' => 'Berkas bukti harus berupa gambar/foto.',
            'photo_evidence.mimes' => 'Format foto harus berupa JPG, JPEG, atau PNG.',
            'photo_evidence.max'   => 'Ukuran foto terlalu besar! Maksimal ukuran file foto adalah 2 MB.',
        ]);

        // Cek Duplikasi Absensi
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

        // Validasi Mode Kebijakan Admin
        if ($schoolMosque && $schoolMosque->is_active) {
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
            $locationNote = 'Sholat di Rumah / PJJ';

            if (!$request->hasFile('photo_evidence')) {
                return response()->json([
                    'message' => 'Wajib mengunggah foto bukti sholat jika dilaksanakan dari rumah.'
                ], 422);
            }
        }

        // Simpan Berkas Foto
        $photoPath = null;
        if ($request->hasFile('photo_evidence')) {
            $photoPath = $request->file('photo_evidence')->store('prayer_photos', 'public');
        }

        // Simpan Transaksi
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
            'location_type'  => $schoolMosque && $schoolMosque->is_active ? 'masjid' : 'rumah',
        ]);

        return response()->json([
            'message' => "Berhasil mencatat ibadah {$validated['prayer_name']}!"
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
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