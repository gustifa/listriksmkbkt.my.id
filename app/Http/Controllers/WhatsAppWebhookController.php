<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsAppSubscriber;
use App\Models\Student;
use App\Services\Attendance\StudentAttendanceService;
use Illuminate\Support\Facades\Http;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request, StudentAttendanceService $attendanceService)
    {
        $sender = $request->input('sender'); // Nomor WA Pengirim (misal: 628123456789)
        $message = $request->input('message');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $subscriber = WhatsAppSubscriber::where('phone_number', $sender)->first();

        // Jika Pengirim Mengirimkan Lokasi (Live Location / Share Location)
        if ($latitude && $longitude) {
            if (!$subscriber || !$subscriber->student_id) {
                $this->sendWAMessage($sender, "⚠️ Nomor WhatsApp Anda belum terhubung dengan akun Siswa.");
                return response()->json(['status' => 'ok']);
            }

            $student = Student::find($subscriber->student_id);
            $result = $attendanceService->processSelfAttendance($student, (float)$latitude, (float)$longitude);

            $this->sendWAMessage($sender, $result['message']);
            return response()->json(['status' => 'ok']);
        }

        // Response Command Teks Biasa (misal: /absen)
        if (strtolower(trim($message)) === '/absen' || strtolower(trim($message)) === 'absen') {
            $this->sendWAMessage($sender, "📍 Silakan gunakan fitur *'Bagikan Lokasi / Share Location'* di WhatsApp Anda untuk melakukan absensi masuk.");
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendWAMessage(string $target, string $message)
    {
        // Contoh API Fonnte / Provider WA
        Http::withHeaders([
            'Authorization' => env('FONNTE_TOKEN'),
        ])->post('https://api.fonnte.com/send', [
            'target'  => $target,
            'message' => $message,
        ]);
    }
}