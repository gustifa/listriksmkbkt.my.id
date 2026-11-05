<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryLoan;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDF; // Barry

class InventoryLoanController extends Controller
{
    // public function index()
    // {
    //     $inventories = Inventory::where('quantity', '>', 0)->get();
    //     $students = Student::orderBy('name', 'asc')->get();
        
    //     return view('inventory_loans.index', compact('inventories', 'students'));
    // }
    public function index()
{
    // Hanya ambil ruangan yang bertipe 'bengkel'
    $rooms = \App\Models\Room::where('type', 'bengkel')
                              ->orderBy('name', 'asc')
                              ->get();

    $students = \App\Models\Student::orderBy('name', 'asc')->get();
    
    return view('inventory_loans.index', compact('rooms', 'students'));
}

    public function storeBatch(Request $request)
    {
        // Validasi Array Items
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'notes'      => 'nullable|string|max:500',
            'items'      => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventories,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $student = Student::findOrFail($request->student_id);
            $updatedStocks = [];

            foreach ($request->items as $itemData) {
                $inventory = Inventory::findOrFail($itemData['id']);
                $qtyRequested = (int) $itemData['qty'];

                // Check Stok
                if ($qtyRequested > $inventory->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok barang '{$inventory->name}' tidak mencukupi (Tersedia: {$inventory->quantity} {$inventory->unit})."
                    ], 400);
                }

                $isBahan  = ($inventory->type === 'bahan');
                $loanType = $isBahan ? 'pemakaian_bahan' : 'peminjaman_alat';
                $status   = $isBahan ? 'habis_pakai' : 'dipinjam';

                // 1. Simpan Loan Record
                InventoryLoan::create([
                    'inventory_id'  => $inventory->id,
                    'student_id'    => $student->id,
                    'borrower_name' => $student->name,
                    'quantity'      => $qtyRequested,
                    'loan_date'     => now(),
                    'loan_type'     => $loanType,
                    'status'        => $status,
                    'notes'         => $request->notes,
                ]);

                // 2. Potong Stok
                $inventory->decrement('quantity', $qtyRequested);

                // Data stok terbaru untuk update tampilan UI
                $updatedStocks[] = [
                    'id' => $inventory->id,
                    'new_stock' => $inventory->quantity,
                    'name' => $inventory->name,
                    'unit' => $inventory->unit,
                    'type' => $inventory->type
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua alat & bahan berhasil diproses!',
                'updated_stocks' => $updatedStocks
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInventoriesByRoom($roomId)
{
    try {
        // Ambil barang berdasarkan room_id yang memiliki stok > 0
        $inventories = Inventory::where('room_id', $roomId)
                                ->where('quantity', '>', 0)
                                ->get(['id', 'name', 'type', 'quantity', 'unit']);

        return response()->json($inventories, 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil data: ' . $e->getMessage()
        ], 500);
    }
}

public function printBAST(Request $request)
{
    $studentId = $request->query('student_id');
    $date      = $request->query('date', date('Y-m-d'));

    $student = Student::findOrFail($studentId);

    // Ambil daftar barang yang dipinjam/dipakai siswa pada tanggal tersebut
    $loans = InventoryLoan::with(['inventory', 'inventory.room'])
                          ->where('student_id', $studentId)
                          ->whereDate('loan_date', $date)
                          ->get();

    if ($loans->isEmpty()) {
        return back()->with('error', 'Tidak ada riwayat peminjaman ditemukan pada tanggal tersebut.');
    }

    $pdf = PDF::loadView('inventory_loans.pdf_bast', compact('student', 'loans', 'date'))
              ->setPaper('a4', 'portrait');

    return $pdf->stream("BAST_Peminjaman_{$student->nis}_{$date}.pdf");
}
}