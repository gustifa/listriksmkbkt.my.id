@extends('layouts.app')

@section('title', 'Detail Materi - ' . $material->title)

@section('content')
<div class="container-fluid py-3 px-2 px-md-3">
    <!-- Header Navigation & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                &larr; Kembali ke Daftar Materi
            </a>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-md-3">{{ $material->title }}</h3>
                <span class="badge bg-primary text-white fw-bold">
                    {{ $material->subject->name ?? 'Mata Pelajaran' }}
                </span>
                <span class="badge bg-light text-dark border fw-bold">
                    {{ $material->classroom->name ?? 'Semua Kelas' }}
                </span>
                @if($material->is_published)
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Dipublikasikan
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">
                        <i class="fas fa-eye-slash me-1"></i> Draf
                    </span>
                @endif
            </div>
        </div>

        <!-- Tombol Aksi (Khusus Guru/Admin) -->
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-warning text-dark fw-semibold btn-sm flex-fill flex-md-grow-0">
                <i class="fas fa-edit me-1"></i> Edit Materi
            </a>
            <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="d-inline flex-fill flex-md-grow-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger fw-semibold btn-sm w-100">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Kolom Kiri: Isi Teks & Video Pembelajaran -->
        <div class="col-lg-8">
            <!-- Card Deskripsi Singkat -->
            @if($material->description)
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-info-circle text-primary me-2"></i>Pengantar / Deskripsi Singkat
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 text-dark">
                        {!! nl2br(e($material->description)) !!}
                    </div>
                </div>
            @endif

            <!-- Card Video Pembelajaran Embed -->
            @if($material->video_url)
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fab fa-youtube text-danger me-2"></i>Video Pembelajaran
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        @php
                            // Format URL YouTube Embed jika link standar
                            $embedUrl = $material->video_url;
                            if (str_contains($embedUrl, 'watch?v=')) {
                                $embedUrl = str_replace('watch?v=', 'embed/', $embedUrl);
                            } elseif (str_contains($embedUrl, 'youtu.be/')) {
                                $embedUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedUrl);
                            }
                        @endphp
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
                            <iframe src="{{ $embedUrl }}" title="Video Pembelajaran" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card Teks / Artikel Pembelajaran -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-book-open text-primary me-2"></i>Konten Materi Pembelajaran
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($material->content)
                        <div class="text-dark leading-relaxed" style="word-break: break-word;">
                            {!! nl2br(e($material->content)) !!}
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-file-alt fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0">Tidak ada uraian teks untuk materi ini. Silakan unduh berkas lampiran yang tersedia.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Informasi Pembuat & Berkas Lampiran -->
        <div class="col-lg-4">
            <!-- Informasi Guru & Mata Pelajaran -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Informasi Guru & Kelas</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Guru Pembuat Materi</small>
                        <span class="fw-bold text-dark fs-6">
                            <i class="fas fa-user-tie text-primary me-1"></i> {{ $material->teacher->name ?? '-' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Mata Pelajaran</small>
                        <span class="fw-semibold text-dark fs-6">{{ $material->subject->name ?? '-' }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Target Kelas</small>
                        <span class="badge bg-light text-dark border fs-6 fw-normal">
                            {{ $material->classroom->name ?? 'Semua Kelas' }}
                        </span>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block mb-1">Dibuat Pada</small>
                        <span class="small text-dark fw-medium">
                            <i class="far fa-clock me-1"></i> {{ $material->created_at ? $material->created_at->format('d M Y, H:i') : '-' }} WIB
                        </span>
                    </div>
                </div>
            </div>

            <!-- Download Lampiran Berkas -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-paperclip text-info me-2"></i>Berkas Lampiran
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($material->file_path)
                        <div class="p-3 bg-light border rounded-3 text-center">
                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                            <span class="d-block text-dark fw-bold small text-truncate mb-3">
                                {{ basename($material->file_path) }}
                            </span>
                            <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="btn btn-primary btn-sm fw-bold w-100">
                                <i class="fas fa-download me-1"></i> Unduh Modul/Berkas
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted">
                            <p class="small mb-0">Tidak ada file lampiran yang diunggah.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection