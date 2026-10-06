@extends('layouts.app')

@section('title', 'Konfirmasi Ujian - ' . $exam->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <!-- Header Card -->
                <div class="card-header bg-primary text-white p-4 rounded-top-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-white text-primary mb-2 fw-bold">
                                {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                            </span>
                            <h4 class="fw-bold mb-0">{{ $exam->title }}</h4>
                        </div>
                        <i class="bi bi-file-earmark-text display-5 text-white-50"></i>
                    </div>
                </div>

                <!-- Body Card -->
                <div class="card-body p-4">
                    <!-- Ringkasan Informasi Ujian -->
                    <div class="row text-center g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block">Jumlah Soal</small>
                                <strong class="fs-5 text-dark">{{ $exam->questions_count ?? 0 }} Soal</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block">Durasi Waktu</small>
                                <strong class="fs-5 text-dark">{{ $exam->duration_minutes }} Menit</strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block">Tipe Soal</small>
                                <strong class="fs-6 text-dark text-uppercase">
                                    {{ str_replace('_', ' ', $exam->type) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block">Guru Pengampu</small>
                                <strong class="fs-6 text-dark">{{ $exam->teacher->name ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Petunjuk / Aturan Ujian -->
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Petunjuk Pengerjaan:</h6>
                        <ul class="mb-0 small ps-3">
                            <li>Pastikan koneksi internet Anda stabil sebelum memulainya.</li>
                            <li>Waktu akan mulai berjalan mundur tepat setelah Anda menekan tombol <strong>"Mulai Ujian"</strong>.</li>
                            <li>Jawaban akan tersimpan secara otomatis setiap kali Anda memilih/mengisi soal.</li>
                            <li>Dilarang me-refresh atau menutup browser saat pengerjaan berlangsung.</li>
                        </ul>
                    </div>

                    <!-- Form Aksi Mulai Ujian -->
                    <form action="{{ route('student.exam.begin', $exam->id) }}" method="POST" id="form-start-exam">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>

                            @if(isset($session) && $session->status === 'completed')
                                <button type="button" class="btn btn-secondary px-4" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Anda Sudah Menyelesaikan Ujian
                                </button>
                            @else
                                <button type="button" class="btn btn-primary btn-lg px-4 fw-bold" id="btn-start-exam">
                                    <i class="bi bi-play-circle me-1"></i> 
                                    {{ isset($session) ? 'Lanjutkan Ujian' : 'Mulai Ujian Sekarang' }}
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
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
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#dc3545'
            });
        @endif

        // Konfirmasi SweetAlert2 Sebelum Memulai Ujian
        const btnStart = document.getElementById('btn-start-exam');
        if (btnStart) {
            btnStart.addEventListener('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('form-start-exam');

                Swal.fire({
                    title: 'Siap Dimulai?',
                    text: 'Waktu ujian sebesar {{ $exam->duration_minutes }} menit akan langsung berjalan setelah tombol diklik!',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Mulai Sekarang!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading saat redirect/submit
                        Swal.fire({
                            title: 'Menyiapkan Soal...',
                            text: 'Harap tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endsection