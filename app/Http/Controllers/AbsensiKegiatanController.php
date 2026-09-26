<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kegiatan; // Ganti sesuai nama model Anda
use App\Models\Absensi;  // Ganti sesuai nama model Anda
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk debugging
use Illuminate\Support\Facades\Auth;

class AbsensiKegiatanController extends Controller
{
    public function index(){
        // Mengambil semua data kegiatan, diurutkan dari yang terbaru
        $kegiatans = Kegiatan::latest()->get();

        return view('kegiatan.index', compact('kegiatans'));
    }
    /**
     * Menampilkan Detail Kegiatan dan QR Code
     */
    public function showKegiatan($id)
    {
        // Mencari data kegiatan (Asumsi tabel: id, nama_kegiatan, tanggal, kode_unik)
        $kegiatan = Kegiatan::findOrFail($id);
        $user = auth()->user();
        // LOGIKA REDIRECT:
        // Jika user adalah 'siswa' atau 'guru', alihkan ke halaman scan
        // Sesuaikan 'siswa' dan 'guru' dengan value di database Bapak
        if ($user->jenis_user == 'siswa' || $user->jenis_user == 'guru') {
            return redirect()->route('kegiatan.scan.camera', $kegiatan->kode_unik)
                            ->with('info', 'Anda diarahkan otomatis ke halaman scan absensi.');
        }

        // Jika user adalah Admin / Kepala Bengkel, tampilkan barcode
        $scanUrl = route('kegiatan.scan', $kegiatan->kode_unik);
        return view('kegiatan.show', compact('kegiatan', 'scanUrl'));
        

        // // Generate URL untuk di-scan. Menggunakan kode unik kegiatan.
        // $scanUrl = route('kegiatan.scan', ['kode_unik' => $kegiatan->kode_unik]);

        // // Generate QR Code dari URL tersebut
        // // QrCode::size(300)->generate($scanUrl) akan dicetak di view
        
        // return view('kegiatan.show', compact('kegiatan', 'scanUrl'));
    }

    /**
     * Mencetak Daftar Hadir beserta Tanda Tangan
     */
    public function printKegiatan($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        // Ambil data absensi beserta relasi user dan profil (guru/siswa) untuk mengambil tanda tangan
        $absensi = Absensi::with(['user.teacher', 'user.student'])
                          ->where('kegiatan_id', $id)
                          ->orderBy('created_at', 'asc')
                          ->get();

        return view('kegiatan.print', compact('kegiatan', 'absensi'));
    }

    /**
     * Logika ketika QR Code di-scan
     */
    // public function scanQr($kode_unik)
    // {
    //     $kegiatan = Kegiatan::where('kode_unik', $kode_unik)->firstOrFail();
    //     $user = auth()->user();

    //     // Cek apakah user sudah absen
    //     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
    //                          ->where('user_id', $user->id)
    //                          ->exists();

    //     if (!$sudahAbsen) {
    //         Absensi::create([
    //             'kegiatan_id' => $kegiatan->id,
    //             'user_id' => $user->id,
    //             'waktu_hadir' => now(),
    //         ]);
    //         return redirect()->route('kegiatan.show', $kegiatan->id)
    //                          ->with('success', 'Berhasil melakukan absensi!');
    //     }

    //     return redirect()->route('kegiatan.show', $kegiatan->id)
    //                      ->with('error', 'Anda sudah melakukan absensi sebelumnya.');
    // }

    // public function scanQr(Request $request, $kode_unik)
    // {
    //     // 1. Mengambil parameter dari URL (setelah tanda ?)
    //     $latSiswa = $request->query('latitude'); 
    //     $lngSiswa = $request->query('longitude');

    //     // 1. Cari kegiatan berdasarkan kode unik
    //     $kegiatan = Kegiatan::where('kode_unik', $kode_unik)->firstOrFail();
    //     $user = auth()->user();

