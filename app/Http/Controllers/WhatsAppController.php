<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\WhatsappLog; // Import Model Log
use App\Jobs\SendWhatsappJob;
use App\Models\WhatsappGateway;

class WhatsAppController extends Controller
{
    /**
     * WEB: Tampilkan Halaman Test WA (Dashboard Admin)
     * Route: GET /whatsapp/test
     */
    public function index()
    {
        return view('whatsapp.send');
    }

    /**
     * WEB: Proses Kirim Pesan Manual (Redirect Back)
     * Route: POST /whatsapp/send
     */
    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required|numeric',
            'message' => 'required|string',
        ]);

        // Panggil fungsi private pengirim pesan (Langsung tanpa Queue untuk testing)
        $result = self::sendMessageToBaileys($request->target, $request->message);

        if ($result['status'] == 'success') {
            return redirect()->back()->with('success', 'Pesan berhasil dikirim via WhatsApp!');
        } else {
            return redirect()->back()->with('error', 'Gagal kirim: ' . $result['message']);
        }
    }

    /**
     * [BARU] Halaman Form Broadcast Per Kelas
     * Route: GET /whatsapp/broadcast
     */
    public function broadcast()
    {
        $gateways = WhatsappGateway::all();
        // Ambil data kelas untuk dropdown
        $classrooms = Classroom::orderBy('name')->get();
         // Ambil Riwayat Pesan Terakhir (5 Data per halaman agar tidak penuh)
        $logs = WhatsappLog::orderBy('created_at')->get();
        return view('whatsapp.broadcast', compact('classrooms', 'logs', 'gateways'));
    }

    /**
     * [BARU] Proses Kirim Broadcast Massal
     * Route: POST /whatsapp/broadcast
     */
    // public function sendBroadcast(Request $request)
    // {
    //     $request->validate([
    //         'classroom_id' => 'required|exists:classrooms,id',
    //         'message'      => 'required|string',
    //         'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Max 5MB
    //     ]);

    //     // 1. Ambil Siswa di Kelas Tersebut
    //     $students = Student::where('classroom_id', $request->classroom_id)
    //                        ->whereNotNull('phone')
    //                        ->get();

    //     if ($students->isEmpty()) {
    //         return back()->with('error', 'Tidak ada siswa dengan nomor HP di kelas ini.');
    //     }

    //     // 2. Handle File Upload (Jika ada lampiran)
    //     $mediaUrl = null;
    //     $type = 'text';
    //     $fileName = null;
    //     $mimeType = null;

    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');
    //         $fileName = $file->getClientOriginalName();
    //         $mimeType = $file->getMimeType();

    //         // Simpan di storage public agar bisa diakses Node.js via URL
    //         // Pastikan Anda sudah menjalankan: php artisan storage:link
    //         $path = $file->store('broadcasts', 'public');
    //         $mediaUrl = asset('storage/' . $path);

    //         // Tentukan tipe pesan (image atau document)
    //         if (str_starts_with($mimeType, 'image/')) {
    //             $type = 'image';
    //         } else {
    //             $type = 'document';
    //         }
    //     }

    //     // 3. Dispatch Job untuk Setiap Siswa
    //     $count = 0;
    //     foreach ($students as $student) {
    //         // Bersihkan nomor HP
    //         if (empty($student->phone)) continue;

    //         // Kirim ke Antrian (Queue)
    //         SendWhatsappJob::dispatch(
    //             $student->phone,
    //             $request->message,
    //             $type,
    //             $mediaUrl,
    //             $fileName,
    //             $mimeType
    //         );
    //         $count++;
    //     }

    //     return back()->with('success', "Pesan sedang dikirim ke $count orang tua siswa di latar belakang.");
    // }

    /**
     * Proses Kirim Pesan Broadcast
     */
    // public function sendBroadcast(Request $request)
    // {
    //     // 1. Validasi Input
    //     $request->validate([
    //         'classroom_id' => 'required|exists:classrooms,id',
    //         'message'      => 'required|string',
    //         'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Max 5MB
    //     ]);

    //     // 2. Ambil Data Kelas & Siswa
    //     $classroom = Classroom::findOrFail($request->classroom_id);
        
    //     // Ambil siswa yang memiliki nomor HP orang tua (parent_phone atau parent_phone_2)
    //     $students = Student::where('classroom_id', $classroom->id)
    //         ->where(function($q) {
    //             $q->whereNotNull('phone');
    //             // $q->whereNotNull('phone')->orWhereNotNull('phone_1');
    //         })
    //         ->get();

    //     if ($students->isEmpty()) {
    //         return back()->with('error', "Tidak ada data nomor HP orang tua di kelas {$classroom->name}.");
    //     }

    //     // 3. Handle File Upload (Jika Ada)
    //     $mediaUrl = null;
    //     $fileName = null;
    //     $mimeType = null;
    //     $messageType = 'text';

    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');
    //         $fileName = $file->getClientOriginalName();
    //         $mimeType = $file->getMimeType();
            
    //         // Simpan ke storage publik agar bisa diakses oleh Node.js WA Service
    //         $path = $file->storeAs('broadcast_files', time() . '_' . $fileName, 'public');
    //         $mediaUrl = asset('storage/' . $path);
            
    //         // Tentukan tipe pesan
    //         if (str_contains($mimeType, 'image')) {
    //             $messageType = 'image';
    //         } else {
    //             $messageType = 'document';
    //         }
    //     }

    //     // 4. Proses Pengiriman (Looping)
    //     $countSent = 0;
        
    //     // Ambil Gateway Aktif (Opsional: Random Load Balancing)
    //     // Jika ingin spesifik, bisa tambahkan input select gateway di form
    //     // Disini kita biarkan null agar Job memilih otomatis
    //     $gatewaySessionId = null; 

    //     foreach ($students as $student) {
    //         // Kumpulkan nomor tujuan (Ortu 1 & Ortu 2)
    //         $numbers = [];
    //         if ($student->phone) $numbers[] = $student->phone;
    //         // if ($student->phone_1) $numbers[] = $student->phone_1;
            
    //         // Hapus duplikat nomor dalam satu keluarga
    //         $numbers = array_unique($numbers);

    //         foreach ($numbers as $phone) {
    //             // Personalization (Opsional): Tambahkan sapaan nama siswa
    //             // $personalizedMsg = "Yth. Wali Murid {$student->name},\n\n" . $request->message;
                
    //             // Gunakan pesan asli saja agar bisa broadcast cepat (tanpa render string berulang)
    //             $finalMessage = $request->message;

    //             // Dispatch Job ke Antrian
    //             SendWhatsappJob::dispatch(
    //                 $phone, 
    //                 $finalMessage, 
    //                 $messageType, 
    //                 $mediaUrl, 
    //                 $fileName, 
    //                 $mimeType,
    //                 $gatewaySessionId
    //             );
                
    //             $countSent++;
    //         }
    //     }

    //     return back()->with('success', "Broadcast sedang diproses! {$countSent} pesan telah dimasukkan ke antrian pengiriman.");
    // }

    // public function sendBroadcast(Request $request)
    // {
    //     // 1. Validasi Input ditingkatkan
    //     $request->validate([
    //         'target_type'  => 'required|in:parents,teachers', // Tambahan pilihan target
    //         'classroom_id' => 'required_if:target_type,parents|nullable|exists:classrooms,id',
    //         'message'      => 'required|string',
    //         'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    //     ]);

    //     // 2. Tentukan Penerima Berdasarkan Target
    //     $recipients = [];
    //     $targetName = "";

    //     if ($request->target_type == 'teachers') {
    //         // Ambil semua User yang memiliki role guru (sesuaikan dengan logic role Anda)
    //         // Disini saya asumsikan guru ada di tabel 'users' dengan field 'phone'
    //         $teachers = \App\Models\User::where('role', 'teacher')
    //                     ->whereNotNull('phone')
    //                     ->get();
            
    //         foreach ($teachers as $teacher) {
    //             $recipients[] = ['phone' => $teacher->phone, 'name' => $teacher->name];
    //         }
    //         $targetName = "Seluruh Guru";

    //     } else {
    //         // Logika untuk Orang Tua (Eksisting)
    //         $classroom = Classroom::findOrFail($request->classroom_id);
    //         $students = Student::where('classroom_id', $classroom->id)
    //                     ->whereNotNull('phone')
    //                     ->get();
            
    //         foreach ($students as $student) {
    //             $recipients[] = ['phone' => $student->phone, 'name' => $student->name];
    //         }
    //         $targetName = "Orang Tua Kelas " . $classroom->name;
    //     }

    //     if (empty($recipients)) {
    //         return back()->with('error', "Tidak ada data nomor HP aktif untuk target {$targetName}.");
    //     }

    //     // 3. Handle File Upload (Eksisting)
    //     $mediaUrl = null; $fileName = null; $mimeType = null; $messageType = 'text';
    //     if ($request->hasFile('attachment')) {
    //         $file = $request->file('attachment');
    //         $fileName = $file->getClientOriginalName();
    //         $mimeType = $file->getMimeType();
    //         $path = $file->storeAs('broadcast_files', time() . '_' . $fileName, 'public');
    //         $mediaUrl = asset('storage/' . $path);
    //         $messageType = str_contains($mimeType, 'image') ? 'image' : 'document';
    //     }

    //     // 4. Proses Antrian
    //     $countSent = 0;
    //     $gatewaySessionId = $request->session_id; // Ambil dari input jika user memilih spesifik

    //     foreach ($recipients as $recipient) {
    //         SendWhatsappJob::dispatch(
    //             $recipient['phone'], 
    //             $request->message, 
    //             $messageType, 
    //             $mediaUrl, 
    //             $fileName, 
    //             $mimeType,
    //             $gatewaySessionId
    //         );
    //         $countSent++;
    //     }

    //     return back()->with('success', "Broadcast ke {$targetName} sedang diproses! {$countSent} pesan masuk antrian.");
    // }

    public function sendBroadcast(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'target_type'  => 'required|in:parents,teachers,manual',
            'classroom_id' => 'required_if:target_type,parents|nullable|exists:classrooms,id',
            'manual_numbers' => 'required_if:target_type,manual|nullable|string',
            'message'      => 'required|string',
            'attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $recipients = [];
        $targetName = "";

        // 2. Tentukan Penerima
        if ($request->target_type == 'manual') {
            // Pecah string nomor (koma/spasi) menjadi array
            $inputNumbers = preg_split('/[,\s]+/', $request->manual_numbers);
            foreach ($inputNumbers as $num) {
                $cleanNum = trim($num);
                if (!empty($cleanNum)) {
                    $recipients[] = ['phone' => $cleanNum];
                }
            }
            $targetName = "Nomor Manual";
        } elseif ($request->target_type == 'teachers') {
            $teachers = \App\Models\User::where('role', 'teacher')->whereNotNull('phone')->get();
            foreach ($teachers as $t) { $recipients[] = ['phone' => $t->phone]; }
            $targetName = "Seluruh Guru";
        } else {
            $classroom = Classroom::findOrFail($request->classroom_id);
            $students = Student::where('classroom_id', $classroom->id)->whereNotNull('phone')->get();
            foreach ($students as $s) { $recipients[] = ['phone' => $s->phone]; }
            $targetName = "Orang Tua Kelas " . $classroom->name;
        }

        if (empty($recipients)) {
            return back()->with('error', "Daftar penerima kosong.");
        }

        // 3. Handle File (Sama seperti sebelumnya)
        $mediaUrl = null; $fileName = null; $mimeType = null; $messageType = 'text';
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->storeAs('broadcast_files', time() . '_' . $file->getClientOriginalName(), 'public');
            $mediaUrl = asset('storage/' . $path);
            $messageType = str_contains($file->getMimeType(), 'image') ? 'image' : 'document';
        }

        // 4. Kirim ke Job
        foreach ($recipients as $recipient) {
            SendWhatsappJob::dispatch($recipient['phone'], $request->message, $messageType, $mediaUrl, null, null, $request->session_id);
        }

        return back()->with('success', "Broadcast ke {$targetName} berhasil masuk antrian.");
    }

    /**
     * API: Proses Kirim Pesan via API (Return JSON)
     * Endpoint: POST /api/whatsapp/send
     */
    public function sendApi(Request $request)
    {
        // Validasi input API
        $validator = Validator::make($request->all(), [
            'target' => 'required|numeric',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Panggil fungsi private pengirim pesan
        $result = self::sendMessageToBaileys($request->target, $request->message);

        // Return JSON Response
        if ($result['status'] == 'success') {
            return response()->json([
                'status' => 'success',
                'message' => 'Pesan berhasil dikirim',
                'data' => $result['data'] ?? null
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * PRIVATE STATIC: Fungsi Reusable untuk Kirim ke Node.js (Baileys)
     * Digunakan oleh method store() dan sendApi() untuk pengiriman langsung (bukan queue)
     */
    private static function sendMessageToBaileys($target, $message)
    {
        try {
            // Tembak ke Service Node.js (Port 3000)
            // Timeout 5 detik agar tidak loading selamanya jika bot mati
            $response = Http::timeout(5)->post('http://localhost:3000/send-message', [
                'number' => $target,
                'message' => $message,
                'type' => 'text'
            ]);

            // Cek status dari Node.js
            if ($response->successful() && isset($response['status']) && $response['status'] == 'success') {
                return ['status' => 'success', 'data' => $response->json()];
            } else {
                return ['status' => 'error', 'message' => $response['message'] ?? 'Bot tidak merespon atau error internal'];
            }

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Koneksi ke Bot Node.js Gagal (Port 3000). Pastikan service "node index.js" berjalan. Error: ' . $e->getMessage()];
        }
    }

    public function scan()
    {
        return view('admin.whatsapp_scan');
    }

    // Hapus Log Satuan (BARU)
    public function deleteLog($id)
    {
        \App\Models\WhatsappLog::findOrFail($id)->delete();
        return back()->with('success', 'Log pesan dihapus.');
    }

    // Bersihkan Semua Log (BARU)
    public function clearLogs()
    {
        \App\Models\WhatsappLog::truncate();
        return back()->with('success', 'Semua riwayat pesan dibersihkan.');
    }
}
