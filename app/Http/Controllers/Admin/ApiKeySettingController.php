<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Exception;

class ApiKeySettingController extends Controller
{
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

        // Simpan langsung teks asli plain-text tanpa enkripsi
        ApiKey::create([
            'provider'   => strtolower($request->provider),
            'label_name' => $request->label_name,
            'api_key'    => trim($request->api_key),
            'is_active'  => $request->has('is_active') ? true : false,
            'priority'   => $request->priority ?? 1,
        ]);

        return back()->with('success', 'API Key AI berhasil ditambahkan!');
    }

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
            $data['api_key'] = trim($request->api_key);
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