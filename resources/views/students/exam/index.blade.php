@extends('layouts.app')

@section('title', 'Daftar Ujian Saya')

@section('content')
<div class="container py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Daftar Ujian Saya</h3>
            <p class="text-muted small mb-0">Pilih ujian yang tersedia untuk mulai atau melanjutkan pengerjaan</p>
        </div>
    </div>

    <!-- Filter Tab: Ujian Aktif & Riwayat Selesai -->
    <ul class="nav nav-pills mb-4 gap-2" id="examTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold px-4" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-exams" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Ujian Tersedia / Berlangsung
                <span class="badge bg-primary text-white ms-1">{{ $activeExams->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold px-4" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-exams" type="button" role="tab">
                <i class="bi bi-check2-all me-1"></i> Riwayat Ujian Selesai
                <span class="badge bg-secondary text-white ms-1">{{ $completedExams->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="examTabsContent">
        <!-- TAB 1: UJIAN TERSAAT INI / BERLANGSUNG -->
        <div class="tab-pane fade show active" id="active-exams" role="tabpanel">
            <div class="row g-4">
                @forelse($activeExams as $exam)
                    @php
                        $studentId = Auth::user()->student->id ?? Auth::id();
                        // Ambil status pengerjaan siswa menggunakan student_id
                        $session = $exam->sessions->where('student_id', $studentId)->first();
                        $isOngoing = $session && $session->status === 'ongoing';
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 rounded-3 position-relative overflow-hidden">
                            <!-- Indicator Strip Banner -->
                            <div class="position-absolute top-0 start-0 end-0 py-1 {{ $isOngoing ? 'bg-warning' : 'bg-primary' }}"></div>

                            <div class="card-body d-flex flex-column p-4 pt-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                    </span>

                                    @if($isOngoing)
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1 fw-bold">
                                            <i class="bi bi-arrow-repeat me-1"></i> Sedang Berlangsung
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            Tersedia
                                        </span>
                                    @endif
                                </div>

                                <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-person me-1"></i> Pengajar: {{ $exam->teacher->name ?? '-' }}
                                </p>

                                <!-- Summary Info Durasi & Soal -->
                                <div class="row text-center border-top border-bottom py-2 my-auto bg-light rounded-2">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block" style="font-size: 11px;">DURASI</small>
                                        <strong class="text-dark">{{ $exam->duration_minutes }} Menit</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 11px;">JUMLAH SOAL</small>
                                        <strong class="text-dark">{{ $exam->questions_count ?? 0 }} Soal</strong>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="pt-3 mt-3 border-top">
                                    @if($isOngoing && $session)
                                        <a href="{{ route('student.exam.show', [$exam->id, $session->id]) }}" class="btn btn-warning text-dark fw-bold w-100 py-2">
                                            Lanjutkan Ujian <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('student.exam.start', $exam->id) }}" class="btn btn-primary fw-bold w-100 py-2">
                                            Mulai Kerjakan <i class="bi bi-play-circle ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center text-muted">
                        <i class="bi bi-journal-x display-4 d-block mb-2 text-secondary"></i>
                        Tidak ada ujian yang sedang aktif atau perlu dikerjakan saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: RIWAYAT UJIAN SELESAI -->
        <div class="tab-pane fade" id="history-exams" role="tabpanel">
            <div class="row g-4">
                @forelse($completedExams as $exam)
                    @php
                        $studentId = Auth::user()->student->id ?? Auth::id();
                        $session = $exam->sessions->where('student_id', $studentId)->first();
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 rounded-3">
                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                        {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                                    </span>
                                    <span class="badge bg-success border border-success px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>

                                <!-- Menampilkan Waktu Selesai dengan Safe Nullsafe Operator (?->) -->
                                <p class="text-muted small mb-3">
                                    Selesai pada: {{ $session?->submit_time ? \Carbon\Carbon::parse($session->submit_time)->translatedFormat('d M Y, H:i') : ($session?->updated_at ? $session->updated_at->translatedFormat('d M Y, H:i') : '-') }}
                                </p>

                                <!-- Score Display jika ada -->
                                @if(isset($session?->score))
                                    <div class="alert alert-light border text-center py-2 mb-3 rounded-2">
                                        <small class="text-muted d-block" style="font-size: 11px;">NILAI AKHIR</small>
                                        <span class="display-6 fw-bold text-primary">{{ number_format($session->score, 2) }}</span>
                                    </div>
                                @endif

                                <div class="mt-auto pt-2">
                                    @if($session)
                                        <a href="{{ route('student.exam.result', [$exam->id, $session->id]) }}" class="btn btn-outline-info w-100 fw-semibold">
                                            <i class="bi bi-eye me-1"></i> Lihat Detail Hasil
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center text-muted">
                        <i class="bi bi-archive display-4 d-block mb-2 text-secondary"></i>
                        Belum ada riwayat ujian yang telah diselesaikan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flash Message Success
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        // Flash Message Error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Perhatian!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc3545'
            });
        @endif
    });
</script>
@endsection