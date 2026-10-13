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
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
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
                                    <!-- Detail / Lihat Materi (Semua User) -->
                                    <a href="{{ route('materials.show', $material->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                        Detail
                                    </a>

                                    <!-- Tombol Edit & Hapus (Hanya Pembuat atau Admin) -->
                                    @if(auth()->user()->hasAnyRole(['admin', 'super_admin']) || (auth()->user()->teacher && auth()->user()->teacher->id === $material->teacher_id))
                                        <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-outline-warning btn-sm flex-fill fw-semibold">
                                            Edit
                                        </a>
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
@endsection