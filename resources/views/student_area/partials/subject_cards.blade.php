<div class="row g-3">
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
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-{{ $bg }}">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="far fa-calendar-alt me-1 text-primary"></i>
                                {{ \Carbon\Carbon::parse($row->date)->translatedFormat('d M Y') }}
                            </span>
                            <span class="badge bg-{{ $bg }} {{ $row->status == 'terlambat' || $row->status == 'izin' ? 'text-dark' : '' }} text-uppercase px-3 py-1">
                                {{ $row->status }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">
                            {{ $row->schedule->subject->name ?? $row->schedule->subject_name ?? '-' }}
                        </h5>
                        
                        <p class="text-muted small mb-3">
                            <i class="fas fa-user-tie me-1"></i> Guru: <strong>{{ $row->schedule->teacher->name ?? '-' }}</strong>
                        </p>
                    </div>

                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="far fa-clock me-1 text-danger"></i> {{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }} WIB
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="showDetailModal('{{ $row->id }}')">
                            <i class="fas fa-eye me-1"></i> Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-info-circle fa-2x mb-2 opacity-50 d-block"></i>
            <p class="mb-0">Tidak ada riwayat absensi pelajaran yang cocok dengan filter.</p>
        </div>
    @endforelse
</div>