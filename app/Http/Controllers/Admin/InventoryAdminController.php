<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Room;
use App\Models\InventoryTransaction;
use App\Imports\ItemImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryTemplateExport;

class InventoryAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::latest();

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);

            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(code) LIKE ?', ['%' . $search . '%']);
            });
        }

        $items = $query->paginate(10)->appends($request->all());

        // Logika Generate Kode Otomatis
        $lastItem = Inventory::orderBy('code', 'desc')->first();
        if ($lastItem && strlen($lastItem->code) >= 4) {
            $lastNumber = (int) substr($lastItem->code, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $autoCode = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('admin.inventory.index', compact('items', 'autoCode'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|unique:inventories,code',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'initial_stock' => 'required|integer|min:0',
        ], [
            'code.unique'   => 'Kode barang sudah terdaftar pada master Inventaris!'
        ]);

        DB::transaction(function () use ($request) {
            $defaultRoomId = Room::value('id');

            // 1. Simpan murni ke tabel inventories
            $inventory = Inventory::create([
                'room_id'        => $request->input('room_id', $defaultRoomId),
                'code'           => $request->code,
                'name'           => $request->name,
                'description'    => $request->description,
                'quantity'       => $request->initial_stock,
                'unit'           => $request->unit ?? 'pcs',
                'category'       => $request->input('category', 'alat'),
                'condition'      => $request->input('condition', 'baik'),
                'year'           => $request->year,
                'funding_source' => $request->funding_source,
            ]);

            // 2. Catat transaksi stok awal
            if ($request->initial_stock > 0) {
                InventoryTransaction::create([
                    'item_id'  => $inventory->id, // Langsung FK ke inventories.id
                    'user_id'  => Auth::id(),
                    'type'     => 'in',
                    'quantity' => $request->initial_stock,
                    'date'     => now(),
                    'notes'    => 'Stok Awal',
                ]);
            }
        });

        return redirect()->route('admin.inventory.index')->with('success', 'Barang baru berhasil ditambahkan ke Inventaris!');
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'item_id'  => 'required|exists:inventories,id',
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'date'     => 'required|date',
            'notes'    => 'nullable|string',
        ]);

        $item = Inventory::findOrFail($request->item_id);

        if ($request->type === 'out' && $item->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi! Stok saat ini: ' . $item->quantity]);
        }

        DB::transaction(function () use ($request, $item) {
            // 1. Catat transaksi
            InventoryTransaction::create([
                'item_id'        => $item->id,
                'user_id'        => Auth::id(),
                'type'           => $request->type,
                'quantity'       => $request->quantity,
                'date'           => $request->date,
                'notes'          => $request->notes,
                'funding_source' => $request->funding_source,
                'year'           => $request->year,
                'receiver'       => $request->receiver,
            ]);

            // 2. Update stok di tabel inventories
            if ($request->type === 'in') {
                $item->increment('quantity', $request->quantity);
            } else {
                $item->decrement('quantity', $request->quantity);
            }
        });

        $tipe = $request->type === 'in' ? 'Masuk' : 'Keluar';
        return redirect()->route('admin.inventory.index')->with('success', "Transaksi Barang $tipe berhasil dicatat!");
    }

    public function history(Request $request)
    {
        $transactions = InventoryTransaction::with(['inventory', 'user'])
                        ->latest('date')
                        ->latest('created_at')
                        ->paginate(15);

        return view('admin.inventory.history', compact('transactions'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $import = new ItemImport;
        Excel::import($import, $request->file('file'));

        return redirect()->back()->with('success',
            $import->importedCount . " barang baru berhasil diimport. " .
            ($import->skippedCount > 0 ? $import->skippedCount . " data dilewati." : "")
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(new InventoryTemplateExport, 'template_inventaris_titl.xlsx');
    }

    public function transactions()
    {
        $transactions = InventoryTransaction::with('inventory')->latest()->get();
        return view('inventory.transactions', compact('transactions'));
    }

    public function createTransaction()
    {
        $items = Inventory::all();
        return view('inventory.create_transaction', compact('items'));
    }
}