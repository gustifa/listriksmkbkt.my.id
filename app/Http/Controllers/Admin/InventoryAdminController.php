<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\InventoryTransaction;
use App\Imports\ItemImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; // <--- TAMBAHKAN BARIS INI
use Illuminate\Support\Facades\Log; // <--- TAMBAHKAN BARIS INI
use App\Exports\InventoryTemplateExport;


class InventoryAdminController extends Controller
{
    /**
     * Menampilkan daftar barang dan stok saat ini
     */
    public function index(Request $request)
    {
        // $query = Item::query();

        // if ($request->has('search') && $request->filled('search')) {
        //     $query->where('name', 'like', '%' . $request->search . '%')
        //           ->orWhere('code', 'like', '%' . $request->search . '%');
        // }

        $query = Item::latest();

        // // Logika Pencarian
        // if ($request->has('search') && $request->filled('search') && $request->search != '') {
        //     $search = $request->search;
        //     $query->where(function($q) use ($search) {
        //         $q->where('name', 'like', '%' . $search . '%')
        //         ->orWhere('code', 'like', '%' . $search . '%');
        //     });
        // }

        // 1. Logika Pencarian (Mendukung Kapital & Huruf Kecil)
        if ($request->has('search') && $request->search != '') {
            // Ubah kata kunci pencarian menjadi huruf kecil semua
            $search = strtolower($request->search);

            $query->where(function($q) use ($search) {
                // Gunakan LOWER() untuk mengubah data di tabel menjadi huruf kecil saat dicocokkan
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                ->orWhereRaw('LOWER(code) LIKE ?', ['%' . $search . '%']);
            });
        }

        $items = $query->latest()->paginate(10)->appends($request->all());
        // --- LOGIKA GENERATE KODE OTOMATIS ---
        $lastItem = Item::orderBy('code', 'desc')->first();
        if ($lastItem) {
            // Ambil angka dari kode terakhir (misal BRG-0005 diambil 5)
            $lastNumber = (int) substr($lastItem->code, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        // Format menjadi BRG-000X
        $autoCode = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('admin.inventory.index', compact('items', 'autoCode'));
    }

    /**
     * Menyimpan master barang baru
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:items,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'initial_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Buat master barang
            $item = Item::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->initial_stock,
                'unit' => $request->unit,
                'year' => $request->year,
                'funding_source'    => $request->funding_source,
            ]);

            // 2. Jika stok awal > 0, catat sebagai barang masuk pertama
            if ($request->initial_stock > 0) {
                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $request->initial_stock,
                    'date' => now(),
                    'notes' => 'Stok Awal',
                ]);
            }
        });

        return redirect()->route('admin.inventory.index')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    /**
     * Mencatat transaksi Barang Masuk atau Keluar
     */
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|exists:items,id',
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'date'     => 'required|date',
            'notes'    => 'nullable|string',
        ]);

        $item = Item::findOrFail($request->item_id);

        // Validasi khusus untuk barang keluar (stok tidak boleh minus)
        if ($request->type === 'out' && $item->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi! Stok saat ini: ' . $item->stock]);
        }

        DB::transaction(function () use ($request, $item) {
            // 1. Catat riwayat transaksi
            InventoryTransaction::create([
                'item_id'  => $item->id,
                'user_id'  => Auth::id(),
                'type'     => $request->type,
                'quantity' => $request->quantity,
                'date'     => $request->date,
                'notes'    => $request->notes,
                'funding_source'    => $request->funding_source,
                'year'    => $request->year,
                'receiver'    => $request->receiver,
            ]);

            // 2. Update total stok di tabel master barang
            if ($request->type === 'in') {
                $item->increment('stock', $request->quantity);
            } else {
                $item->decrement('stock', $request->quantity);
            }
        });

        $tipe = $request->type === 'in' ? 'Masuk' : 'Keluar';
        return redirect()->route('admin.inventory.index')->with('success', "Transaksi Barang $tipe berhasil dicatat!");
    }

    /**
     * Menampilkan riwayat mutasi/transaksi barang
     */
    public function history(Request $request)
    {
        $transactions = InventoryTransaction::with(['item', 'user'])
                        ->latest('date')
                        ->latest('created_at')
                        ->paginate(15);

        return view('admin.inventory.history', compact('transactions'));
    }

        /**
     * Memproses Import Excel Master Barang
     */
    public function import(Request $request)
    {
            $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = new ItemImport;
        Excel::import($import, $request->file('file'));

        return redirect()->back()->with('success',
            $import->importedCount . " barang baru berhasil diimport. " .
            ($import->skippedCount > 0 ? $import->skippedCount . " data dilewati karena kode sudah ada." : "")
        );
    }

    /**
     * Download Template Excel
     */
    // public function downloadTemplate()
    // {
    //     $headers = [
    //         'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
    //         'Content-type'        => 'text/csv',
    //         'Content-Disposition' => 'attachment; filename=Template_Import_Barang.csv',
    //         'Expires'             => '0',
    //         'Pragma'              => 'public'
    //     ];

    //     $columns = ['kode', 'nama_barang', 'satuan', 'deskripsi', 'stok_awal', 'sumber_dana', 'tahun_anggaran'];

    //     $callback = function() use ($columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);
    //         // Contoh isi
    //         fputcsv($file, ['INV-001', 'Laptop Asus Core i5', 'Unit', 'Warna Hitam', '5', 'Dana BOS', '2024']);
    //         fputcsv($file, ['INV-002', 'Kertas HVS A4', 'Rim', 'Sinar Dunia 80gr', '20', 'Dana Yayasan', '2024']);
    //         fclose($file);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }
    // Di InventoryController.php
    public function downloadTemplate()
    {
        return Excel::download(new InventoryTemplateExport, 'template_inventaris_titl.xlsx');
    }

     // Menampilkan riwayat transaksi
    public function transactions()
    {
        $transactions = InventoryTransaction::with('item')->latest()->get();
        return view('inventory.transactions', compact('transactions'));
    }

    // Form transaksi masuk/keluar
    public function createTransaction()
    {
        $items = Item::all();
        return view('inventory.create_transaction', compact('items'));
    }


}
