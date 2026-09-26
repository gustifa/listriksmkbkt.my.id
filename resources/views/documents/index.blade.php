@extends('layouts.app') @section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Arsip Dokumen & Surat</h2>
        <a href="{{ route('documents.create') }}" class="btn btn-primary shadow-sm">
            Upload Dokumen Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="12%">Kategori</th>
                            <th width="15%">Nomor Surat</th>
                            <th>Judul Dokumen</th>
                            <th width="20%">Keterangan</th>
                            <th class="text-center" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $doc)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($doc->tanggal_dokumen)->translatedFormat('d M Y') }}</td>
                            <td>
                                @if($doc->kategori == 'Surat Masuk')
                                    <span class="badge bg-success">{{ $doc->kategori }}</span>
                                @elseif($doc->kategori == 'Surat Keluar')
                                    <span class="badge bg-warning text-dark">{{ $doc->kategori }}</span>
                                @elseif($doc->kategori == 'SK')
                                    <span class="badge bg-danger">{{ $doc->kategori }}</span>
                                @elseif($doc->kategori == 'Jobsheet/Modul')
                                    <span class="badge bg-info text-dark">{{ $doc->kategori }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $doc->kategori }}</span>
                                @endif
                            </td>
                            <td>{{ $doc->nomor_surat ?? '-' }}</td>
                            <td><strong>{{ $doc->judul }}</strong></td>
                            <td>
                                {{ \Illuminate\Support\Str::limit($doc->keterangan ?? '-', 40) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-sm btn-info text-white" title="Lihat Pratinjau">
                                        Lihat
                                    </a>
                                    
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-success" title="Unduh File">
                                        Unduh
                                    </a>
                                    
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dokumen ini secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Dokumen">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <em>Belum ada arsip dokumen atau surat yang diupload.</em>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection