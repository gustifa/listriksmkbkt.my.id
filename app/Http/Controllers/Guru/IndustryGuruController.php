<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Internship;
use App\Models\Industry;
use Illuminate\Support\Facades\Auth;

class IndustryGuruController extends Controller
{
    /**
     * Tampilkan daftar Industri yang dibimbing oleh Guru ini
     */
    public function index()
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        // Cari ID Industri dari siswa yang dibimbing guru ini
        $industryIds = Internship::where('advisor_id', $teacher->id)
            ->whereIn('status', ['active', 'completed'])
            ->pluck('industry_id')
            ->unique();

        $industries = Industry::whereIn('id', $industryIds)->get();

        return view('guru.industries.index', compact('industries'));
    }

    /**
     * Guru update lokasi Industri
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:10',
        ]);

        // Pastikan guru ini memang membimbing siswa di industri tersebut (Security)
        $teacher = Teacher::where('user_id', Auth::id())->first();
        $isAdvisor = Internship::where('advisor_id', $teacher->id)
            ->where('industry_id', $id)
            ->exists();

        if (!$isAdvisor) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk industri ini.');
        }

        $industry = Industry::findOrFail($id);
        $industry->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
        ]);

        return back()->with('success', 'Lokasi PKL berhasil diperbarui.');
    }
}
