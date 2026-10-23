@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Action -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                @hasrole('siswa')
                    Materi Pembelajaran Saya
                @else
                    Materi Pembelajaran
                @endhasrole
            </h3>
            <p class="text-muted small mb-0">
                @hasrole('siswa')
                    Daftar modul, bahan ajar, dan berkas pembelajaran untuk kelas Anda.
                @else
                    Kelola modul, bahan ajar, dan berkas pembelajaran siswa.
                @endhasrole
            </p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group bg-white p-1 rounded-3 border shadow-sm" role="group" aria-label="View Switcher">
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 active" id="btn-view-card" onclick="switchView('card')">
                    <i class="fas fa-th-large me-1"></i> Card
                </button>
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0" id="btn-view-table" onclick="switchView('table')">
                    <i class="fas fa-list me-1"></i> Tabel
                </button>
            </div>

            <!-- Tombol Tambah Materi (Hanya untuk Guru dan Admin) -->
            @hasanyrole('guru|admin|super_admin')
                <a href="{{ route('materials.create') }}" class="btn btn-primary fw-semibold">
                    <i class="fas fa-plus me-1"></i> Tambah Materi
                </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- FORM FILTER -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Cari judul materi..." onkeyup="filterMaterials()">
                    </div>
                </div>

                <div class="col-md-5 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <input type="text" id="filter-teacher" class="form-control border-start-0" placeholder="Cari nama pembuat..." onkeyup="filterMaterials()">
                    </div>
                </div>

                <div class="col-md-2 col-12 d-grid">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="resetFilter()">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. TAMPILAN CARD -->
    <div id="view-card-container">
        <div class="row g-4" id="card-material-list">
            @forelse($materials as $material)
                <div class="col-md-6 col-lg-4 material-card-item" 
                     data-title="{{ strtolower($material->title) }}" 
                     data-teacher="{{ strtolower($material->teacher->name ?? '') }}">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                        {{ $material->subject->name ?? 'Mata Pelajaran' }}
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        {{ $material->classroom->name ?? 'Semua Kelas' }}
                                    </span>
                                </div>

                                <h4 class="fw-bold text-dark mb-1">{{ $material->title }}</h4>
                                <small class="text-muted d-block mb-3">Pembuat: <strong>{{ $material->teacher->name ?? '-' }}</strong></small>

                                <p class="text-secondary small mb-3 text-truncate-2">
                                    {{ $material->description ?? 'Tidak ada deskripsi singkat.' }}
                                </p>
                            </div>

                            <div>
                                <div class="d-flex gap-2">
                                    <!-- Detail / Lihat Materi -->
                                    <a href="{{ route('materials.show', $material->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                        Detail
                                    </a>

                                    <!-- Tombol Edit Modal (Hanya Pembuat atau Admin) -->
                                    @if(auth()->user()->hasAnyRole(['admin', 'super_admin']) || (auth()->user()->teacher && auth()->user()->teacher->id === $material->teacher_id))
                                        <button type="button" 
                                                class="btn btn-outline-warning btn-sm flex-fill fw-semibold"
                                                onclick="openEditModal('{{ $material->id }}')">
                                            Edit
                                        </button>
                                        
                                        <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <p class="mb-0">Belum ada data materi pembelajaran yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL POP-UP EDIT MATERI PEMBELAJARAN -->
<div class="modal fade" id="modalEditMaterial" tabindex="-1" aria-labelledby="modalEditMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalEditMaterialLabel">
                    <i class="fas fa-edit text-warning me-2"></i>Edit Materi Pembelajaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditMaterial" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <!-- Mata Pelajaran -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="subject_id" id="edit-subject_id" class="form-select" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($allSubjects as $subj)
                                    <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kelas Target (Dropdown Single Class) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Kelas Target</label>
                            <select name="classroom_id" id="edit-classroom_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @foreach($allClassrooms->where('is_active', true) as $cls)
                                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Judul Materi -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Judul Materi <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit-title" class="form-control" placeholder="Masukkan judul materi..." required>
                    </div>

                    <!-- Deskripsi Ringkas -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Deskripsi Singkat</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="2" placeholder="Penjelasan singkat..."></textarea>
                    </div>

                    <!-- Konten Lengkap / Catatan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Konten / Isi Materi</label>
                        <textarea name="content" id="edit-content" class="form-control" rows="4" placeholder="Isi teks atau instruksi materi..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- File Lampiran Baru -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Ganti Berkas (PDF, DOCX, PPTX, ZIP)</label>
                            <input type="file" name="file" class="form-control">
                            <small class="text-muted" style="font-size: 0.72rem;">Biarkan kosong jika tidak ingin mengganti file.</small>
                        </div>

                        <!-- URL Video Pembelajaran -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">URL Video (Youtube/Vimeo)</label>
                            <input type="url" name="video_url" id="edit-video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>

                    <!-- Toggle Status Publikasi -->
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
    // FUNGSI MEMBUKA MODAL EDIT
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

                // Isi Form Input di Modal
                $('#edit-subject_id').val(data.subject_id);
                $('#edit-classroom_id').val(data.classroom_id || '');
                $('#edit-title').val(data.title);
                $('#edit-description').val(data.description || '');
                $('#edit-content').val(data.content || '');
                $('#edit-video_url').val(data.video_url || '');
                
                // Set status checkbox
                $('#edit-is_published').prop('checked', data.is_published == 1 || data.is_published == true);

                // Update URL Form Method Put
                $('#formEditMaterial').attr('action', '/materials/' + id);

                // Buka Modal Bootstrap
                var editModal = new bootstrap.Modal(document.getElementById('modalEditMaterial'));
                editModal.show();
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Tidak dapat mengambil data materi pembelajaran.',
                    position: 'center'
                });
            }
        });
    }

    // FILTERING BANTUAN HASIL CARD
    function filterMaterials() {
        const searchVal = $('#filter-search').val().toLowerCase();
        const teacherVal = $('#filter-teacher').val().toLowerCase();

        $('.material-card-item').each(function() {
            const title = $(this).data('title');
            const teacher = $(this).data('teacher');

            if (title.includes(searchVal) && teacher.includes(teacherVal)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function resetFilter() {
        $('#filter-search').val('');
        $('#filter-teacher').val('');
        $('.material-card-item').show();
    }
</script>
@endsection