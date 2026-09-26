@section('title', 'Akses PKL Dibatasi')

<x-app-layout>
    <div class="page-content d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="text-center col-md-6">
            <div class="mb-4">
                <i class="fas fa-lock text-secondary opacity-25" style="font-size: 8rem;"></i>
            </div>
            <h2 class="fw-bold text-dark">Akses Pengguna Belum Diaktifkan</h2>
            <p class="text-muted">
                Halo <strong>{{ $teacher->name }}</strong>, saat ini belum Akun Anda untuk belum diaktifkan.
            </p>
            <div class="alert alert-warning d-inline-block px-4 py-2 rounded-pill mt-2">
                <i class="fas fa-info-circle me-1"></i> Silakan hubungi Admin.
            </div>
            <div class="mt-5">
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary px-4 rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>