@extends('layouts.app')

@section('title', 'Daftar Ujian Guru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Switcher Tampilan -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Kelola Ujian</h3>
            <p class="text-muted small mb-0">Kelola seluruh data jadwal, durasi, dan informasi ujian di sistem.</p>
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

            <a href="{{ route('exams.create') }}" class="btn btn-primary fw-semibold">
                <i class="fas fa-plus me-1"></i> Buat Ujian Baru
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
                <div class="col-md-4 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Cari nama / judul ujian..." onkeyup="filterExams()">
                    </div>
                </div>

                <div class="col-md-4 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <input type="text" id="filter-teacher" class="form-control border-start-0" placeholder="Cari nama guru..." onkeyup="filterExams()">
                    </div>
                </div>

                <div class="col-md-2 col-7">
                    <select id="filter-status" class="form-select" onchange="filterExams()">
                        <option value="all">-- Semua Status --</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="col-md-2 col-5 d-grid">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="resetFilter()">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. TAMPILAN BENTUK CARD -->
    <div id="view-card-container">
        <div class="row g-4" id="card-exam-list">
            @forelse($exams as $exam)
                <div class="col-md-6 col-lg-4 exam-card-item" 
                     data-title="{{ strtolower($exam->title) }}" 
                     data-teacher="{{ strtolower(($exam->teacher->name ?? '') . ' ' . ($exam->collaborators->pluck('name')->implode(' ') ?? '')) }}" 
                     data-status="{{ $exam->is_active ? 'aktif' : 'nonaktif' }}">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                            {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                        </span>
                                        @if($exam->is_team_teaching || $exam->collaborators->count() > 0)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" title="Team Teaching">
                                                <i class="fas fa-users"></i> Team
                                            </span>
                                        @endif
                                    </div>
                                    <span class="badge {{ $exam->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $exam->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <h4 class="fw-bold text-dark mb-1 exam-title-text">{{ $exam->title }}</h4>
                                
                                <!-- Guru Pengampu & Kolaborator -->
                                <div class="mb-2 small">
                                    <span class="text-muted d-block">Pembuat: <strong>{{ $exam->teacher->name ?? '-' }}</strong></span>
                                    @if($exam->collaborators->count() > 0)
                                        <span class="text-info d-block">
                                            <i class="fas fa-user-friends me-1"></i> Tim: 
                                            {{ $exam->collaborators->pluck('name')->implode(', ') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block fw-semibold mb-1">Target Kelas:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($exam->classrooms as $cls)
                                            <span class="badge bg-light text-dark border">{{ $cls->name }}</span>
                                        @empty
                                            <span class="text-muted small">-</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="bg-light rounded-3 p-2 d-flex justify-content-around text-center mb-3 border">
                                    <div>
                                        <small class="text-muted d-block">Durasi</small>
                                        <span class="fw-bold text-dark">{{ $exam->duration_minutes }} Menit</span>
                                    </div>
                                    <div class="border-end"></div>
                                    <div>
                                        <small class="text-muted d-block">Jumlah Soal</small>
                                        <span class="fw-bold text-dark">{{ $exam->questions_count ?? ($exam->questions ? $exam->questions->count() : 0) }} Soal</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                        Detail
                                    </a>
                                    <button type="button" class="btn btn-outline-warning btn-sm flex-fill fw-semibold" data-bs-toggle="modal" data-bs-target="#editExamModal-{{ $exam->id }}">
                                        Edit
                                    </button>
                                    <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-outline-success btn-sm flex-fill fw-semibold">
                                        Import
                                    </a>
                                    <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian ini?');">
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
                    <p class="mb-0">Belum ada data ujian yang tersedia.</p>
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
                                <th>Informasi Ujian</th>
                                <th>Target Kelas</th>
                                <th>Durasi</th>
                                <th>Jumlah Soal</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-exam-list">
                            @forelse($exams as $index => $exam)
                                <tr class="exam-row-item" 
                                    data-title="{{ strtolower($exam->title) }}" 
                                    data-teacher="{{ strtolower(($exam->teacher->name ?? '') . ' ' . ($exam->collaborators->pluck('name')->implode(' ') ?? '')) }}" 
                                    data-status="{{ $exam->is_active ? 'aktif' : 'nonaktif' }}">
                                    <td class="ps-4 fw-semibold row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="badge bg-primary-subtle text-primary border">{{ $exam->subject->name ?? 'Mapel' }}</span>
                                            @if($exam->is_team_teaching || $exam->collaborators->count() > 0)
                                                <span class="badge bg-info-subtle text-info border">Team Teaching</span>
                                            @endif
                                        </div>
                                        <div class="fw-bold text-dark fs-6 exam-title-text">{{ $exam->title }}</div>
                                        <small class="text-muted d-block">Guru: <strong>{{ $exam->teacher->name ?? '-' }}</strong></small>
                                        @if($exam->collaborators->count() > 0)
                                            <small class="text-info d-block"><i class="fas fa-users me-1"></i> {{ $exam->collaborators->pluck('name')->implode(', ') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($exam->classrooms as $cls)
                                                <span class="badge bg-light text-dark border">{{ $cls->name }}</span>
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $exam->duration_minutes }}</span> <small class="text-muted">Menit</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $exam->questions_count ?? ($exam->questions ? $exam->questions->count() : 0) }}</span> <small class="text-muted">Soal</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $exam->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} px-3 py-1">
                                            {{ $exam->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-sm btn-outline-primary fw-semibold" title="Detail & Soal">
                                                Detail
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#editExamModal-{{ $exam->id }}" title="Edit Data">
                                                Edit
                                            </button>
                                            <a href="{{ route('guru.exams.report', $exam->id) }}" class="btn btn-sm btn-outline-info fw-semibold" title="Rekap Nilai">
                                                Nilai
                                            </a>
                                            <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-sm btn-outline-success fw-semibold" title="Import Soal">
                                                Import
                                            </a>
                                            <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold" title="Hapus Ujian">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Belum ada data ujian yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT UJIAN -->
    @foreach($exams as $exam)
        <div class="modal fade" id="editExamModal-{{ $exam->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('guru.exams.update', $exam->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="fas fa-edit text-warning me-2"></i>Edit Waktu & Informasi Ujian
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Judul Ujian <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $exam->title }}" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="duration_minutes" class="form-control" value="{{ $exam->duration_minutes }}" min="1" required>
                                        <span class="input-group-text">Menit</span>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 p-2 ps-0 mb-1">
                                        <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" name="is_active" id="is_active_{{ $exam->id }}" value="1" {{ $exam->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer mb-0" for="is_active_{{ $exam->id }}">
                                            Status Ujian Aktif
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Jadwal Mulai Ujian</label>
                                    <input type="datetime-local" name="start_time" class="form-control" value="{{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('Y-m-d\TH:i') : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Jadwal Selesai Ujian</label>
                                    <input type="datetime-local" name="end_time" class="form-control" value="{{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>

                            <!-- Target Kelas -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Target Kelas <span class="text-danger">*</span></label>
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="row g-2">
                                        @foreach($allClassrooms ?? \App\Models\Classroom::all() as $cls)
                                            <div class="col-md-4 col-6">
                                                <div class="form-check bg-white p-2 border rounded d-flex align-items-center">
                                                    <input class="form-check-input ms-1 me-2" type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}" id="cls_{{ $exam->id }}_{{ $cls->id }}" {{ $exam->classrooms->contains($cls->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium text-dark cursor-pointer mb-0" for="cls_{{ $exam->id }}_{{ $cls->id }}">
                                                        {{ $cls->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Pengaturan Acak Ujian -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-2">Pengaturan Acak Ujian</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light h-100">
                                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                                <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="randomize_questions_{{ $exam->id }}" name="randomize_questions" value="1" {{ $exam->randomize_questions ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="randomize_questions_{{ $exam->id }}">
                                                    <i class="fas fa-random text-primary me-1"></i> Acak Urutan Soal
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light h-100">
                                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                                <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="randomize_options_{{ $exam->id }}" name="randomize_options" value="1" {{ $exam->randomize_options ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="randomize_options_{{ $exam->id }}">
                                                    <i class="fas fa-sort-alpha-down-alt text-success me-1"></i> Acak Pilihan Jawaban
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pengaturan Hasil & Review Siswa -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-2">Pengaturan Hasil & Review Siswa</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light h-100">
                                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                                <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="allow_review_{{ $exam->id }}" name="allow_review" value="1" {{ $exam->allow_review ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="allow_review_{{ $exam->id }}">
                                                    <i class="fas fa-eye text-info me-1"></i> Izinkan Review Ujian
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light h-100">
                                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-2 ps-0">
                                                <input class="form-check-input ms-0 me-2" style="width: 2.5em; height: 1.3em;" type="checkbox" role="switch" id="show_correct_answer_{{ $exam->id }}" name="show_correct_answer" value="1" {{ $exam->show_correct_answer ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="show_correct_answer_{{ $exam->id }}">
                                                    <i class="fas fa-check-circle text-warning me-1"></i> Tampilkan Kunci Jawaban
                                                </label>
                                            </div>
                                        </div>
                                    </div>
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

    function filterExams() {
        const searchInput = document.getElementById('filter-search').value.toLowerCase().trim();
        const teacherInput = document.getElementById('filter-teacher').value.toLowerCase().trim();
        const statusSelect = document.getElementById('filter-status').value;

        const cardItems = document.querySelectorAll('.exam-card-item');
        cardItems.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const teacher = item.getAttribute('data-teacher') || '';
            const status = item.getAttribute('data-status') || '';

            const matchTitle = title.includes(searchInput);
            const matchTeacher = teacher.includes(teacherInput);
            const matchStatus = (statusSelect === 'all') || (status === statusSelect);

            if (matchTitle && matchTeacher && matchStatus) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });

        const tableRows = document.querySelectorAll('.exam-row-item');
        let visibleIndex = 1;

        tableRows.forEach(row => {
            const title = row.getAttribute('data-title') || '';
            const teacher = row.getAttribute('data-teacher') || '';
            const status = row.getAttribute('data-status') || '';

            const matchTitle = title.includes(searchInput);
            const matchTeacher = teacher.includes(teacherInput);
            const matchStatus = (statusSelect === 'all') || (status === statusSelect);

            if (matchTitle && matchTeacher && matchStatus) {
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
        document.getElementById('filter-status').value = 'all';
        filterExams();
    }

    document.addEventListener('DOMContentLoaded', function() {
        switchView('card');
    });
</script>
@endsection