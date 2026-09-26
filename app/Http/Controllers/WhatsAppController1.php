<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

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

        // Panggil fungsi private pengirim pesan
        $result = $this->sendMessageToBaileys($request->target, $request->message);

        if ($result['status'] == 'success') {
            return redirect()->back()->with('success', 'Pesan berhasil dikirim via WhatsApp!');
        } else {
            return redirect()->back()->with('error', 'Gagal kirim: ' . $result['message']);
        }
    }

    /**
     * API: Proses Kirim Pesan via API (Return JSON)
     * Endpoint: POST /api/whatsapp/send (jika diaktifkan di api.php)
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
        $result = $this->sendMessageToBaileys($request->target, $request->message);

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
     * PRIVATE: Fungsi Reusable untuk Kirim ke Node.js (Baileys)
     * Digunakan oleh method store() dan sendApi()
     */
    private function sendMessageToBaileys($target, $message)
    {
        try {
            // Tembak ke Service Node.js (Port 3000)
            // Timeout 5 detik agar tidak loading selamanya jika bot mati
            $response = Http::timeout(5)->post('http://localhost:3000/send-message', [
                'number' => $target,
                'message' => $message,
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
}
