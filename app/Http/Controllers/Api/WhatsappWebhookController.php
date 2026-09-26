<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Jobs\SendWhatsappJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache; // PENTING: Tambahkan ini untuk fitur Cache
use Illuminate\Support\Facades\Storage; // Untuk save PDF

// Library PDF
use Barryvdh\DomPDF\Facade\Pdf;

// Library Google Sheets & Drive
use Revolution\Google\Sheets\Facades\Sheets;
use Google\Service\Drive;
use Google\Service\Drive\Permission;

class WhatsappWebhookController extends Controller
// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Validasi input dasar
//         $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//         $messageRaw = trim($request->input('message'));
//         $sessionId = $request->input('session_id');

//         if (!$senderNumber || !$messageRaw) {
//             return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//         }

//         // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//         $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//         // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//         // Sesuaikan logika ini dengan format penyimpanan di DB Anda
//         $phoneLocal = $phone;
//         if (substr($phone, 0, 2) == '62') {
//             $phoneLocal = '0' . substr($phone, 2);
//         }

//         // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//         $student = Student::where('phone', $phoneLocal)
//                     ->orWhere('parent_phone', $phoneLocal)
//                     ->orWhere('parent_phone_2', $phoneLocal)
//                     ->first();

//         if (!$student) {
//             // Log nomor tak dikenal untuk analisis
//             Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//             return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//         }

//         // --- LOGIKA CHATBOT ---
//         $message = strtoupper($messageRaw);

//         try {
//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }

//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }

//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan "Ketik MENU untuk bantuan"
//                 // $this->sendText($phone, "Maaf perintah tidak dikenali. Ketik *MENU* untuk melihat pilihan.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Exception $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json(['status' => 'error'], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         Http::post($this->nodeServerUrl, $payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         // $absen = Attendance::where('student_id', $student->id)->whereDate('created_at', now())->first();
//         // $status = $absen ? $absen->status : 'Belum Absen';

//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         Http::post($this->nodeServerUrl, [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }

//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }

//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());

//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             $response = Http::post($this->nodeServerUrl, $payload);
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             if (Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             Cache::put($cacheKey, true, 5);


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }

//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }

//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());

//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => "Halo, Bapak/Ibu dari *{$student->name}*.\nSilakan pilih menu informasi di bawah ini:",
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);

//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             // FIX: Menggunakan Fully Qualified Class Name (\Illuminate\...)
//             // untuk mencegah error "Class Cache not found" jika namespace berbeda
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }

//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }

//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());

//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         // CATATAN PENTING:
//         // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
//         // Menu hanya muncul di Aplikasi HP (Android/iOS).
//         // Oleh karena itu, kita tambahkan teks instruksi di 'message' sebagai fallback.

//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan pilih menu informasi dengan menekan tombol di bawah, " .
//                      "atau ketik perintah berikut jika tombol tidak muncul:\n\n" .
//                      "👉 Ketik *CEK_ABSENSI_HARI_INI*\n" .
//                      "👉 Ketik *CEK_JADWAL*\n" .
//                      "👉 Ketik *CEK_SPP*";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "Buka Menu Pilihan",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);

//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             // FIX: Menggunakan Fully Qualified Class Name (\Illuminate\...)
//             // untuk mencegah error "Class Cache not found" jika namespace berbeda
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Trigger Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }

//             // B. Cek Absensi (Triggered by List Button ID)
//             elseif ($message === 'CEK_ABSENSI_HARI_INI') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }

//             // C. Cek Jadwal (Triggered by List Button ID)
//             elseif ($message === 'CEK_JADWAL') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }

//             // D. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 // $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());

//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         // CATATAN PENTING:
//         // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
//         // Menu hanya muncul di Aplikasi HP (Android/iOS).
//         // Oleh karena itu, kita tambahkan teks instruksi di 'message' sebagai fallback.

//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan klik tombol *MENU PILIHAN* di bawah. \n\n" .
//                      "⚠️ _Jika tombol tidak muncul (WA Web/Laptop), ketik perintah:_ \n" .
//                      "👉 *CEK_ABSENSI_HARI_INI*\n" .
//                      "👉 *CEK_JADWAL*\n" .
//                      "👉 *CEK_SPP*";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Siswa',
//                     'rows' => [
//                         [
//                             'title' => 'Cek Absensi Hari Ini',
//                             'rowId' => 'CEK_ABSENSI_HARI_INI', // ID ini akan dikirim balik saat diklik
//                             'description' => 'Status kehadiran siswa hari ini'
//                         ],
//                         [
//                             'title' => 'Jadwal Pelajaran',
//                             'rowId' => 'CEK_JADWAL',
//                             'description' => 'Mata pelajaran hari ini'
//                         ]
//                     ]
//                 ],
//                 [
//                     'title' => 'Administrasi',
//                     'rows' => [
//                          [
//                             'title' => 'Info SPP',
//                             'rowId' => 'CEK_SPP',
//                             'description' => 'Cek tagihan SPP bulan berjalan'
//                         ]
//                     ]
//                 ]
//             ]
//         ];

//         // Kirim ke Node.js
//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         // Logika query absensi di sini (contoh dummy)
//         $status = "HADIR (07:05 WIB)"; // Dummy data

//         $message = "Laporan Absensi Hari Ini:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Tanggal: " . date('d-m-Y') . "\n" .
//                    "Status: *{$status}*";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);

//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port dengan index.js)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         // Bungkus SEMUA logika dalam try-catch agar error terlihat jelas
//         try {
//             // Validasi input dasar
//             $senderNumber = $request->input('from'); // Format: 62812xxx@s.whatsapp.net
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             // Kita buat unique key dari: Session ID + Nomor Pengirim + Hash Isi Pesan
//             // Jika request yang sama persis masuk dalam 5 detik, kita abaikan.
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             // FIX: Menggunakan Fully Qualified Class Name (\Illuminate\...)
//             // untuk mencegah error "Class Cache not found" jika namespace berbeda
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 // Log untuk debug bahwa duplikat dicegah
//                 Log::info("WA Ignored: Duplicate message detected from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }

//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);


//             // 1. Bersihkan nomor (ambil angkanya saja: 62812345678)
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);

//             // Normalisasi untuk pencarian database (misal DB simpan 0812.., ubah 62 jadi 0)
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa (Cek nomor siswa ATAU nomor orang tua)
//             // Pastikan model Student ada. Jika error di sini, cek database Anda.
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 // Log nomor tak dikenal untuk analisis
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar di sistem.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
//             // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }
//             // C. Cek Jadwal (Via Tombol atau Angka '2')
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
//             // D. Cek SPP (Via Tombol atau Angka '3')
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phone, $student, $sessionId);
//             }
//             // E. Rekap Mingguan (Angka 4)
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//             }
//             // F. Rekap Bulanan (Angka 5)
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//             }
//             // G. Rekap Semester (Angka 6)
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//             }

//             // H. Fallback jika perintah tidak dikenali
//             else {
//                 // Opsional: Kirim pesan balasan default jika user mengetik sembarangan
//                 $this->sendText($phone, "Maaf, perintah tidak dikenali. Ketik *MENU* untuk melihat pilihan.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             // Tangkap Error System (Database, Model not found, dll)
//             Log::error("WA Error: " . $e->getMessage());