    //     // 2. Cek apakah user sudah absen
    //     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
    //                         ->where('user_id', $user->id)
    //                         ->exists();

    //     if ($sudahAbsen) {
    //         return redirect()->route('kegiatan.show', $kegiatan->id)
    //                         ->with('error', 'Anda sudah melakukan absensi sebelumnya.');
    //     }

    //     // 3. LOGIKA VALIDASI LOKASI (GEOFENCING)
    //     // Jika di tabel kegiatan Bapak mengisi latitude & longitude, maka jarak akan dicek
    //     if ($kegiatan->latitude && $kegiatan->longitude) {
    //         $latSiswa = $request->query('latitude');
    //         $lngSiswa = $request->query('longitude');

    //         if (!$latSiswa || !$lngSiswa) {
    //             return redirect()->back()->with('error', 'Gagal! Lokasi tidak terdeteksi. Pastikan GPS aktif sebelum melakukan scan.');
    //         }

    //         // Hitung jarak menggunakan Haversine Formula
    //         $earthRadius = 6371000; // dalam meter
    //         $dLat = deg2rad($latSiswa - $kegiatan->latitude);
    //         $dLng = deg2rad($lngSiswa - $kegiatan->longitude);
    //         $a = sin($dLat/2) * sin($dLat/2) +
    //             cos(deg2rad($kegiatan->latitude)) * cos(deg2rad($latSiswa)) *
    //             sin($dLng/2) * sin($dLng/2);
    //         $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    //         $jarak = $earthRadius * $c;

    //         // Cek apakah jarak siswa melebihi radius yang ditentukan (misal 50 meter)
    //         if ($jarak > $kegiatan->radius) {
    //             return redirect()->route('kegiatan.show', $kegiatan->id)
    //                             ->with('error', 'Gagal! Anda berada di luar jangkauan area kegiatan (Jarak: ' . round($jarak) . ' meter).');
    //         }
    //     }

    //     // 4. Simpan Absensi Beserta Lokasi Siswa
    //     Absensi::create([
    //         'kegiatan_id' => $kegiatan->id,
    //         'user_id'     => $user->id,
    //         'waktu_hadir' => now(),
    //         'latitude'    => $request->query('latitude'), // Simpan latitude siswa
    //         'longitude'   => $request->query('longitude'), // Simpan longitude siswa
    //     ]);

