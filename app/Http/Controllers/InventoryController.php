<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Room;
use App\Models\Setting;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Menampilkan daftar inventaris
     * Bisa difilter berdasarkan ruangan (bengkel) & kategori
     */
    // public function index(Request $request)
    // {
    //     $query = Inventory::with('room')->orderBy('name', 'asc');

    //     // Filter berdasarkan Ruangan/Bengkel
    //     if ($request->filled('room_id')) {
    //         $query->where('room_id', $request->room_id);
    //     }

    //     // Filter Kategori
    //     if ($request->filled('category')) {
    //         $query->where('category', $request->category);
    //     }

    //     $inventories = $query->paginate(20)->withQueryString();

    //     // Ambil semua ruangan untuk dropdown filter & modal
    //     $rooms = Room::orderBy('name')->get();

    //     return view('inventory.index', compact('inventories', 'rooms'));
    // }

    public function index(Request $request)
{
    $query = Inventory::with('room')->orderBy('name', 'asc');

    if ($request->filled('room_id')) {
        $query->where('room_id', $request->room_id);
    }

    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    // Ubah nama variabel penampung menjadi $items
    $items = $query->paginate(20)->withQueryString();

    $rooms = Room::orderBy('name')->get();

    // Passing $items ke view
    return view('inventory.index', compact('items', 'rooms'));
}

    /**
     * Simpan Data Baru (Mendukung AJAX & Direct Request)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:100|unique:inventories,code',
            'category'  => 'required|in:alat,bahan,mesin',
            'quantity'  => 'required|integer|min:0',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'unit'      => 'nullable|string|max:20',
        ]);

        try {
            $inventory = Inventory::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Barang berhasil ditambahkan ke inventaris.',
                    'data'    => $inventory
                ], 200);
            }

            return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke inventaris.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Update Data (Mendukung AJAX & Direct Request)
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $validated = $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:100|unique:inventories,code,' . $id,
            'category'  => 'required|in:alat,bahan,mesin',
            'quantity'  => 'required|integer|min:0',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'unit'      => 'nullable|string|max:20',
        ]);

        try {
            $inventory->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data barang berhasil diperbarui.',
                    'data'    => $inventory
                ], 200);
            }

            return redirect()->back()->with('success', 'Data barang berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Data (Mendukung AJAX & Cek Relasi)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);

            // Cek apakah barang memiliki riwayat peminjaman aktif
            if (method_exists($inventory, 'loans') && $inventory->loans()->where('status', 'dipinjam')->exists()) {
                $msg = 'Barang tidak dapat dihapus karena sedang dalam status dipinjam!';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $inventory->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Barang berhasil dihapus dari inventaris.'
                ], 200);
            }

            return redirect()->back()->with('success', 'Barang berhasil dihapus dari inventaris.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus barang: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    /**
     * FITUR: CETAK BARCODE / QR CODE
     */
    public function printBarcode(Request $request, $id)
    {
        $inventory = Inventory::with('room')->findOrFail($id);

        $schoolName = Setting::value('school_name', 'SMK TEKNIK');

        // Jumlah cetak berdasarkan parameter URL 'qty', default ke ketersediaan stok
        $defaultQty = $inventory->quantity > 0 ? $inventory->quantity : 1;
        $printQty = (int) $request->input('qty', $defaultQty);

        // Batasi maksimal cetak sekaligus (50 pcs) agar browser tidak hang
        $printQty = min(max($printQty, 1), 50);

        return view('inventory.barcode', compact('inventory', 'schoolName', 'printQty'));
    }
}