<?php

namespace App\Http\Controllers;

use App\Models\PrayerAttendance;
use App\Models\PrayerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrayerAttendanceController extends Controller
{
    /**
     * Helper untuk memetakan nama sholat (fardhu/rawatib/sunnah) ke kunci waktu utama
     */
    private function getBasePrayerKey($prayerName)
    {
        $prayerName = strtolower($prayerName);

        if (str_contains($prayerName, 'subuh')) return 'subuh';
        if (str_contains($prayerName, 'dzuhur') || str_contains($prayerName, 'zuhur') || str_contains($prayerName, 'jumat')) return 'zuhur';
        if (str_contains($prayerName, 'ashar')) return 'ashar';
        if (str_contains($prayerName, 'maghrib')) return 'maghrib';
        if (str_contains($prayerName, 'isya')) return 'isya';

        // Untuk sunnah lainnya (tarawih, dhuha, dll) default-kan ke null
        return null;
    }

    /**
     * Helper untuk mengecek apakah sholat tertentu WAJIB di Masjid
     */
    private function isMustAtMasjid($schoolMosque, $prayerName)
    {
        if (!$schoolMosque || !$schoolMosque->is_active) {
            return false;
        }

        $baseKey = $this->getBasePrayerKey($prayerName);
        if (!$baseKey) {
            return false;
        }

        $columnName = "is_must_at_masjid_{$baseKey}";
        return (bool) ($schoolMosque->{$columnName} ?? false);
    }

    public function index()
    {
        $today = Carbon::now()->toDateString();
        $student = Auth::user()->student;

        // 1. Ambil Data Lokasi Mesjid Sekolah Utama
        $schoolMosque = PrayerLocation::first();
        $masjidLat = $schoolMosque ? (float) $schoolMosque->latitude : -0.312221;
        $masjidLng = $schoolMosque ? (float) $schoolMosque->longitude : 100.383403;
        $radius    = $schoolMosque ? (int) $schoolMosque->radius_meters : 10;

        // Mode Kebijakan Global
        $isMosqueMode = $schoolMosque ? (bool) $schoolMosque->is_active : true;

        // Map status wajib masjid per waktu sholat untuk JS
        $mustAtMasjidRules = [
            'subuh'   => $this->isMustAtMasjid($schoolMosque, 'subuh'),
            'dzuhur'  => $this->isMustAtMasjid($schoolMosque, 'dzuhur'),
            'ashar'   => $this->isMustAtMasjid($schoolMosque, 'ashar'),
            'maghrib' => $this->isMustAtMasjid($schoolMosque, 'maghrib'),
            'isya'    => $this->isMustAtMasjid($schoolMosque, 'isya'),
        ];

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
            'mustAtMasjidRules',
            'student'
        ));
    }

    // Sebelum diupdate 31102016

    // public function store(Request $request)
    // {
    //     $student = Auth::user()->student;

    //     if (!$student) {
    //         return response()->json([
    //             'message' => 'Data profil siswa tidak ditemukan!'
    //         ], 422);
    //     }

    //     $today = Carbon::now()->toDateString();
    //     $status = $request->input('status', 'hadir');

    //     // ------------------------------------------------------------------
    //     // LOGIKA KHUSUS SISWA PEREMPUAN BERHALANGAN (UDZUR SYAR'I)
    //     // ------------------------------------------------------------------
    //     if ($status === 'berhalangan') {
    //         if (strtoupper($student->gender) !== 'P') {
    //             return response()->json([
    //                 'message' => 'Status berhalangan hanya berlaku untuk siswa perempuan.'
    //             ], 403);
    //         }

    //         $alreadyDone = PrayerAttendance::where('student_id', $student->id)
    //             ->where('date', $today)
    //             ->where('prayer_name', $request->prayer_name)
    //             ->exists();

    //         if ($alreadyDone) {
    //             return response()->json([
    //                 'message' => "Anda sudah mencatat status ibadah {$request->prayer_name} hari ini."
    //             ], 422);
    //         }

    //         PrayerAttendance::create([
    //             'student_id'     => $student->id,
    //             'date'           => $today,
    //             'prayer_name'    => $request->prayer_name,
    //             'check_in_time'  => Carbon::now()->toTimeString(),
    //             'status'         => 'berhalangan',
    //             'photo_evidence' => null,
    //             'notes'          => 'Berhalangan (Halangan Syar\'i)',
    //             'latitude'       => $request->input('latitude'),
    //             'longitude'      => $request->input('longitude'),
    //             'location_type'  => 'rumah',
    //         ]);

    //         return response()->json([
    //             'message' => "Status berhalangan untuk sholat {$request->prayer_name} berhasil dicatat."
    //         ]);
    //     }

    //     // ------------------------------------------------------------------
    //     // LOGIKA STANDAR ABSENSI SHOLAT (STATUS: HADIR)
    //     // ------------------------------------------------------------------
    //     $validated = $request->validate([
    //         'prayer_name'    => 'required|string',
    //         'latitude'       => 'required|numeric',
    //         'longitude'      => 'required|numeric',
    //         'photo_evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ], [
    //         'prayer_name.required' => 'Nama sholat wajib dipilih.',
    //         'latitude.required'    => 'Koordinat GPS (Latitude) tidak terdeteksi.',
    //         'longitude.required'   => 'Koordinat GPS (Longitude) tidak terdeteksi.',
    //         'photo_evidence.image' => 'Berkas bukti harus berupa gambar/foto.',
    //         'photo_evidence.mimes' => 'Format foto harus berupa JPG, JPEG, atau PNG.',
    //         'photo_evidence.max'   => 'Ukuran foto terlalu besar! Maksimal ukuran file foto adalah 2 MB.',
    //     ]);

    //     $alreadyDone = PrayerAttendance::where('student_id', $student->id)
    //         ->where('date', $today)
    //         ->where('prayer_name', $validated['prayer_name'])
    //         ->exists();

    //     if ($alreadyDone) {
    //         return response()->json([
    //             'message' => "Anda sudah mencatat ibadah {$validated['prayer_name']} hari ini."
    //         ], 422);
    //     }

    //     $schoolMosque = PrayerLocation::first();
    //     $isMustAtMasjid = $this->isMustAtMasjid($schoolMosque, $validated['prayer_name']);

    //     // Validasi Kebijakan per Waktu Sholat
    //     if ($isMustAtMasjid) {
    //         $distance = $this->calculateDistance(
    //             $validated['latitude'],
    //             $validated['longitude'],
    //             $schoolMosque->latitude,
    //             $schoolMosque->longitude
    //         );

    //         if ($distance > $schoolMosque->radius_meters) {
    //             return response()->json([
    //                 'message' => "Gagal! Sholat {$validated['prayer_name']} WAJIB dilaksanakan di Masjid Sekolah. Jarak Anda ({$distance}m) melebihi radius maksimal ({$schoolMosque->radius_meters}m)."
    //             ], 422);
    //         }

    //         $locationNote = 'Masjid Sekolah';
    //     } else {
    //         $locationNote = 'Sholat di Rumah / PJJ';

    //         if (!$request->hasFile('photo_evidence')) {
    //             return response()->json([
    //                 'message' => "Wajib mengunggah foto bukti sholat jika dilaksanakan dari rumah."
    //             ], 422);
    //         }
    //     }

    //     // Simpan Berkas Foto
    //     $photoPath = null;
    //     if ($request->hasFile('photo_evidence')) {
    //         $photoPath = $request->file('photo_evidence')->store('prayer_photos', 'public');
    //     }

    //     // Simpan Transaksi
    //     PrayerAttendance::create([
    //         'student_id'     => $student->id,
    //         'date'           => $today,
    //         'prayer_name'    => $validated['prayer_name'],
    //         'check_in_time'  => Carbon::now()->toTimeString(),
    //         'status'         => 'hadir',
    //         'photo_evidence' => $photoPath,
    //         'notes'          => $locationNote,
    //         'latitude'       => $validated['latitude'],
    //         'longitude'      => $validated['longitude'],
    //         'location_type'  => $isMustAtMasjid ? 'masjid' : 'rumah',
    //     ]);

    //     return response()->json([
    //         'message' => "Berhasil mencatat ibadah {$validated['prayer_name']}!"
    //     ]);
    // }

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

        // Ambil Pengaturan dari Database
        $setting = \App\Models\AttendanceSetting::first();
        $enableTelegram = $setting ? (bool) ($setting->enable_telegram_notification ?? true) : true;
        $enableWA = $setting ? (bool) ($setting->enable_wa_notification ?? true) : true;

        // ------------------------------------------------------------------
        // LOGIKA KHUSUS SISWA PEREMPUAN BERHALANGAN (UDZUR SYAR'I)
        // ------------------------------------------------------------------
        if ($status === 'berhalangan') {
            if (strtoupper($student->gender) !== 'P') {
                return response()->json([
                    'message' => 'Status berhalangan hanya berlaku untuk siswa perempuan.'
                ], 403);
            }

            $alreadyDone = PrayerAttendance::where('student_id', $student->id)
                ->where('date', $today)
                ->where('prayer_name', $request->prayer_name)
                ->exists();

            if ($alreadyDone) {
                return response()->json([
                    'message' => "Anda sudah mencatat status ibadah {$request->prayer_name} hari ini."
                ], 422);
            }

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

            $prayerFormatted = ucfirst($request->prayer_name);
            $msgBerhalangan = "🕌 *LAPORAN PRESENSI IBADAH SHOLAT*\n\n" .
                               "Siswa: *{$student->name}*\n" .
                               "Sholat: *{$prayerFormatted}*\n" .
                               "Waktu: " . Carbon::now()->format('H:i') . " WIB\n" .
                               "Status: *BERHALANGAN (UDZUR SYAR'I)*\n\n" .
                               "_Sistem Presensi Sholat SISFO_";

            // Kirim Notifikasi Telegram (Jika Aktif)
            if ($enableTelegram) {
                $subscriber = \App\Models\TelegramSubscriber::where('student_id', $student->id)
                                ->where('is_active', true)
                                ->first();
                $chatId = $subscriber ? $subscriber->chat_id : $student->telegram_chat_id;

                if ($chatId) {
                    $telegramService = app(\App\Services\TelegramService::class);
                    $telegramService->sendMessage($chatId, $msgBerhalangan, 'absensi', "Presensi Sholat {$prayerFormatted}");
                }
            }

            // Kirim Notifikasi WA (Jika Aktif)
            if ($enableWA && !empty($student->phone)) {
                try {
                    \App\Jobs\SendWhatsappJob::dispatch($student->phone, $msgBerhalangan);
                } catch (\Exception $e) {}
            }

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
        $isMustAtMasjid = $this->isMustAtMasjid($schoolMosque, $validated['prayer_name']);

        // Validasi Kebijakan per Waktu Sholat
        if ($isMustAtMasjid) {
            $distance = $this->calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $schoolMosque->latitude,
                $schoolMosque->longitude
            );

            if ($distance > $schoolMosque->radius_meters) {
                return response()->json([
                    'message' => "Gagal! Sholat {$validated['prayer_name']} WAJIB dilaksanakan di Masjid Sekolah. Jarak Anda ({$distance}m) melebihi radius maksimal ({$schoolMosque->radius_meters}m)."
                ], 422);
            }

            $locationNote = 'Masjid Sekolah';
        } else {
            $locationNote = 'Sholat di Rumah / PJJ';

            if (!$request->hasFile('photo_evidence')) {
                return response()->json([
                    'message' => "Wajib mengunggah foto bukti sholat jika dilaksanakan dari rumah."
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
            'location_type'  => $isMustAtMasjid ? 'masjid' : 'rumah',
        ]);

        // =========================================================
        // PENGIRIMAN NOTIFIKASI ABSENSI SHOLAT
        // =========================================================
        $prayerNameFormatted = ucfirst($validated['prayer_name']);
        $msgSholat = "🕌 *LAPORAN PRESENSI IBADAH SHOLAT*\n\n" .
                     "Siswa: *{$student->name}*\n" .
                     "Sholat: *{$prayerNameFormatted}*\n" .
                     "Waktu: " . Carbon::now()->format('H:i') . " WIB\n" .
                     "Status: *HADIR*\n" .
                     "Lokasi: *{$locationNote}*\n\n" .
                     "_Sistem Presensi Sholat SISFO_";

        // 1. Kirim Telegram jika diaktifkan di DB
        if ($enableTelegram) {
            $subscriber = \App\Models\TelegramSubscriber::where('student_id', $student->id)
                            ->where('is_active', true)
                            ->first();
            $chatId = $subscriber ? $subscriber->chat_id : $student->telegram_chat_id;

            if ($chatId) {
                $telegramService = app(\App\Services\TelegramService::class);
                
                // Kirim Foto Bukti jika ada file foto dari rumah
                if (!empty($photoPath)) {
                    $telegramService->sendPhoto($chatId, $photoPath, $msgSholat, 'absensi', "Presensi Sholat {$prayerNameFormatted}");
                } else {
                    $telegramService->sendMessage($chatId, $msgSholat, 'absensi', "Presensi Sholat {$prayerNameFormatted}");
                }
            }
        }

        // 2. Kirim WA jika diaktifkan di DB
        if ($enableWA && !empty($student->phone)) {
            try {
                \App\Jobs\SendWhatsappJob::dispatch($student->phone, $msgSholat);
            } catch (\Exception $e) {}
        }

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