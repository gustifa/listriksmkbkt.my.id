@section('title')
   Pengaturan Kop Surat & Sekolah
@endsection
<x-app-layout>
    <div class="page-content">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Detail Guru</h5>
                        <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-light text-primary fw-bold">Kembali</a>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                            <h4 class="mt-2 fw-bold">{{ $teacher->user->name }}</h4>
                            <span class="badge bg-info text-dark">NIP: {{ $teacher->nip ?? '-' }}</span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Email (Login)</label>
                                <p class="border-bottom pb-2">{{ $teacher->user->email }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Nomor HP</label>
                                <p class="border-bottom pb-2">{{ $teacher->phone ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Jenis Kelamin</label>
                                <p class="border-bottom pb-2">
                                    {{ $teacher->gender == 'L' ? 'Laki-laki' : ($teacher->gender == 'P' ? 'Perempuan' : '-') }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Pendidikan Terakhir</label>
                                <p class="border-bottom pb-2">{{ $teacher->education_level ?? '-' }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="small text-muted fw-bold">Alamat</label>
                                <p class="border-bottom pb-2">{{ $teacher->address ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i> Edit Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
