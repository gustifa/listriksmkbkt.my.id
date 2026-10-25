@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama project Anda --}}

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    {{-- Ilustrasi / Icon --}}
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-person-x text-muted opacity-50" viewBox="0 0 16 16">
                            <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C8.907 9.038 8.46 9 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m-.646-4.854.646.647.646-.647a.5.5 0 0 1 .708.708l-.647.646.647.646a.5.5 0 0 1-.708.708l-.646-.647-.646.647a.5.5 0 0 1-.708-.708l.647-.646-.647-.646a.5.5 0 0 1 .708-.708"/>
                        </svg>
                    </div>

                    {{-- Pesan --}}
                    <h4 class="fw-bold text-dark mb-2">Data Siswa Tidak Ditemukan</h4>
                    <p class="text-muted mb-4">
                        Akun Anda belum terhubung dengan data siswa aktif atau status akun Anda sedang nonaktif. Silakan hubungi pihak administrator sekolah untuk informasi lebih lanjut.
                    </p>

                    {{-- Tombol Aksi / Logout --}}
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar / Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection