<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApiKeySettingController extends Controller
{
    /**
     * Helper Private untuk Ekstraksi API Key Murni (Sanitasi Hulu)
     */
    private function sanitizeAndEncryptKey(string $rawKey): string
    {
        $clean = trim(urldecode($rawKey));

        // 1. Ambil bagian setelah 'key=' jika terikut URL lengkap
        if (str_contains($clean, 'key=')) {
            $parts = explode('key=', $clean);
            $clean = end($parts);
        }

        // 2. Buang semua kurung siku, kurung biasa, spasi, dan quote
        $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $clean);

        // 3. Ekstrak string kunci murni saja (AIzaSy... / AQAb... / gsk_...)
        if (preg_match('/(AIzaSy[a-zA-Z0-9_\-]{33}|AQAb[a-zA-Z0-9_\-]{30,60}|gsk_[a-zA-Z0-9_\-]{30,60})/', $clean, $matches)) {
            $pureKey = $matches[1];
        } else {
            $pureKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $clean);
        }

        // Enkripsi key murni sebelum disimpan ke DB
        return Crypt::encryptString($pureKey);
    }

    public function index()
    {
        $apiKeys = ApiKey::orderBy('priority', 'asc')->get();
        return view('admin.settings.api_keys', compact('apiKeys'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider'   => 'required|string',
            'label_name' => 'required|string|max:255',
            'api_key'    => 'required|string',
            'priority'   => 'required|integer|min:1',
        ]);

        // Sanitasi key murni dari input form
        $pureEncryptedKey = $this->sanitizeAndEncryptKey($request->api_key);

        $apiKey = ApiKey::create([
            'provider'   => strtolower($request->provider),
            'label_name' => $request->label_name,
            'api_key'    => $pureEncryptedKey,
            'priority'   => $request->priority,
            'is_active'  => $request->has('is_active') ? true : false,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'API Key AI berhasil ditambahkan!',
                'data'    => $apiKey
            ]);
        }

        return back()->with('success', 'API Key AI berhasil ditambahkan!');
    }

    /**
     * Endpoint AJAX untuk Mengambil Data ke Modal Edit
     */
    public function edit($id)
    {
        $apiKey = ApiKey::findOrFail($id);

        // Dekripsi API Key agar tampil sebagai plain text di dalam form modal
        try {
            $apiKey->api_key_plain = Crypt::decryptString($apiKey->api_key);
        } catch (\Exception $e) {
            $apiKey->api_key_plain = $apiKey->api_key;
        }

        return response()->json([
            'status' => true,
            'data'   => $apiKey
        ]);
    }

    /**
     * Update Data via Modal AJAX / Form
     */
    public function update(Request $request, $id)
    {
        $apiKey = ApiKey::findOrFail($id);

        $request->validate([
            'provider'   => 'required|string',
            'label_name' => 'required|string|max:255',
            'priority'   => 'required|integer|min:1',
        ]);

        $data = [
            'provider'   => strtolower($request->provider),
            'label_name' => $request->label_name,
            'priority'   => $request->priority,
            'is_active'  => $request->has('is_active') || $request->is_active == '1' || $request->is_active == 'true' ? true : false,
        ];

        // Jika kolom API Key diisi saat update, lakukan sanitasi & enkripsi ulang
        if ($request->filled('api_key')) {
            $data['api_key'] = $this->sanitizeAndEncryptKey($request->api_key);
        }

        $apiKey->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'API Key AI berhasil diperbarui!',
                'data'    => $apiKey
            ]);
        }

        return back()->with('success', 'API Key AI berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'API Key AI berhasil dihapus!'
            ]);
        }

        return back()->with('success', 'API Key AI berhasil dihapus!');
    }
}