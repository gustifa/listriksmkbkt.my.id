<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

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

        ApiKey::create([
            'provider'   => strtolower($request->provider),
            'label_name' => $request->label_name,
            'api_key'    => $request->api_key,
            'priority'   => $request->priority,
            'is_active'  => $request->has('is_active') ? true : false,
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
            'is_active'  => $request->has('is_active') ? true : false,
        ];

        // Jika kolom API Key diisi baru, update
        if ($request->filled('api_key')) {
            $data['api_key'] = $request->api_key;
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

        return back()->with('success', 'Status API Key berhasil diubah!');
    }
}