//             // Kembalikan pesan error asli untuk debugging di Postman
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'line' => $e->getLine(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     // {
//     //     try {
//     //         // Validasi input
//     //         $senderNumber = $request->input('from');
//     //         $messageRaw = trim($request->input('message'));
//     //         $sessionId = $request->input('session_id');

//     //         if (!$senderNumber || !$messageRaw) {
//     //             return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//     //         }

//     //         // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//     //         $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//     //         if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//     //             Log::info("WA Ignored: Duplicate message from $senderNumber");
//     //             return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//     //         }
//     //         // Kunci pesan ini selama 5 detik
//     //         \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

//     //         // 1. Bersihkan nomor
//     //         $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
//     //         $phoneLocal = $phone;
//     //         if (substr($phone, 0, 2) == '62') {
//     //             $phoneLocal = '0' . substr($phone, 2);
//     //         }

//     //         // 2. Cari Siswa
//     //         $student = Student::where('phone', $phoneLocal)
//     //                     ->orWhere('parent_phone', $phoneLocal)
//     //                     ->orWhere('parent_phone_2', $phoneLocal)
//     //                     ->first();

//     //         if (!$student) {
//     //             Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//     //             return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//     //         }

//     //         // --- LOGIKA CHATBOT ---
//     //         $message = strtoupper($messageRaw);

//     //         // A. Menu Utama
//     //         if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//     //             $this->sendMenu($phone, $student, $sessionId);
//     //         }
//     //         // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//     //         elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//     //             $this->cekAbsensiHarian($phone, $student, $sessionId);
//     //         }
//     //         // C. Cek Jadwal (Via Tombol atau Angka '2')
//     //         elseif ($message === 'CEK_JADWAL' || $message === '2') {
//     //             $this->cekJadwal($phone, $student, $sessionId);
//     //         }
//     //         // D. Cek SPP (Via Tombol atau Angka '3')
//     //         elseif ($message === 'CEK_SPP' || $message === '3') {
//     //             $this->cekSPP($phone, $student, $sessionId);
//     //         }
//     //         // E. Rekap Mingguan (Angka 4)
//     //         elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//     //         }
//     //         // F. Rekap Bulanan (Angka 5)
//     //         elseif ($message === 'REKAP_BULAN' || $message === '5') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//     //         }
//     //         // G. Rekap Semester (Angka 6)
//     //         elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//     //             $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//     //         }

//     //         return response()->json(['status' => 'processed']);

//     //     } catch (\Throwable $e) {
//     //         Log::error("WA Error: " . $e->getMessage());
//     //         return response()->json([
//     //             'status' => 'error',
//     //             'message' => $e->getMessage(),
//     //             'file' => basename($e->getFile())
//     //         ], 500);
//     //     }
//     // }

//     /**
//      * Mengirim Menu Pilihan (List Message)
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     // {
//     //     // CATATAN PENTING:
//     //     // List Message (Menu Tombol) TIDAK MUNCUL di WhatsApp Web/Desktop.
//     //     // Menu hanya muncul di Aplikasi HP (Android/iOS).
//     //     // Oleh karena itu, kita tambahkan teks instruksi angka di 'message' sebagai fallback.

//     //     $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//     //                  "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//     //                  "1️⃣ Ketik *1* : Laporan Harian Siswa\n" .
//     //                  "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//     //                  "3️⃣ Ketik *3* : Info Tagihan SPP\n\n" .
//     //                  "Atau tekan tombol di bawah jika menggunakan HP:";

//     //     $payload = [
//     //         'session_id' => $sessionId,
//     //         'number' => $phone,
//     //         'type' => 'list', // Tipe List agar index.js memprosesnya sebagai Menu
//     //         'message' => $pesanTeks,
//     //         'title' => "Sistem Informasi Sekolah",
//     //         'footer' => "SMK GATECH - Bot Otomatis",
//     //         'buttonText' => "MENU PILIHAN",
//     //         'sections' => [
//     //             [
//     //                 'title' => 'Informasi Siswa',
//     //                 'rows' => [
//     //                     [
//     //                         'title' => 'Laporan Harian Siswa',
//     //                         'rowId' => 'LAPORAN_HARIAN', // ID ini akan dikirim balik saat diklik
//     //                         'description' => 'Balas 1'
//     //                     ],
//     //                     [
//     //                         'title' => 'Jadwal Pelajaran',
//     //                         'rowId' => 'CEK_JADWAL',
//     //                         'description' => 'Balas 2'
//     //                     ]
//     //                 ]
//     //             ],
//     //             [
//     //                 'title' => 'Administrasi',
//     //                 'rows' => [
//     //                      [
//     //                         'title' => 'Info SPP',
//     //                         'rowId' => 'CEK_SPP',
//     //                         'description' => 'Balas 3'
//     //                     ]
//     //                 ]
//     //             ]
//     //         ]
//     //     ];

//     //     // Kirim ke Node.js
//     //     $this->postToNode($payload);
//     // }

//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//                      "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
//                      "----------------------------------\n" .
//                      "📊 *MENU REKAPITULASI:*\n" .
//                      "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
//                      "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
//                      "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
//                      "Atau tekan tombol di bawah jika menggunakan HP:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list',
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
//                         ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
//                     ]
//                 ],
//                 [
//                     'title' => 'Laporan Rekapitulasi',
//                     'rows' => [
//                         ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
//                         ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
//                         ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * Contoh Logika Cek Absensi
//      */
//     private function cekAbsensiGerbang($phone, $student, $sessionId)
//     // {
//     //     $today = Carbon::today();

//     //     // Cari data absensi siswa ini untuk tanggal hari ini
//     //     // Asumsi struktur tabel: student_id, status (hadir/sakit/izin), created_at
//     //     $attendance = DailyAttendance::where('student_id', $student->id)
//     //                                  ->whereDate('created_at', $today)
//     //                                  ->first();

//     //     if ($attendance) {
//     //         $jamMasuk = Carbon::parse($attendance->arrival_time)->format('H:i');
//     //         $statusRaw = strtoupper($attendance->status); // HADIR, TELAT, DLL
//     //         $statusDisplay = "{$statusRaw} (Pukul {$jamMasuk})";
//     //     } else {
//     //         $statusDisplay = "BELUM ABSEN / ALPA";
//     //     }

//     //     $message = "Laporan Absensi Gerbang Hari Ini:\n\n" .
//     //                "Nama: *{$student->name}*\n" .
//     //                "Tanggal: " . $today->format('d-m-Y') . "\n" .
//     //                "Status: *{$statusDisplay}*";

//     //     $this->sendText($phone, $message, $sessionId);

//     // }

//     // {
//     //     $today = Carbon::today();

//     //     // --- 1. KEHADIRAN GERBANG ---
//     //     $gate = DailyAttendance::where('student_id', $student->id)
//     //                             ->whereDate('created_at', $today)
//     //                             ->first();

//     //     if ($gate) {
//     //         // Gunakan arrival_time jika ada, fallback ke created_at
//     //         $waktu = $gate->arrival_time ?? $gate->created_at;
//     //         $jamMasuk = Carbon::parse($waktu)->format('H:i');
//     //         $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
//     //     } else {
//     //         $statusGerbang = "BELUM ABSEN / ALPA";
//     //     }

//     //     // --- 2. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//     //     // Asumsi: Anda memiliki model 'SubjectAttendance' untuk absensi per mapel
//     //     // Pastikan model SubjectAttendance di-import atau gunakan path lengkap
//     //     // Jika tabel belum ada, bagian ini akan return list kosong atau error (bisa dikomentari jika belum siap)

//     //     $listPembelajaran = "";

//     //     try {
//     //         // Mencari data absensi mapel siswa hari ini
//     //         // Relasi 'subject' diasumsikan ada di model SubjectAttendance
//     //         $lessons = \App\Models\Attendance::with('subject')
//     //                                 ->where('student_id', $student->id)
//     //                                 ->whereDate('created_at', $today)
//     //                                 ->orderBy('created_at', 'asc')
//     //                                 ->get();

//     //         if ($lessons->count() > 0) {
//     //             foreach ($lessons as $lesson) {
//     //                 // Ambil nama mapel. Jika relasi null, cek kolom subject_name (tergantung DB Anda)
//     //                 $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//     //                 $statusLesson = strtoupper($lesson->status); // HADIR, IZIN, SAKIT
//     //                 $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');

//     //                 $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//     //             }
//     //         } else {
//     //             $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//     //         }
//     //     } catch (\Throwable $e) {
//     //         // Fallback jika tabel/model SubjectAttendance belum ada agar bot tidak crash
//     //         $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//     //         Log::error("SubjectAttendance Error: " . $e->getMessage());
//     //     }

//     //     // --- SUSUN PESAN FINAL ---
//     //     $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//     //                "Nama: *{$student->name}*\n" .
//     //                "Tanggal: " . $today->format('d-m-Y') . "\n\n" .
//     //                "🏫 *ABSENSI GERBANG*\n" .
//     //                "Status: *{$statusGerbang}*\n\n" .
//     //                "📚 *ABSENSI PEMBELAJARAN*\n" .
//     //                $listPembelajaran;

//     //     $this->sendText($phone, $message, $sessionId);
//     // }

//     {
//         $today = Carbon::today();
//         $hariIndo = [
//             'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
//             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
//         ];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // --- 1. KEHADIRAN GERBANG ---
//         $gate = DailyAttendance::where('student_id', $student->id)
//                                 ->whereDate('created_at', $today)
//                                 ->first();

//         if ($gate) {
//             $waktuDatang = $gate->arrival_time ?? $gate->created_at;
//             $waktuPulang = $gate->departure_time;
//             if($waktuPulang !== null){
//                 $jamPulang = Carbon::parse($waktuPulang)->format('H:i');
//             }else{
//                 $jamPulang = 'Belum Pulang';
//             }
//             $jamMasuk = Carbon::parse($waktuDatang)->format('H:i');

//             $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk}) - (Pulang: {$jamPulang})";
//         } else {
//             $statusGerbang = "BELUM ABSEN / ALPA";
//         }

//         // --- 2. JADWAL PELAJARAN (Hari Ini) ---
//         $listJadwal = "";
//         try {
//             // Asumsi: Model Schedule memiliki relasi 'subject' dan 'teacher'
//             // Sesuaikan namespace model dan nama relasi dengan database Anda
//             $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
//                                     ->where('classroom_id', $student->classroom_id)
//                                     ->where('day', $namaHari)
//                                     ->orderBy('start_time', 'asc')
//                                     ->get();

//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $mapel = $sched->subject->name ?? 'Mapel';
//                     $guru = $sched->teacher->name ?? '-'; // Menambahkan Nama Guru
//                     $jamMulai = substr($sched->start_time, 0, 5);
//                     $jamSelesai = substr($sched->end_time, 0, 5);

//                     // Format: Mapel (07:00-08:00) - Guru
//                     $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
//                 }
//             } else {
//                 // Jika tidak ada data di DB, tampilkan pesan default
//                 $listJadwal = "_Tidak ada jadwal pelajaran di database_";
//             }
//         } catch (\Throwable $e) {
//             // Fallback jika tabel/model Schedule belum ada atau relasi guru tidak ada
//             $listJadwal = "_Data jadwal belum tersedia / Error: " . $e->getMessage() . "_";
//         }

//         // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//         $listPembelajaran = "";
//         try {
//             $lessons = \App\Models\Attendance::with('subject')
//                                     ->where('student_id', $student->id)
//                                     ->whereDate('created_at', $today)
//                                     ->orderBy('created_at', 'asc')
//                                     ->get();

//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $lesson) {
//                     $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//                     $statusLesson = strtoupper($lesson->status); // HADIR, IZIN, SAKIT
//                     $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');

//                     $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//                 }
//             } else {
//                 $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//             }
//         } catch (\Throwable $e) {
//             $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//         }

//         // --- SUSUN PESAN FINAL ---
//         $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
//                    "🏫 *KEHADIRAN GERBANG*\n" .
//                    "Status: *{$statusGerbang}*\n\n" .
//                    "📅 *JADWAL HARI INI*\n" .
//                    $listJadwal . "\n" .
//                    "📚 *KEHADIRAN PEMBELAJARAN*\n" .
//                    $listPembelajaran;

//         $this->sendText($phone, $message, $sessionId);
//     }


//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         $endDate = Carbon::now();
//         $label = "";

//         // Tentukan Rentang Waktu
//         if ($period == 'weekly') {
//             $startDate = Carbon::now()->subDays(7);
//             $label = "MINGGU INI (7 Hari Terakhir)";
//         } elseif ($period == 'monthly') {
//             $startDate = Carbon::now()->startOfMonth();
//             $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
//         } elseif ($period == 'semester') {
//             // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
//             $month = Carbon::now()->month;
//             if ($month >= 7) {
//                 $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
//                 $label = "SEMESTER GANJIL";
//             } else {
//                 $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
//                 $label = "SEMESTER GENAP";
//             }
//         }

//         // 1. REKAP GERBANG (Hitung jumlah Status)
//         // Output: [HADIR => 5, SAKIT => 1, ...]
//         $rekapGerbang = DailyAttendance::where('student_id', $student->id)
//                         ->whereBetween('created_at', [$startDate, $endDate])
//                         ->selectRaw('status, count(*) as total')
//                         ->groupBy('status')
//                         ->pluck('total', 'status')
//                         ->toArray();

//         // Format Text Gerbang
//         $txtGerbang = "";
//         $totalKehadiran = 0;
//         if (empty($rekapGerbang)) {
//             $txtGerbang = "_Belum ada data absensi_";
//         } else {
//             foreach ($rekapGerbang as $status => $total) {
//                 $statusUpper = strtoupper($status);
//                 $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
//                 $totalKehadiran += $total;
//             }
//             $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
//         }

//         // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
//         try {
//             $totalMapel = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->where('status', 'HADIR') // Asumsi hanya menghitung kehadiran
//                             ->count();

//             $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
//         } catch (\Throwable $e) {
//             $txtMapel = "-";
//         }

//         // SUSUN PESAN
//         $message = "📈 *LAPORAN REKAPITULASI*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Periode: {$label}\n\n" .
//                    "🏫 *ABSENSI GERBANG*\n" .
//                    $txtGerbang . "\n\n" .
//                    "📚 *ABSENSI PEMBELAJARAN*\n" .
//                    $txtMapel . "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }


//     /**
//      * Contoh Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }



//     /**
//      * Contoh Logika Cek SPP (Baru Ditambahkan)
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         // Logika query SPP di sini (contoh dummy)
//         $message = "Info Keuangan Siswa:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Bulan: " . date('F Y') . "\n" .
//                    "Status SPP: *LUNAS*\n" .
//                    "Tunggakan Lain: Rp 0,-";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper untuk kirim pesan teks biasa
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request ke Node.js
//      */
//     private function postToNode($payload)
//     {
//         try {
//             // Tambahkan timeout agar controller tidak hang jika node.js lambat
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);

//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             // Kita throw lagi agar tertangkap di handle() dan muncul di Postman
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         try {
//             // Validasi input
//             $senderNumber = $request->input('from');
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 Log::info("WA Ignored: Duplicate message from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }
//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

//             // 1. Bersihkan nomor
//             $phone = str_replace('@s.whatsapp.net', '', $senderNumber);
//             $phoneLocal = $phone;
//             if (substr($phone, 0, 2) == '62') {
//                 $phoneLocal = '0' . substr($phone, 2);
//             }

//             // 2. Cari Siswa
//             $student = Student::where('phone', $phoneLocal)
//                         // ->orWhere('parent_phone', $phoneLocal)
//                         // ->orWhere('parent_phone_2', $phoneLocal)
//                         ->first();

//             if (!$student) {
//                 Log::info("WA Ignored: Nomor $phone tidak terdaftar.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phone, $student, $sessionId);
//             }
//             // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phone, $student, $sessionId);
//             }
//             // C. Cek Jadwal (Via Tombol atau Angka '2')
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phone, $student, $sessionId);
//             }
//             // D. Cek SPP (Via Tombol atau Angka '3')
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phone, $student, $sessionId);
//             }
//             // E. Rekap Mingguan (Angka 4)
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'weekly');
//             }
//             // F. Rekap Bulanan (Angka 5)
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'monthly');
//             }
//             // G. Rekap Semester (Angka 6)
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phone, $student, $sessionId, 'semester');
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//                      "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
//                      "----------------------------------\n" .
//                      "📊 *MENU REKAPITULASI:*\n" .
//                      "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
//                      "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
//                      "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
//                      "Atau tekan tombol di bawah jika menggunakan HP:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list',
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
//                         ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
//                     ]
//                 ],
//                 [
//                     'title' => 'Laporan Rekapitulasi',
//                     'rows' => [
//                         ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
//                         ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
//                         ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * Cek Absensi Lengkap (Gerbang, Jadwal & Pembelajaran) - Harian
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         $today = Carbon::today();
//         $hariIndo = [
//             'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
//             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
//         ];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // --- 1. KEHADIRAN GERBANG ---
//         $gate = DailyAttendance::where('student_id', $student->id)
//                                 ->whereDate('created_at', $today)
//                                 ->first();

