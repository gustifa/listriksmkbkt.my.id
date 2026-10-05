@extends('layouts.app')

@section('title', 'Daftar Ujian Siswa')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Daftar Ujian</h3>
            <p class="text-muted small mb-0">Kelola dan kerjakan ujian yang tersedia untuk kelas Anda.</p>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Navigasi Tab Ujian -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="examTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold px-4 py-2" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-exams" type="button" role="tab">
                <i class="bi bi-journal-text me-2"></i> Ujian Aktif
                <span class="badge bg-white text-primary ms-2">{{ $activeExams->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold px-4 py-2" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-exams" type="button" role="tab">
                <i class="bi bi-check2-circle me-2"></i> Riwayat Ujian
                <span class="badge bg-secondary ms-2">{{ $completedExams->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Konten Tab -->
    <div class="tab-content" id="examTabsContent">
        <!-- 1. TAB UJIAN AKTIF -->
        <div class="tab-pane fade show active" id="active-exams" role="tabpanel">
            <div class="row g-4">
                @forelse($activeExams as $exam)
                    @php
                        // Ambil sesi ujian siswa jika ada
                        $session = $exam->sessions->first();
                        $hasSession = !is_null($session);
                        $isCompleted = $hasSession && $session->status === 'completed';
                    @endphp

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100 hover-shadow transition-all">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Header Card: Subject & Status Badges -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
                                            {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                        </span>
                                        @if($isCompleted)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Selesai</span>
                                        @elseif($hasSession)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Sedang Dikerjakan</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">Tersedia</span>
                                        @endif
                                    </div>

                                    <!-- Judul Ujian & Guru -->
                                    <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-person me-1"></i> Guru: <strong>{{ $exam->teacher->name ?? '-' }}</strong>
                                    </p>

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
                                    <!-- Durasi & Jumlah Soal -->
                                    <div class="bg-light rounded-3 p-2 d-flex justify-content-around text-center mb-3 border">
                                        <div>
                                            <small class="text-muted d-block">Durasi</small>
                                            <span class="fw-bold text-dark">{{ $exam->duration_minutes }} Menit</span>
                                        </div>
                                        <div class="border-end"></div>
                                        <div>
                                            <small class="text-muted d-block">Jumlah Soal</small>
                                            <span class="fw-bold text-dark">{{ $exam->questions_count }} Soal</span>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    @if($isCompleted)
                                        <a href="{{ route('student.exam.result', [$exam->id, $session->id]) }}" class="btn btn-outline-success w-100 fw-bold py-2">
                                            <i class="bi bi-eye me-1"></i> Lihat Hasil
                                        </a>
                                    @elseif($hasSession)
                                        <a href="{{ route('student.exam.show', [$exam->id, $session->id]) }}" class="btn btn-warning text-white w-100 fw-bold py-2">
                                            <i class="bi bi-play-circle me-1"></i> Lanjutkan Ujian
                                        </a>
                                    @else
                                        <a href="{{ route('student.exam.start', $exam->id) }}" class="btn btn-primary w-100 fw-bold py-2">
                                            <i class="bi bi-pencil-square me-1"></i> Mulai Ujian
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 py-5 text-center text-muted">
                            <i class="bi bi-journal-x fs-1 mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Tidak Ada Ujian Aktif</h6>
                            <p class="small mb-0">Saat ini belum ada ujian aktif yang didaftarkan untuk kelas Anda.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. TAB RIWAYAT UJIAN -->
        <div class="tab-pane fade" id="completed-exams" role="tabpanel">
            <div class="row g-4">
                @forelse($completedExams as $exam)
                    @php
                        $session = $exam->sessions->first();
                    @endphp

                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 fw-semibold">
                                            {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                        </span>
                                        <span class="badge bg-success">Selesai</span>
                                    </div>

                                    <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                                    <p class="text-muted small mb-3">
                                        Selesai pada: {{ $session && $session->finished_at ? \Carbon\Carbon::parse($session->finished_at)->format('d M Y, H:i') : '-' }}
                                    </p>

                                    <div class="mb-3">
                                        <small class="text-muted d-block fw-semibold mb-1">Target Kelas:</small>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($exam->classrooms as $cls)
                                                <span class="badge bg-light text-dark border">{{ $cls->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    @if($session)
                                        <a href="{{ route('student.exam.result', [$exam->id, $session->id]) }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                                            <i class="bi bi-bar-chart-line me-1"></i> Lihat Hasil Nilai
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 py-5 text-center text-muted">
                            <i class="bi bi-clock-history fs-1 mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Riwayat Ujian</h6>
                            <p class="small mb-0">Ujian yang telah Anda selesaikan akan muncul di sini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important;
    }
</style>
@endsection
