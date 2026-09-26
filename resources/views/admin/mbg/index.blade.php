<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-utensils me-2"></i>Laporan MBG (Makan Bergizi Gratis)</h4>
                <p class="text-muted mb-0">Rekap harian pengambilan dan pengembalian alat makan.</p>
            </div>
            <div>
                <a href="{{ route('admin.mbg.scan') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-qrcode me-2"></i>Buka Scanner
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Total Transaksi</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Sedang Makan (Belum Kembali)</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['taken'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase mb-2 opacity-75">Selesai (Sudah Kembali)</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['returned'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table -->
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <form action="{{ route('admin.mbg.index') }}" method="GET" class="d-flex gap-2 w-auto">
                    <input type="date" name="date" class="form-control w-auto" value="{{ $date }}">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu Ambil</th>
                                <th>Bukti Ambil</th>
                                <th>Waktu Kembali</th>
                                <th>Bukti Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $idx => $row)
                            <tr>
                                <td class="ps-4">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->student->name }}</div>
                                    <small class="text-muted">{{ $row->student->nis }}</small>
                                </td>
                                <td>{{ $row->student->classroom->name ?? '-' }}</td>
                                
                                {{-- AMBIL --}}
                                <td>
                                    @if($row->taken_at)
                                        <div class="fw-bold text-primary">{{ \Carbon\Carbon::parse($row->taken_at)->format('H:i') }}</div>
                                        <small class="text-muted">{{ $row->taken_method }}</small>
                                    @else - @endif
                                </td>
                                <td>
                                    @if($row->taken_image)
                                        <button class="btn btn-sm btn-outline-primary" onclick="showEvidence('{{ asset('storage/'.$row->taken_image) }}', 'Ambil - {{ $row->student->name }}')">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    @else - @endif
                                </td>

                                {{-- KEMBALI --}}
                                <td>
                                    @if($row->returned_at)
                                        <div class="fw-bold text-success">{{ \Carbon\Carbon::parse($row->returned_at)->format('H:i') }}</div>
                                        <small class="text-muted">{{ $row->returned_method }}</small>
                                    @else
                                        <span class="text-muted text-italic">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->returned_image)
                                        <button class="btn btn-sm btn-outline-success" onclick="showEvidence('{{ asset('storage/'.$row->returned_image) }}', 'Kembali - {{ $row->student->name }}')">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    @else - @endif
                                </td>

                                <td>
                                    @if($row->status == 'returned')
                                        <span class="badge bg-success">SELESAI</span>
                                    @else
                                        <span class="badge bg-warning text-dark">MAKAN</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">Belum ada data transaksi hari ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bukti -->
    <div class="modal fade" id="evidenceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evidenceTitle">Bukti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="evidenceImg" src="" class="img-fluid rounded shadow-sm">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showEvidence(url, title) {
            document.getElementById('evidenceImg').src = url;
            document.getElementById('evidenceTitle').innerText = title;
            new bootstrap.Modal(document.getElementById('evidenceModal')).show();
        }
    </script>
    @endpush
</x-app-layout>