@extends('layouts.app') @section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Dokumen & Surat</h2>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Informasi Detail</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">Judul</th>
                            <td width="5%">:</td>
                            <td>{{ $document->judul }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>:</td>
                            <td><span class="badge bg-primary">{{ $document->kategori }}</span></td>
                        </tr>
                        <tr>
                            <th>Nomor Surat</th>
                            <td>:</td>
                            <td>{{ $document->nomor_surat ?? 'Tidak ada nomor' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($document->tanggal_dokumen)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Diunggah</th>
                            <td>:</td>
                            <td>{{ $document->created_at->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>:</td>
                            <td>{{ $document->keterangan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success w-100">
                        <i class="bi bi-download"></i> Unduh File Asli
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark">Pratinjau Dokumen</h5>
                </div>
                <div class="card-body text-center p-0 d-flex flex-column justify-content-center bg-secondary bg-opacity-10" style="min-height: 500px;">
                    
                    @php
                        // Mendapatkan ekstensi file untuk menentukan cara preview
                        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
                        // Membuat URL untuk mengakses file di storage public
                        $fileUrl = asset('storage/' . $document->file_path);
                    @endphp

                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                        
                        <div class="p-3">
                            <img src="{{ $fileUrl }}" class="img-fluid rounded shadow-sm" alt="Pratinjau Gambar" style="max-height: 700px;">
                        </div>
                        
                    @elseif(strtolower($extension) === 'pdf')
                        
                        <iframe src="{{ $fileUrl }}" width="100%" height="700px" style="border: none;"></iframe>
                        
                    @elseif(in_array(strtolower($extension), ['doc', 'docx', 'xls', 'xlsx']))
                        
                        <div class="p-5">
                            <div class="mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-file-earmark-text text-primary" viewBox="0 0 16 16">
                                  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                                  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2.5a1 1 0 0 0 1 1H13v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                                </svg>
                            </div>
                            <h5 class="text-muted">Dokumen Office (Word/Excel)</h5>
                            <p class="text-muted small">Browser tidak mendukung pratinjau langsung untuk format ini.</p>
                            
                            <a href="https://docs.google.com/viewer?url={{ urlencode($fileUrl) }}&embedded=true" target="_blank" class="btn btn-outline-primary mt-2">
                                Buka Pratinjau via Google Viewer
                            </a>
                        </div>

                    @else
                        
                        <div class="p-5 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle mb-3" viewBox="0 0 16 16">
                              <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                              <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                            </svg>
                            <p>Pratinjau tidak tersedia untuk format file ini.</p>
                        </div>
                        
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection