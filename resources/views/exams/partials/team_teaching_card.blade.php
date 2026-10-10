<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fas fa-users text-primary me-2"></i>Team Teaching / Guru Kolaborator
        </h6>
        @if(Auth::user()->hasRole('admin') || $exam->teacher_id === optional(Auth::user()->teacher)->id)
            <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTeamTeaching">
                <i class="fas fa-user-plus me-1"></i> Kelola Tim
            </button>
        @endif
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Pembuat Utama -->
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-crown me-1 text-warning"></i> {{ $exam->teacher->name ?? 'Admin' }} (Pembuat Utama)
            </span>

            <!-- Guru Kolaborator -->
            @forelse($exam->collaborators ?? [] as $collaborator)
                <span class="badge bg-secondary px-3 py-2">
                    <i class="fas fa-user-check me-1"></i> {{ $collaborator->name }}
                </span>
            @empty
                <small class="text-muted ms-2">Belum ada guru kolaborator yang ditambahkan.</small>
            @endforelse
        </div>
    </div>
</div>