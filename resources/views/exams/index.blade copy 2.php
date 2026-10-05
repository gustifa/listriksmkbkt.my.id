@extends('layouts.app')

@section('title', 'Daftar Ujian Guru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & Tombol Tambah Ujian -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Kelola Ujian</h3>
            <p class="text-muted small mb-0">Kelola seluruh data jadwal, durasi, dan informasi ujian di sistem.</p>
        </div>
        <a href="{{ route('exams.create') }}" class="btn btn-primary fw-semibold">
            <i class="fas fa-plus me-1"></i> Buat Ujian Baru
        </a>
    </div>

    <!-- Alert Notifikasi Sukses / Error -->
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

    <!-- Grid Kartu Ujian -->
    <div class="row g-4">
        @forelse($exams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100 position-relative">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Badges -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                    {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                </span>
                                <span class="badge {{ $exam->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $exam->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <!-- Judul Ujian & Guru -->
                            <h4 class="fw-bold text-dark mb-1">{{ $exam->title }}</h4>
                            <small class="text-muted d-block mb-3">Guru: {{ $exam->teacher->name ?? '-' }}</small>

                            <!-- Target Kelas -->
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
                            <!-- Ringkasan Durasi & Jumlah Soal -->
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

                            <!-- Baris Tombol Aksi (Detail, Edit Waktu, Import, Hapus) -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                    Detail
                                </a>
                                
                                <!-- Tombol Modal Edit Waktu/Data Ujian -->
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

            <!-- MODAL POPUP EDIT UJIAN & WAKTU -->
            <div class="modal fade" id="editExamModal-{{ $exam->id }}" tabindex="-1" aria-labelledby="editExamModalLabel-{{ $exam->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('guru.exams.update', $exam->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold text-dark" id="editExamModalLabel-{{ $exam->id }}">
                                    <i class="fas fa-clock text-warning me-2"></i>Edit Waktu & Informasi Ujian
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <!-- Judul Ujian -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Judul Ujian <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ $exam->title }}" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Durasi (Menit) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="duration_minutes" class="form-control" value="{{ $exam->duration_minutes }}" min="1" required>
                                            <span class="input-group-text">Menit</span>
                                        </div>
                                    </div>

                                    <!-- Status Aktif -->
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active_{{ $exam->id }}" value="1" {{ $exam->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-dark" for="is_active_{{ $exam->id }}">
                                                Status Ujian Aktif
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Tanggal & Waktu Mulai -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Jadwal Mulai Ujian</label>
                                        <input type="datetime-local" name="start_time" class="form-control" value="{{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('Y-m-d\TH:i') : '' }}">
                                    </div>

                                    <!-- Tanggal & Waktu Selesai -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Jadwal Selesai Ujian</label>
                                        <input type="datetime-local" name="end_time" class="form-control" value="{{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('Y-m-d\TH:i') : '' }}">
                                    </div>
                                </div>

                                <!-- Target Kelas -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark">Target Kelas <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 border rounded-3 p-3 bg-light">
                                        @foreach($allClassrooms ?? \App\Models\Classroom::all() as $cls)
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}" id="cls_{{ $exam->id }}_{{ $cls->id }}" {{ $exam->classrooms->contains($cls->id) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium text-dark" for="cls_{{ $exam->id }}_{{ $cls->id }}">
                                                    {{ $cls->name }}
                                                </label>
                                            </div>
                                        @endforeach
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
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-folder-open fs-1 mb-2"></i>
                <p class="mb-0">Belum ada data ujian yang tersedia.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection