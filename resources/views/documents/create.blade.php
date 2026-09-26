@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Upload Dokumen / Surat Baru</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Judul / Nama Dokumen <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control" required placeholder="Misal: Undangan Rapat Komite">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Surat Masuk">Surat Masuk</option>
                    <option value="Surat Keluar">Surat Keluar</option>
                    <option value="SK">SK (Surat Keputusan)</option>
                    <option value="Jobsheet/Modul">Jobsheet / Modul Ajar</option>
                    <option value="Laporan">Laporan Program Kerja</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nomor Surat</label>
                <input type="text" name="nomor_surat" class="form-control" placeholder="Kosongkan jika bukan surat resmi">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Dokumen <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_dokumen" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload File <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
            <small class="text-muted">Format didukung: PDF, Word, Excel, JPG, PNG (Maks 5MB).</small>
        </div>

        <div class="mb-4">
            <label class="form-label">Keterangan Singkat</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Upload Dokumen</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection