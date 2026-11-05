<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Inventory;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Halaman Riwayat Peminjaman Saya (Guru / Siswa)
     */
    public function index()
    {
        $user = Auth::user();

        $query = Loan::with(['inventory.room', 'teacher', 'student']);

        // Jika user adalah Guru
        if ($user->hasRole('guru')) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        } 
        // Jika user adalah Siswa
        elseif ($user->hasRole('siswa')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('student_id', $student->id);
            }
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('loans.index', compact('loans'));
    }

    /**
     * Form Peminjaman Alat / Bahan (Dengan Auto-Filter Bengkel PBM Siswa)
     */
    // public function create()
    // {
    //     $user = Auth::user();
    //     $student = Student::where('user_id', $user->id)->first();
        
    //     $activeRoomId = null;
    //     $activeRoomName = null;

    //     // Cek jika yang membuka form adalah Siswa
    //     if ($student) {
    //         $now = Carbon::now();
    //         $dayOfWeek = $now->isoFormat('dddd'); // Format nama hari (misal: 'Senin')
    //         $currentTime = $now->format('H:i:s');

    //         // Cari jadwal PBM berjalan milik rombel/kelas siswa saat ini
    //         $currentSchedule = Schedule::where('classroom_id', $student->classroom_id)
    //             ->where('day', $dayOfWeek)
    //             ->whereTime('start_time', '<=', $currentTime)
    //             ->whereTime('end_time', '>=', $currentTime)
    //             ->first();

    //         if ($currentSchedule && $currentSchedule->room_id) {
    //             $activeRoomId = $currentSchedule->room_id;
    //             $activeRoomName = $currentSchedule->room->name ?? null;
    //         }
    //     }

    //     $rooms = Room::all();

    //     return view('loans.create', compact('rooms', 'activeRoomId', 'activeRoomName'));
    // }

    // 1. Method untuk menampilkan halaman form peminjaman siswa
public function create()
{
    $user = Auth::user();
    $student = \App\Models\Student::where('user_id', $user->id)->first();
    
    $activeRoomId = null;
    $activeRoomName = null;

    // Cek jadwal PBM aktif siswa saat ini
    if ($student) {
        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->isoFormat('dddd'); // Contoh: 'Senin'
        $currentTime = $now->format('H:i:s');

        $currentSchedule = \App\Models\Schedule::where('classroom_id', $student->classroom_id)
            ->where('day', $dayOfWeek)
            ->whereTime('start_time', '<=', $currentTime)
            ->whereTime('end_time', '>=', $currentTime)
            ->first();

        if ($currentSchedule && $currentSchedule->room_id) {
            $activeRoomId = $currentSchedule->room_id;
            $activeRoomName = $currentSchedule->room->name ?? null;
        }
    }

    $rooms = \App\Models\Room::all();

    return view('loans.create', compact('rooms', 'activeRoomId', 'activeRoomName'));
}

