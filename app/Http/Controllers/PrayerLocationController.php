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
                'name'          => 'Masjid Sekolah',
                'latitude'      => -0.312221,
                'longitude'     => 100.383403,
                'radius_meters' => 10,
                'is_active'     => true,
            ]);
        }

        return view('prayer_locations.index', compact('location'));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'radius_meters' => 'required|integer|min:1|max:10000',
            'is_active'     => 'required', // Terima input 1/0
        ]);

        // Konversi eksplisit string "1"/"0" atau "true"/"false" menjadi Boolean murni
        $isActiveBoolean = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

        $location = PrayerLocation::first();

        if ($location) {
            $location->update([
                'name'          => $validated['name'],
                'latitude'      => $validated['latitude'],
                'longitude'     => $validated['longitude'],
                'radius_meters' => $validated['radius_meters'],
                'is_active'     => $isActiveBoolean, // Boolean eksplisit
            ]);
        } else {
            PrayerLocation::create([
                'name'          => $validated['name'],
                'latitude'      => $validated['latitude'],
                'longitude'     => $validated['longitude'],
                'radius_meters' => $validated['radius_meters'],
                'is_active'     => $isActiveBoolean,
            ]);
        }

        return back()->with('success', 'Pengaturan lokasi & mode absensi berhasil diperbarui!');
    }
}