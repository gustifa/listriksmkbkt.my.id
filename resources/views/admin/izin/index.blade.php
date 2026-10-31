<x-app-layout>
    <!-- CDN Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i> Kelola Approval Izin & Sakit Siswa</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filter -->
                <form method="GET" action="{{ route('admin.izin.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Jenis --</option>
                            <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Siswa / Kelas</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Lampiran</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($izinList as $item)
            <tr>
                <td>
                    <strong>{{ optional($item->student)->name ?? '-' }}</strong><br>
                    <small class="text-muted">{{ optional($item->student->classroom)->name ?? '-' }}</small>
                </td>
                <td>
                    <span class="badge {{ $item->type === 'sakit' ? 'bg-warning text-dark' : 'bg-info' }}">
                        {{ strtoupper($item->type) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                <td>{{ $item->reason }}</td>
                <td>
                    @if($item->status === 'approved')
                        <span class="badge bg-success">Approved</span><br>
                        <small class="text-muted">oleh: {{ $item->approved_by ?? '-' }}</small>
                    @else
                        <span class="badge bg-secondary">Pending</span>
                    @endif
                </td>
                <td>
                    @if($item->attachment_path && Storage::disk('public')->exists($item->attachment_path))
                        <a href="{{ asset('storage/' . $item->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-image"></i> Lihat Foto
                        </a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center align-items-center gap-1">
                        {{-- Tombol Setujui (Approve) --}}
                        @if($item->status === 'pending')
                            <form action="{{ route('admin.izin.approve', $item->id) }}" method="POST" class="d-inline m-0 p-0">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Setujui Pengajuan">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Tombol Tolak / Batalkan --}}
                        @if($item->status === 'pending')
                            <form action="{{ route('admin.izin.reject', $item->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Yakin ingin menolak pengajuan ini?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning px-2 py-1 text-white" title="Tolak Pengajuan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        @endif

                        {{-- Tombol Hapus Permanen --}}
                        <form action="{{ route('admin.izin.destroy', $item->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data izin siswa ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Hapus Permanen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                        {{-- Tombol Cetak Surat --}}
                        @if($item->type === 'izin')
                            <a href="{{ route('admin.izin.cetak-surat', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Cetak Surat Izin">
                                <i class="bi bi-printer"></i>
                            </a>
                        @else
                            <a href="{{ route('admin.izin.cetak-sakit', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Cetak Keterangan Dokter">
                                <i class="bi bi-printer"></i>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan izin/sakit.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

                <div class="mt-3">
                    {{ $izinList->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>