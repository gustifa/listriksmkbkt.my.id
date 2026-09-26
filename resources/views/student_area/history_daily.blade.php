@section('title')
   Data Seluruh Murid
@endsection

<x-app-layout>
    <div class="page-content">

        <div class="container">
            <h4 class="mb-4 fw-bold text-primary"><i class="fas fa-door-open me-2"></i> Riwayat Datang & Pulang</h4>

            <div class="border-0 shadow card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Datang</th>
                                    <th>Jam Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailies as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('l, d F Y') }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-sign-in-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($row->arrival_time)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($row->departure_time)
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-sign-out-alt me-1"></i>
                                                    {{ \Carbon\Carbon::parse($row->departure_time)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted small">- Belum Pulang -</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->status == 'hadir')
                                                <span class="badge bg-success">TEPAT WAKTU</span>
                                            @elseif($row->status == 'terlambat')
                                                <span class="badge bg-warning text-dark">TERLAMBAT</span>
                                            @else
                                                <span class="badge bg-secondary">{{ strtoupper($row->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-muted">Belum ada data absensi harian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $dailies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
