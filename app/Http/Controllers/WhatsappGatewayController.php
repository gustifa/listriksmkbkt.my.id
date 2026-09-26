<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappGateway;
use Illuminate\Support\Facades\Http;

class WhatsappGatewayController extends Controller
{
    // List Semua Gateway
    public function index()
    {
        // Sinkronisasi status dari Node.js ke DB saat halaman dibuka
        try {
            $response = Http::timeout(3)->get('http://localhost:3000/sessions');
            if ($response->successful()) {
                $sessions = $response->json();
                foreach ($sessions as $s) {
                    WhatsappGateway::where('session_id', $s['session_id'])->update([
                        'status' => $s['status'],
                        'number' => $s['phone'] ?? null
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Node.js mati atau tidak bisa dijangkau, abaikan agar halaman tetap loading
        }

        $gateways = WhatsappGateway::all();
        return view('admin.whatsapp.index', compact('gateways'));
    }

    // Tambah Gateway Baru
    public function store(Request $request)
    {
        // Tidak ada validasi request karena semua data digenerate otomatis
        
        $count = WhatsappGateway::count() + 1;
        $sessionId = 'gateway_' . $count . '_' . time();

        // Register ke Node.js
        try {
            Http::post('http://localhost:3000/session/start', ['session_id' => $sessionId]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal koneksi ke WA Service Node.js. Pastikan service berjalan.');
        }

        WhatsappGateway::create([
            'session_id' => $sessionId,
            'name' => 'Gateway #' . $count,
            'status' => 'scan_needed'
        ]);

        return back()->with('success', 'Gateway baru ditambahkan. Silakan Scan QR.');
    }

    // Hapus Gateway
    public function destroy($id)
    {
        $gateway = WhatsappGateway::findOrFail($id);
        
        // Logout di Node.js
        try {
            Http::post('http://localhost:3000/session/logout', ['session_id' => $gateway->session_id]);
        } catch (\Exception $e) {}

        $gateway->delete();
        return back()->with('success', 'Gateway dihapus.');
    }

    // Halaman Scan QR per Gateway
    public function scan($id)
    {
        $gateway = WhatsappGateway::findOrFail($id);
        return view('admin.whatsapp.scan', compact('gateway'));
    }

    // Halaman Form Kirim Pesan
    public function send()
    {
        $gateways = WhatsappGateway::all();
        return view('admin.whatsapp.send', compact('gateways'));
    }

    // Proses Kirim Pesan
    public function sendProcess(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'message' => 'required',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $targets = explode(',', $request->target);
        $message = $request->message;
        $sessionId = $request->session_id;

        // Handle Media Upload if exists
        $mediaUrl = null;
        $type = 'text';
        
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $path = $file->store('whatsapp_media', 'public');
            $mediaUrl = asset('storage/' . $path);
            $type = in_array($file->extension(), ['pdf']) ? 'document' : 'image';
        }

        foreach ($targets as $number) {
            $number = trim($number);
            if(empty($number)) continue;

            // Dispatch Job
            \App\Jobs\SendWhatsappJob::dispatch(
                $number, 
                $message, 
                $type, 
                $mediaUrl, 
                null, 
                null, 
                $sessionId
            );
        }

        return back()->with('success', 'Pesan sedang diproses dalam antrian.');
    }
}