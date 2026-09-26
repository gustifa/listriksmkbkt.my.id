<?php

namespace App\Http\Controllers;

use App\Models\InventoryLoan;
use App\Models\Inventory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryLoanController extends Controller
{
    /**
     * Simpan Peminjaman Baru dengan Validasi Stok
     */
    public function store(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'borrower_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'loan_date' => 'required|date',
        ]);

        $item = Inventory::findOrFail($request->inventory_id);

        // --- VALIDASI STOK BERJALAN ---

        // 1. Hitung total barang yang sedang dipinjam (Status 'dipinjam')
        $borrowedQty = InventoryLoan::where('inventory_id', $item->id)
                        ->where('status', 'dipinjam')
                        ->sum('quantity');

        // 2. Hitung sisa stok yang tersedia di lemari/gudang
        $availableQty = $item->quantity - $borrowedQty;

        // 3. Cek apakah permintaan melebihi sisa stok
        if ($request->quantity > $availableQty) {
            // Kembalikan error ke halaman sebelumnya
            return redirect()->back()
                ->withErrors(['quantity' => "Stok tidak mencukupi! Total: {$item->quantity}, Dipinjam: {$borrowedQty}, Tersedia: {$availableQty}"])
                ->withInput(); // Kembalikan input user agar tidak perlu ketik ulang
        }

        // --- SIMPAN DATA JIKA VALID ---

        InventoryLoan::create([
            'inventory_id' => $request->inventory_id,
            'borrower_name' => $request->borrower_name,
            'quantity' => $request->quantity,
            'loan_date' => $request->loan_date,
            'status' => 'dipinjam',
            'notes' => $request->input('notes')
        ]);

        return redirect()->back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    /**
     * Proses Pengembalian Barang
     */
    public function returnItem(Request $request, $id)
    {
        $loan = InventoryLoan::findOrFail($id);

        $returnNote = $request->input('notes');

        $finalNotes = $loan->notes;

        if (!empty($returnNote)) {
            $timestamp = Carbon::now()->format('d/m H:i');
            $appendString = "Kembali ($timestamp): " . $returnNote;

            if (!empty($finalNotes)) {
                $finalNotes .= " | " . $appendString;
            } else {
                $finalNotes = $appendString;
            }
        }

        $loan->update([
            'status' => 'kembali',
            'return_date' => Carbon::now(),
            'notes' => $finalNotes
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Barang berhasil dikembalikan.']);
        }

        return redirect()->back()->with('success', 'Barang berhasil dikembalikan.');
    }

    /**
     * API: Ambil Daftar Riwayat Peminjaman (Aktif & Selesai)
     */
    public function activeLoans()
    {
        $loans = InventoryLoan::with('inventory')
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

        return response()->json($loans);
    }

    /**
     * Cetak Bukti Peminjaman / Pengembalian (PDF)
     */
    public function printProof($id)
    {
        $loan = InventoryLoan::with(['inventory.room'])->findOrFail($id);
        $school = $this->getSchoolData();

        $pdf = Pdf::loadView('pdf.inventory_loan_proof', compact('loan', 'school'));
        $pdf->setPaper('a5', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'chroot' => public_path(),
        ]);

        return $pdf->stream('Bukti_Peminjaman_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $loan->borrower_name) . '.pdf');
    }

    private function imageToBase64($path) {
        if (!$path || $path == '-') return null;
        $cleanPath = str_replace('storage/', '', $path);

        $pathsToCheck = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($path)
        ];

        foreach ($pathsToCheck as $fullPath) {
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return null;
    }

    private function getSchoolData()
    {
        return [
            'school_name'    => Setting::value('school_name', 'SMK DEFAULT'),
            'school_address' => Setting::value('school_address', 'Alamat Sekolah'),
            'school_phone'   => Setting::value('school_phone', '-'),
            'school_web'     => Setting::value('school_web', '-'),
            'school_email'   => Setting::value('school_email', '-'),
            'logo_left'      => $this->imageToBase64(Setting::value('logo_left')),
            'logo_right'     => $this->imageToBase64(Setting::value('logo_right')),
            'sign_city'      => Setting::value('signature_city', 'Jakarta'),
            'petugas_name'   => 'Petugas Bengkel'
        ];
    }
}
