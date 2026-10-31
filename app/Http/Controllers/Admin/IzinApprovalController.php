<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinSiswa;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Setujui Pengajuan Izin / Sakit
     */
    public function approve(Request $request, $id, TelegramService $telegram)
    {
        $izin = IzinSiswa::findOrFail($id);
        $approverName = Auth::user()->name ?? 'Admin SISFO';

        $izin->update([
            'status'      => 'approved',
            'approved_by' => $approverName,
            'approved_at' => now(),
        ]);

        // Notifikasi ke Telegram Siswa / Orang Tua jika terhubung
        if ($izin->telegram_chat_id) {
            $studentName = optional($izin->student)->name ?? 'Siswa';
            $typeText    = strtoupper($izin->type);
            $msg = "🎉 *PENGAJUAN {$typeText} DISETUJUI!*\n\n" .
                   "Pengajuan untuk *{$studentName}* pada tanggal *{$izin->date}* telah disetujui oleh *{$approverName}*.";
            $telegram->sendMessage($izin->telegram_chat_id, $msg);
        }

        return redirect()->back()->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    /**
     * Tolak / Batalkan Pengajuan Izin
     */
    public function reject(Request $request, $id, TelegramService $telegram)
    {
        $izin = IzinSiswa::findOrFail($id);
        $approverName = Auth::user()->name ?? 'Admin SISFO';
        $userChat     = $izin->telegram_chat_id;
        $studentName  = optional($izin->student)->name ?? 'Siswa';
        $typeText     = strtoupper($izin->type);
        $date         = $izin->date;

        // Notifikasi ke Telegram Siswa
        if ($userChat) {
            $msg = "❌ *PENGAJUAN {$typeText} DIBATALKAN / DITOLAK*\n\n" .
                   "Pengajuan untuk *{$studentName}* pada tanggal *{$date}* telah dibatalkan oleh *{$approverName}*. Catatan telah dihapus dari sistem.";
            $telegram->sendMessage($userChat, $msg);
        }

        $izin->delete();

        return redirect()->back()->with('success', 'Pengajuan izin berhasil dibatalkan dan dihapus.');
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