//         if ($gate) {
//             $waktu = $gate->arrival_time ?? $gate->created_at;
//             $jamMasuk = Carbon::parse($waktu)->format('H:i');
//             $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
//         } else {
//             $statusGerbang = "BELUM ABSEN / ALPA";
//         }

//         // --- 2. JADWAL PELAJARAN (Hari Ini) ---
//         $listJadwal = "";
//         try {
//             $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
//                                     ->where('classroom_id', $student->classroom_id)
//                                     ->where('day', $namaHari)
//                                     ->orderBy('start_time', 'asc')
//                                     ->get();

//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $mapel = $sched->subject->name ?? 'Mapel';
//                     $guru = $sched->teacher->name ?? '-';
//                     $jamMulai = substr($sched->start_time, 0, 5);
//                     $jamSelesai = substr($sched->end_time, 0, 5);
//                     $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
//                 }
//             } else {
//                 $listJadwal = "_Tidak ada jadwal pelajaran_";
//             }
//         } catch (\Throwable $e) {
//             $listJadwal = "_Data jadwal belum tersedia_";
//         }

//         // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//         $listPembelajaran = "";
//         try {
//             $lessons = \App\Models\Attendance::with('subject')
//                                     ->where('student_id', $student->id)
//                                     ->whereDate('created_at', $today)
//                                     ->orderBy('created_at', 'asc')
//                                     ->get();

//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $lesson) {
//                     $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//                     $statusLesson = strtoupper($lesson->status);
//                     $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
//                     $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//                 }
//             } else {
//                 $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//             }
//         } catch (\Throwable $e) {
//             $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//         }

//         // --- SUSUN PESAN FINAL ---
//         $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
//                    "🏫 *KEHADIRAN GERBANG*\n" .
//                    "Status: *{$statusGerbang}*\n\n" .
//                    "📅 *JADWAL HARI INI*\n" .
//                    $listJadwal . "\n" .
//                    "📚 *KEHADIRAN PEMBELAJARAN*\n" .
//                    $listPembelajaran. "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Hitung Rekapitulasi Absensi (Mingguan/Bulanan/Semester)
//      */
//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         $endDate = Carbon::now();
//         $label = "";

//         // Tentukan Rentang Waktu
//         if ($period == 'weekly') {
//             $startDate = Carbon::now()->subDays(7);
//             $label = "MINGGU INI (7 Hari Terakhir)";
//         } elseif ($period == 'monthly') {
//             $startDate = Carbon::now()->startOfMonth();
//             $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
//         } elseif ($period == 'semester') {
//             // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
//             $month = Carbon::now()->month;
//             if ($month >= 7) {
//                 $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
//                 $label = "SEMESTER GANJIL";
//             } else {
//                 $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
//                 $label = "SEMESTER GENAP";
//             }
//         }

//         // 1. REKAP GERBANG (Hitung jumlah Status)
//         // Output: [HADIR => 5, SAKIT => 1, ...]
//         $rekapGerbang = DailyAttendance::where('student_id', $student->id)
//                         ->whereBetween('created_at', [$startDate, $endDate])
//                         ->selectRaw('status, count(*) as total')
//                         ->groupBy('status')
//                         ->pluck('total', 'status')
//                         ->toArray();

//         // Format Text Gerbang
//         $txtGerbang = "";
//         $totalKehadiran = 0;
//         if (empty($rekapGerbang)) {
//             $txtGerbang = "_Belum ada data absensi_";
//         } else {
//             foreach ($rekapGerbang as $status => $total) {
//                 $statusUpper = strtoupper($status);
//                 $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
//                 $totalKehadiran += $total;
//             }
//             $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
//         }

//         // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
//         try {
//             $totalMapel = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->where('status', 'hadir') // Asumsi hanya menghitung kehadiran
//                             ->count();

//             $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
//         } catch (\Throwable $e) {
//             $txtMapel = "-";
//         }

//         // SUSUN PESAN
//         $message = "📈 *LAPORAN REKAPITULASI*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Periode: {$label}\n\n" .
//                    "🏫 *ABSENSI GERBANG*\n" .
//                    $txtGerbang . "\n\n" .
//                    "📚 *ABSENSI PEMBELAJARAN*\n" .
//                    $txtMapel . "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Logika Cek SPP
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         $message = "Info Keuangan Siswa:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Bulan: " . date('F Y') . "\n" .
//                    "Status SPP: *LUNAS*\n" .
//                    "Tunggakan Lain: Rp 0,-";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper kirim pesan teks
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request
//      */
//     private function postToNode($payload)
//     {
//         try {
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         try {
//             // Validasi input
//             $senderNumber = $request->input('from');
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // --- 0. MENCEGAH DOUBLE REPLY (DEBOUNCE) ---
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);

//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 Log::info("WA Ignored: Duplicate message from $senderNumber");
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }
//             // Kunci pesan ini selama 5 detik
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

//             // 1. Normalisasi Nomor Telepon (Buat 2 Versi: 08xx dan 628xx)
//             // Bersihkan suffix @s.whatsapp.net
//             $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber); // Contoh: 62812345678

//             $phone62 = $phonePure; // Versi 62 (default dari WA)
//             $phone0 = $phonePure;  // Versi 0 (format lokal)

//             // Jika diawali 62, buat versi 0-nya
//             if (substr($phonePure, 0, 2) == '62') {
//                 $phone0 = '0' . substr($phonePure, 2);
//             }
//             // Jika diawali 0, buat versi 62-nya (jaga-jaga input aneh)
//             elseif (substr($phonePure, 0, 1) == '0') {
//                 $phone62 = '62' . substr($phonePure, 1);
//             }

//             // 2. Cari Siswa (Cari KEDUA format di SEMUA kolom telepon)
//             // Ini memastikan nomor ketemu tidak peduli admin simpan pakai 08 atau 62
//             $student = Student::where(function($query) use ($phone0, $phone62) {
//                             $query->where('phone', $phone0)
//                                   ->orWhere('phone', $phone62);
//                         })
//                         // ->orWhere(function($query) use ($phone0, $phone62) {
//                         //     $query->where('parent_phone', $phone0)
//                         //           ->orWhere('parent_phone', $phone62);
//                         // })
//                         // ->orWhere(function($query) use ($phone0, $phone62) {
//                         //     $query->where('parent_phone_2', $phone0)
//                         //           ->orWhere('parent_phone_2', $phone62);
//                         // })
//                         ->first();

//             if (!$student) {

//                 // --- FIX: Tetap balas pesan meskipun nomor tidak dikenal ---
//                 // Ini penting agar Anda tahu botnya HIDUP, hanya datanya yang tidak ketemu.
//                 $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar di sistem sekolah. Silakan hubungi Tata Usaha untuk update data.", $sessionId);
//                 // Log detail agar Anda tahu nomor mana yang gagal dikenali
//                 Log::info("WA Ignored: Nomor $phonePure (Cek: $phone0 / $phone62) tidak ditemukan di database.");
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phonePure, $student, $sessionId);
//             }
//             // B. Cek Absensi Hari Ini (Via Tombol atau Angka '1')
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phonePure, $student, $sessionId);
//             }
//             // C. Cek Jadwal (Via Tombol atau Angka '2')
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phonePure, $student, $sessionId);
//             }
//             // D. Cek SPP (Via Tombol atau Angka '3')
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phonePure, $student, $sessionId);
//             }
//             // E. Rekap Mingguan (Angka 4)
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
//             }
//             // F. Rekap Bulanan (Angka 5)
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
//             }
//             // G. Rekap Semester (Angka 6)
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
//             }
//             else {
//                 // Opsional: Kirim pesan balasan default
//                 $this->sendText($phonePure, "Maaf, perintah tidak dikenali. Ketik MENU.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//                 'file' => basename($e->getFile())
//             ], 500);
//         }
//     }

//     /**
//      * Mengirim Menu Pilihan
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu yang diinginkan:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal Pelajaran\n" .
//                      "3️⃣ Ketik *3* : Info Tagihan SPP\n" .
//                      "----------------------------------\n" .
//                      "📊 *MENU REKAPITULASI:*\n" .
//                      "4️⃣ Ketik *4* : Rekap Absensi Minggu Ini\n" .
//                      "5️⃣ Ketik *5* : Rekap Absensi Bulan Ini\n" .
//                      "6️⃣ Ketik *6* : Rekap Absensi Semester Ini\n\n" .
//                      "Atau tekan tombol di bawah jika menggunakan HP:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list',
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan - Bot Otomatis",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => 'CEK_ABSENSI_HARI_INI', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => 'CEK_JADWAL', 'description' => 'Balas 2'],
//                         ['title' => 'Info SPP', 'rowId' => 'CEK_SPP', 'description' => 'Balas 3']
//                     ]
//                 ],
//                 [
//                     'title' => 'Laporan Rekapitulasi',
//                     'rows' => [
//                         ['title' => 'Rekap Mingguan', 'rowId' => 'REKAP_MINGGU', 'description' => 'Balas 4'],
//                         ['title' => 'Rekap Bulanan', 'rowId' => 'REKAP_BULAN', 'description' => 'Balas 5'],
//                         ['title' => 'Rekap Semester', 'rowId' => 'REKAP_SEMESTER', 'description' => 'Balas 6']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * Cek Absensi Lengkap (Gerbang, Jadwal & Pembelajaran) - Harian
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         $today = Carbon::today();
//         $hariIndo = [
//             'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
//             'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
//         ];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // --- 1. KEHADIRAN GERBANG ---
//         $gate = DailyAttendance::where('student_id', $student->id)
//                                 ->whereDate('created_at', $today)
//                                 ->first();

//         if ($gate) {
//             $waktu = $gate->arrival_time ?? $gate->created_at;
//             $jamMasuk = Carbon::parse($waktu)->format('H:i');
//             $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
//         } else {
//             $statusGerbang = "BELUM ABSEN / ALPA";
//         }

//         // --- 2. JADWAL PELAJARAN (Hari Ini) ---
//         $listJadwal = "";
//         try {
//             $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
//                                     ->where('classroom_id', $student->classroom_id)
//                                     ->where('day', $namaHari)
//                                     ->orderBy('start_time', 'asc')
//                                     ->get();

//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $mapel = $sched->subject->name ?? 'Mapel';
//                     $guru = $sched->teacher->name ?? '-';
//                     $jamMulai = substr($sched->start_time, 0, 5);
//                     $jamSelesai = substr($sched->end_time, 0, 5);
//                     $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
//                 }
//             } else {
//                 $listJadwal = "_Tidak ada jadwal pelajaran_";
//             }
//         } catch (\Throwable $e) {
//             $listJadwal = "_Data jadwal belum tersedia_";
//         }

//         // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
//         $listPembelajaran = "";
//         try {
//             $lessons = \App\Models\Attendance::with('subject')
//                                     ->where('student_id', $student->id)
//                                     ->whereDate('created_at', $today)
//                                     ->orderBy('created_at', 'asc')
//                                     ->get();

//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $lesson) {
//                     $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
//                     $statusLesson = strtoupper($lesson->status);
//                     $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
//                     $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
//                 }
//             } else {
//                 $listPembelajaran = "_Belum ada data pembelajaran masuk_";
//             }
//         } catch (\Throwable $e) {
//             $listPembelajaran = "_Data pembelajaran tidak tersedia_";
//         }

//         // --- SUSUN PESAN FINAL ---
//         $message = "📊 *LAPORAN HARIAN SISWA*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Hari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n" .
//                    "🏫 *KEHADIRAN GERBANG*\n" .
//                    "Status: *{$statusGerbang}*\n\n" .
//                    "📅 *JADWAL HARI INI*\n" .
//                    $listJadwal . "\n" .
//                    "📚 *KEHADIRAN PEMBELAJARAN*\n" .
//                    $listPembelajaran;

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Hitung Rekapitulasi Absensi (Mingguan/Bulanan/Semester)
//      */
//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         $endDate = Carbon::now();
//         $label = "";

