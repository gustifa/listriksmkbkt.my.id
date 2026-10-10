@extends('layouts.app')

@section('title', 'Hasil Ujian - ' . $exam->title)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden">
                <!-- Header Card -->
                <div class="card-header bg-primary text-white p-4 border-0">
                    <span class="badge bg-white text-primary mb-2 fw-bold px-3 py-2 rounded-pill">
                        {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                    </span>
                    <h4 class="fw-bold mb-0">{{ $exam->title }}</h4>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Icon & Score Badge -->
                    <div class="mb-4">
                        @if(isset($session->score) && $session->score >= 75)
                            <div class="avatar-lg bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-trophy-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-success">Selamat! Ujian Telah Selesai</h5>
                        @else
                            <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-check-circle-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Ujian Telah Berhasil Diselesaikan</h5>
                        @endif
                    </div>

                    <!-- Display Nilai -->
                    <div class="p-4 bg-light rounded-3 border mb-4">
                        <small class="text-muted d-block fw-bold text-uppercase tracking-wide mb-1" style="font-size: 11px;">Nilai Akhir Anda</small>
                        <h1 class="display-3 fw-extrabold text-primary mb-0">
                            {{ isset($session->score) ? number_format($session->score, 2) : '-' }}
                        </h1>
                    </div>

                    <!-- Informasi Detail Waktu -->
                    <div class="row g-2 text-start small mb-4">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">WAKTU MULAI</span>
                                <strong class="text-dark">
                                    {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->translatedFormat('d M Y, H:i') : '-' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-white">
                                <span class="text-muted d-block" style="font-size: 11px;">WAKTU SELESAI</span>
                                <strong class="text-dark">
                                    {{ $session->submit_time ? \Carbon\Carbon::parse($session->submit_time)->translatedFormat('d M Y, H:i') : '-' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('student.exam.index') }}" class="btn btn-primary btn-lg fw-bold rounded-3">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Ujian
                        </a>
                        <!-- Tombol Review Jawaban (Hanya jika Guru mengizinkan) -->
                        @if($exam->allow_review)
                            <a href="{{ route('student.exams.review', ['exam' => $exam->id, 'session' => $session->id]) }}" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-eye me-1"></i> Review Jawaban
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flash Message Success dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Ujian Selesai!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        // Flash Message Error (misal waktu habis)
        @if(session('error'))
            Swal.fire({
                icon: 'warning',
                title: 'Waktu Habis!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc3545'
            });
        @endif
    });
</script>
@endsection