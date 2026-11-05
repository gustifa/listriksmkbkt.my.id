<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDF; // Jika menggunakan DomPDF
use App\Exports\InventoriesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventoriesImport;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Tampilkan Halaman Utama Pengelolaan Inventaris
     */
    public function index(Request $request)
    {
        $rooms = Room::where('type', 'bengkel')->orderBy('name', 'asc')->get();
        return view('inventory.index', compact('rooms'));
    }

    /**
     * AJAX Endpoint: Datatable / Fetch Data Inventaris Dinamis
     */
    public function getData(Request $request)
    {
        $query = Inventory::with('room');

        // Filter berdasarkan Ruang / Bengkel
        if ($request->has('room_id') && !empty($request->room_id)) {
            $query->where('room_id', $request->room_id);
        }

        // Filter berdasarkan Jenis (Alat / Bahan)
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Search Keyword (Nama / Kode / Brand)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%")
                  ->orWhere('brand_model', 'ILIKE', "%{$search}%")
                  ->orWhere('category', 'ILIKE', "%{$search}%");
            });
        }

        $inventories = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($inventories);
    }

    /**
     * AJAX Endpoint: Simpan Data Inventaris Baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'             => 'required|string|max:50|unique:inventories,code',
            'name'             => 'required|string|max:255',
            'room_id'          => 'required|exists:rooms,id',
            'type'             => 'required|in:alat,bahan',
            'category'         => 'required|string|max:100',
            'brand_model'      => 'nullable|string|max:255',
            'quantity'         => 'required|integer|min:0',
            'min_quantity'     => 'required|integer|min:0',
            'unit'             => 'required|string|max:50',
            'condition'        => 'required|in:baik,rusak_ringan,rusak_berat',
            'funding_source'   => 'required|in:BOS,DAK,APBD_SUMBAR,APBN,HIBAH',
            'acquisition_year' => 'nullable|integer|digits:4',
            'description'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $inventory = Inventory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Data inventaris berhasil ditambahkan!',
                'data'    => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX Endpoint: Fetch Data Single untuk Edit Modal
     */
    public function show($id)
    {
        $inventory = Inventory::with('room')->find($id);

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
        }

        return response()->json(['success' => true, 'data' => $inventory]);
    }

    /**
     * AJAX Endpoint: Update Data Inventaris
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan!'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code'             => 'required|string|max:50|unique:inventories,code,' . $id,
            'name'             => 'required|string|max:255',
            'room_id'          => 'required|exists:rooms,id',
            'type'             => 'required|in:alat,bahan',
            'category'         => 'required|string|max:100',
            'brand_model'      => 'nullable|string|max:255',
            'quantity'         => 'required|integer|min:0',
            'min_quantity'     => 'required|integer|min:0',
            'unit'             => 'required|string|max:50',
            'condition'        => 'required|in:baik,rusak_ringan,rusak_berat',
            'funding_source'   => 'required|in:BOS,DAK,APBD_SUMBAR,APBN,HIBAH',
            'acquisition_year' => 'nullable|integer|digits:4',
            'description'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $inventory->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Data inventaris berhasil diperbarui!',
                'data'    => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX Endpoint: Hapus Inventaris
     */
    public function destroy($id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus dari inventaris.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Generate & Cetak Laporan Inventaris Berdasarkan Ruangan (KIR)
 */
public function printByRoom(Request $request, $roomId)
{
    $room = Room::findOrFail($roomId);
    
    // Ambil semua barang di ruangan tersebut
    $inventories = Inventory::where('room_id', $roomId)
                            ->orderBy('type', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();

    // Data Statistik Singkat
    $totalAlat  = $inventories->where('type', 'alat')->count();
    $totalBahan = $inventories->where('type', 'bahan')->count();

    // Render tampilan cetak / PDF
    $pdf = PDF::loadView('inventory.pdf_kir', compact('room', 'inventories', 'totalAlat', 'totalBahan'))
              ->setPaper('a4', 'landscape');

    return $pdf->stream("KIR_{$room->code}_{$room->name}.pdf");
}

    public function exportExcel(Request $request)
    {
        $roomId = $request->query('room_id');
        $filename = 'Rekapitulasi_Stok_Inventaris_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new InventoriesExport($roomId), $filename);
    }

    /**
 * Download Template Excel untuk Import Barang
 */
public function downloadTemplate()
{
    $filepath = public_path('templates/Template_Import_Inventaris.xlsx');
    
    // Jika belum ada file fisik, bisa langsung kembalikan response array/file
    return response()->download($filepath, 'Template_Import_Inventaris_Bengkel.xlsx');
}

/**
 * Simpan Data Import Excel via AJAX
 */
public function importExcel(Request $request)
{
    $request->validate([
        'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
    ]);

    try {
        Excel::import(new InventoriesImport, $request->file('file_excel'));

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengimpor data inventaris baru dari Excel!'
        ]);
    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $errorMsg = 'Gagal import di baris ke-' . $failures[0]->row() . ': ' . $failures[0]->errors()[0];
        
        return response()->json(['success' => false, 'message' => $errorMsg], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function getTransactions($id)
{
    $inventory = Inventory::findOrFail($id);
    
    // Ambil semua transaksi berdasarkan item_id
    $transactions = InventoryTransaction::with('user')
                        ->where('item_id', $id)
                        ->orderBy('created_at', 'desc')
                        ->get();

    return response()->json([
        'success'      => true,
        'item_name'    => $inventory->name . ' (' . $inventory->code . ')',
        'transactions' => $transactions
    ]);
}
public function transfer(Request $request, $id)
{
    $request->validate([
        'target_room_id' => 'required|exists:rooms,id',
        'quantity'       => 'required|integer|min:1',
        'notes'          => 'nullable|string',
    ]);

    $sourceInventory = Inventory::with('room')->findOrFail($id);
    $transferQty = (int) $request->quantity;

    if ($transferQty > $sourceInventory->quantity) {
        return response()->json([
            'success' => false,
            'message' => "Stok tidak mencukupi! Stok tersedia hanya {$sourceInventory->quantity} {$sourceInventory->unit}."
        ], 422);
    }

    $targetRoom = Room::findOrFail($request->target_room_id);

    if ($sourceInventory->room_id == $targetRoom->id) {
        return response()->json([
            'success' => false,
            'message' => 'Ruangan tujuan mutasi tidak boleh sama dengan ruangan asal!'
        ], 422);
    }

    DB::transaction(function () use ($sourceInventory, $targetRoom, $transferQty, $request) {
        $sourceRoomName = $sourceInventory->room ? $sourceInventory->room->name : 'Ruangan Asal';

        // 1. JIKA SELURUH STOK DIPINDAHKAN
        if ($transferQty === $sourceInventory->quantity) {
            // Cukup ubah room_id barang ini ke ruangan tujuan
            $sourceInventory->update([
                'room_id' => $targetRoom->id
            ]);

            $targetInventoryId = $sourceInventory->id;
        } else {
            // 2. JIKA SEBAGIAN STOK DIPINDAHKAN
            // Kurangi stok ruangan asal
            $sourceInventory->decrement('quantity', $transferQty);

            // Cek apakah barang serupa sudah ada di ruangan tujuan (berdasarkan nama & spesifikasi)
            $targetInventory = Inventory::where('name', $sourceInventory->name)
                ->where('room_id', $targetRoom->id)
                ->first();

            if ($targetInventory) {
                // Tambahkan stok ke barang yang sudah ada di ruangan tujuan
                $targetInventory->increment('quantity', $transferQty);
                $targetInventoryId = $targetInventory->id;
            } else {
                // Buat record baru di ruangan tujuan dengan kode unik baru (ditambahkan suffix/random)
                $newInventory = $sourceInventory->replicate();
                $newInventory->room_id = $targetRoom->id;
                $newInventory->quantity = $transferQty;
                
                // Buat kode unik baru agar tidak bentrok dengan constraint unique
                $newInventory->code = $sourceInventory->code . '-M' . rand(10, 99); 
                $newInventory->save();

                $targetInventoryId = $newInventory->id;
            }
        }

        // 3. Catat Transaksi KELUAR dari Ruangan Asal
        InventoryTransaction::create([
            'item_id'        => $sourceInventory->id,
            'user_id'        => Auth::id() ?? null,
            'type'           => 'out',
            'quantity'       => $transferQty,
            'date'           => date('Y-m-d'),
            'notes'          => "Mutasi barang dipindahkan ke {$targetRoom->name}. " . ($request->notes ?? ''),
            'funding_source' => $sourceInventory->funding_source,
            'year'           => date('Y'),
        ]);

        // 4. Catat Transaksi MASUK ke Ruangan Tujuan
        InventoryTransaction::create([
            'item_id'        => $targetInventoryId,
            'user_id'        => Auth::id() ?? null,
            'type'           => 'in',
            'quantity'       => $transferQty,
            'date'           => date('Y-m-d'),
            'notes'          => "Mutasi barang diterima dari {$sourceRoomName}. " . ($request->notes ?? ''),
            'funding_source' => $sourceInventory->funding_source,
            'year'           => date('Y'),
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => "Berhasil memindahkan {$transferQty} {$sourceInventory->unit} ke {$targetRoom->name}."
    ]);
}

// app/Http/Controllers/InventoryController.php

public function generateCode(Request $request)
{
    $type = $request->query('type', 'alat'); // 'alat' atau 'bahan'
    $roomId = $request->query('room_id');
    $year = date('Y');

    // Prefix tipe barang
    $prefixType = ($type === 'bahan') ? 'BHP' : 'ALT';

    // Kode/Singkatan Ruangan (jika dipilih)
    $roomCode = 'GEN';
    if ($roomId) {
        $room = Room::find($roomId);
        if ($room) {
            // Mengambil kode ruangan atau 3 huruf pertama nama ruangan
            $roomCode = $room->code ?? strtoupper(substr(str_replace(' ', '', $room->name), 0, 3));
        }
    }

    $prefix = "{$prefixType}-{$roomCode}-{$year}-";

    // Cari urutan angka terbesar dengan prefix tersebut
    $latest = Inventory::where('code', 'LIKE', "{$prefix}%")
        ->orderBy('code', 'desc')
        ->first();

    if ($latest) {
        // Ambil 4 digit terakhir dan tambahkan 1
        $lastNum = (int) substr($latest->code, -4);
        $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNum = '0001';
    }

    return response()->json([
        'success' => true,
        'code'    => $prefix . $nextNum
    ]);
}


}