    //     return redirect()->route('kegiatan.show', $kegiatan->id)
    //                     ->with('success', 'Alhamdulillah, absensi berhasil dicatat di lokasi kegiatan!');
    // }

//     public function scanQr(Request $request, $kode_unik)
// {
//     // 1. Ambil & Bersihkan parameter dari URL
//     // Trim untuk menghapus spasi yang tidak sengaja terbawa
//     $kodeBersih = trim(strtoupper($kode_unik));
//     $latSiswa = $request->query('latitude'); 
//     $lngSiswa = $request->query('longitude');

//     // 2. Cari kegiatan (Gunakan query manual agar lebih fleksibel dibanding findOrFail)
//     $kegiatan = Kegiatan::where('kode_unik', $kodeBersih)->first();

//     if (!$kegiatan) {
//         return redirect()->route('kegiatan.index')
//                          ->with('error', 'Kegiatan tidak ditemukan atau kode salah.');
//     }

//     $user = auth()->user();

//     // 3. Cek apakah user sudah absen
//     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
//                          ->where('user_id', $user->id)
//                          ->exists();

//     if ($sudahAbsen) {
//         return redirect()->route('kegiatan.show', $kegiatan->id)
//                          ->with('error', 'Anda sudah melakukan absensi sebelumnya.');
//     }

//     // 4. LOGIKA VALIDASI LOKASI (GEOFENCING)
//     if ($kegiatan->latitude && $kegiatan->longitude) {
        
//         // Validasi ketersediaan data GPS dari URL
//         if (is_null($latSiswa) || is_null($lngSiswa)) {
//             return redirect()->back()->with('error', 'Lokasi tidak terdeteksi. Mohon aktifkan GPS dan gunakan scanner kembali.');
//         }

//         // Hitung jarak menggunakan Haversine Formula
//         $earthRadius = 6371000; // dalam meter
//         $dLat = deg2rad($latSiswa - $kegiatan->latitude);
//         $dLng = deg2rad($lngSiswa - $kegiatan->longitude);
//         $a = sin($dLat/2) * sin($dLat/2) +
//              cos(deg2rad($kegiatan->latitude)) * cos(deg2rad($latSiswa)) *
//              sin($dLng/2) * sin($dLng/2);
//         $c = 2 * atan2(sqrt($a), sqrt(1-$a));
//         $jarak = $earthRadius * $c;

//         // Toleransi radius (Default 50m jika radius di tabel kosong)
//         $radiusMaks = $kegiatan->radius ?? 50;

//         if ($jarak > $radiusMaks) {
//             return redirect()->route('kegiatan.show', $kegiatan->id)
//                              ->with('error', 'Gagal! Anda berada di luar jangkauan (Jarak: ' . round($jarak) . ' meter).');
//         }
//     }

//     // 5. Simpan Absensi Beserta Lokasi Siswa
//     Absensi::create([
//         'kegiatan_id' => $kegiatan->id,
//         'user_id'     => $user->id,
//         'waktu_hadir' => now(),
//         'latitude'    => $latSiswa,
//         'longitude'   => $lngSiswa,
//     ]);

//     return redirect()->route('kegiatan.show', $kegiatan->id)
//                      ->with('success', 'Alhamdulillah, absensi berhasil dicatat di lokasi kegiatan!');
// }

public function scanQr(Request $request, $kode_unik = null)
{
    // 1. LOGIKA PENYAMBUNG: Jika {kode_unik} kosong, ambil dari Query String (setelah tanda ?)
    if (!$kode_unik) {
        // Mengambil kunci pertama dari query string (misal: KEG-J2D6E0K9)
        $kode_unik = collect($request->query())->keys()->first();
    }

    // Jika tetap kosong, baru tampilkan error
    if (!$kode_unik) {
        return redirect()->route('kegiatan.index')->with('error', 'Kode QR tidak valid.');
    }

    $kodeBersih = trim(strtoupper($kode_unik));
    $latSiswa = $request->query('latitude'); 
    $lngSiswa = $request->query('longitude');

    // 2. Cari kegiatan
    $kegiatan = Kegiatan::where('kode_unik', $kodeBersih)->first();

    if (!$kegiatan) {
        return redirect()->route('kegiatan.index')
                         ->with('error', 'Kegiatan dengan kode ['.$kodeBersih.'] tidak ditemukan.');
    }

    $user = auth()->user();

    // 3. Cek apakah user sudah absen
    $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
                         ->where('user_id', $user->id)
                         ->exists();

    if ($sudahAbsen) {
        return redirect()->route('kegiatan.show', $kegiatan->id)
                         ->with('error', 'Anda sudah melakukan absensi sebelumnya.');
    }

    // 4. VALIDASI LOKASI (Wajib Terdeteksi)
    if ($kegiatan->latitude && $kegiatan->longitude) {
        if (is_null($latSiswa) || is_null($lngSiswa)) {
            // Jika lewat link langsung tanpa scanner, arahkan ke halaman scanner agar GPS terbaca
            return redirect()->route('kegiatan.scan.camera', ['kode' => $kodeBersih])
                             ->with('info', 'Mohon tunggu hingga GPS terkunci sebelum absen.');
        }

        // Hitung jarak (Haversine)
        $earthRadius = 6371000;
        $dLat = deg2rad($latSiswa - $kegiatan->latitude);
        $dLng = deg2rad($lngSiswa - $kegiatan->longitude);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($kegiatan->latitude)) * cos(deg2rad($latSiswa)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $jarak = $earthRadius * $c;

        if ($jarak > ($kegiatan->radius ?? 50)) {
            return redirect()->route('kegiatan.show', $kegiatan->id)
                             ->with('error', 'Gagal! Anda berada di luar area ('.round($jarak).'m).');
        }
    }

    // 5. Simpan Data
    Absensi::create([
        'kegiatan_id' => $kegiatan->id,
        'user_id'     => $user->id,
        'waktu_hadir' => now(),
        'latitude'    => $latSiswa,
        'longitude'   => $lngSiswa,
    ]);

    return redirect()->route('kegiatan.show', $kegiatan->id)
                     ->with('success', 'Absensi Berhasil!');
}

