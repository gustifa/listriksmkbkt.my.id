<div class="row g-3">
    @forelse($dailies as $row)
        @php
            $bg = match(strtolower($row->status)) {
                'hadir' => 'success',
                'terlambat' => 'warning',
                'sakit', 'izin' => 'info',
                default => 'danger'
            };
        @endphp
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-{{ $bg }}">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark border">
                                <i class="far fa-calendar-alt me-1 text-primary"></i>
                                {{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}
                            </span>
                            <span class="badge bg-{{ $bg }} {{ $row->status == 'terlambat' || $row->status == 'izin' ? 'text-dark' : '' }} px-3 py-1 text-uppercase">
                                {{ $row->status == 'hadir' ? 'TEPAT WAKTU' : $row->status }}
                            </span>
                        </div>

                        <div class="row text-center g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">
                                        <i class="fas fa-sign-in-alt text-success me-1"></i> Jam Datang
                                    </small>
                                    <span class="fw-bold text-dark">
                                        {{ $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('H:i') . ' WIB' : '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">
                                        <i class="fas fa-sign-out-alt text-danger me-1"></i> Jam Pulang
                                    </small>
                                    <span class="fw-bold text-dark">
                                        @if($row->departure_time)
                                            {{ \Carbon\Carbon::parse($row->departure_time)->format('H:i') }} WIB
                                        @else
                                            <small class="text-muted fw-normal">Belum Pulang</small>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-top d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="showDailyDetailModal('{{ $row->id }}')">
                            <i class="fas fa-eye me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-door-closed fa-2x mb-2 opacity-50 d-block"></i>
            <p class="mb-0">Tidak ada riwayat absensi harian yang cocok dengan filter.</p>
        </div>
    @endforelse
</div>