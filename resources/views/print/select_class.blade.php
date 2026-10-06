@section('title')
    Cetak Kartu & Akun Siswa
@endsection

<x-app-layout>
    <div class="page-content">
        <!-- Breadcrumb & Top Bar -->
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-id-card me-2"></i> Cetak Kartu Identitas & Akun Siswa</h4>
                <small class="text-muted">Pilih metode pencetakan kartu atau rekap akun (Username & Password)</small>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <!-- Tombol Cetak Massal Kartu (Satu Sekolah) -->
                <a href="{{ route('print.all') }}" class="shadow-sm btn btn-dark" target="_blank">
                    <i class="fas fa-print me-1"></i> Cetak Seluruh Kartu
                </a>

                <!-- FITUR BARU: Cetak Akun (Username, Password & Barcode) Seluruh Sekolah -->
                <a href="{{ route('print.accounts.all') }}" class="shadow-sm btn btn-info text-white" target="_blank">
                    <i class="fas fa-key me-1"></i> Cetak Seluruh Akun & Barcode
                </a>
            </div>
        </div>

        <!-- Grid Daftar Kelas -->
        <div class="row">
            @foreach($classrooms as $class)
            <div class="mb-4 col-md-6 col-lg-4">
                <div class="shadow-sm card h-100 border-left-primary">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-primary">{{ $class->name }}</h5>
                                <span class="border badge bg-light text-dark">{{ $class->students_count }} Siswa</span>
                            </div>

                            <hr class="my-2">
                        </div>

                        <div>
                            @if($class->students_count > 0)
                                <!-- Baris 1: Cetak Kartu Siswa -->
                                <div class="mb-2 gap-2 d-flex">
                                    <a href="{{ route('print.class', $class->id) }}" target="_blank" class="btn btn-sm btn-primary flex-fill" title="Cetak Kartu Full Satu Kelas">
                                        <i class="fas fa-id-card me-1"></i> Kartu Kelas
                                    </a>

                                    <a href="{{ route('print.select', $class->id) }}" class="btn btn-sm btn-success flex-fill" title="Pilih Siswa Tertentu">
                                        <i class="fas fa-check-square me-1"></i> Pilih Siswa
                                    </a>
                                </div>

                                <!-- Baris 2: FITUR BARU Cetak Akun & Barcode Kelas -->
                                <a href="{{ route('print.accounts.class', $class->id) }}" target="_blank" class="btn btn-sm btn-outline-info w-100 fw-semibold" title="Cetak Username, Password & Barcode">
                                    <i class="fas fa-qrcode me-1"></i> Cetak Akun & Barcode Kelas
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary w-100" disabled>Kelas Kosong</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <style>
        /* Aksen border kiri berwarna biru */
        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        /* Efek hover agar kartu sedikit terangkat */
        .card:hover {
            transform: translateY(-3px);
            transition: transform 0.3s ease;
        }
    </style>
</x-app-layout>
