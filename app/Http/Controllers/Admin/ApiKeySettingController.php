<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeySettingController extends Controller
{
    /**
     * Sanitasi ketat untuk mengambil string API key murni saja (Tanpa Enkripsi)
     */
    // private function sanitizeKey(string $rawKey): string
    // {
    //     $clean = trim(urldecode($rawKey));

    //     // 1. Ambil bagian setelah 'key=' jika terikut URL
    //     if (str_contains($clean, 'key=')) {
    //         $parts = explode('key=', $clean);
    //         $clean = end($parts);
    //     }

    //     // 2. Buang karakter markdown, kurung siku, quote, dan spasi
    //     $clean = str_replace(['[', ']', '(', ')', '{', '}', '"', "'", ' ', '%5B', '%5D', '%5b', '%5d'], '', $clean);

    //     // 3. Ekstrak string kunci murni (Mendukung AIzaSy..., AQ.Ab..., AQAb..., gsk_...)
    //     if (preg_match('/(AIzaSy[a-zA-Z0-9_\-\.]{30,80}|AQ\.[a-zA-Z0-9_\-\.]{30,80}|AQAb[a-zA-Z0-9_\-\.]{30,80}|gsk_[a-zA-Z0-9_\-\.]{30,80})/', $clean, $matches)) {
    //         return $matches[1];
    //     }

    //     return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);
    // }

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

        // $pureKey = $this->sanitizeKey($request->api_key);

        // $apiKey = ApiKey::create([
        //     'provider'   => strtolower($request->provider),
        //     'label_name' => $request->label_name,
        //     'api_key'    => $request->api_key, // Simpan Murni Plain-Text
        //     'priority'   => $request->priority,
        //     'is_active'  => $request->has('is_active') ? true : false,
        // ]);

                ApiKey::create([
                    'id'          => (string) Str::uuid(),
                    'provider'    => $request->provider,
                    'label_name'  => $request->label_name ?? 'Gemini Key',
                    'api_key'     => $request->api_key, // <-- Simpan langsung teks aslinya (plain text)
                    'is_active'   => $request->has('is_active') ? true : false,
                    'priority'    => $request->priority ?? 1,
                ]);

        return back()->with('success', 'API Key AI berhasil ditambahkan!');
    }

//     public function store(Request $request)
// {
//     $request->validate([
//         'api_key' => 'required|string',
//         'provider' => 'required|string',
//         // Validasi lainnya...
//     ]);

//     $rawKey = $request->input('api_key');
    
//     // --- BERSIHKAN KUNCI SEBELUM DISIMPAN KE DATABASE ---
//     $raw = urldecode((string) $rawKey);
    
//     if (str_contains($raw, 'key=')) {
//         $parts = explode('key=', $raw);
//         $pureKey = trim(end($parts));
//         $pureKey = rtrim($pureKey, ')]>}%5D%5d');
//     } else {
//         $clean = preg_replace('/\[.*?\]\(.*?\)/', '', $raw);
//         $clean = preg_replace('/https?:\/\/[^\s]+/', '', $clean);
//         $pureKey = trim(str_replace([
//             ']', ')', '[', '(', ' ', '"', "'", 
//             '%5B', '%5D', '%5b', '%5d', '%28', '%29'
//         ], '', $clean));
//     }
//     // ---------------------------------------------------

//     // Simpan data yang sudah bersih ke database
//     ApiKey::create([
//         'id'        => (string) Str::uuid(),
//         'provider'  => $request->provider,
//         'label_name' => $request->label_name,
//         'api_key'   => $pureKey, // Disimpan dalam keadaan murni
//         'is_active' => $request->has('is_active') ? true : false,
//         'priority'  => $request->priority ?? 1,
//     ]);

//     return redirect()->back()->with('success', 'Kunci API berhasil disimpan secara bersih!');
// }

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
            'is_active'  => $request->has('is_active') || $request->is_active == '1' ? true : false,
        ];

        if ($request->filled('api_key')) {
            $data['api_key'] = $this->sanitizeKey($request->api_key);
        }

        $apiKey->update($data);

        return back()->with('success', 'API Key AI berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return back()->with('success', 'API Key AI berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return back()->with('success', 'Status API Key berhasil diperbarui!');
    }
}