//         // Tentukan Rentang Waktu
//         if ($period == 'weekly') {
//             $startDate = Carbon::now()->subDays(7);
//             $label = "MINGGU INI (7 Hari Terakhir)";
//         } elseif ($period == 'monthly') {
//             $startDate = Carbon::now()->startOfMonth();
//             $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
//         } elseif ($period == 'semester') {
//             // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
//             $month = Carbon::now()->month;
//             if ($month >= 7) {
//                 $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
//                 $label = "SEMESTER GANJIL";
//             } else {
//                 $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
//                 $label = "SEMESTER GENAP";
//             }
//         }

//         // 1. REKAP GERBANG (Hitung jumlah Status)
//         // Output: [HADIR => 5, SAKIT => 1, ...]
//         $rekapGerbang = DailyAttendance::where('student_id', $student->id)
//                         ->whereBetween('created_at', [$startDate, $endDate])
//                         ->selectRaw('status, count(*) as total')
//                         ->groupBy('status')
//                         ->pluck('total', 'status')
//                         ->toArray();

//         // Format Text Gerbang
//         $txtGerbang = "";
//         $totalKehadiran = 0;
//         if (empty($rekapGerbang)) {
//             $txtGerbang = "_Belum ada data absensi_";
//         } else {
//             foreach ($rekapGerbang as $status => $total) {
//                 $statusUpper = strtoupper($status);
//                 $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
//                 $totalKehadiran += $total;
//             }
//             $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
//         }

//         // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
//         try {
//             $totalMapel = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->where('status', 'hadir') // Asumsi hanya menghitung kehadiran
//                             ->count();

//             $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
//         } catch (\Throwable $e) {
//             $txtMapel = "-";
//         }

//         // SUSUN PESAN
//         $message = "📈 *LAPORAN REKAPITULASI*\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Periode: {$label}\n\n" .
//                    "🏫 *ABSENSI GERBANG*\n" .
//                    $txtGerbang . "\n\n" .
//                    "📚 *ABSENSI PEMBELAJARAN*\n" .
//                    $txtMapel . "\n\n" .
//                    "_Balas MENU untuk kembali ke menu utama._";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Logika Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $message = "Jadwal Pelajaran Hari Ini:\n\n" .
//                    "1. Matematika\n" .
//                    "2. Bahasa Indonesia\n" .
//                    "3. Pemrograman Web";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Logika Cek SPP
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         $message = "Info Keuangan Siswa:\n\n" .
//                    "Nama: *{$student->name}*\n" .
//                    "Bulan: " . date('F Y') . "\n" .
//                    "Status SPP: *LUNAS*\n" .
//                    "Tunggakan Lain: Rp 0,-";

//         $this->sendText($phone, $message, $sessionId);
//     }

//     /**
//      * Helper kirim pesan teks
//      */
//     private function sendText($phone, $message, $sessionId)
//     {
//         $this->postToNode([
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'text',
//             'message' => $message
//         ]);
//     }

//     /**
//      * Wrapper Http Request
//      */
//     private function postToNode($payload)
//     {
//         try {
//             $response = Http::timeout(5)->post($this->nodeServerUrl, $payload);
//             if ($response->failed()) {
//                 Log::error("Gagal kirim ke Node.js: " . $response->body());
//             }
//         } catch (\Exception $e) {
//             Log::error("Koneksi Node.js Refused: " . $e->getMessage());
//             throw new \Exception("Gagal menghubungi Server WA (Node.js). Pastikan 'node index.js' berjalan.");
//         }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         try {
//             // 1. Validasi Input
//             $senderNumber = $request->input('from');
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // 2. Debounce (Cegah Double Reply)
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

//             // 3. Normalisasi Nomor HP (08xx / 628xx)
//             $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber);
//             $phone0 = (substr($phonePure, 0, 2) == '62') ? '0' . substr($phonePure, 2) : $phonePure;
//             $phone62 = (substr($phonePure, 0, 1) == '0') ? '62' . substr($phonePure, 1) : $phonePure;

//             // 4. Cari Siswa (Cek Siswa atau Orang Tua)
//             $student = Student::where(function($q) use ($phone0, $phone62) {
//                             $q->where('phone', $phone0)->orWhere('phone', $phone62);
//                         // })->orWhere(function($q) use ($phone0, $phone62) {
//                         //     $q->where('parent_phone', $phone0)->orWhere('parent_phone', $phone62);
//                         // })->orWhere(function($q) use ($phone0, $phone62) {
//                         //     $q->where('parent_phone_2', $phone0)->orWhere('parent_phone_2', $phone62);
//                         })->first();

//             // Jika nomor tidak dikenal
//             if (!$student) {
//                 $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar di sistem sekolah.", $sessionId);
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phonePure, $student, $sessionId);
//             }
//             // B. Menu Harian
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phonePure, $student, $sessionId);
//             }
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phonePure, $student, $sessionId);
//             }
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phonePure, $student, $sessionId);
//             }
//             // C. Menu Rekap Text
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
//             }
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
//             }
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
//             }
//             // D. EKSPOR GOOGLE SHEET (Angka 7)
//             elseif ($message === 'EXPORT_SHEET' || $message === '7') {
//                 $this->sendText($phonePure, "⏳ Sedang membuat Google Sheet rekap absensi...\nMohon tunggu sebentar.", $sessionId);
//                 $this->exportRekapSheet($phonePure, $student, $sessionId);
//             }
//             // E. EKSPOR PDF (Angka 8)
//             elseif ($message === 'EXPORT_PDF' || $message === '8') {
//                 $this->sendText($phonePure, "⏳ Sedang membuat PDF rekap absensi...\nMohon tunggu sebentar.", $sessionId);
//                 $this->exportRekapPDF($phonePure, $student, $sessionId);
//             }
//             // Fallback
//             else {
//                  $this->sendText($phonePure, "Halo {$student->name}, ketik *MENU* untuk melihat pilihan.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
//         }
//     }

//     /**
//      * 1. Kirim Menu Pilihan
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal\n" .
//                      "3️⃣ Ketik *3* : Info SPP\n" .
//                      "4️⃣ Ketik *4* : Rekap Mingguan (Text)\n" .
//                      "----------------------------------\n" .
//                      "📂 *DOWNLOAD LAPORAN:*\n" .
//                      "7️⃣ Ketik *7* : Ekspor Google Sheet 📊\n" .
//                      "8️⃣ Ketik *8* : Ekspor PDF 📄\n\n" .
//                      "Atau tekan tombol di bawah:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list',
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => '1', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => '2', 'description' => 'Balas 2']
//                     ]
//                 ],
//                 [
//                     'title' => 'Download Laporan',
//                     'rows' => [
//                         ['title' => 'Download Excel/Sheet', 'rowId' => '7', 'description' => 'Via Google Sheet'],
//                         ['title' => 'Download PDF', 'rowId' => '8', 'description' => 'Via Dokumen WA']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * 2. Cek Absensi Harian (Gerbang + Jadwal + Mapel)
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         $today = Carbon::today();
//         $hariIndo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // A. Gerbang
//         $gate = DailyAttendance::where('student_id', $student->id)->whereDate('created_at', $today)->first();
//         $statusGerbang = $gate ? strtoupper($gate->status) . " (" . Carbon::parse($gate->arrival_time)->format('H:i') . ")" : "BELUM ABSEN / ALPA";

//         // B. Jadwal
//         $listJadwal = "";
//         try {
//             $schedules = Schedule::with(['subject', 'teacher'])
//                         ->where('classroom_id', $student->classroom_id)->where('day', $namaHari)
//                         ->orderBy('start_time', 'asc')->get();
//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $guru = $sched->teacher->name ?? '-';
//                     $listJadwal .= "• {$sched->subject->name} (" . substr($sched->start_time, 0, 5) . "-" . substr($sched->end_time, 0, 5) . ")\n  👨‍🏫 {$guru}\n";
//                 }
//             } else { $listJadwal = "_Tidak ada jadwal_"; }
//         } catch (\Throwable $e) { $listJadwal = "-"; }

//         // C. Mapel
//         $listPembelajaran = "";
//         try {
//             $lessons = Attendance::with('subject')->where('student_id', $student->id)->whereDate('created_at', $today)->get();
//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $l) {
//                     $listPembelajaran .= "• " . ($l->subject->name ?? 'Mapel') . ": *" . strtoupper($l->status) . "*\n";
//                 }
//             } else { $listPembelajaran = "_Belum ada data_"; }
//         } catch (\Throwable $e) { $listPembelajaran = "-"; }

//         $msg = "📊 *LAPORAN HARIAN*\nNama: {$student->name}\nHari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n🏫 *GERBANG*: {$statusGerbang}\n\n📅 *JADWAL*:\n{$listJadwal}\n📚 *PEMBELAJARAN*:\n{$listPembelajaran}";
//         $this->sendText($phone, $msg, $sessionId);
//     }

//     /**
//      * 3. Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $this->cekAbsensiHarian($phone, $student, $sessionId); // Reuse logic
//     }

//     /**
//      * 4. Cek SPP
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         $this->sendText($phone, "Info SPP: LUNAS (Data Dummy)", $sessionId);
//     }

//     /**
//      * 5. Rekap Text (Mingguan/Bulanan)
//      */
//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         if ($period == 'weekly') $startDate->subDays(7);
//         elseif ($period == 'monthly') $startDate->startOfMonth();
//         elseif ($period == 'semester') $startDate->subMonths(6);

//         $rekap = DailyAttendance::where('student_id', $student->id)
//                 ->whereBetween('created_at', [$startDate, Carbon::now()])
//                 ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();

//         $txt = "📈 *REKAP " . strtoupper($period) . "*\n\n";
//         foreach ($rekap as $k => $v) { $txt .= "• " . strtoupper($k) . ": $v\n"; }
//         if (empty($rekap)) $txt .= "_Tidak ada data absensi_";

//         $this->sendText($phone, $txt, $sessionId);
//     }

//     /**
//      * 6. Export Google Sheet
//      */
//     private function exportRekapSheet($phone, $student, $sessionId)
//     {
//         try {
//             $startDate = Carbon::now()->startOfMonth();
//             $attendances = DailyAttendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, Carbon::now()])
//                             ->orderBy('created_at', 'asc')->get();

//             $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Ket']];
//             foreach ($attendances as $att) {
//                 $t = Carbon::parse($att->created_at);
//                 $rows[] = [$t->format('d-m-Y'), $t->translatedFormat('l'), $t->format('H:i'), $att->status, $att->notes];
//             }

//             $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
//             $spreadsheet = Sheets::create($sheetTitle, function ($sheet) use ($rows) { $sheet->append($rows); });

//             // Set Public Permission
//             $client = Sheets::getGoogleService()->getClient();
//             $drive = new \Google\Service\Drive($client);
//             $perm = new \Google\Service\Drive\Permission(['type' => 'anyone', 'role' => 'reader']);
//             $drive->permissions->create($spreadsheet->id, $perm);

//             $this->sendText($phone, "✅ *Sheet Siap!*\nLink: " . $spreadsheet->spreadsheetUrl, $sessionId);

//         } catch (\Throwable $e) {
//             Log::error("Sheet Error: " . $e->getMessage());
//             $this->sendText($phone, "Gagal membuat Sheet. Cek Server Log.", $sessionId);
//         }
//     }

//     /**
//      * 7. Export PDF
//      */
//     private function exportRekapPDF($phone, $student, $sessionId)
//     {
//         try {
//             $startDate = Carbon::now()->startOfMonth();
//             $attendances = DailyAttendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, Carbon::now()])
//                             ->orderBy('created_at', 'asc')->get();

//             // Load View PDF
//             $pdf = Pdf::loadView('pdf.recap', [
//                 'student' => $student,
//                 'attendances' => $attendances,
//                 'period' => $startDate->translatedFormat('F Y')
//             ]);

//             // Save & Get URL
//             $fileName = 'rekap_' . $student->id . '_' . time() . '.pdf';
//             Storage::put('public/pdf/' . $fileName, $pdf->output());
//             $url = asset('storage/pdf/' . $fileName);

