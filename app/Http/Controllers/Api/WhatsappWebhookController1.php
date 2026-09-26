<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentPermission; // Dari kode 1
use App\Models\DailyAttendance;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Dari kode 1
use Barryvdh\DomPDF\Facade\Pdf;

class WhatsappWebhookController extends Controller
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

            // 2. Debounce (Cegah Double Reply dalam 5 detik)
            $cacheKey = "wa_reply_lock_{$sessionId}_{$senderNumber}_" . md5($messageRaw);
            if (Cache::has($cacheKey)) {
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate_request']);
            }
            Cache::put($cacheKey, true, 5);

            // 3. Normalisasi Nomor HP (Hapus @s.whatsapp.net dan buat versi 08x & 628x)
            $phonePure = str_replace('@s.whatsapp.net', '', $senderNumber);
            $phone0 = (substr($phonePure, 0, 2) == '62') ? '0' . substr($phonePure, 2) : $phonePure;
            $phone62 = (substr($phonePure, 0, 1) == '0') ? '62' . substr($phonePure, 1) : $phonePure;

            // ==========================================
            // LOGIKA PENGAJUAN IZIN (Dari Kode Pertama)
            // Diletakkan di sini agar bisa diakses walau nomor HP tidak terdaftar,
            // asalkan NIS yang dimasukkan benar.
            // ==========================================
            if (preg_match('/^IZIN#(\d+)#(.*)$/i', $messageRaw, $matches)) {
                $nis = $matches[1];
                $alasan = $matches[2];

                // Cari siswa berdasarkan NIS di tabel students
                $student = Student::where('nis', $nis)->first();

                if ($student) {
                    // Simpan ke database
                    StudentPermission::create([
                        'student_id' => $student->id,
                        'date' => now()->toDateString(),
                        'reason' => $alasan,
                        'status' => 'pending',
                        'wa_number' => $senderNumber // Simpan nomor pengirim asli
                    ]);

                    // Balasan otomatis berhasil
                    $this->sendText($phonePure, "Terima kasih, pengajuan izin untuk siswa *{$student->name}* telah diterima dan menunggu persetujuan (Approval) dari Admin.", $sessionId);

                    return response()->json(['status' => 'success', 'message' => 'Izin berhasil diajukan']);
                } else {
                    // Balasan otomatis gagal (NIS tidak ditemukan)
                    $this->sendText($phonePure, "Maaf, siswa dengan NIS {$nis} tidak ditemukan di database.", $sessionId);

                    return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan']);
                }
            }
            // ==========================================

            // 4. Cari Siswa berdasarkan nomor (Bisa di-expand ke parent_phone jika perlu)
            $student = Student::where(function($q) use ($phone0, $phone62) {
                $q->where('phone', $phone0)->orWhere('phone', $phone62);
            })->first();

            // Jika nomor tidak terdaftar (dan bukan format IZIN), hentikan proses
            if (!$student) {
                $this->sendText($phonePure, "Maaf, nomor Anda ($phone0) belum terdaftar di sistem sekolah. Ketik dengan format *IZIN#NIS#Alasan* jika ingin mengajukan izin.", $sessionId);
                return response()->json(['status' => 'ignored', 'reason' => 'unknown_number']);
            }

            // --- LOGIKA CHATBOT MENU UTAMA ---
            $message = strtoupper($messageRaw);

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
            elseif ($message === 'REKAP_MINGGU' || $message === '4') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'weekly');
            }
            elseif ($message === 'REKAP_BULAN' || $message === '5') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'monthly');
            }
            elseif ($message === 'REKAP_SEMESTER' || $message === '6') {
                $this->cekRekapAbsensi($phonePure, $student, $sessionId, 'semester');
            }
            elseif ($message === 'EXPORT_SHEET' || $message === '7') {
                $this->sendText($phonePure, "⏳ Sedang membuat Spreadsheet...\nMohon tunggu sebentar.", $sessionId);
                $this->exportRekapSheet($phonePure, $student, $sessionId);
            }
            elseif ($message === 'EXPORT_PDF' || $message === '8') {
                $this->sendText($phonePure, "⏳ Sedang membuat PDF rekap absensi...\nMohon tunggu sebentar.", $sessionId);
                $this->exportRekapPDF($phonePure, $student, $sessionId);
            }
            else {
                $this->sendText($phonePure, "Halo {$student->name}, ketik *MENU* untuk bantuan atau ketik *IZIN#NIS#Alasan* untuk izin absen.", $sessionId);
            }

            return response()->json(['status' => 'processed']);

        } catch (\Throwable $e) {
            Log::error("WA Error: " . $e->getMessage());
            try {
                $this->sendText($request->input('from'), "Terjadi kesalahan sistem: " . substr($e->getMessage(), 0, 100), $request->input('session_id'));
            } catch (\Exception $ex) {}

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function sendMenu($phone, $student, $sessionId)
    {
        $pesanTeks = "Halo, Bapak/Ibu dari *{$student->name}*.\n\nSilakan pilih menu:\n1️⃣ Cek Absensi Hari Ini\n2️⃣ Cek Jadwal\n3️⃣ Info SPP\n4️⃣ Rekap Mingguan\n-----------------\n7️⃣ Export Excel\n8️⃣ Export PDF\n\n_Untuk izin, ketik manual:_\n*IZIN#NIS#Alasan*";

        $payload = [
            'session_id' => $sessionId,
            'number' => $phone,
            'type' => 'list',
            'message' => $pesanTeks,
            'title' => "Sistem Sekolah",
            'footer' => "SMK Teladan",
            'buttonText' => "MENU",
            'sections' => [
                ['title' => 'Menu', 'rows' => [
                    ['title' => 'Cek Absensi', 'rowId' => '1'],
                    ['title' => 'Cek Jadwal', 'rowId' => '2'],
                    ['title' => 'Export PDF', 'rowId' => '8']
                ]]
            ]
        ];

        $this->postToNode($payload);
    }

    private function cekAbsensiHarian($phone, $student, $sessionId)
    {
        $today = Carbon::today();
        $hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $namaHari = $hariIndo[$today->format('l')] ?? $today->format('l');

        // 1. Kehadiran Gerbang
        $gate = DailyAttendance::where('student_id', $student->id)->whereDate('created_at', $today)->first();
        if ($gate) {
            $waktu = $gate->arrival_time ?? $gate->created_at;
            $jamMasuk = Carbon::parse($waktu)->format('H:i');
            $statusGerbang = strtoupper($gate->status) . " (Pukul {$jamMasuk})";
        } else {
            $statusGerbang = "BELUM ABSEN / ALPA";
        }

        // 2. Jadwal Pelajaran
        $listJadwal = "";
        try {
            $schedules = Schedule::with(['subject', 'teacher'])
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

        // 3. Kehadiran Pembelajaran (Mapel)
        $listPembelajaran = "";
        try {
            $lessons = Attendance::with('subject')
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

        // Susun Pesan
        $message = "📊 *LAPORAN HARIAN SISWA*\n" .
                   "Nama: *{$student->name}*\n" .
                   "Hari: {$namaHari}, " . $today->translatedFormat('d F Y') . "\n\n" .
                   "🏫 *KEHADIRAN GERBANG*\n" .
                   "Status: *{$statusGerbang}*\n\n" .
                   "📅 *JADWAL HARI INI*\n" .
                   $listJadwal . "\n" .
                   "📚 *KEHADIRAN PEMBELAJARAN*\n" .
                   $listPembelajaran;

        $this->sendText($phone, $message, $sessionId);
    }

    private function cekJadwal($phone, $student, $sessionId) {
        // Cek jadwal tergabung dengan laporan harian
        $this->cekAbsensiHarian($phone, $student, $sessionId);
    }

    private function cekSPP($phone, $student, $sessionId) {
        $this->sendText($phone, "Info Keuangan:\n\nStatus SPP: *LUNAS* (Contoh Data)", $sessionId);
    }

    private function cekRekapAbsensi($phone, $student, $sessionId, $period)
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now();
        $label = "";

        if ($period == 'weekly') {
            $startDate = Carbon::now()->subDays(7);
            $label = "MINGGU INI (7 Hari Terakhir)";
        } elseif ($period == 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $label = "BULAN INI (" . Carbon::now()->format('F Y') . ")";
        } elseif ($period == 'semester') {
            $month = Carbon::now()->month;
            if ($month >= 7) {
                $startDate = Carbon::createFromDate(null, 7, 1);
                $label = "SEMESTER GANJIL";
            } else {
                $startDate = Carbon::createFromDate(null, 1, 1);
                $label = "SEMESTER GENAP";
            }
        }

        $rekapGerbang = DailyAttendance::where('student_id', $student->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $txtGerbang = "";
        $totalKehadiran = 0;
        if (empty($rekapGerbang)) {
            $txtGerbang = "_Belum ada data absensi_";
        } else {
            foreach ($rekapGerbang as $status => $total) {
                $txtGerbang .= "• " . strtoupper($status) . " : {$total} hari\n";
                $totalKehadiran += $total;
            }
            $txtGerbang .= "Total Hari Efektif: {$totalKehadiran}";
        }

        try {
            $totalMapel = Attendance::where('student_id', $student->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'hadir')
                ->count();
            $txtMapel = "Total Mapel Diikuti: {$totalMapel} sesi";
        } catch (\Throwable $e) {
            $txtMapel = "-";
        }

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
    {
        try {
            ini_set('max_execution_time', 300);

            $startDate = Carbon::now()->startOfMonth();
            $attendances = DailyAttendance::where('student_id', $student->id)
                ->whereBetween('created_at', [$startDate, Carbon::now()])
                ->orderBy('created_at', 'asc')->get();

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

            $client = new \Google\Client();
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
                throw new \Exception("File credentials.json tidak ditemukan di konfigurasi.");
            }

            $client->setAuthConfig($authConfig);
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS, \Google\Service\Drive::DRIVE]);
            $client->setCacheConfig(['store' => 'array']);
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));

            $service = new \Google\Service\Sheets($client);
            $sheetTitle = "Rekap " . $student->name . " " . date('M Y');
            $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
                'properties' => ['title' => $sheetTitle]
            ]);

            $sheet = $service->spreadsheets->create($spreadsheet);
            $spreadsheetId = $sheet->spreadsheetId;
            $spreadsheetUrl = $sheet->spreadsheetUrl;

            $body = new \Google\Service\Sheets\ValueRange(['values' => $rows]);
            $params = ['valueInputOption' => 'RAW'];
            $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A1', $body, $params);

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

            $msg = "✅ *Sheet Siap!*\n\nNama: {$student->name}\nPeriode: " . $startDate->translatedFormat('F Y') . "\n\nLink: " . $spreadsheetUrl . $permissionNote;
            $this->sendText($phone, $msg, $sessionId);

        } catch (\Google\Service\Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            $pesanError = $errors['error']['message'] ?? $e->getMessage();
            Log::error("Google API Full Error: " . $e->getMessage());
            $this->sendText($phone, "⚠️ Gagal akses Google API.\n\nPastikan 'Google Sheets API' & 'Drive API' sudah aktif di Google Console.", $sessionId);
        } catch (\Throwable $e) {
            Log::error("Sheet Error: " . $e->getMessage());
            $this->sendText($phone, "⚠️ Gagal membuat Sheet.\nErr: " . substr($e->getMessage(), 0, 150), $sessionId);
        }
    }

    private function exportRekapPDF($phone, $student, $sessionId)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        try {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();

            $attendances = DailyAttendance::where('student_id', $student->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'asc')->get();

            if (!view()->exists('pdf.recap')) {
                throw new \Exception("File View 'pdf.recap' tidak ditemukan.");
            }

            $pdf = Pdf::loadView('pdf.recap', [
                'student' => $student,
                'attendances' => $attendances,
                'periodAwal' => $startDate->translatedFormat('d F Y'),
                'periodAkhir' => $endDate->translatedFormat('d F Y'),
                'school' => function_exists('get_school_data') ? get_school_data() : 'Sekolah Kita'
            ]);
            $pdf->setPaper('a4', 'portrait');

            // Convert PDF to Base64 (Aman untuk kirim file via Node.js local)
            $pdfContent = $pdf->output();
            $base64 = base64_encode($pdfContent);
            $dataUri = 'data:application/pdf;base64,' . $base64;

            $this->postToNode([
                'session_id' => $sessionId,
                'number' => $phone,
                'type' => 'document',
                'media_url' => $dataUri,
                'message' => "📄 Laporan Rekap Absensi\nPeriode: " . $startDate->translatedFormat('F Y'),
                'title' => 'Rekap-' . str_replace(' ', '-', $student->name) . '.pdf'
            ]);

        } catch (\Throwable $e) {
            Log::error("PDF Gagal: " . $e->getMessage());
            $this->sendText($phone, "⚠️ Gagal membuat PDF.\nErr: " . substr($e->getMessage(), 0, 100), $sessionId);
        }
    }

    private function sendText($phone, $msg, $sessionId)
    {
        $this->postToNode([
            'session_id' => $sessionId,
            'number' => $phone,
            'type' => 'text',
            'message' => $msg
        ]);
    }

    private function postToNode($payload)
    {
        $response = Http::timeout(20)->post($this->nodeServerUrl, $payload);

        if ($response->failed()) {
            throw new \Exception("Gagal kirim ke Node.js: " . $response->status() . " - " . $response->body());
        }
    }
}
