<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Tanggal</th>
                <th>Jam Datang</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th class="text-center pe-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailies as $row)
                @php
                    $bg = match(strtolower($row->status)) {
                        'hadir' => 'success',
                        'terlambat' => 'warning',
                        'sakit', 'izin' => 'info',
                        default => 'danger'
                    };
                @endphp
                <tr>
                    <td class="ps-3 fw-semibold">
                        {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l, d F Y') }}
                    </td>
                    <td>
                        @if($row->arrival_time)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                {{ \Carbon\Carbon::parse($row->arrival_time)->format('H:i') }} WIB
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if($row->departure_time)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                {{ \Carbon\Carbon::parse($row->departure_time)->format('H:i') }} WIB
                            </span>
                        @else
                            <span class="text-muted small">- Belum Pulang -</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $bg }} {{ $row->status == 'terlambat' || $row->status == 'izin' ? 'text-dark' : '' }} px-3 py-1 text-uppercase">
                            {{ $row->status == 'hadir' ? 'TEPAT WAKTU' : $row->status }}
                        </span>
                    </td>
                    <td class="text-center pe-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="showDailyDetailModal('{{ $row->id }}')">
                            <i class="fas fa-eye me-1"></i> Detail
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-4 text-center text-muted">Tidak ada data absensi harian yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>