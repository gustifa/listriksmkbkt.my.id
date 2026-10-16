<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function index()
    {
        return view('admin.holidays.index');
    }

    // Mengambil data kalender untuk FullCalendar
    public function getEvents()
    {
        $holidays = Holiday::all();
        $events = [];

        foreach ($holidays as $holiday) {
            // Tentukan warna berdasarkan tipe atau status aktif
            $color = '#dc3545'; // Merah bawaan untuk libur nasional
            if (!$holiday->is_active) {
                $color = '#6c757d'; // Abu-abu jika dinonaktifkan
            } elseif ($holiday->type === 'sekolah') {
                $color = '#fd7e14'; // Oranye untuk libur sekolah
            } elseif ($holiday->type === 'khusus') {
                $color = '#0d6efd'; // Biru untuk kegiatan khusus
            }

            // Pastikan format tanggal murni String YYYY-MM-DD
            $startDate = Carbon::parse($holiday->start_date)->format('Y-m-d');
            // FullCalendar memerlukan end date +1 hari agar hari terakhir terblokir sempurna
            $endDate = Carbon::parse($holiday->end_date)->addDay()->format('Y-m-d');

            $events[] = [
                'id'          => (string) $holiday->id, // UUID
                'title'       => $holiday->title . ($holiday->is_active ? '' : ' (Nonaktif)'),
                'start'       => $startDate,
                'end'         => $endDate,
                'allDay'      => true,
                'description' => $holiday->description ?? '',
                'color'       => $color,
                'extendedProps' => [
                    'type'      => $holiday->type,
                    'is_active' => $holiday->is_active,
                ]
            ];
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'type'        => 'required|in:nasional,sekolah,khusus',
            'description' => 'nullable|string|max:500',
        ], [
            'title.required'          => 'Nama libur/kegiatan wajib diisi.',
            'start_date.required'     => 'Tanggal mulai wajib diisi.',
            'end_date.required'       => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'type.required'           => 'Tipe libur wajib dipilih.',
            'type.in'                 => 'Pilihan tipe libur tidak valid.',
        ]);

        // Tangkap status switch active secara eksplisit boolean (true/false)
        $validated['is_active'] = $request->has('is_active');

        $holiday = Holiday::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Hari libur berhasil ditambahkan!',
            'data'    => $holiday
        ]);
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'type'        => 'required|in:nasional,sekolah,khusus',
            'description' => 'nullable|string|max:500',
        ], [
            'title.required'          => 'Nama libur/kegiatan wajib diisi.',
            'start_date.required'     => 'Tanggal mulai wajib diisi.',
            'end_date.required'       => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'type.required'           => 'Tipe libur wajib dipilih.',
            'type.in'                 => 'Pilihan tipe libur tidak valid.',
        ]);

        // Tangkap status switch active secara eksplisit boolean (true/false)
        $validated['is_active'] = $request->has('is_active');

        $holiday->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Hari libur berhasil diperbarui!',
            'data'    => $holiday
        ]);
    }

    // Ambil detail 1 data libur berdasarkan UUID (untuk form Edit)
    public function show($id)
    {
        $holiday = Holiday::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data'   => $holiday
        ]);
    }

    

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id); // Pencarian berdasarkan UUID
        $holiday->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Hari libur berhasil dihapus!'
        ]);
    }
}