<x-app-layout>
    <div class="page-content">

        <!-- Header & Filter -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-utensils me-2"></i>Absensi MBG (Makan Bergizi Gratis)</h4>
                <p class="mb-0 text-muted">Laporan harian pengambilan makan siang siswa.</p>
            </div>
            <div>
                <a href="{{ route('admin.mbg.scan') }}" class="shadow-sm btn btn-primary">
                    <i class="fas fa-qrcode me-2"></i>Buka Scanner
                </a>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="mb-4 row">
            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-primary h-100">
                    <div class="card-body">
                        <h6 class="mb-2 opacity-75 text-uppercase">Total Hari Ini</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['total'] }} <small class="fs-6">Siswa</small></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-info h-100">
                    <div class="card-body">
                        <h6 class="mb-2 opacity-75 text-uppercase">Via Barcode</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['barcode'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-success h-100">
                    <div class="card-body">
                        <h6 class="mb-2 opacity-75 text-uppercase">Via Face Rec.</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['face'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-0 shadow-sm card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="mb-2 opacity-75 text-uppercase">Input Manual</h6>
                        <h2 class="mb-0 fw-bold">{{ $stats['manual'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="border-0 shadow-lg card">
            <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                <form action="{{ route('admin.mbg.index') }}" method="GET" class="gap-2 d-flex">
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                </form>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Waktu Ambil</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Metode</th>
                                <th>Bukti</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $idx => $row)
                            <tr>
                                <td class="ps-4">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-primary">{{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i:s') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $row->student->name }}</div>
                                    <small class="text-muted">{{ $row->student->nis }}</small>
                                </td>
                                <td>{{ $row->student->classroom->name ?? '-' }}</td>
                                <td>
                                    @if($row->method == 'barcode') <span class="badge bg-info">Barcode</span>
                                    @elseif($row->method == 'face') <span class="badge bg-success">Wajah</span>
                                    @else <span class="badge bg-warning text-dark">Manual</span> @endif
                                </td>
                                <td>
                                    @if($row->image_evidence)
                                        <button class="btn btn-sm btn-outline-primary" onclick="showEvidence('{{ asset('storage/'.$row->image_evidence) }}', '{{ $row->student->name }}')">
                                            <i class="fas fa-image"></i> Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $row->recorded_by ?? 'System' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">
                                    <i class="mb-3 fas fa-box-open fa-3x"></i><br>
                                    Belum ada data pengambilan makan untuk tanggal ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Foto -->
    <div class="modal fade" id="evidenceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evidenceTitle">Bukti Pengambilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="text-center modal-body">
                    <img id="evidenceImg" src="" class="rounded shadow-sm img-fluid">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showEvidence(url, name) {
            document.getElementById('evidenceImg').src = url;
            document.getElementById('evidenceTitle').innerText = 'Bukti: ' + name;
            new bootstrap.Modal(document.getElementById('evidenceModal')).show();
        }
    </script>
    @endpush
</x-app-layout>