    public function storeKegiatan(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'deskripsi'     => 'nullable|string',
        ]);

        // Generate kode unik acak untuk QR Code (contoh: KEG-A1B2C3D4)
        $kodeUnik = 'KEG-' . strtoupper(Str::random(8));

        // Simpan ke database
        Kegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal'       => $request->tanggal,
            'kode_unik'     => $kodeUnik,
            'waktu_berakhir' => $request->waktu_berakhir, // <--- TAMBAHAN INI
            'deskripsi'     => $request->deskripsi,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'radius'        => $request->radius,
        ]);

        return redirect()->back()->with('success', 'Kegiatan berhasil ditambahkan!');
    }

    public function scan()
    {
        return view('kegiatan.scan');
    }

     /**
     * Memproses hasil scan QR Code (Ajax/Post).
     */
    // public function proses(Request $request)
    // {
    //     // 1. Validasi input dari QR Code (berupa teks/string 'kode_unik')
    //     $request->validate([
    //         'kode_unik' => 'required|string',
    //     ]);

    //     $kodeQr = $request->kode_unik;

    //     // 2. Cari kegiatan berdasarkan kode unik tersebut
    //     // Pastikan kegiatan aktif/ada di database
    //     $kegiatan = Kegiatan::where('kode_unik', $kodeQr)->first();

    //     // Jika QR Code tidak valid (kegiatan tidak ditemukan)
    //     if (!$kegiatan) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'QR Code tidak valid atau kegiatan tidak ditemukan.'
    //         ], 404);
    //     }

    //     // 3. Cek apakah user (guru) sudah pernah absen di kegiatan ini
    //     // Kita asumsikan user sudah login (menggunakan Auth::id())
    //     $userId = Auth::id(); // Ganti dengan ID user dummy jika belum pasang sistem login
        
    //     // Contoh jika belum ada sistem login, gunakan user id = 1 untuk testing:
    //     // $userId = 1; 

    //     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
    //                          ->where('user_id', $userId)
    //                          ->exists();

    //     if ($sudahAbsen) {
    //         return response()->json([
    //             'status'  => 'warning',
    //             'message' => 'Anda sudah melakukan absensi untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //         ], 200);
    //     }

    //     // 4. Jika valid dan belum absen, simpan data ke tabel absensis
    //     Absensi::create([
    //         'kegiatan_id' => $kegiatan->id,
    //         'user_id'     => $userId,
    //         'waktu_hadir' => now(), // Waktu saat ini
    //     ]);

    //     // 5. Kembalikan response sukses ke halaman scan (scanner HTML5)
    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Berhasil absen untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //     ], 200);
    // }

    // public function proses(Request $request)
    // {
    //     $request->validate([
    //         'kode_unik' => 'required|string',
    //     ]);

    //     // 1. Bersihkan spasi di awal/akhir dan pastikan huruf besar
    //     // Jika QR Code Anda formatnya berbeda, sesuaikan di sini.
    //     $kodeQrRaw = $request->kode_unik;
    //     $kodeQrBersih = trim(strtoupper($kodeQrRaw));

    //     // --- DEBUGGING (PENTING) ---
    //     // Buka file storage/logs/laravel.log untuk melihat apa yang sebenarnya dikirim oleh scanner
    //     Log::info('Menerima hasil scan:', [
    //         'raw_data' => $kodeQrRaw,
    //         'cleaned_data' => $kodeQrBersih
    //     ]);
    //     // ---------------------------

    //     // 2. Jika QR Code ternyata berisi URL (misal: https://web.com/scan/KEG-123)
    //     // Kita ekstrak bagian terakhir (kode-nya saja)
    //     if (filter_var($kodeQrBersih, FILTER_VALIDATE_URL)) {
    //         // Ambil segmen terakhir dari URL
    //         $segments = explode('/', parse_url($kodeQrBersih, PHP_URL_PATH));
    //         $kodeQrBersih = end($segments);
    //         Log::info('Ekstrak dari URL menjadi:', ['kode_akhir' => $kodeQrBersih]);
    //     }

    //     // 3. Cari kegiatan berdasarkan kode unik yang sudah dibersihkan
    //     $kegiatan = Kegiatan::where('kode_unik', $kodeQrBersih)->first();

    //     if (!$kegiatan) {
    //         // Jika tetap tidak ketemu, kirim balik kode yang dicari agar Anda tahu salahnya di mana
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => "Kegiatan tidak ditemukan. Kode yang dicari: [{$kodeQrBersih}]"
    //         ], 404);
    //     }

    //     // 4. Proses Absensi
    //     // Ganti Auth::id() dengan angka statis (misal 1) JIKA Anda belum mengaktifkan sistem Login
    //     $userId = Auth::id() ?? 1; // Fallback ke user 1 jika belum login untuk testing

    //     $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
    //                          ->where('user_id', $userId)
    //                          ->exists();

    //     if ($sudahAbsen) {
    //         return response()->json([
    //             'status'  => 'warning',
    //             'message' => 'Anda sudah melakukan absensi untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //         ], 200);
    //     }

    //     Absensi::create([
    //         'kegiatan_id' => $kegiatan->id,
    //         'user_id'     => $userId,
    //         'waktu_hadir' => now(),
    //     ]);

    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Berhasil absen untuk kegiatan: ' . $kegiatan->nama_kegiatan
    //     ], 200);
    // }

    public function proses(Request $request)
{
    $request->validate([
        'kode_unik' => 'required|string',
        'latitude'  => 'nullable|numeric', // Tambahkan validasi lokasi
        'longitude' => 'nullable|numeric',
    ]);

    $kodeQrRaw = $request->kode_unik;
    $kodeQrBersih = trim(strtoupper($kodeQrRaw));

    // Logging untuk memantau data yang masuk termasuk lokasi
    Log::info('Menerima hasil scan:', [
        'raw_data'  => $kodeQrRaw,
        'lat_siswa' => $request->latitude,
        'lng_siswa' => $request->longitude
    ]);

    // Ekstrak kode jika input berupa URL
    if (filter_var($kodeQrBersih, FILTER_VALIDATE_URL)) {
        $segments = explode('/', parse_url($kodeQrBersih, PHP_URL_PATH));
        $kodeQrBersih = end($segments);
    }

    $kegiatan = Kegiatan::where('kode_unik', $kodeQrBersih)->first();

    if (!$kegiatan) {
        return response()->json([
            'status'  => 'error',
            'message' => "Kegiatan tidak ditemukan. Kode: [{$kodeQrBersih}]"
        ], 404);
    }

    // --- LOGIKA GEOFENCING (KUNCI LOKASI) ---
    if ($kegiatan->latitude && $kegiatan->longitude) {
        $latSiswa = $request->latitude;
        $lngSiswa = $request->longitude;

        if (!$latSiswa || !$lngSiswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mendeteksi lokasi. Pastikan izin GPS aktif di browser/HP Anda.'
            ], 422);
        }

        // Hitung Jarak dengan rumus Haversine
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($latSiswa - $kegiatan->latitude);
        $dLng = deg2rad($lngSiswa - $kegiatan->longitude);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($kegiatan->latitude)) * cos(deg2rad($latSiswa)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $jarak = $earthRadius * $c;

        // Cek apakah siswa berada di luar radius (default radius di database)
        if ($jarak > $kegiatan->radius) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda berada di luar area kegiatan. Jarak Anda: ' . round($jarak) . ' meter.'
            ], 403);
        }
    }
    // ----------------------------------------

    $userId = Auth::id() ?? 1;

    $sudahAbsen = Absensi::where('kegiatan_id', $kegiatan->id)
                         ->where('user_id', $userId)
                         ->exists();

    if ($sudahAbsen) {
        return response()->json([
            'status'  => 'warning',
            'message' => 'Anda sudah melakukan absensi untuk: ' . $kegiatan->nama_kegiatan
        ], 200);
    }

    // Simpan Absensi Beserta Lokasi
    Absensi::create([
        'kegiatan_id' => $kegiatan->id,
        'user_id'     => $userId,
        'waktu_hadir' => now(),
        'latitude'    => $request->latitude, // Simpan koordinat siswa
        'longitude'   => $request->longitude,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Berhasil absen! Lokasi Anda terverifikasi di area kegiatan.'
    ], 200);
}

    public function apiTotalHadir(Request $request)
    {
        // Mengambil semua ID yang ada di halaman untuk dihitung sekaligus
        $ids = $request->ids; 
        
        $counts = Absensi::select('kegiatan_id', \DB::raw('count(*) as total'))
                    ->whereIn('kegiatan_id', $ids)
                    ->groupBy('kegiatan_id')
                    ->get()
                    ->pluck('total', 'kegiatan_id');

        return response()->json($counts);
    }

    // public function getTotalHadir($id)
    // {
    //     $total = Absensi::where('kegiatan_id', $id)->count();

    //     return response()->json([
    //         'total' => $total
    //     ]);
    // }

    // public function getTotalHadir($id)
    // {
    //     // Ambil data absensi terbaru beserta user-nya
    //     $absensi = \App\Models\Absensi::with('user')
    //                 ->where('kegiatan_id', $id)
    //                 ->latest()
    //                 ->get();

    //     return response()->json([
    //         'total' => $absensi->count(),
    //         'names' => $absensi->map(function($item) {
    //             return [
    //                 'name' => $item->user->name, // Mengambil nama dari relasi user
    //                 'waktu' => \Carbon\Carbon::parse($item->waktu_hadir)->format('H:i:s') // Format jam:menit:detik
    //             ];
    //         })
    //     ]);
    // }

    public function getTotalHadir($id)
    {
        // Ambil data absensi terbaru beserta user-nya
        $absensi = \App\Models\Absensi::with('user')
                    ->where('kegiatan_id', $id)
                    ->latest()
                    ->get();

        return response()->json([
            'total' => $absensi->count(),
            'data'  => $absensi->map(function($item) {
                return [
                    'name'  => $item->user->name,
                    // Pastikan format waktu sesuai keinginan Bapak
                    'waktu' => \Carbon\Carbon::parse($item->waktu_hadir)->format('H:i:s')
                ];
            })
        ]);
    }

    // 5. Fitur Hapus Kegiatan
    public function destroy($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $user = auth()->user();

        if ($user->jenis_user == 'siswa' || $user->jenis_user == 'guru') {
            return redirect()->route('kegiatan.scan.camera', $kegiatan->kode_unik)
                            ->with('info', 'Anda diarahkan otomatis ke halaman scan absensi.');
        }

        // Hapus juga data absensi yang terkait dengan kegiatan ini
        Absensi::where('kegiatan_id', $kegiatan->id)->delete();
        
        // Hapus kegiatannya
        $kegiatan->delete();

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan beserta data absensinya berhasil dihapus permanen.');
    }
}