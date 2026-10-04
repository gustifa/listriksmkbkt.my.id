<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    /**
     * Menampilkan daftar Jam Pelajaran.
     */
    public function index()
    {
        $timeSlots = TimeSlot::orderBy('sort_order', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('admin.time_slots.index', compact('timeSlots'));
    }

    /**
     * Menyimpan data Jam Pelajaran baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label'      => 'required|string|max:255',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'type'       => 'required|in:lesson,break',
            'sort_order' => 'nullable|integer',
        ]);

        TimeSlot::create([
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'type'       => $request->type,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Jam Pelajaran berhasil ditambahkan!');
    }

    /**
     * Memperbarui data Jam Pelajaran.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'label'      => 'required|string|max:255',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'type'       => 'required|in:lesson,break',
            'sort_order' => 'nullable|integer',
        ]);

        $timeSlot = TimeSlot::findOrFail($id);
        $timeSlot->update([
            'label'      => $request->label,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'type'       => $request->type,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Jam Pelajaran berhasil diperbarui!');
    }

    /**
     * Menghapus data Jam Pelajaran.
     */
    public function destroy($id)
    {
        $timeSlot = TimeSlot::findOrFail($id);
        $timeSlot->delete();

        return redirect()->back()->with('success', 'Jam Pelajaran berhasil dihapus!');
    }
}