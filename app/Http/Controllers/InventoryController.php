<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Room;
use App\Models\Setting;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Menampilkan daftar inventaris
     * Bisa difilter berdasarkan ruangan (bengkel)
     */
    public function index(Request $request)
    {
        $query = Inventory::with('room')->orderBy('name', 'asc');

        // Filter berdasarkan Ruangan/Bengkel
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $inventories = $query->paginate(20)->withQueryString();

        // Ambil semua ruangan untuk dropdown filter & modal
        $rooms = Room::orderBy('name')->get();

        return view('inventory.index', compact('inventories', 'rooms'));
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|unique:inventories,code',
            'category'=> 'required|in:alat,bahan,mesin',
            'quantity'=> 'required|integer|min:0',
            'condition'=> 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        Inventory::create($request->all());

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke inventaris.');
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|unique:inventories,code,' . $id,
            'category'=> 'required|in:alat,bahan,mesin',
            'quantity'=> 'required|integer|min:0',
            'condition'=> 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        $inventory->update($request->all());

        return redirect()->back()->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();

        return redirect()->back()->with('success', 'Barang dihapus dari inventaris.');
    }

    /**
     * FITUR BARU: CETAK BARCODE / QR CODE
     * Menangani error "Call to undefined method"
     */
    // public function printBarcode($id)
    // {
    //     $inventory = Inventory::with('room')->findOrFail($id);

        // Ambil nama sekolah dari setting, default ke 'SMK TEKNIK' jika tidak ada
        // Pastikan model Setting sudah ada dan method value() tersedia (biasanya scope atau static helper)
        // Jika error di Setting::value, ganti dengan Setting::where('key', 'school_name')->value('value')
    //     $schoolName = Setting::value('school_name', 'SMK TEKNIK');

    //     return view('inventory.barcode', compact('inventory', 'schoolName'));
    // }

    /**
     * FITUR BARU: CETAK BARCODE / QR CODE
     * Menggunakan jumlah stok sebagai default jumlah cetak
     */
    public function printBarcode(Request $request, $id)
    {
        $inventory = Inventory::with('room')->findOrFail($id);

        $schoolName = Setting::value('school_name', 'SMK TEKNIK');

        // Ambil jumlah cetak dari parameter 'qty' di URL,
        // Jika tidak ada, default ke jumlah stok barang saat ini ($inventory->quantity)
        // Jika stok 0, default cetak 1 label master
        $defaultQty = $inventory->quantity > 0 ? $inventory->quantity : 1;
        $printQty = $request->input('qty', $defaultQty);

        // Batasi maksimal cetak sekaligus (misal 50) untuk mencegah browser hang
        $printQty = min($printQty, 50);

        return view('inventory.barcode', compact('inventory', 'schoolName', 'printQty'));
    }


}