//             // Kirim Dokumen
//             $this->postToNode([
//                 'session_id' => $sessionId,
//                 'number' => $phone,
//                 'type' => 'document',
//                 'media_url' => $url,
//                 'message' => "📄 Rekap PDF Siap",
//                 'title' => "Rekap-{$student->name}.pdf"
//             ]);

//         } catch (\Throwable $e) {
//             Log::error("PDF Error: " . $e->getMessage());
//             $this->sendText($phone, "Gagal membuat PDF. Cek Log.", $sessionId);
//         }
//     }

//     // --- Helpers ---
//     private function sendText($phone, $msg, $sessionId) {
//         $this->postToNode(['session_id' => $sessionId, 'number' => $phone, 'type' => 'text', 'message' => $msg]);
//     }

//     private function postToNode($payload) {
//         try { Http::timeout(10)->post($this->nodeServerUrl, $payload); }
//         catch (\Exception $e) { Log::error("NodeJS Error: " . $e->getMessage()); }
//     }
// }

// {
//     // URL Node.js Server (Sesuaikan port)
//     protected $nodeServerUrl = 'http://localhost:3000/send-message';

//     public function handle(Request $request)
//     {
//         try {
//             // 1. Validasi Input
//             $senderNumber = $request->input('from');
//             $messageRaw = trim($request->input('message'));
//             $sessionId = $request->input('session_id');

//             if (!$senderNumber || !$messageRaw) {
//                 return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
//             }

//             // 2. Debounce (Cegah Double Reply)
//             $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);
//             if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
//                 return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
//             }
//             \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

//             // 3. Normalisasi Nomor HP (08xx / 628xx)
//             $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber);
//             $phone0 = (substr($phonePure, 0, 2) == '62') ? '0' . substr($phonePure, 2) : $phonePure;
//             $phone62 = (substr($phonePure, 0, 1) == '0') ? '62' . substr($phonePure, 1) : $phonePure;

//             // 4. Cari Siswa (Cek Siswa atau Orang Tua)
//             $student = Student::where(function($q) use ($phone0, $phone62) {
//                             $q->where('phone', $phone0)->orWhere('phone', $phone62);
//                         // })->orWhere(function($q) use ($phone0, $phone62) {
//                         //     $q->where('parent_phone', $phone0)->orWhere('parent_phone', $phone62);
//                         // })->orWhere(function($q) use ($phone0, $phone62) {
//                         //     $q->where('parent_phone_2', $phone0)->orWhere('parent_phone_2', $phone62);
//                         })->first();

//             // Jika nomor tidak dikenal
//             if (!$student) {
//                 $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar di sistem sekolah.", $sessionId);
//                 return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
//             }

//             // --- LOGIKA CHATBOT ---
//             $message = strtoupper($messageRaw);

//             // A. Menu Utama
//             if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES', 'PING', 'HI', 'HELP', 'ASSALAMUALAIKUM'])) {
//                 $this->sendMenu($phonePure, $student, $sessionId);
//             }
//             // B. Menu Harian
//             elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
//                 $this->cekAbsensiHarian($phonePure, $student, $sessionId);
//             }
//             elseif ($message === 'CEK_JADWAL' || $message === '2') {
//                 $this->cekJadwal($phonePure, $student, $sessionId);
//             }
//             elseif ($message === 'CEK_SPP' || $message === '3') {
//                 $this->cekSPP($phonePure, $student, $sessionId);
//             }
//             // C. Menu Rekap Text
//             elseif ($message === 'REKAP_MINGGU' || $message === '4') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
//             }
//             elseif ($message === 'REKAP_BULAN' || $message === '5') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
//             }
//             elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
//                 $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
//             }
//             // D. EKSPOR GOOGLE SHEET (Angka 7)
//             elseif ($message === 'EXPORT_SHEET' || $message === '7') {
//                 $this->sendText($phonePure, "⏳ Sedang membuat Google Sheet rekap absensi...\nMohon tunggu sebentar.", $sessionId);
//                 $this->exportRekapSheet($phonePure, $student, $sessionId);
//             }
//             // E. EKSPOR PDF (Angka 8)
//             elseif ($message === 'EXPORT_PDF' || $message === '8') {
//                 $this->sendText($phonePure, "⏳ Sedang membuat PDF rekap absensi...\nMohon tunggu sebentar.", $sessionId);
//                 $this->exportRekapPDF($phonePure, $student, $sessionId);
//             }
//             // Fallback
//             else {
//                  $this->sendText($phonePure, "Halo {$student->name}, ketik *MENU* untuk melihat pilihan.", $sessionId);
//             }

//             return response()->json(['status' => 'processed']);

//         } catch (\Throwable $e) {
//             Log::error("WA Error: " . $e->getMessage());
//             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
//         }
//     }

//     /**
//      * 1. Kirim Menu Pilihan
//      */
//     private function sendMenu($phone, $student, $sessionId)
//     {
//         $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\n" .
//                      "Silakan balas pesan ini dengan *ANGKA* menu:\n\n" .
//                      "1️⃣ Ketik *1* : Cek Absensi Hari Ini\n" .
//                      "2️⃣ Ketik *2* : Cek Jadwal\n" .
//                      "3️⃣ Ketik *3* : Info SPP\n" .
//                      "4️⃣ Ketik *4* : Rekap Mingguan (Text)\n" .
//                      "----------------------------------\n" .
//                      "📂 *DOWNLOAD LAPORAN:*\n" .
//                      "7️⃣ Ketik *7* : Ekspor Google Sheet 📊\n" .
//                      "8️⃣ Ketik *8* : Ekspor PDF 📄\n\n" .
//                      "Atau tekan tombol di bawah:";

//         $payload = [
//             'session_id' => $sessionId,
//             'number' => $phone,
//             'type' => 'list',
//             'message' => $pesanTeks,
//             'title' => "Sistem Informasi Akademik",
//             'footer' => "SMK Teladan",
//             'buttonText' => "MENU PILIHAN",
//             'sections' => [
//                 [
//                     'title' => 'Informasi Harian',
//                     'rows' => [
//                         ['title' => 'Cek Absensi Hari Ini', 'rowId' => '1', 'description' => 'Balas 1'],
//                         ['title' => 'Jadwal Pelajaran', 'rowId' => '2', 'description' => 'Balas 2']
//                     ]
//                 ],
//                 [
//                     'title' => 'Download Laporan',
//                     'rows' => [
//                         ['title' => 'Download Excel/Sheet', 'rowId' => '7', 'description' => 'Via Google Sheet'],
//                         ['title' => 'Download PDF', 'rowId' => '8', 'description' => 'Via Dokumen WA']
//                     ]
//                 ]
//             ]
//         ];

//         $this->postToNode($payload);
//     }

//     /**
//      * 2. Cek Absensi Harian (Gerbang + Jadwal + Mapel)
//      */
//     private function cekAbsensiHarian($phone, $student, $sessionId)
//     {
//         $today = Carbon::today();
//         $hariIndo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
//         $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

//         // A. Gerbang
//         $gate = DailyAttendance::where('student_id', $student->id)->whereDate('created_at', $today)->first();
//         $statusGerbang = $gate ? strtoupper($gate->status) . " (" . Carbon::parse($gate->arrival_time)->format('H:i') . ")" : "BELUM ABSEN / ALPA";

//         // B. Jadwal
//         $listJadwal = "";
//         try {
//             $schedules = Schedule::with(['subject', 'teacher'])
//                         ->where('classroom_id', $student->classroom_id)->where('day', $namaHari)
//                         ->orderBy('start_time', 'asc')->get();
//             if ($schedules->count() > 0) {
//                 foreach ($schedules as $sched) {
//                     $guru = $sched->teacher->name ?? '-';
//                     $listJadwal .= "• {$sched->subject->name} (" . substr($sched->start_time, 0, 5) . "-" . substr($sched->end_time, 0, 5) . ")\n  👨‍🏫 {$guru}\n";
//                 }
//             } else { $listJadwal = "_Tidak ada jadwal_"; }
//         } catch (\Throwable $e) { $listJadwal = "-"; }

//         // C. Mapel
//         $listPembelajaran = "";
//         try {
//             $lessons = Attendance::with('subject')->where('student_id', $student->id)->whereDate('created_at', $today)->get();
//             if ($lessons->count() > 0) {
//                 foreach ($lessons as $l) {
//                     $listPembelajaran .= "• " . ($l->subject->name ?? 'Mapel') . ": *" . strtoupper($l->status) . "*\n";
//                 }
//             } else { $listPembelajaran = "_Belum ada data_"; }
//         } catch (\Throwable $e) { $listPembelajaran = "-"; }

//         $msg = "📊 *LAPORAN HARIAN*\nNama: {$student->name}\nHari: {$namaHari}, " . $today->format('d-m-Y') . "\n\n🏫 *GERBANG*: {$statusGerbang}\n\n📅 *JADWAL*:\n{$listJadwal}\n📚 *PEMBELAJARAN*:\n{$listPembelajaran}";
//         $this->sendText($phone, $msg, $sessionId);
//     }

//     /**
//      * 3. Cek Jadwal
//      */
//     private function cekJadwal($phone, $student, $sessionId)
//     {
//         $this->cekAbsensiHarian($phone, $student, $sessionId); // Reuse logic
//     }

//     /**
//      * 4. Cek SPP
//      */
//     private function cekSPP($phone, $student, $sessionId)
//     {
//         $this->sendText($phone, "Info SPP: LUNAS (Data Dummy)", $sessionId);
//     }

//     /**
//      * 5. Rekap Text (Mingguan/Bulanan)
//      */
//     private function cekRekapAbsensi($phone, $student, $sessionId, $period)
//     {
//         $startDate = Carbon::now();
//         if ($period == 'weekly') $startDate->subDays(7);
//         elseif ($period == 'monthly') $startDate->startOfMonth();
//         elseif ($period == 'semester') $startDate->subMonths(6);

//         $rekap = DailyAttendance::where('student_id', $student->id)
//                 ->whereBetween('created_at', [$startDate, Carbon::now()])
//                 ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();

//         $txt = "📈 *REKAP " . strtoupper($period) . "*\n\n";
//         foreach ($rekap as $k => $v) { $txt .= "• " . strtoupper($k) . ": $v\n"; }
//         if (empty($rekap)) $txt .= "_Tidak ada data absensi_";

//         $this->sendText($phone, $txt, $sessionId);
//     }

//     /**
//      * 6. Export Google Sheet
//      */
//     private function exportRekapSheet($phone, $student, $sessionId)
//     {
//         try {
//             $startDate = Carbon::now()->startOfMonth();
//             $attendances = DailyAttendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, Carbon::now()])
//                             ->orderBy('created_at', 'asc')->get();

//             $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Ket']];
//             foreach ($attendances as $att) {
//                 $t = Carbon::parse($att->created_at);
//                 $rows[] = [$t->format('d-m-Y'), $t->translatedFormat('l'), $t->format('H:i'), $att->status, $att->notes];
//             }

//             $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
//             $spreadsheet = Sheets::create($sheetTitle, function ($sheet) use ($rows) { $sheet->append($rows); });

//             // Set Public Permission
//             $client = Sheets::getGoogleService()->getClient();
//             $drive = new \Google\Service\Drive($client);
//             $perm = new \Google\Service\Drive\Permission(['type' => 'anyone', 'role' => 'reader']);
//             $drive->permissions->create($spreadsheet->id, $perm);

//             $this->sendText($phone, "✅ *Sheet Siap!*\nLink: " . $spreadsheet->spreadsheetUrl, $sessionId);

//         } catch (\Throwable $e) {
//             Log::error("Sheet Error: " . $e->getMessage());
//             $this->sendText($phone, "Gagal membuat Sheet. Cek Server Log.", $sessionId);
//         }
//     }

//     /**
//      * 7. Export PDF (FIXED ERROR HANDLING)
//      */
//     private function exportRekapPDF($phone, $student, $sessionId)
//     // {
//     //     try {
//     //         $startDate = Carbon::now()->startOfMonth();
//     //         $attendances = Attendance::where('student_id', $student->id)
//     //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
//     //                         ->orderBy('created_at', 'asc')->get();