// 2. Method AJAX untuk mengambil daftar alat & bahan berdasarkan bengkel
public function getItemsByRoom(Request $request)
{
    $roomId = $request->query('room_id');

    $items = \App\Models\Inventory::where('room_id', $roomId)
        ->where('quantity', '>', 0)
        ->select('id', 'code', 'name', 'type', 'quantity', 'unit', 'brand_model')
        ->orderBy('name', 'asc')
        ->get();

    return response()->json([
        'success' => true,
        'data'    => $items
    ]);
}

    /**
     * AJAX Endpoint: Ambil daftar Alat & Bahan khusus Bengkel tertentu
     */
    // public function getItemsByRoom(Request $request)
    // {
    //     $roomId = $request->query('room_id');

    //     $items = Inventory::where('room_id', $roomId)
    //         ->where('quantity', '>', 0) // Hanya barang yang stoknya masih ada
    //         ->select('id', 'code', 'name', 'type', 'quantity', 'unit', 'brand_model')
    //         ->orderBy('name', 'asc')
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $items
    //     ]);
    // }

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

        // 2. Validasi dengan Pesan Error Kustom
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|exists:inventories,code',
            'amount'  => 'nullable|numeric|min:1'
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
        $student = Student::where('user_id', $user->id)->first();

        // Validasi ketersediaan profil peminjam
        if (!$teacher && !$student) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Akun Anda tidak terdaftar sebagai Guru maupun Siswa.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 3. Cari Barang berdasarkan Kode
            $item = Inventory::where('code', $barcodeValue)->firstOrFail();
            $borrowAmount = $request->input('amount', 1);

            // 4. Cek ketersediaan Stok Fisik
            if ($item->quantity < $borrowAmount) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => "Stok barang '{$item->name}' kurang! Tersedia: {$item->quantity} {$item->unit}"
                ], 400);
            }

            // 5. Buat Record Peminjaman (Fleksibel untuk Guru / Siswa)
            $loanData = [
                'inventory_id' => $item->id,
                'borrow_date'  => now(),
                'status'       => 'borrowed',
                'amount'       => $borrowAmount,
                'notes'        => $request->input('notes', 'Peminjaman Alat/Bahan Praktikum')
            ];

            if ($teacher) {
                $loanData['teacher_id'] = $teacher->id;
            } else {
                $loanData['student_id'] = $student->id;
            }

            $loan = Loan::create($loanData);

            // 6. Kurangi Stok Inventaris
            $item->decrement('quantity', $borrowAmount);

            DB::commit();

            return response()->json([
                'status'          => 'success',
                'message'         => "Berhasil meminjam: {$item->name} ({$borrowAmount} {$item->unit})",
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
    // public function returnItem($id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $loan = Loan::findOrFail($id);

    //         if ($loan->status != 'borrowed') {
    //             return back()->with('error', 'Barang ini sudah dikembalikan.');
    //         }

    //         // Update status loan
    //         $loan->update([
    //             'status'      => 'returned',
    //             'return_date' => now()
    //         ]);

    //         // Kembalikan Stok ke Inventaris
    //         $inventory = Inventory::find($loan->inventory_id);
    //         if ($inventory) {
    //             $inventory->increment('quantity', $loan->amount);
    //         }

    //         DB::commit();
    //         return back()->with('success', 'Barang berhasil dikembalikan.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Gagal mengembalikan barang.');
    //     }
    // }

    /**
 * Cetak Bukti Struk Peminjaman
 */
public function printReceipt($id)
{
    $loan = Loan::with(['inventory.room', 'teacher', 'student.classroom'])->findOrFail($id);

    return view('loans.print_receipt', compact('loan'));
}

/**
 * Cetak Struk Bukti Pengembalian Barang
 */
public function printReturnReceipt($id)
{
    $loan = Loan::with(['inventory.room', 'teacher', 'student.classroom'])->findOrFail($id);

    // Pastikan hanya transaksi yang sudah dikembalikan yang bisa dicetak
    if ($loan->status !== 'returned') {
        return back()->with('error', 'Barang belum dikembalikan, struk pengembalian tidak dapat dicetak.');
    }

    return view('loans.print_return_receipt', compact('loan'));
}

/**
 * Proses Pengembalian Barang dengan Otorisasi Role
 */
public function returnItem($id)
{
    $user = Auth::user();
    $loan = Loan::findOrFail($id);

    if ($loan->status != 'borrowed') {
        return back()->with('error', 'Barang ini sudah dikembalikan sebelumnya.');
    }

    // --- CEK HAK AKSES OTORISASI ---
    $canReturn = false;

    // 1. Super Admin, Admin, Kabeng, Kaprog, dan Toolman bisa mengembalikan barang SIAPA SAJA
    if ($user->hasAnyRole(['super_admin', 'admin', 'kabeng', 'kaprog', 'toolman'])) {
        $canReturn = true;
    }
    // 2. Guru hanya bisa mengembalikan barang yang dipinjam oleh dirinya sendiri
    elseif ($user->hasRole('guru')) {
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        if ($teacher && $loan->teacher_id === $teacher->id) {
            $canReturn = true;
        }
    }
    // 3. Siswa hanya bisa mengembalikan barang yang dipinjam oleh dirinya sendiri
    elseif ($user->hasRole('siswa')) {
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if ($student && $loan->student_id === $student->id) {
            $canReturn = true;
        }
    }

    if (!$canReturn) {
        return back()->with('error', 'Anda tidak memiliki hak akses untuk memproses pengembalian barang ini.');
    }

    // --- PROSES PENGEMBALIAN & PENAMBAHAN STOK ---
    DB::beginTransaction();
    try {
        $loan->update([
            'status'      => 'returned',
            'return_date' => now()
        ]);

        $inventory = Inventory::find($loan->inventory_id);
        if ($inventory) {
            $inventory->increment('quantity', $loan->amount);
        }

        DB::commit();
        return back()->with('success', 'Barang berhasil dikembalikan dan stok inventaris telah diperbarui.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan sistem saat mengembalikan barang.');
    }
}
}