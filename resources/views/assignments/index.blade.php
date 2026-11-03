@extends('layouts.app')

@section('title', 'Daftar Tugas Siswa')

@push('styles')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            padding-top: 2px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Switcher Tampilan -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                @hasrole('siswa')
                    Tugas Saya
                @else
                    Kelola Tugas Siswa
                @endhasrole
            </h3>
            <p class="text-muted small mb-0">
                @hasrole('siswa')
                    Daftar tugas pembelajaran yang harus dikumpulkan sesuai kelas Anda.
                @else
                    Kelola instruksi tugas, tenggat waktu pengumpulan, dan penugasan kelas.
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

            <!-- Tombol Buat Tugas Baru (Hanya tampil untuk Guru & Admin) -->
            @hasanyrole('guru|admin')
                <a href="{{ route('assignments.create') }}" class="btn btn-primary fw-semibold">
                    <i class="fas fa-plus me-1"></i> Buat Tugas Baru
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
                        <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Cari judul tugas..." onkeyup="filterAssignments()">
                    </div>
                </div>

                <div class="col-md-5 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <input type="text" id="filter-teacher" class="form-control border-start-0" placeholder="Cari nama guru..." onkeyup="filterAssignments()">
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
        <div class="row g-4" id="card-assignment-list">
            @forelse($assignments as $assignment)
                <div class="col-md-6 col-lg-4 assignment-card-item" 
                     data-title="{{ strtolower($assignment->title) }}" 
                     data-teacher="{{ strtolower($assignment->teacher->name ?? '') }}">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                        {{ $assignment->subject->name ?? 'Mata Pelajaran' }}
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        {{ $assignment->classroom->name ?? 'Kelas' }}
                                    </span>
                                </div>

                                <h4 class="fw-bold text-dark mb-1 assignment-title-text">{{ $assignment->title }}</h4>
                                
                                <div class="mb-3 small">
                                    <span class="text-muted d-block">Guru: <strong>{{ $assignment->teacher->name ?? '-' }}</strong></span>
                                </div>
                            </div>

                            <div>
                                <div class="bg-light rounded-3 p-2 d-flex justify-content-around text-center mb-3 border">
                                    <div>
                                        <small class="text-muted d-block">Batas Waktu</small>
                                        <span class="fw-bold text-danger">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                    <div class="border-end"></div>
                                    <div>
                                        @hasrole('siswa')
                                            <small class="text-muted d-block">Status Saya</small>
                                            @php
                                                $mySubmission = $assignment->submissions->where('student_id', optional(Auth::user()->student)->id)->first();
                                            @endphp
                                            @if($mySubmission)
                                                <span class="badge bg-success-subtle text-success border">Sudah Mengumpulkan</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border">Belum Dikumpul</span>
                                            @endif
                                        @else
                                            <small class="text-muted d-block">Pengumpulan</small>
                                            <span class="fw-bold text-dark">{{ $assignment->submissions ? $assignment->submissions->count() : 0 }} Siswa</span>
                                        @endhasrole
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <!-- Tombol Detail / Kerjakan (Tampil untuk Semua Role) -->
                                    <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                        @hasrole('siswa')
                                            <i class="fas fa-paper-plane me-1"></i> Kerjakan / Detail
                                        @else
                                            Detail
                                        @endhasrole
                                    </a>

                                    <!-- Tombol Edit & Hapus (Hanya Tampil untuk Guru & Admin) -->
                                    @hasanyrole('guru|admin')
                                        <button type="button" class="btn btn-outline-warning btn-sm flex-fill fw-semibold" data-bs-toggle="modal" data-bs-target="#editAssignmentModal-{{ $assignment->id }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm fw-semibold">
                                                Hapus
                                            </button>
                                        </form>
                                    @endhasanyrole
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted" id="empty-card-message">
                    <p class="mb-0">Belum ada data tugas yang tersedia.</p>
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
                                <th>Informasi Tugas</th>
                                <th>Kelas</th>
                                <th>Tenggat Waktu</th>
                                <th>
                                    @hasrole('siswa') Status Saya @else Pengumpulan @endhasrole
                                </th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-assignment-list">
                            @forelse($assignments as $index => $assignment)
                                <tr class="assignment-row-item" 
                                    data-title="{{ strtolower($assignment->title) }}" 
                                    data-teacher="{{ strtolower($assignment->teacher->name ?? '') }}">
                                    <td class="ps-4 fw-semibold row-number">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border mb-1">{{ $assignment->subject->name ?? 'Mapel' }}</span>
                                        <div class="fw-bold text-dark fs-6">{{ $assignment->title }}</div>
                                        <small class="text-muted">Guru: {{ $assignment->teacher->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $assignment->classroom->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-semibold">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        @hasrole('siswa')
                                            @php
                                                $mySub = $assignment->submissions->where('student_id', optional(Auth::user()->student)->id)->first();
                                            @endphp
                                            @if($mySub)
                                                <span class="badge bg-success-subtle text-success border">Sudah Mengumpulkan</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border">Belum Dikumpul</span>
                                            @endif
                                        @else
                                            <span class="fw-bold text-dark">{{ $assignment->submissions ? $assignment->submissions->count() : 0 }}</span> <small class="text-muted">Siswa</small>
                                        @endhasrole
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                                @hasrole('siswa') Kerjakan @else Detail @endhasrole
                                            </a>
                                            @hasanyrole('guru|admin')
                                                <button type="button" class="btn btn-sm btn-outline-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#editAssignmentModal-{{ $assignment->id }}">
                                                    Edit
                                                </button>
                                                <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endhasanyrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada data tugas yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT TUGAS (Hanya di-render dan dibuka untuk Guru & Admin) -->
    @hasanyrole('guru|admin')
        @foreach($assignments as $assignment)
            <div class="modal fade" id="editAssignmentModal-{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold text-dark">
                                    <i class="fas fa-edit text-warning me-2"></i>Edit Informasi Tugas
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <!-- Judul Tugas -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Judul Tugas <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $assignment->title) }}" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- 1. Target Kelas (Hanya Kelas yang Diajar oleh Guru Tersebut) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Target Kelas <span class="text-danger">*</span></label>
                                        <select name="classroom_id" id="edit_classroom_id_{{ $assignment->id }}" class="form-select select2-edit-{{ $assignment->id }}" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($allClassrooms as $cls)
                                                <option value="{{ $cls->id }}" {{ old('classroom_id', $assignment->classroom_id) == $cls->id ? 'selected' : '' }}>
                                                    {{ $cls->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- 2. Mata Pelajaran (Dynamic Dependent Dropdown) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                                        <select name="subject_id" id="edit_subject_id_{{ $assignment->id }}" class="form-select select2-edit-{{ $assignment->id }}" required>
                                            <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 3. Tujuan Pembelajaran (TP) + Tombol Modal Tambah TP -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold text-dark mb-0">Tujuan Pembelajaran (TP)</label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" id="btn_edit_tp_modal_{{ $assignment->id }}" data-bs-toggle="modal" data-bs-target="#modalCreateTpEdit-{{ $assignment->id }}">
                                            <i class="fas fa-plus-circle me-1"></i> Tambah TP Baru
                                        </button>
                                    </div>
                                    <select name="learning_objective_id" id="edit_tp_id_{{ $assignment->id }}" class="form-select select2-edit-{{ $assignment->id }}">
                                        <option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>
                                    </select>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Tenggat Waktu (Due Date) <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="due_date" class="form-control" value="{{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d\TH:i') : '' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Nilai Maksimal <span class="text-danger">*</span></label>
                                        <input type="number" name="max_score" class="form-control" value="{{ old('max_score', (int)$assignment->max_score) }}" min="1" max="100" step="1" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Instruksi Tugas <span class="text-danger">*</span></label>
                                    <textarea name="instructions" rows="4" class="form-control" required>{{ old('instructions', $assignment->instructions) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Ganti Lampiran File Instruksi (Opsional)</label>
                                    <input type="file" name="file" class="form-control">
                                    @if($assignment->file_path)
                                        <small class="text-muted d-block mt-1">
                                            File saat ini: <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="text-primary">{{ basename($assignment->file_path) }}</a>
                                        </small>
                                    @endif
                                </div>

                                <!-- 4. Pilihan Publikasi ke Siswa -->
                                <div class="p-3 bg-light rounded border mb-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published_{{ $assignment->id }}" value="1" {{ old('is_published', $assignment->is_published) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark" for="is_published_{{ $assignment->id }}">
                                            Publikasikan Tugas ke Siswa
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Jika <strong>tidak dicentang</strong>, tugas hanya tampil pada rekapitulasi guru dan disembunyikan dari dashboard siswa.
                                    </small>
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

            <!-- MODAL TAMBAH TP BARU UNTUK EDIT TUGAS INI -->
            <div class="modal fade" id="modalCreateTpEdit-{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="fas fa-bullseye text-primary me-2"></i>Tambah Tujuan Pembelajaran (TP)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formCreateTpEdit-{{ $assignment->id }}">
                            <div class="modal-body p-4">
                                <div id="tp-modal-alert-edit-{{ $assignment->id }}" class="alert d-none" role="alert"></div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Kode TP (Opsional)</label>
                                    <input type="text" id="tp_code_edit_{{ $assignment->id }}" class="form-control" placeholder="Contoh: TP-1.1 atau 10.1">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Deskripsi Tujuan Pembelajaran <span class="text-danger">*</span></label>
                                    <textarea id="tp_description_edit_{{ $assignment->id }}" class="form-control" rows="4" placeholder="Tuliskan deskripsi kompetensi / tujuan pembelajaran..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary fw-semibold" id="btnSaveTpEdit-{{ $assignment->id }}">
                                    <i class="fas fa-save me-1"></i> Simpan TP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endhasanyrole
</div>
@endsection

@push('scripts')
    <!-- jQuery & Select2 JS -->
    <!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        function filterAssignments() {
            const searchInput = document.getElementById('filter-search').value.toLowerCase().trim();
            const teacherInput = document.getElementById('filter-teacher').value.toLowerCase().trim();

            const cardItems = document.querySelectorAll('.assignment-card-item');
            cardItems.forEach(item => {
                const title = item.getAttribute('data-title') || '';
                const teacher = item.getAttribute('data-teacher') || '';

                if (title.includes(searchInput) && teacher.includes(teacherInput)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });

            const tableRows = document.querySelectorAll('.assignment-row-item');
            let visibleIndex = 1;

            tableRows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                const teacher = row.getAttribute('data-teacher') || '';

                if (title.includes(searchInput) && teacher.includes(teacherInput)) {
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
            filterAssignments();
        }

        document.addEventListener('DOMContentLoaded', function() {
            switchView('card');

            // Inisialisasi Dependent Dropdown (Kelas -> Mapel -> TP) & Select2 untuk setiap Modal Edit Tugas
            @hasanyrole('guru|admin')
                @foreach($assignments as $assignment)
                    (function() {
                        const modalId = '#editAssignmentModal-{{ $assignment->id }}';
                        const classroomSelect = $('#edit_classroom_id_{{ $assignment->id }}');
                        const subjectSelect = $('#edit_subject_id_{{ $assignment->id }}');
                        const tpSelect = $('#edit_tp_id_{{ $assignment->id }}');
                        const btnOpenTpEdit = $('#btn_edit_tp_modal_{{ $assignment->id }}');

                        const selectedSubjectId = "{{ old('subject_id', $assignment->subject_id) }}";
                        const selectedTpId = "{{ old('learning_objective_id', $assignment->learning_objective_id) }}";

                        if ($.fn.select2) {
                            $('.select2-edit-{{ $assignment->id }}').select2({
                                theme: 'bootstrap-5',
                                dropdownParent: $(modalId),
                                width: '100%'
                            });
                        }

                        // Function untuk mengambil TP berdasarkan Mapel
                        function fetchEditTP(subjectId, activeTpId = null) {
                            if (!subjectId) {
                                tpSelect.html('<option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>').trigger('change');
                                btnOpenTpEdit.prop('disabled', true);
                                return;
                            }

                            btnOpenTpEdit.prop('disabled', false);
                            tpSelect.html('<option value="">-- Memuat TP... --</option>');

                            $.ajax({
                                url: `/api/get-tp-by-subject/${subjectId}`,
                                type: 'GET',
                                dataType: 'json',
                                success: function(tps) {
                                    let options = '<option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>';
                                    if (tps && tps.length > 0) {
                                        tps.forEach(function(tp) {
                                            const isSel = (activeTpId && activeTpId == tp.id) ? 'selected' : '';
                                            const codeStr = tp.code ? tp.code + ' - ' : '';
                                            options += `<option value="${tp.id}" ${isSel}>${codeStr}${tp.description}</option>`;
                                        });
                                    } else {
                                        options = '<option value="">-- Tidak Ada TP untuk Mapel Ini --</option>';
                                    }
                                    tpSelect.html(options).trigger('change');
                                },
                                error: function() {
                                    tpSelect.html('<option value="">-- Gagal Memuat TP --</option>').trigger('change');
                                }
                            });
                        }

                        // Function untuk mengambil Mapel berdasarkan Kelas
                        function fetchEditSubjects(classroomId, activeSubjId = null, activeTpId = null) {
                            if (!classroomId) {
                                subjectSelect.html('<option value="">-- Pilih Kelas Terlebih Dahulu --</option>').trigger('change');
                                tpSelect.html('<option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>').trigger('change');
                                btnOpenTpEdit.prop('disabled', true);
                                return;
                            }

                            subjectSelect.html('<option value="">-- Memuat Mata Pelajaran... --</option>');

                            $.ajax({
                                url: `/api/get-subjects-by-classroom/${classroomId}`,
                                type: 'GET',
                                dataType: 'json',
                                success: function(subjects) {
                                    let options = '<option value="">-- Pilih Mata Pelajaran --</option>';
                                    if (subjects && subjects.length > 0) {
                                        subjects.forEach(function(subj) {
                                            const isSel = (activeSubjId && activeSubjId == subj.id) ? 'selected' : '';
                                            options += `<option value="${subj.id}" ${isSel}>${subj.name}</option>`;
                                        });
                                        subjectSelect.html(options).trigger('change');

                                        const currentSubj = activeSubjId || subjectSelect.val();
                                        if (currentSubj) {
                                            fetchEditTP(currentSubj, activeTpId);
                                        }
                                    } else {
                                        subjectSelect.html('<option value="">-- Tidak Ada Mapel di Kelas Ini --</option>').trigger('change');
                                        tpSelect.html('<option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>').trigger('change');
                                        btnOpenTpEdit.prop('disabled', true);
                                    }
                                },
                                error: function() {
                                    subjectSelect.html('<option value="">-- Gagal Memuat Mapel --</option>').trigger('change');
                                }
                            });
                        }

                        // Load mapel & TP pertama kali saat modal dibuka
                        $(modalId).on('shown.bs.modal', function () {
                            fetchEditSubjects(classroomSelect.val(), selectedSubjectId, selectedTpId);
                        });

                        // Trigger saat kelas diubah
                        classroomSelect.on('change', function() {
                            fetchEditSubjects($(this).val());
                        });

                        // Trigger saat mapel diubah
                        subjectSelect.on('change', function() {
                            fetchEditTP($(this).val());
                        });

                        // Submit Tambah TP Baru pada Modal Edit
                        $(`#formCreateTpEdit-{{ $assignment->id }}`).on('submit', function(e) {
                            e.preventDefault();

                            const subjectId = subjectSelect.val();
                            const code = $(`#tp_code_edit_{{ $assignment->id }}`).val();
                            const description = $(`#tp_description_edit_{{ $assignment->id }}`).val();
                            const $btnSave = $(`#btnSaveTpEdit-{{ $assignment->id }}`);
                            const $alert = $(`#tp-modal-alert-edit-{{ $assignment->id }}`);

                            if (!subjectId) {
                                $alert.removeClass('d-none alert-success').addClass('alert-danger').html('Pilih mata pelajaran terlebih dahulu.');
                                return;
                            }

                            $btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

                            $.ajax({
                                url: "{{ route('api.store.tp') }}",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    subject_id: subjectId,
                                    code: code,
                                    description: description
                                },
                                success: function(response) {
                                    if (response.success) {
                                        const newTp = response.data;
                                        const codeStr = newTp.code ? newTp.code + ' - ' : '';
                                        const newOption = new Option(`${codeStr}${newTp.description}`, newTp.id, true, true);
                                        
                                        tpSelect.append(newOption).trigger('change');

                                        $(`#formCreateTpEdit-{{ $assignment->id }}`)[0].reset();
                                        $alert.addClass('d-none');
                                        $(`#modalCreateTpEdit-{{ $assignment->id }}`).modal('hide');
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Gagal menyimpan TP.';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                    }
                                    $alert.removeClass('d-none alert-success').addClass('alert-danger').html(errorMsg);
                                },
                                complete: function() {
                                    $btnSave.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan TP');
                                }
                            });
                        });

                    })();
                @endforeach
            @endhasanyrole
        });
    </script>
@endpush