<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Inventory;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Halaman Riwayat Peminjaman Saya (Guru)
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Ambil data peminjaman milik guru tersebut
        $loans = Loan::with('inventory')
                    ->where('teacher_id', $teacher->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('loans.index', compact('loans'));
    }

    /**
     * Halaman Scanner Barcode
     */
    public function scan()
    {
        return view('loans.scan');
    }

    /**
     * Proses Simpan Peminjaman dari Scan Barcode
     */
    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:inventories,code', // Barcode = Kode Barang
        ]);

        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json(['status' => 'error', 'message' => 'Anda bukan guru terdaftar.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Cari Barang
            $item = Inventory::where('code', $request->barcode)->firstOrFail();

            // 2. Cek Stok
            if ($item->quantity < 1) {
                return response()->json(['status' => 'error', 'message' => "Stok barang '{$item->name}' habis!"], 400);
            }

            // 3. Cek apakah guru ini SEDANG meminjam barang yang sama dan BELUM dikembalikan?
            // (Opsional: aktifkan jika 1 guru cuma boleh pinjam 1 item jenis yang sama dalam satu waktu)
            /*
            $isBorrowing = Loan::where('teacher_id', $teacher->id)
                                ->where('inventory_id', $item->id)
                                ->where('status', 'borrowed')
                                ->exists();
            if ($isBorrowing) {
                return response()->json(['status' => 'error', 'message' => "Anda belum mengembalikan '{$item->name}' sebelumnya."], 400);
            }
            */

            // 4. Buat Record Peminjaman
            Loan::create([
                'teacher_id' => $teacher->id,
                'inventory_id' => $item->id,
                'borrow_date' => now(),
                'status' => 'borrowed',
                'amount' => 1,
                'notes' => 'Scan QR Mandiri'
            ]);

            // 5. Kurangi Stok Inventaris
            $item->decrement('quantity', 1);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil meminjam: {$item->name}",
                'item_name' => $item->name,
                'remaining_stock' => $item->quantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Pengembalian Barang
     */
    public function returnItem($id)
    {
        DB::beginTransaction();
        try {
            $loan = Loan::findOrFail($id);

            // Validasi: Hanya pemilik atau admin yang bisa mengembalikan (disini asumsi guru klik sendiri)
            if ($loan->status != 'borrowed') {
                return back()->with('error', 'Barang ini sudah dikembalikan.');
            }

            // Update status loan
            $loan->update([
                'status' => 'returned',
                'return_date' => now()
            ]);

            // Kembalikan Stok
            $inventory = Inventory::find($loan->inventory_id);
            if ($inventory) {
                $inventory->increment('quantity', $loan->amount);
            }

            DB::commit();
            return back()->with('success', 'Barang berhasil dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengembalikan barang.');
        }
    }
}