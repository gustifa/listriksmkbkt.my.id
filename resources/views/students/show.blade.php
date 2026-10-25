@section('title', 'Detail Data Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Siswa</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}"><i class="bx bx-user"></i> Data Murid</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Siswa</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow border-0 text-center p-3">
                    <div class="card-body">
                        <img src="{{ asset('assets/images/default-avatar.png') }}" class="rounded-circle p-1 bg-primary" width="110" height="110" alt="Foto Siswa">
                        <h5 class="mb-0 mt-3">{{ $student->name }}</h5>
                        <p class="text-secondary mb-1">NIS: {{ $student->nis }}</p>
                        <span class="badge bg-success">{{ $student->classroom->name ?? 'Belum Ada Kelas' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Informasi Detail Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Jenis Kelamin</div>
                            <div class="col-md-8">: {{ $student->gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">No. Telepon</div>
                            <div class="col-md-8">: {{ $student->phone ?? '-' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Alamat</div>
                            <div class="col-md-8">: {{ $student->address ?? '-' }}</div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('students.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>