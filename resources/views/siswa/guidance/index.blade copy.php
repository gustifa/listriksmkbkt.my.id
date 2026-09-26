@section('title', 'Riwayat Kedisiplinan')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Riwayat Kedisiplinan & Pembinaan</h4>
        </div>

        <!-- Header Info Siswa -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 fw-bold">{{ $student->name }}</h4>
                    <p class="mb-0 op-8">NIS: {{ $student->nis }} | Kelas: {{ $student->classroom->name ?? '-' }}</p>
                </div>
                <div class="text-center">
                    <small class="d-block text-white-50 uppercase fw-bold">Total Poin Pelanggaran</small>
                    <h2 class="mb-0 fw-bold">{{ $student->violations->sum(fn($v) => $v->type->points ?? 0) }}</h2>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- KOLOM KIRI: RIWAYAT PELANGGARAN -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-exclamation-circle me-2"></i>Riwayat Pelanggaran Saya</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Pelanggaran</th>
                                        <th class="text-center">Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($student->violations->sortByDesc('date') as $v)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ \Carbon\Carbon::parse($v->date)->format('d/m/y') }}</td>
                                        <td>
                                            <span class="fw-bold d-block text-dark">{{ $v->type->name ?? 'Unknown' }}</span>
                                            <small class="text-muted">{{ $v->note }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger rounded-pill">{{ $v->type->points ?? 0 }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada pelanggaran tercatat. Alhamdulillah!</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: RIWAYAT PEMBINAAN & UPLOAD -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-success"><i class="fas fa-hands-helping me-2"></i>Riwayat Pembinaan & Konseling</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($student->guidances->sortByDesc('date') as $g)
                            <li class="list-group-item px-0 pb-4">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-primary">{{ $g->teacher->name ?? ($g->teacher->user->name ?? 'Guru Pembimbing') }} <small class="text-muted">({{ ucfirst($g->role_context) }})</small></span>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($g->date)->format('d M Y') }}</small>
                                </div>
                                <p class="mb-1 small mt-2"><strong>Masalah:</strong> {{ $g->problem_summary }}</p>
                                <p class="mb-2 small text-success"><strong>Solusi/Janji:</strong> {{ $g->advice }} / {{ $g->student_commitment }}</p>
                                
                                <!-- FORM UPLOAD SURAT -->
                                <div class="p-3 bg-light rounded border border-secondary mt-3">
                                    <h6 class="fw-bold small mb-2"><i class="fas fa-file-contract me-1"></i> Surat Perjanjian</h6>
                                    
                                    @if($g->agreement_file)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i> Telah Diunggah</span>
                                            <a href="{{ asset('storage/'.$g->agreement_file) }}" target="_blank" class="btn btn-sm btn-outline-info">Lihat Berkas Anda</a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning small py-2 mb-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Minta format surat kepada Guru BK/Walas, lalu upload bukti tanda tangan (Orang Tua & Siswa) di sini.
                                        </div>
                                        <form action="{{ route('student.guidance.upload', $g->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                                            @csrf
                                            <input type="file" name="agreement_file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png">
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Upload</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                            @empty
                            <div class="text-center text-muted small py-4">Belum ada riwayat pembinaan.</div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>