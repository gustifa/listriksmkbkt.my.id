<?php

namespace App\Http\Controllers;

use App\Models\ExternalExaminer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExternalExaminerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'position'     => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'logo'         => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // Max 2MB
        ]);

        $data = $request->only(['name', 'company_name', 'position', 'phone', 'address']);

        // Upload File Logo jika ada
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('dudi_logos', 'public');
            $data['logo'] = $path;
        }

        ExternalExaminer::create($data);

        return back()->with('success', 'Data Penguji Eksternal & Logo DUDI berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $examiner = ExternalExaminer::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'logo'         => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $data = $request->only(['name', 'company_name', 'position', 'phone', 'address']);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($examiner->logo && Storage::disk('public')->exists($examiner->logo)) {
                Storage::disk('public')->delete($examiner->logo);
            }
            $data['logo'] = $request->file('logo')->store('dudi_logos', 'public');
        }

        $examiner->update($data);

        return back()->with('success', 'Data Penguji Eksternal berhasil diperbarui.');
    }
}