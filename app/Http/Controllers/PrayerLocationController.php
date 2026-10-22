<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PrayerLocation;
use Illuminate\Http\Request;

class PrayerLocationController extends Controller
{
    public function index()
    {
        $location = PrayerLocation::first();

        if (!$location) {
            $location = PrayerLocation::create([
                'name'                     => 'Masjid Sekolah',
                'latitude'                 => -0.312221,
                'longitude'                => 100.383403,
                'radius_meters'            => 10,
                'is_active'                => true,
                'is_must_at_masjid_subuh'   => false,
                'is_must_at_masjid_zuhur'   => true, // Default Zuhur wajib di Masjid
                'is_must_at_masjid_ashar'   => false,
                'is_must_at_masjid_maghrib' => false,
                'is_must_at_masjid_isya'    => false,
            ]);
        }

        return view('prayer_locations.index', compact('location'));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:255',
            'latitude'                  => 'required|numeric',
            'longitude'                 => 'required|numeric',
            'radius_meters'             => 'required|integer|min:1|max:10000',
            'is_active'                 => 'required', // Terima input 1/0
            'is_must_at_masjid_subuh'   => 'nullable|boolean',
            'is_must_at_masjid_zuhur'   => 'nullable|boolean',
            'is_must_at_masjid_ashar'   => 'nullable|boolean',
            'is_must_at_masjid_maghrib' => 'nullable|boolean',
            'is_must_at_masjid_isya'    => 'nullable|boolean',
        ]);

        // Konversi eksplisit string "1"/"0" atau "true"/"false" menjadi Boolean murni
        $isActiveBoolean = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

        // Data yang akan disimpan/diperbarui
        $dataToSave = [
            'name'                      => $validated['name'],
            'latitude'                  => $validated['latitude'],
            'longitude'                 => $validated['longitude'],
            'radius_meters'             => $validated['radius_meters'],
            'is_active'                 => $isActiveBoolean,
            'is_must_at_masjid_subuh'   => $request->has('is_must_at_masjid_subuh'),
            'is_must_at_masjid_zuhur'   => $request->has('is_must_at_masjid_zuhur'),
            'is_must_at_masjid_ashar'   => $request->has('is_must_at_masjid_ashar'),
            'is_must_at_masjid_maghrib' => $request->has('is_must_at_masjid_maghrib'),
            'is_must_at_masjid_isya'    => $request->has('is_must_at_masjid_isya'),
        ];

        $location = PrayerLocation::first();

        if ($location) {
            $location->update($dataToSave);
        } else {
            PrayerLocation::create($dataToSave);
        }

        return back()->with('success', 'Pengaturan lokasi & mode absensi berhasil diperbarui!');
    }
}