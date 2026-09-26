<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    /**
     * Tampilkan daftar ruangan
     */
    public function index()
    {
        $rooms = Room::orderBy('name', 'asc')->get();
        return view('rooms.index', compact('rooms'));
    }

    /**
     * Simpan ruangan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:rooms,code',
            'type' => 'required|in:teori,labor,bengkel,lapangan,lainnya',
            'capacity' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
        ]);

        Room::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan!');
    }

    /**
     * Update ruangan
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:rooms,code,' . $room->id,
            'type' => 'required|in:teori,labor,bengkel,lapangan,lainnya',
            'capacity' => 'nullable|integer',
        ]);

        $room->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Data ruangan berhasil diperbarui!');
    }

    /**
     * Hapus ruangan
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
