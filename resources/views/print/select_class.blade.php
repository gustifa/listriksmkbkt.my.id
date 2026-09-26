@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            {{-- <h3><i class="fas fa-id-card me-2"></i> Cetak Kartu Identitas Siswa</h3> --}}
            <div>
                <!-- Tombol Cetak Massal (Satu Sekolah) -->
                <a href="{{ route('print.all') }}" class="shadow-sm btn btn-dark" target="_blank">
                    <i class="fas fa-print me-1"></i> Cetak Seluruh Sekolah
                </a>
            </div>
        </div>

        <!-- Grid Daftar Kelas -->
        <div class="row">
            @foreach($classrooms as $class)
            <div class="mb-4 col-md-4">
                <div class="shadow-sm card h-100 border-left-primary">
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold text-primary">{{ $class->name }}</h5>
                            <span class="border badge bg-light text-dark">{{ $class->students_count }} Siswa</span>
                        </div>

                        <hr>

                        <div class="gap-2 d-flex">
                            @if($class->students_count > 0)
                                <!-- Opsi 1: Cetak Full Satu Kelas -->
                                <a href="{{ route('print.class', $class->id) }}" target="_blank" class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-print"></i> Full Kelas
                                </a>

                                <!-- Opsi 2: Pilih Manual Siswa Tertentu -->
                                <a href="{{ route('print.select', $class->id) }}" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-check-square"></i> Pilih Siswa
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
    /* Aksen border kiri berwarna biru agar mirip dashboard admin */
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
