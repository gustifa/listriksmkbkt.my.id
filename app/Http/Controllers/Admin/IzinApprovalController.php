<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinSiswa;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class IzinApprovalController extends Controller
{
    /**
     * Menampilkan daftar pengajuan izin dan sakit siswa
     */
    public function index(Request $request)
    {
        $query = IzinSiswa::with(['student.classroom'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $izinList = $query->paginate(15);

        return view('admin.izin.index', compact('izinList'));
    }

    /**
     * Setujui Pengajuan Izin / Sakit (Mendukung AJAX & Direct Form)
     */
    public function approve(Request $request, $id, TelegramService $telegram)
    {
        try {
            $izin = IzinSiswa::findOrFail($id);
            $approverName = Auth::user()->name ?? 'Petugas Sekolah';

            $izin->update([
                'status'      => 'approved',
                'approved_by' => $approverName,
                'approved_at' => now(),
            ]);

            if ($izin->telegram_chat_id) {
                $studentName = optional($izin->student)->name ?? 'Siswa';
                $typeText    = strtoupper($izin->type);
                $msg = "🎉 *PENGAJUAN {$typeText} DISETUJUI!*\n\n" .
                       "Pengajuan untuk *{$studentName}* pada tanggal *{$izin->date}* telah disetujui oleh *{$approverName}*.";
                $telegram->sendMessage($izin->telegram_chat_id, $msg);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Pengajuan izin berhasil disetujui.',
                    'approved_by' => $approverName
                ]);
            }

            return redirect()->back()->with('success', 'Pengajuan izin berhasil disetujui.');
        } catch (\Exception $e) {
            Log::error("Error Approve Izin: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal memproses persetujuan izin.');
        }
    }

    /**
     * Tolak / Batalkan Pengajuan Izin (Mendukung AJAX & Direct Form)
     */
    public function reject(Request $request, $id, TelegramService $telegram)
    {
        try {
            $izin = IzinSiswa::findOrFail($id);
            $approverName = Auth::user()->name ?? 'Petugas Sekolah';
            $userChat     = $izin->telegram_chat_id;
            $studentName  = optional($izin->student)->name ?? 'Siswa';
            $typeText     = strtoupper($izin->type);
            $date         = $izin->date;

            if ($userChat) {
                $msg = "❌ *PENGAJUAN {$typeText} DIBATALKAN / DITOLAK*\n\n" .
                       "Pengajuan untuk *{$studentName}* pada tanggal *{$date}* telah dibatalkan oleh *{$approverName}*. Catatan telah dihapus dari sistem.";
                $telegram->sendMessage($userChat, $msg);
            }

            if ($izin->attachment_path && Storage::disk('public')->exists($izin->attachment_path)) {
                Storage::disk('public')->delete($izin->attachment_path);
            }

            $izin->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan izin berhasil ditolak dan dihapus.'
                ]);
            }

            return redirect()->back()->with('success', 'Pengajuan izin berhasil dibatalkan dan dihapus.');
        } catch (\Exception $e) {
            Log::error("Error Reject Izin: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal menolak pengajuan.');
        }
    }

    /**
     * Hapus Data Pengajuan Izin Siswa secara Permanen (Mendukung AJAX & Direct Form)
     */
    public function destroy(Request $request, $id, TelegramService $telegram)
    {
        try {
            $izin = IzinSiswa::findOrFail($id);
            $userChat    = $izin->telegram_chat_id;
            $studentName = optional($izin->student)->name ?? 'Siswa';
            $date        = $izin->date;

            if ($izin->attachment_path && Storage::disk('public')->exists($izin->attachment_path)) {
                Storage::disk('public')->delete($izin->attachment_path);
            }

            if ($userChat) {
                $msg = "🗑️ *DATA PENGAJUAN DIHAPUS*\n\n" .
                       "Data pengajuan izin/sakit untuk *{$studentName}* pada tanggal *{$date}* telah dihapus oleh Petugas/Admin Sekolah.";
                $telegram->sendMessage($userChat, $msg);
            }

            $izin->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengajuan izin siswa berhasil dihapus secara permanen.'
                ]);
            }

            return redirect()->back()->with('success', 'Data pengajuan izin siswa berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            Log::error("Error Destroy Izin: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Cetak Surat Permohonan Izin / Keterangan Orang Tua
     */
    public function cetakSuratIzin($id)
    {
        $izin = IzinSiswa::with(['student.classroom'])->findOrFail($id);

        return view('admin.izin.cetak_izin', compact('izin'));
    }

    /**
     * Cetak Lembar Lampiran Surat Dokter / Keterangan Sakit
     */
    public function cetakSuratSakit($id)
    {
        $izin = IzinSiswa::with(['student.classroom'])->findOrFail($id);

        return view('admin.izin.cetak_sakit', compact('izin'));
    }
}