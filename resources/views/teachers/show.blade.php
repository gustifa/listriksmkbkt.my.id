@section('title', 'Detail Data Guru')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Detail Guru</h5>
                        <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-light text-primary fw-bold">Kembali</a>
                    </div>
                    <div class="card-body p-4">
                        <!-- HEADER PROFIL -->
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 90px; height: 90px;">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $teacher->user->name ?? '-' }}</h4>
                            <span class="badge bg-info text-dark">NIP: {{ $teacher->nip ?? '-' }}</span>
                        </div>

                        <!-- RINCIAN DATA -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Email (Login)</label>
                                <p class="border-bottom pb-2 text-dark">{{ $teacher->user->email ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Nomor HP / WhatsApp</label>
                                <p class="border-bottom pb-2 text-dark">{{ $teacher->phone ?? '-' }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Jenis Kelamin</label>
                                <p class="border-bottom pb-2 text-dark">
                                    {{ ($teacher->gender ?? 'L') == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Pendidikan Terakhir</label>
                                <p class="border-bottom pb-2 text-dark">{{ $teacher->education_level ?? '-' }}</p>
                            </div>

                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Tempat, Tanggal Lahir</label>
                                <p class="border-bottom pb-2 text-dark">
                                    {{ $teacher->place_of_birth ?? '-' }}{{ $teacher->date_of_birth ? ', ' . \Carbon\Carbon::parse($teacher->date_of_birth)->translatedFormat('d F Y') : '' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Tugas Tambahan</label>
                                <p class="border-bottom pb-2 text-dark">
                                    @if($teacher->additionalTask)
                                        {{ $teacher->additionalTask->name }} 
                                        <span class="badge bg-secondary ms-1">
                                            {{ $teacher->additionalTask->hours }} Jam ({{ $teacher->additionalTask->type }})
                                        </span>
                                    @else
                                        {{ $teacher->tugas_tambahan ?? '-' }}
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Pangkat / Golongan</label>
                                <p class="border-bottom pb-2 text-dark">
                                    {{ $teacher->pangkat ?? '-' }} {{ $teacher->golongan ? '('.$teacher->golongan.')' : '' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Alamat Lengkap</label>
                                <p class="border-bottom pb-2 text-dark">{{ $teacher->address ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-warning px-4">
                                <i class="fas fa-edit me-1"></i> Edit Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>