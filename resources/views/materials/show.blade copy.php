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
        @if(auth()->user()->hasAnyRole(['admin', 'super_admin']) || (auth()->user()->teacher && auth()->user()->teacher->id === $material->teacher_id))
            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
                <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm flex-fill flex-md-grow-0" onclick="openEditModal('{{ $material->id }}')">
                    <i class="fas fa-edit me-1"></i> Edit Materi
                </button>
                <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="d-inline flex-fill flex-md-grow-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger fw-semibold btn-sm w-100">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Kolom Kiri: Isi Teks & Media Pembelajaran -->
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

            <!-- Card Media Pembelajaran Embed (YouTube / Google Docs / Google Drive) -->
            @if($material->video_url)
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-play-circle text-danger me-2"></i>Media / Tautan Pembelajaran
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        @php
                            $embedUrl = $material->video_url;

                            // 1. Format YouTube Embed
                            if (str_contains($embedUrl, 'watch?v=')) {
                                $embedUrl = str_replace('watch?v=', 'embed/', $embedUrl);
                            } elseif (str_contains($embedUrl, 'youtu.be/')) {
                                $embedUrl = str_replace('youtu.be/', 'www.youtube.com/embed/', $embedUrl);
                            } 
                            // 2. Format Google Docs / Drive Embed (/edit -> /preview)
                            elseif (str_contains($embedUrl, 'docs.google.com') || str_contains($embedUrl, 'drive.google.com')) {
                                $embedUrl = preg_replace('/\/edit.*$/', '/preview', $embedUrl);
                                if (!str_contains($embedUrl, '/preview')) {
                                    $embedUrl = rtrim($embedUrl, '/') . '/preview';
                                }
                            }
                        @endphp
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border">
                            <iframe src="{{ $embedUrl }}" title="Media Pembelajaran" allowfullscreen></iframe>
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

<!-- MODAL POP-UP EDIT MATERI -->
<div class="modal fade" id="modalEditMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-edit text-warning me-2"></i>Edit Materi Pembelajaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditMaterial" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Mata Pelajaran</label>
                            <input type="text" class="form-control" value="{{ $material->subject->name ?? '-' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Target Kelas</label>
                            <input type="text" class="form-control" value="{{ $material->classroom->name ?? 'Semua Kelas' }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Judul Materi <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit-title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Deskripsi Singkat</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Isi / Konten Materi</label>
                        <textarea name="content" id="edit-content" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Ganti Berkas (PDF, DOCX, ZIP)</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">URL Video / Google Docs</label>
                            <input type="url" name="video_url" id="edit-video_url" class="form-control">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="edit-is_published" value="1">
                        <label class="form-check-label fw-bold text-dark small" for="edit-is_published">
                            Publikasikan Materi
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function openEditModal(id) {
        Swal.fire({
            title: 'Memuat Data...',
            position: 'center',
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "/materials/" + id + "/edit",
            type: "GET",
            dataType: "json",
            success: function(data) {
                Swal.close();

                $('#edit-title').val(data.title);
                $('#edit-description').val(data.description || '');
                $('#edit-content').val(data.content || '');
                $('#edit-video_url').val(data.video_url || '');
                $('#edit-is_published').prop('checked', data.is_published == 1 || data.is_published == true);

                $('#formEditMaterial').attr('action', '/materials/' + id);

                var editModal = new bootstrap.Modal(document.getElementById('modalEditMaterial'));
                editModal.show();
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Tidak dapat mengambil data materi.',
                    position: 'center'
                });
            }
        });
    }
</script>
@endsection