//     //         // Cek apakah View ada
//     //         if (!view()->exists('pdf.recap')) {
//     //             throw new \Exception("File View 'resources/views/pdf/recap.blade.php' tidak ditemukan.");
//     //         }

//     //         // Load View PDF
//     //         $pdf = Pdf::loadView('pdf.recap', [
//     //             'student' => $student,
//     //             'attendances' => $attendances,
//     //             'period' => $startDate->translatedFormat('F Y')
//     //         ]);

//     //         // Pastikan folder penyimpanan ada
//     //         if (!Storage::exists('public/pdf')) {
//     //             Storage::makeDirectory('public/pdf');
//     //         }

//     //         // Save PDF
//     //         $fileName = 'rekap_' . $student->id . '_' . time() . '.pdf';
//     //         $saved = Storage::put('public/pdf/' . $fileName, $pdf->output());

//     //         if (!$saved) {
//     //             throw new \Exception("Gagal menyimpan file PDF ke folder storage.");
//     //         }

//     //         // Get URL
//     //         // Pastikan Anda sudah menjalankan: php artisan storage:link
//     //         $url = asset('storage/pdf/' . $fileName);

//     //         Log::info("PDF Created: " . $url);

//     //         // Kirim Dokumen
//     //         $this->postToNode([
//     //             'session_id' => $sessionId,
//     //             'number' => $phone,
//     //             'type' => 'document',
//     //             'media_url' => $url,
//     //             'message' => "📄 Rekap PDF Siap",
//     //             'title' => "Rekap-{$student->name}.pdf"
//     //         ]);

//     //     } catch (\Throwable $e) {
//     //         Log::error("PDF Error: " . $e->getMessage());
//     //         // Kirim pesan error yang JELAS ke WA User
//     //         $errMsg = substr($e->getMessage(), 0, 150); // Ambil 150 karakter pertama
//     //         $this->sendText($phone, "⚠️ Gagal membuat PDF.\nPenyebab: {$errMsg}", $sessionId);
//     //     }
//     // }
//     {
//         // 1. Naikkan Limit Eksekusi & Memori (PENTING untuk DOMPDF)
//         ini_set('max_execution_time', 300); // 300 detik (5 menit)
//         ini_set('memory_limit', '512M');    // 512 MB RAM

//         try {
//             $startDate = Carbon::now()->startOfMonth();
//             $endDate = Carbon::now();

//             // Ambil data
//             $attendances = Attendance::where('student_id', $student->id)
//                             ->whereBetween('created_at', [$startDate, $endDate])
//                             ->orderBy('created_at', 'asc')->get();

//             // Cek apakah View ada
//             if (!view()->exists('pdf.recap')) {
//                 throw new \Exception("File View 'resources/views/pdf/recap.blade.php' tidak ditemukan.");
//             }

//             // 2. Generate PDF
//             $pdf = Pdf::loadView('pdf.recap', [
//                 'student' => $student,
//                 'attendances' => $attendances,
//                 'period' => $startDate->translatedFormat('F Y')
//             ]);

//             // Set ukuran kertas
//             $pdf->setPaper('a4', 'portrait');

//             // 3. Pastikan Folder Ada
//             $folderPath = 'public/pdf';
//             if (!Storage::exists($folderPath)) {
//                 Storage::makeDirectory($folderPath);
//             }

//             // 4. Simpan File
//             // Sanitasi nama file
//             $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $student->name));
//             $fileName = 'Laporan-' . $safeName . '-' . time() . '.pdf';
//             $filePath = $folderPath . '/' . $fileName;

//             Storage::put($filePath, $pdf->output());

//             // 5. Generate URL Publik
//             // NOTE: Pastikan sudah run 'php artisan storage:link'
//             $publicUrl = asset('storage/pdf/' . $fileName);

//             Log::info("PDF Created: " . $publicUrl);

//             // 6. Kirim ke Node.js
//             $this->postToNode([
//                 'session_id' => $sessionId,
//                 'number' => $phone,
//                 'type' => 'document',
//                 'media_url' => $publicUrl,
//                 'message' => "📄 Berikut Laporan Rekap Absensi (PDF)\nPeriode: " . $startDate->translatedFormat('F Y'),
//                 'title' => $fileName
//             ]);

//         } catch (\Throwable $e) {
//             Log::error("PDF Gagal: " . $e->getMessage());

//             // Kirim pesan error yang jelas ke WA
//             $errMsg = substr($e->getMessage(), 0, 200);
//             $this->sendText($phone, "⚠️ Gagal membuat PDF.\nError: {$errMsg}\n\nHubungi admin jika berlanjut.", $sessionId);
//         }
//     }

//     // --- Helpers ---
//     private function sendText($phone, $msg, $sessionId) {
//         $this->postToNode(['session_id' => $sessionId, 'number' => $phone, 'type' => 'text', 'message' => $msg]);
//     }

//     private function postToNode($payload) {
//         try { Http::timeout(10)->post($this->nodeServerUrl, $payload); }
//         catch (\Exception $e) { Log::error("NodeJS Error: " . $e->getMessage()); }
//     }
// }

