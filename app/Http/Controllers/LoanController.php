<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Inventory;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
     * Proses Simpan Peminjaman dari Scan / Manual Input Barcode
     */
    public function store(Request $request)
    {
        // 1. Ambil input barcode baik dari key 'barcode' maupun 'code'
        $barcodeValue = $request->input('barcode') ?? $request->input('code');

        // Merge ke request agar mudah divalidasi
        $request->merge(['barcode' => $barcodeValue]);

        // 2. Validasi dengan Pesan Error Kustom (Mencegah teks 'validation.exists')
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|exists:inventories,code',
        ], [
            'barcode.required' => 'Kode barang / Barcode wajib diisi atau di-scan.',
            'barcode.exists'   => "Kode barang '{$barcodeValue}' tidak ditemukan dalam inventaris sekolah."
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first('barcode')
            ], 422);
        }

        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Akun Anda tidak terdaftar sebagai Guru.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 3. Cari Barang berdasarkan Kode
            $item = Inventory::where('code', $barcodeValue)->firstOrFail();

            // 4. Cek Stok Fisik
            if ($item->quantity < 1) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => "Stok barang '{$item->name}' saat ini habis!"
                ], 400);
            }

            // 5. Buat Record Peminjaman
            $loan = Loan::create([
                'teacher_id'   => $teacher->id,
                'inventory_id' => $item->id,
                'borrow_date'  => now(),
                'status'       => 'borrowed',
                'amount'       => 1,
                'notes'        => 'Scan Barcode Mandiri'
            ]);

            // 6. Kurangi Stok Inventaris
            $item->decrement('quantity', 1);

            DB::commit();

            return response()->json([
                'status'          => 'success',
                'message'         => "Berhasil meminjam: {$item->name}",
                'item_name'       => $item->name,
                'remaining_stock' => $item->quantity
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error', 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
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

            if ($loan->status != 'borrowed') {
                return back()->with('error', 'Barang ini sudah dikembalikan.');
            }

            // Update status loan
            $loan->update([
                'status'      => 'returned',
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