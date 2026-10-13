@extends('layouts.app')

@section('title', 'Daftar Materi Pembelajaran')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Switcher Tampilan -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Materi Pembelajaran</h3>
            <p class="text-muted small mb-0">Kelola modul, bahan ajar, dan berkas pembelajaran siswa.</p>
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

            <a href="{{ route('materials.create') }}" class="btn btn-primary fw-semibold">
                <i class="fas fa-plus me-1"></i> Tambah Materi
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
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

    <!-- 1. TAMPILAN BENTUK CARD -->
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

                                <h4 class="fw-bold text-dark mb-1 material-title-text">{{ $material->title }}</h4>
                                
                                <div class="mb-3 small">
                                    <span class="text-muted d-block">Pembuat: <strong>{{ $material->teacher->name ?? '-' }}</strong></span>
                                </div>

                                @if($material->description)
                                    <p class="text-muted small mb-3 text-truncate-2">
                                        {{ $material->description }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <div class="bg-light rounded-3 p-2 d-flex justify-content-around text-center mb-3 border">
                                    <div>
                                        <small class="text-muted d-block">Berkas Lampiran</small>
                                        @if($material->file_path)
                                            <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                                <i class="fas fa-download me-1"></i> Unduh File
                                            </a>
                                        @else
                                            <span class="text-muted fw-semibold">-</span>
                                        @endif
                                    </div>
                                    @if($material->video_url)
                                        <div class="border-end"></div>
                                        <div>
                                            <small class="text-muted d-block">Video</small>
                                            <a href="{{ $material->video_url }}" target="_blank" class="fw-bold text-danger text-decoration-none">
                                                <i class="fab fa-youtube me-1"></i> Tonton
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('materials.show', $material->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                        Detail
                                    </a>
                                    <button type="button" class="btn btn-outline-warning btn-sm flex-fill fw-semibold" data-bs-toggle="modal" data-bs-target="#editMaterialModal-{{ $material->id }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted" id="empty-card-message">
                    <p class="mb-0">Belum ada data materi pembelajaran yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. TAMPILAN BENTUK TABEL -->
    <div id="view-table-container" class="d-none">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Informasi Materi</th>
                                <th>Target Kelas</th>
                                <th>Guru Pembuat</th>
                                <th>Lampiran Berkas</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-material-list">
                            @forelse($materials as $index => $material)
                                <tr class="material-row-item" 
                                    data-title="{{ strtolower($material->title) }}" 
                                    data-teacher="{{ strtolower($material->teacher->name ?? '') }}">
                                    <td class="ps-4 fw-semibold row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border mb-1">{{ $material->subject->name ?? 'Mapel' }}</span>
                                        <div class="fw-bold text-dark fs-6 material-title-text">{{ $material->title }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $material->classroom->name ?? 'Semua Kelas' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $material->teacher->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($material->file_path)
                                            <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="badge bg-info-subtle text-info border border-info-subtle text-decoration-none px-2 py-1">
                                                <i class="fas fa-file-download me-1"></i> File PDF/Doc
                                            </a>
                                        @endif
                                        @if($material->video_url)
                                            <a href="{{ $material->video_url }}" target="_blank" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none px-2 py-1 ms-1">
                                                <i class="fab fa-youtube me-1"></i> Video
                                            </a>
                                        @endif
                                        @if(!$material->file_path && !$material->video_url)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('materials.show', $material->id) }}" class="btn btn-sm btn-outline-primary fw-semibold" title="Detail Materi">
                                                Detail
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#editMaterialModal-{{ $material->id }}" title="Edit Data">
                                                Edit
                                            </button>
                                            <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold" title="Hapus Materi">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada data materi pembelajaran yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT MATERI PEMBELAJARAN -->
    @foreach($materials as $material)
        <div class="modal fade" id="editMaterialModal-{{ $material->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('materials.update', $material->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="fas fa-edit text-warning me-2"></i>Edit Materi Pembelajaran
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <!-- Judul Materi -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Judul Materi Pembelajaran <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $material->title }}" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Mata Pelajaran -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select name="subject_id" class="form-select" required>
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                        @foreach($allSubjects ?? \App\Models\Subject::all() as $subj)
                                            <option value="{{ $subj->id }}" {{ $material->subject_id == $subj->id ? 'selected' : '' }}>
                                                {{ $subj->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Target Kelas -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Target Kelas (Opsional)</label>
                                    <select name="classroom_id" class="form-select">
                                        <option value="">-- Semua Kelas --</option>
                                        @foreach($allClassrooms ?? \App\Models\Classroom::all() as $cls)
                                            <option value="{{ $cls->id }}" {{ $material->classroom_id == $cls->id ? 'selected' : '' }}>
                                                {{ $cls->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Deskripsi Singkat -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Deskripsi Singkat / Pengantar</label>
                                <textarea name="description" rows="3" class="form-control">{{ $material->description }}</textarea>
                            </div>

                            <!-- Isi Teks Materi -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Isi Materi Pembelajaran (Teks/Artikel)</label>
                                <textarea name="content" rows="5" class="form-control">{{ $material->content }}</textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- File Dokumen -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Ganti File Dokumen (PDF, DOCX, PPTX, ZIP)</label>
                                    <input type="file" name="file" class="form-control">
                                    @if($material->file_path)
                                        <small class="text-muted d-block mt-1">
                                            File saat ini: <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="text-primary">{{ basename($material->file_path) }}</a>
                                        </small>
                                    @endif
                                </div>

                                <!-- Link Video -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Link Video (YouTube/Drive)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted">
                                            <i class="fab fa-youtube text-danger"></i>
                                        </span>
                                        <input type="url" name="video_url" class="form-control" value="{{ $material->video_url }}" placeholder="https://youtube.com/...">
                                    </div>
                                </div>
                            </div>

                            <!-- Status Publikasi -->
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 mb-0">
                                    <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" name="is_published" id="is_published_{{ $material->id }}" value="1" {{ $material->is_published ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="is_published_{{ $material->id }}">
                                        Publikasikan Materi Ini (Siswa dapat mengakses)
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function switchView(mode) {
        const cardContainer = document.getElementById('view-card-container');
        const tableContainer = document.getElementById('view-table-container');
        const btnCard = document.getElementById('btn-view-card');
        const btnTable = document.getElementById('btn-view-table');

        if (mode === 'card') {
            cardContainer.classList.remove('d-none');
            tableContainer.classList.add('d-none');
            btnCard.classList.add('active', 'bg-primary', 'text-white');
            btnTable.classList.remove('active', 'bg-primary', 'text-white');
        } else {
            tableContainer.classList.remove('d-none');
            cardContainer.classList.add('d-none');
            btnTable.classList.add('active', 'bg-primary', 'text-white');
            btnCard.classList.remove('active', 'bg-primary', 'text-white');
        }
    }

    function filterMaterials() {
        const searchInput = document.getElementById('filter-search').value.toLowerCase().trim();
        const teacherInput = document.getElementById('filter-teacher').value.toLowerCase().trim();

        const cardItems = document.querySelectorAll('.material-card-item');
        cardItems.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const teacher = item.getAttribute('data-teacher') || '';

            const matchTitle = title.includes(searchInput);
            const matchTeacher = teacher.includes(teacherInput);

            if (matchTitle && matchTeacher) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });

        const tableRows = document.querySelectorAll('.material-row-item');
        let visibleIndex = 1;

        tableRows.forEach(row => {
            const title = row.getAttribute('data-title') || '';
            const teacher = row.getAttribute('data-teacher') || '';

            const matchTitle = title.includes(searchInput);
            const matchTeacher = teacher.includes(teacherInput);

            if (matchTitle && matchTeacher) {
                row.classList.remove('d-none');
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.innerText = visibleIndex++;
            } else {
                row.classList.add('d-none');
            }
        });
    }

    function resetFilter() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-teacher').value = '';
        filterMaterials();
    }

    document.addEventListener('DOMContentLoaded', function() {
        switchView('card');
    });
</script>
@endsection