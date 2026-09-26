@section('title', 'Daftar Pembinaan Siswa')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Daftar Pembinaan Siswa</h4>
                <p class="text-muted mb-0">Daftar siswa yang memerlukan perhatian khusus berdasarkan poin pelanggaran.</p>
            </div>
            
            <!-- Statistik Ringkas (Opsional) -->
            <div class="d-none d-md-flex gap-3">
                <div class="px-3 py-2 bg-white rounded shadow-sm border-start border-4 border-danger">
                    <small class="text-muted d-block">Kasus Berat</small>
                    <span class="fw-bold text-danger">{{ $students->where('total_points', '>=', 50)->count() }} Siswa</span>
                </div>
                <div class="px-3 py-2 bg-white rounded shadow-sm border-start border-4 border-warning">
                    <small class="text-muted d-block">Kasus Sedang</small>
                    <span class="fw-bold text-warning">{{ $students->whereBetween('total_points', [20, 49])->count() }} Siswa</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol me-2"></i>Ranking Poin Pelanggaran</h6>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('admin.guidance.index') }}" method="GET" class="d-flex gap-2 justify-content-md-end">
                            <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="Cari Nama / NIS..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="30%">Identitas Siswa</th>
                                <th width="15%">Kelas</th>
                                <th width="15%" class="text-center">Total Poin</th>
                                <th width="15%" class="text-center">Kategori</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $student)
                                @php
                                    $points = $student->total_points;
                                    $badgeClass = 'bg-success';
                                    $category = 'Ringan';
                                    
                                    if ($points >= 50) {
                                        $badgeClass = 'bg-danger';
                                        $category = 'BERAT';
                                    } elseif ($points >= 20) {
                                        $badgeClass = 'bg-warning text-dark';
                                        $category = 'SEDANG';
                                    }
                                @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $students->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold border" style="width: 40px; height: 40px;">
                                            {{ substr($student->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $student->name }}</div>
                                            <small class="text-muted font-monospace">{{ $student->nis }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</span>
                                </td>
                                <td class="text-center">
                                    <h5 class="mb-0 fw-bold {{ $points >= 50 ? 'text-danger' : ($points >= 20 ? 'text-warning' : 'text-success') }}">
                                        {{ $points }}
                                    </h5>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3">{{ $category }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.guidance.show', $student->id) }}" class="btn btn-sm btn-primary shadow-sm px-3 fw-bold">
                                        <i class="fas fa-edit me-1"></i> Bina / Proses
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/success.svg" class="w-25 opacity-50 mb-3" alt="No Data">
                                    <h5 class="text-muted">Tidak ada siswa dengan catatan pelanggaran.</h5>
                                    <p class="text-secondary small">Sekolah aman dan tertib!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>