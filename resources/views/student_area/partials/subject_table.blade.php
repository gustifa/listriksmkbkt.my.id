<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Tanggal</th>
                <th>Jam</th>
                <th>Mata Pelajaran</th>
                <th>Guru</th>
                <th>Status</th>
                <th class="text-center pe-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $row)
                @php
                    $bg = match($row->status) { 
                        'hadir' => 'success', 
                        'terlambat' => 'warning', 
                        'sakit' => 'primary', 
                        'izin' => 'info', 
                        default => 'danger' 
                    };
                @endphp
                <tr>
                    <td class="ps-3 fw-semibold">
                        {{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="far fa-clock me-1 text-danger"></i>
                            {{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }} WIB
                        </span>
                    </td>
                    <td class="fw-bold text-dark">
                        {{ $row->schedule->subject->name ?? $row->schedule->subject_name ?? '-' }}
                    </td>
                    <td>{{ $row->schedule->teacher->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $bg }} {{ $row->status == 'terlambat' || $row->status == 'izin' ? 'text-dark' : '' }} text-uppercase px-3 py-1">{{ $row->status }}</span>
                    </td>
                    <td class="text-center pe-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="showDetailModal('{{ $row->id }}')">
                            <i class="fas fa-eye me-1"></i> Detail
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-muted">Tidak ada riwayat absensi yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>