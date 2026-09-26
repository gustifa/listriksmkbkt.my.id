<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileSignatureController extends Controller
{
    /**
     * Menampilkan halaman form tanda tangan
     */
    public function edit()
    {
        $user = Auth::user();
        $signature = null;

        // ASUMSI: Anda memiliki field 'role' di tabel users ('guru' atau 'siswa')
        // Dan memiliki relasi $user->teacher atau $user->student
        if ($user->jenis_user === 'guru' && $user->teacher) {
            $signature = $user->teacher->signature;
        } elseif ($user->jenis_user === 'siswa' && $user->student) {
            $signature = $user->student->signature;
        }

        return view('profile.signature', compact('signature', 'user'));
    }

    /**
     * Memproses dan menyimpan tanda tangan baru
     */
    public function update(Request $request)
    {
        $request->validate([
            'signature_base64' => 'required|string',
        ], [
            'signature_base64.required' => 'Tanda tangan tidak boleh kosong. Silakan coret pada area yang disediakan.'
        ]);

        $user = Auth::user();
        $base64Image = $request->signature_base64;

        // Decode Base64 menjadi file gambar
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, dll
            $base64Image = str_replace(' ', '+', $base64Image);
            $base64Image = base64_decode($base64Image);

            // Tentukan prefix nama file berdasarkan role
            $prefix = $user->jenis_user === 'guru' ? 'guru' : 'siswa';
            $fileName = 'signatures/' . $prefix . '_' . $user->id . '_' . time() . '_' . Str::random(5) . '.' . $type;
            
            // Simpan gambar ke storage public
            Storage::disk('public')->put($fileName, $base64Image);

            // Hapus file tanda tangan lama jika ada (Opsional agar storage tidak penuh)
            $oldSignature = null;
            if ($user->jenis_user === 'guru' && $user->teacher) {
                $oldSignature = $user->teacher->signature;
                $user->teacher->update(['signature' => $fileName]);
            } elseif ($user->jenis_user === 'siswa' && $user->student) {
                $oldSignature = $user->student->signature;
                $user->student->update(['signature' => $fileName]);
            }

            if ($oldSignature && Storage::disk('public')->exists($oldSignature)) {
                Storage::disk('public')->delete($oldSignature);
            }

            return back()->with('success', 'Tanda tangan berhasil diperbarui!');
        }

        return back()->with('error', 'Format tanda tangan tidak valid.');
    }
}