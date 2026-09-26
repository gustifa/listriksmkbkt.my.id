<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->get();
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'nomor_surat'     => 'nullable|string|max:100',
            'kategori'        => 'required|in:Surat Masuk,Surat Keluar,SK,Jobsheet/Modul,Laporan,Lainnya',
            'tanggal_dokumen' => 'required|date',
            'file'            => 'required|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:5120', // Maks 5MB
            'keterangan'      => 'nullable|string'
        ]);

        // Proses upload file ke storage/app/public/arsip_dokumen
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('arsip_dokumen', $fileName, 'public');

        Document::create([
            'judul'           => $request->judul,
            'nomor_surat'     => $request->nomor_surat,
            'kategori'        => $request->kategori,
            'tanggal_dokumen' => $request->tanggal_dokumen,
            'file_path'       => $filePath,
            'keterangan'      => $request->keterangan
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diupload.');
    }

    // Menampilkan detail dan pratinjau (preview) dokumen
    public function show($id)
    {
        $document = Document::findOrFail($id);
        return view('documents.show', compact('document'));
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $filePath = storage_path('app/public/' . $document->file_path);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        return back()->with('error', 'File tidak ditemukan di server.');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        
        // Hapus file fisik dari storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}