{
    // URL Node.js Server
    protected $nodeServerUrl = 'http://localhost:3000/send-message';

    public function handle(Request $request)
    {
        try {
            // 1. Validasi Input
            $senderNumber = $request->input('from');
            $messageRaw = trim($request->input('message'));
            $sessionId = $request->input('session_id');

            if (!$senderNumber || !$messageRaw) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            // 2. Debounce (Cegah Double Reply)
            $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);

            // 3. Normalisasi Nomor HP
            $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber);
            $phone0 = (substr($phonePure, 0, 2) == '62') ? '0' . substr($phonePure, 2) : $phonePure;
            $phone62 = (substr($phonePure, 0, 1) == '0') ? '62' . substr($phonePure, 1) : $phonePure;

            // 4. Cari Siswa
            $student = Student::where(function($q) use ($phone0, $phone62) {
                            $q->where('phone', $phone0)->orWhere('phone', $phone62);
                        // })->orWhere(function($q) use ($phone0, $phone62) {
                        //     $q->where('parent_phone', $phone0)->orWhere('parent_phone', $phone62);
                        // })->orWhere(function($q) use ($phone0, $phone62) {
                        //     $q->where('parent_phone_2', $phone0)->orWhere('parent_phone_2', $phone62);
                        })->first();

            if (!$student) {
                // Hapus baris ini jika tidak ingin bot membalas nomor asing
                $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar.", $sessionId);
                return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
            }

            // --- LOGIKA CHATBOT ---
            $message = strtoupper($messageRaw);

            // Menu & Fitur
            if (in_array($message, ['MENU', 'HALO', 'INFO', 'TES'])) {
                $this->sendMenu($phonePure, $student, $sessionId);
            }
            elseif ($message === 'CEK_ABSENSI_HARI_INI' || $message === '1') {
                $this->cekAbsensiHarian($phonePure, $student, $sessionId);
            }
            elseif ($message === 'CEK_JADWAL' || $message === '2') {
                $this->cekJadwal($phonePure, $student, $sessionId);
            }
            elseif ($message === 'CEK_SPP' || $message === '3') {
                $this->cekSPP($phonePure, $student, $sessionId);
            }
            elseif ($message === '4' || $message === 'REKAP_MINGGU') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
            }
            elseif ($message === '5' || $message === 'REKAP_BULAN') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
            }
            elseif ($message === '6' || $message === 'REKAP_SEMESTER') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
            }
            elseif ($message === '7' || $message === 'EXPORT_SHEET') {
                $this->sendText($phonePure, "⏳ Sedang membuat Spreadsheet...", $sessionId);
                $this->exportRekapSheet($phonePure, $student, $sessionId);
            }
            elseif ($message === '8' || $message === 'EXPORT_PDF') {
                $this->sendText($phonePure, "⏳ Sedang membuat PDF rekap absensi...\nMohon tunggu sebentar.", $sessionId);
                $this->exportRekapPDF($phonePure, $student, $sessionId);
            }
            else {
                 $this->sendText($phonePure, "Halo {$student->name}, ketik *MENU* untuk bantuan.", $sessionId);
            }

            return response()->json(['status' => 'processed']);

        } catch (\Throwable $e) {
            Log::error("WA Error: " . $e->getMessage());
            // Kirim notif error ke WA agar tidak diam saja
            try {
                $this->sendText($request->input('from'), "Terjadi kesalahan sistem: " . substr($e->getMessage(), 0, 100), $request->input('session_id'));
            } catch (\Exception $ex) {}

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * EKSPOR PDF DENGAN METODE BASE64 (SOLUSI ANTI-GAGAL LOCALHOST)
     */
    private function exportRekapPDF($phone, $student, $sessionId)
    {
        // Naikkan resource limits
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        try {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();



            // Ambil data
            $attendances = DailyAttendance::where('student_id', $student->id)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->orderBy('created_at', 'asc')->get();

            // Cek View
            if (!view()->exists('pdf.recap')) {
                throw new \Exception("File View 'pdf.recap' tidak ditemukan.");
            }

            // Generate PDF
            $pdf = Pdf::loadView('pdf.recap', [
                'student' => $student,
                'attendances' => $attendances,
                'periodAwal' => $startDate->translatedFormat('d F Y'),
                'periodAkhir' => $endDate->translatedFormat('d F Y'),
                'school' => get_school_data()

            ]);
            $pdf->setPaper('a4', 'portrait');

            // --- PERUBAHAN UTAMA: Convert ke Base64 ---
            // Kita tidak menyimpan ke storage/public, tapi langsung ambil kontennya
            // Ini mengatasi masalah Node.js tidak bisa akses URL localhost
            $pdfContent = $pdf->output();
            $base64 = base64_encode($pdfContent);
            $dataUri = 'data:application/pdf;base64,' . $base64;

            // Log ukuran file untuk debug
            Log::info("PDF Generated. Size: " . strlen($pdfContent) . " bytes");

            // Kirim Data URI ke Node.js
            $this->postToNode([
                'session_id' => $sessionId,
                'number' => $phone,
                'type' => 'document',
                'media_url' => $dataUri, // Kirim data langsung, bukan URL
                'message' => "📄 Laporan Rekap Absensi\nPeriode: " . $startDate->translatedFormat('F Y'),
                'title' => 'Rekap-' . str_replace(' ', '-', $student->name) . '.pdf'
            ]);

        } catch (\Throwable $e) {
            Log::error("PDF Gagal: " . $e->getMessage());
            $this->sendText($phone, "⚠️ Gagal membuat PDF.\nErr: " . substr($e->getMessage(), 0, 100), $sessionId);
        }
    }

    // ... (Method Helper Lain: sendMenu, cekAbsensi, dll TETAP SAMA seperti sebelumnya) ...
    // Copy paste method sendMenu, cekAbsensiHarian, cekJadwal, cekSPP, cekRekapAbsensi, exportRekapSheet dari kode sebelumnya

    private function sendMenu($phone, $student, $sessionId) {
        $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\nSilakan pilih menu:\n1️⃣ Cek Absensi Hari Ini\n2️⃣ Cek Jadwal\n3️⃣ Info SPP\n4️⃣ Rekap Mingguan\n-----------------\n7️⃣ Export Excel\n8️⃣ Export PDF";
        $payload = [
            'session_id' => $sessionId, 'number' => $phone, 'type' => 'list', 'message' => $pesanTeks, 'title' => "Sistem Sekolah", 'footer' => "SMK Teladan", 'buttonText' => "MENU",
            'sections' => [['title' => 'Menu', 'rows' => [
                ['title' => 'Cek Absensi', 'rowId' => '1'], ['title' => 'Cek Jadwal', 'rowId' => '2'], ['title' => 'Export PDF', 'rowId' => '8']
            ]]]
        ];
        $this->postToNode($payload);
    }

    private function cekAbsensiHarian($phone, $student, $sessionId)
    // {
    //     $msg = "Cek Absensi Harian: HADIR"; // Sederhanakan utk contoh
    //     $this->sendText($phone, $msg, $sessionId);
    // }

    {
        $today = Carbon::today();
        $hariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

        // --- 1. KEHADIRAN GERBANG ---
        $gate = DailyAttendance::where('student_id', $student->id)
                                ->whereDate('created_at', $today)
                                ->first();

        if ($gate) {
            $waktu = $gate->arrival_time ?? $gate->created_at;
            $jamMasuk = Carbon::parse($waktu)->format('H:i');
            $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
        } else {
            $statusGerbang = "BELUM ABSEN / ALPA";
        }

        // --- 2. JADWAL PELAJARAN (Hari Ini) ---
        $listJadwal = "";
        try {
            $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
                                    ->where('classroom_id', $student->classroom_id)
                                    ->where('day', $namaHari)
                                    ->orderBy('start_time', 'asc')
                                    ->get();

            if ($schedules->count() > 0) {
                foreach ($schedules as $sched) {
                    $mapel = $sched->subject->name ?? 'Mapel';
                    $guru = $sched->teacher->name ?? '-';
                    $jamMulai = substr($sched->start_time, 0, 5);
                    $jamSelesai = substr($sched->end_time, 0, 5);
                    $listJadwal .= "• {$mapel} ({$jamMulai}-{$jamSelesai})\n  👨‍🏫 {$guru}\n";
                }
            } else {
                $listJadwal = "_Tidak ada jadwal pelajaran_\n";
            }
        } catch (\Throwable $e) {
            $listJadwal = "_Data jadwal belum tersedia_\n";
        }

        // --- 3. KEHADIRAN PEMBELAJARAN (MAPEL) ---
        $listPembelajaran = "";
        try {
            $lessons = \App\Models\Attendance::with('subject')
                                    ->where('student_id', $student->id)
                                    ->whereDate('created_at', $today)
                                    ->orderBy('created_at', 'asc')
                                    ->get();

            if ($lessons->count() > 0) {
                foreach ($lessons as $lesson) {
                    $mapelName = $lesson->subject ? $lesson->subject->name : ($lesson->subject_name ?? 'Mapel');
                    $statusLesson = strtoupper($lesson->status);
                    $jamLesson = Carbon::parse($lesson->created_at)->format('H:i');
                    $listPembelajaran .= "• {$mapelName}: *{$statusLesson}* ({$jamLesson})\n";
                }
            } else {
                $listPembelajaran = "_Belum ada data pembelajaran masuk_";
            }
        } catch (\Throwable $e) {
            $listPembelajaran = "_Data pembelajaran tidak tersedia_";
        }

        // --- SUSUN PESAN FINAL ---
        $message = "📊 *LAPORAN HARIAN SISWA*\n" .
                   "Nama: *{$student->name}*\n" .
                   "Hari: {$namaHari}, " . $today->translatedformat('d F Y') . "\n\n" .
                   "🏫 *KEHADIRAN GERBANG*\n" .
                   "Status: *{$statusGerbang}*\n\n" .
                   "📅 *JADWAL HARI INI*\n" .
                   $listJadwal . "\n" .
                   "📚 *KEHADIRAN PEMBELAJARAN*\n" .
                   $listPembelajaran;

        $this->sendText($phone, $message, $sessionId);
    }

    private function cekJadwal($phone, $student, $sessionId) { $this->sendText($phone, "Jadwal Pelajaran...", $sessionId); }
    private function cekSPP($phone, $student, $sessionId) { $this->sendText($phone, "Info SPP...", $sessionId); }
    private function cekRekapAbsensi($phone, $student, $sessionId, $period)
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now();
        $label = "";

        // Tentukan Rentang Waktu
        if ($period == 'weekly') {
            $startDate = Carbon::now()->subDays(7);
            $label = "MINGGU INI (7 Hari Terakhir)";
        } elseif ($period == 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
        } elseif ($period == 'semester') {
            // Logika semester: Jan-Jun (Genap), Jul-Des (Ganjil)
            $month = Carbon::now()->month;
            if ($month >= 7) {
                $startDate = Carbon::createFromDate(null, 7, 1); // 1 Juli
                $label = "SEMESTER GANJIL";
            } else {
                $startDate = Carbon::createFromDate(null, 1, 1); // 1 Jan
                $label = "SEMESTER GENAP";
            }
        }

        // 1. REKAP GERBANG (Hitung jumlah Status)
        // Output: [HADIR => 5, SAKIT => 1, ...]
        $rekapGerbang = DailyAttendance::where('student_id', $student->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->selectRaw('status, count(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status')
                        ->toArray();

        // Format Text Gerbang
        $txtGerbang = "";
        $totalKehadiran = 0;
        if (empty($rekapGerbang)) {
            $txtGerbang = "_Belum ada data absensi_";
        } else {
            foreach ($rekapGerbang as $status => $total) {
                $statusUpper = strtoupper($status);
                $txtGerbang .= "• {$statusUpper} : {$total} hari\n";
                $totalKehadiran += $total;
            }
            $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
        }

        // 2. REKAP PEMBELAJARAN (Total Mapel Diikuti)
        try {
            $totalMapel = Attendance::where('student_id', $student->id)
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->where('status', 'hadir') // Asumsi hanya menghitung kehadiran
                            ->count();

            $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
        } catch (\Throwable $e) {
            $txtMapel = "-";
        }

        // SUSUN PESAN
        $message = "📈 *LAPORAN REKAPITULASI*\n" .
                   "Nama: *{$student->name}*\n" .
                   "Periode: {$label}\n\n" .
                   "🏫 *ABSENSI GERBANG*\n" .
                   $txtGerbang . "\n\n" .
                   "📚 *ABSENSI PEMBELAJARAN*\n" .
                   $txtMapel . "\n\n" .
                   "_Balas MENU untuk kembali ke menu utama._";

        $this->sendText($phone, $message, $sessionId);
    }

    private function exportRekapSheet($phone, $student, $sessionId)
    // { $this->sendText($phone, "Fitur Sheet...", $sessionId); }
    // {
    //     try {
    //         // Naikkan limit waktu agar tidak timeout saat konek ke Google
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data untuk Sheet
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Buat Sheet Baru
    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
    //         $spreadsheet = Sheets::create($sheetTitle, function ($sheet) use ($rows) {
    //             $sheet->append($rows);
    //         });

    //         // 4. Atur Permission (Public Reader)
    //         $client = Sheets::getGoogleService()->getClient();
    //         $drive = new \Google\Service\Drive($client);
    //         $perm = new \Google\Service\Drive\Permission([
    //             'type' => 'anyone',
    //             'role' => 'reader'
    //         ]);
    //         $drive->permissions->create($spreadsheet->id, $perm);

    //         // 5. Kirim Link ke WA
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheet->spreadsheetUrl;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 100), $sessionId);
    //     }
    // }
    // {
    //     try {
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Buat Spreadsheet Baru (GUNAKAN SERVICE GOOGLE API ASLI)
    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');

    //         // Dapatkan service Google Sheets asli
    //         $service = Sheets::getService();
    //         $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
    //             'properties' => [
    //                 'title' => $sheetTitle
    //             ]
    //         ]);

    //         // Create file
    //         $sheet = $service->spreadsheets->create($spreadsheet);
    //         $spreadsheetId = $sheet->spreadsheetId;
    //         $spreadsheetUrl = $sheet->spreadsheetUrl;

    //         // 4. Isi Data ke Sheet1
    //         Sheets::spreadsheet($spreadsheetId)->sheet('Sheet1')->append($rows);

    //         // 5. Atur Permission (Public Reader) via Drive API
    //         $driveService = new \Google\Service\Drive(Sheets::getGoogleService()->getClient());

    //         $newPermission = new \Google\Service\Drive\Permission();
    //         $newPermission->setType('anyone');
    //         $newPermission->setRole('reader');

    //         $driveService->permissions->create($spreadsheetId, $newPermission);

    //         // 6. Kirim Link
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 100), $sessionId);
    //     }
    // }
    // {
    //     try {
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Konfigurasi Manual Google Client (Solusi Ampuh untuk SSL & Auth)
    //         $client = new \Google\Client();

    //         // A. Ambil path credentials (sesuaikan dengan config Anda)
    //         $authConfig = config('google.sheets.credentials') ?? storage_path('app/google/credentials.json');

    //         if (!file_exists($authConfig)) {
    //             throw new \Exception("File credentials.json tidak ditemukan di: $authConfig");
    //         }

    //         $client->setAuthConfig($authConfig);
    //         $client->addScope([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);

    //         // B. Bypass SSL (PENTING: Set HttpClient sebelum membuat Service)
    //         $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

    //         // 4. Buat Service Sheets
    //         $service = new \Google\Service\Sheets($client);

    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
    //         $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
    //             'properties' => ['title' => $sheetTitle]
    //         ]);

    //         // Create file via API
    //         $sheet = $service->spreadsheets->create($spreadsheet);
    //         $spreadsheetId = $sheet->spreadsheetId;
    //         $spreadsheetUrl = $sheet->spreadsheetUrl;

    //         // 5. Isi Data ke Sheet1
    //         $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
    //         $params = ['valueInputOption' => 'RAW'];
    //         $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

    //         // 6. Atur Permission (Public Reader) via Drive API
    //         $driveService = new \Google\Service\Drive($client);

    //         $newPermission = new \Google\Service\Drive\Permission();
    //         $newPermission->setType('anyone');
    //         $newPermission->setRole('reader');

    //         $driveService->permissions->create($spreadsheetId, $newPermission);

    //         // 7. Kirim Link ke WA
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 100), $sessionId);
    //     }
    // }
    // {
    //     try {
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Konfigurasi Manual Google Client
    //         $client = new \Google\Client();

    //         // Cek path credentials
    //         $possiblePaths = [
    //             config('google.sheets.credentials'),
    //             storage_path('app/google/credentials.json'),
    //             storage_path('app/credentials.json'),
    //             base_path('credentials.json')
    //         ];

    //         $authConfig = null;
    //         foreach ($possiblePaths as $path) {
    //             if ($path && file_exists($path)) {
    //                 $authConfig = $path;
    //                 break;
    //             }
    //         }

    //         if (!$authConfig) {
    //             throw new \Exception("File credentials.json TIDAK DITEMUKAN di folder storage/app/google/");
    //         }

    //         $client->setAuthConfig($authConfig);
    //         $client->addScope([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);
    //         $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

    //         // 4. Buat Service Sheets
    //         $service = new \Google\Service\Sheets($client);

    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
    //         $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
    //             'properties' => ['title' => $sheetTitle]
    //         ]);

    //         // Create file
    //         $sheet = $service->spreadsheets->create($spreadsheet);
    //         $spreadsheetId = $sheet->spreadsheetId;
    //         $spreadsheetUrl = $sheet->spreadsheetUrl;

    //         // 5. Isi Data ke Sheet1
    //         $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
    //         $params = ['valueInputOption' => 'RAW'];
    //         $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

    //         // 6. Atur Permission (Public Reader)
    //         $driveService = new \Google\Service\Drive($client);
    //         $newPermission = new \Google\Service\Drive\Permission();
    //         $newPermission->setType('anyone');
    //         $newPermission->setRole('reader');

    //         $driveService->permissions->create($spreadsheetId, $newPermission);

    //         // 7. Kirim Link ke WA
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Google\Service\Exception $e) {
    //         // Tangkap Error Khusus Google (Format JSON)
    //         $errors = json_decode($e->getMessage(), true);
    //         $pesanError = $errors['error']['message'] ?? $e->getMessage();

    //         Log::error("Google API Error: " . $pesanError);
    //         $this->sendText($phone, "⚠️ Gagal akses Google API.\nPenyebab: " . $pesanError . "\n\nSolusi: Cek Google Cloud Console, pastikan 'Google Sheets API' & 'Drive API' sudah di-ENABLE.", $sessionId);
    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 150), $sessionId);
    //     }
    // }
    // {
    //     try {
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Konfigurasi Manual Google Client
    //         $client = new \Google\Client();

    //         // Cek path credentials
    //         $possiblePaths = [
    //             config('google.sheets.credentials'),
    //             storage_path('app/google/credentials.json'),
    //             storage_path('app/credentials.json'),
    //             base_path('credentials.json')
    //         ];

    //         $authConfig = null;
    //         foreach ($possiblePaths as $path) {
    //             if ($path && file_exists($path)) {
    //                 $authConfig = $path;
    //                 break;
    //             }
    //         }

    //         if (!$authConfig) {
    //             throw new \Exception("File credentials.json TIDAK DITEMUKAN di folder storage/app/google/");
    //         }

    //         $client->setAuthConfig($authConfig);
    //         $client->addScope([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);
    //         $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

    //         // 4. Buat Service Sheets
    //         $service = new \Google\Service\Sheets($client);

    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
    //         $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
    //             'properties' => ['title' => $sheetTitle]
    //         ]);

    //         // Create file
    //         $sheet = $service->spreadsheets->create($spreadsheet);
    //         $spreadsheetId = $sheet->spreadsheetId;
    //         $spreadsheetUrl = $sheet->spreadsheetUrl;

    //         // 5. Isi Data ke Sheet1
    //         $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
    //         $params = ['valueInputOption' => 'RAW'];
    //         $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

    //         // 6. Atur Permission (Public Reader)
    //         // UPDATE: Bungkus dalam try-catch terpisah agar jika permission gagal, link tetap terkirim
    //         $permissionNote = "";
    //         try {
    //             $driveService = new \Google\Service\Drive($client);
    //             $newPermission = new \Google\Service\Drive\Permission();
    //             $newPermission->setType('anyone');
    //             $newPermission->setRole('reader');

    //             $driveService->permissions->create($spreadsheetId, $newPermission);
    //         } catch (\Throwable $e) {
    //             Log::warning("Gagal set permission public: " . $e->getMessage());
    //             $permissionNote = "\n_(Note: Link mungkin bersifat Private, silakan request access saat membuka)_";
    //         }

    //         // 7. Kirim Link ke WA
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl . $permissionNote;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Google\Service\Exception $e) {
    //         // Tangkap Error Khusus Google (Format JSON)
    //         $errors = json_decode($e->getMessage(), true);
    //         $pesanError = $errors['error']['message'] ?? $e->getMessage();

    //         Log::error("Google API Error: " . $pesanError);
    //         $this->sendText($phone, "⚠️ Gagal akses Google API.\nPenyebab: " . $pesanError . "\n\nSolusi: Cek Google Cloud Console, pastikan 'Google Sheets API' & 'Drive API' sudah di-ENABLE.", $sessionId);
    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 150), $sessionId);
    //     }
    // }
    // {
    //     try {
    //         ini_set('max_execution_time', 300);

    //         // 1. Ambil Data
    //         $startDate = Carbon::now()->startOfMonth();
    //         $attendances = DailyAttendance::where('student_id', $student->id)
    //                         ->whereBetween('created_at', [$startDate, Carbon::now()])
    //                         ->orderBy('created_at', 'asc')->get();

    //         // 2. Format Data
    //         $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
    //         foreach ($attendances as $att) {
    //             $t = Carbon::parse($att->created_at);
    //             $rows[] = [
    //                 $t->format('d-m-Y'),
    //                 $t->translatedFormat('l'),
    //                 $t->format('H:i'),
    //                 $att->status,
    //                 $att->notes ?? '-'
    //             ];
    //         }

    //         // 3. Konfigurasi Manual Google Client
    //         $client = new \Google\Client();

    //         // Cek path credentials
    //         $possiblePaths = [
    //             config('google.sheets.credentials'),
    //             storage_path('app/google/credentials.json'),
    //             storage_path('app/credentials.json'),
    //             base_path('credentials.json')
    //         ];

    //         $authConfig = null;
    //         foreach ($possiblePaths as $path) {
    //             if ($path && file_exists($path)) {
    //                 $authConfig = $path;
    //                 break;
    //             }
    //         }

    //         if (!$authConfig) {
    //             throw new \Exception("File credentials.json TIDAK DITEMUKAN di folder storage/app/google/");
    //         }

    //         // --- DEBUGGING: Cek Isi JSON ---
    //         $jsonContent = json_decode(file_get_contents($authConfig), true);
    //         if (!isset($jsonContent['type']) || $jsonContent['type'] !== 'service_account') {
    //             throw new \Exception("File JSON salah! Harus tipe 'Service Account'. Tipe saat ini: " . ($jsonContent['type'] ?? 'Unknown'));
    //         }
    //         // Log info untuk memastikan project yang dipakai benar (cek file laravel.log)
    //         Log::info("Google Auth Info: Project=" . ($jsonContent['project_id'] ?? '-') . ", Email=" . ($jsonContent['client_email'] ?? '-'));

    //         $client->setAuthConfig($authConfig);

    //         // FIX PENTING: Gunakan setScopes (bukan addScope) dan Matikan Cache Token
    //         $client->setScopes([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);

    //         // Gunakan array cache (memory only) untuk mencegah token kadaluarsa nyangkut di file
    //         $client->setCacheConfig(['store' => 'array']);

    //         $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

    //         // 4. Buat Service Sheets
    //         $service = new \Google\Service\Sheets($client);

    //         $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
    //         $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
    //             'properties' => ['title' => $sheetTitle]
    //         ]);

    //         // Create file via API
    //         // Jika error 403 disini, pastikan Drive API aktif di project ID yang muncul di Log
    //         $sheet = $service->spreadsheets->create($spreadsheet);
    //         $spreadsheetId = $sheet->spreadsheetId;
    //         $spreadsheetUrl = $sheet->spreadsheetUrl;

    //         // 5. Isi Data ke Sheet1
    //         $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
    //         $params = ['valueInputOption' => 'RAW'];
    //         $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

    //         // 6. Atur Permission (Public Reader)
    //         // Bungkus dalam try-catch terpisah agar jika permission gagal, link tetap terkirim
    //         $permissionNote = "";
    //         try {
    //             $driveService = new \Google\Service\Drive($client);
    //             $newPermission = new \Google\Service\Drive\Permission();
    //             $newPermission->setType('anyone');
    //             $newPermission->setRole('reader');

    //             $driveService->permissions->create($spreadsheetId, $newPermission);
    //         } catch (\Throwable $e) {
    //             Log::warning("Gagal set permission public: " . $e->getMessage());
    //             $permissionNote = "\n_(Note: Link mungkin bersifat Private, silakan request access saat membuka)_";
    //         }

    //         // 7. Kirim Link ke WA
    //         $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl . $permissionNote;
    //         $this->sendText($phone, $msg, $sessionId);

    //     } catch (\Google\Service\Exception $e) {
    //         // Tangkap Error Khusus Google
    //         $errors = json_decode($e->getMessage(), true);
    //         $pesanError = $errors['error']['message'] ?? $e->getMessage();

    //         // Log detail lengkap ke laravel.log
    //         Log::error("Google API Full Error: " . $e->getMessage());

    //         $this->sendText($phone, "⚠️ Gagal akses Google API (403 Permission).\n\nSolusi:\n1. Buka Google Cloud Console.\n2. Pastikan Project ID sesuai dengan file JSON.\n3. Enable 'Google Sheets API' & 'Drive API' di project tersebut.\n4. Tunggu 2-5 menit.", $sessionId);
    //     } catch (\Throwable $e) {
    //         Log::error("Sheet Error: " . $e->getMessage());
    //         $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 150), $sessionId);
    //     }
    // }
    {
        try {
            ini_set('max_execution_time', 300);

            // 1. Ambil Data
            $startDate = Carbon::now()->startOfMonth();
            $attendances = DailyAttendance::where('student_id', $student->id)
                            ->whereBetween('created_at', [$startDate, Carbon::now()])
                            ->orderBy('created_at', 'asc')->get();

            // 2. Format Data
            $rows = [['Tanggal', 'Hari', 'Jam', 'Status', 'Keterangan']];
            foreach ($attendances as $att) {
                $t = Carbon::parse($att->created_at);
                $rows[] = [
                    $t->format('d-m-Y'),
                    $t->translatedFormat('l'),
                    $t->format('H:i'),
                    $att->status,
                    $att->notes ?? '-'
                ];
            }

            // 3. Konfigurasi Manual Google Client
            $client = new \Google\Client();

            // Cek path credentials
            $possiblePaths = [
                config('google.sheets.credentials'),
                storage_path('app/google/credentials.json'),
                storage_path('app/credentials.json'),
                base_path('credentials.json')
            ];

            $authConfig = null;
            foreach ($possiblePaths as $path) {
                if ($path && file_exists($path)) {
                    $authConfig = $path;
                    break;
                }
            }

            if (!$authConfig) {
                throw new \Exception("File credentials.json TIDAK DITEMUKAN di folder storage/app/google/");
            }

            // --- DEBUGGING: Cek Isi JSON ---
            $jsonContent = json_decode(file_get_contents($authConfig), true);
            if (!isset($jsonContent['type']) || $jsonContent['type'] !== 'service_account') {
                throw new \Exception("File JSON salah! Harus tipe 'Service Account'. Tipe saat ini: " . ($jsonContent['type'] ?? 'Unknown'));
            }
            // Log info untuk memastikan project yang dipakai benar (cek file laravel.log)
            Log::info("Google Auth Info: Project=" . ($jsonContent['project_id'] ?? '-') . ", Email=" . ($jsonContent['client_email'] ?? '-'));

            $client->setAuthConfig($authConfig);

            // FIX PENTING: Gunakan setScopes (bukan addScope) dan Matikan Cache Token
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);

            // Gunakan array cache (memory only) untuk mencegah token kadaluarsa nyangkut di file
            $client->setCacheConfig(['store' => 'array']);

            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            // 4. Buat Service Sheets
            $service = new \Google\Service\Sheets($client);

            $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
            $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
                'properties' => ['title' => $sheetTitle]
            ]);

            // Create file via API
            // Jika error 403 disini, pastikan Drive API aktif di project ID yang muncul di Log
            $sheet = $service->spreadsheets->create($spreadsheet);
            $spreadsheetId = $sheet->spreadsheetId;
            $spreadsheetUrl = $sheet->spreadsheetUrl;

            // 5. Isi Data ke Sheet1
            $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

            // 6. Atur Permission (Public Reader)
            // Bungkus dalam try-catch terpisah agar jika permission gagal, link tetap terkirim
            $permissionNote = "";
            try {
                $driveService = new \Google\Service\Drive($client);
                $newPermission = new \Google\Service\Drive\Permission();
                $newPermission->setType('anyone');
                $newPermission->setRole('reader');

                $driveService->permissions->create($spreadsheetId, $newPermission);
            } catch (\Throwable $e) {
                Log::warning("Gagal set permission public: " . $e->getMessage());
                $permissionNote = "\n_(Note: Link mungkin bersifat Private, silakan request access saat membuka)_";
            }

            // 7. Kirim Link ke WA
            $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl . $permissionNote;
            $this->sendText($phone, $msg, $sessionId);

        } catch (\Google\Service\Exception $e) {
            // Tangkap Error Khusus Google
            $errors = json_decode($e->getMessage(), true);
            $pesanError = $errors['error']['message'] ?? $e->getMessage();

            // Log detail lengkap ke laravel.log
            Log::error("Google API Full Error: " . $e->getMessage());

            $this->sendText($phone, "⚠️ Gagal akses Google API (403 Permission).\n\nSolusi:\n1. Buka Google Cloud Console.\n2. Pastikan Project ID di credentials.json sesuai.\n3. Enable 'Google Sheets API' & 'Drive API'.\n4. *PENTING*: Tunggu 2-5 menit setelah enable.", $sessionId);
        } catch (\Throwable $e) {
            Log::error("Sheet Error: " . $e->getMessage());
            $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 150), $sessionId);
        }
    }


    private function sendText($phone, $msg, $sessionId) {
        $this->postToNode(['session_id' => $sessionId, 'number' => $phone, 'type' => 'text', 'message' => $msg]);
    }

    /**
     * Helper POST ke Node.js (FIX: THROW ERROR AGAR TIDAK SILENT)
     */
    private function postToNode($payload)
    {
        // Hapus Try-Catch di sini agar error naik ke handle() atau exportRekapPDF
        // Gunakan timeout agak panjang untuk kirim file base64
        $response = Http::timeout(20)->post($this->nodeServerUrl, $payload);

        if ($response->failed()) {
            throw new \Exception("Gagal kirim ke Node.js: " . $response->status() . " - " . $response->body());
        }
    }
}
