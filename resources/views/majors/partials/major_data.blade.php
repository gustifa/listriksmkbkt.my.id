<!-- MODE CARD -->
<div id="view-card-container" class="row g-3">
    @forelse($majors as $major)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border shadow-sm h-100 rounded-3">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary text-uppercase px-2 py-1" style="font-size: 11px; font-weight: 700;">
                            {{ $major->code }}
                        </span>
                        @hasanyrole('admin|super-admin')
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm p-1 rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <button type="button" class="dropdown-item py-2 btn-edit-major" data-id="{{ $major->id }}">
                                        <i class="bx bx-edit text-warning me-2"></i> Edit Jurusan
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item py-2 text-danger btn-delete-major" data-id="{{ $major->id }}" data-name="{{ $major->name }}">
                                        <i class="bx bx-trash me-2"></i> Hapus Jurusan
                                    </button>
                                </li>
                            </ul>
                        </div>
                        @endhasanyrole
                    </div>

                    <h6 class="fw-bold text-dark mb-1">{{ $major->name }}</h6>
                    <p class="text-muted small mb-3">
                        <i class="bx bx-bookmark me-1 text-primary"></i>
                        {{ $major->program->name ?? 'Tanpa Program Keahlian' }}
                    </p>

                    <hr class="my-2 text-muted opacity-25">

                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted" style="font-size: 11px;">Ketua Program:</span>
                            @if(isset($major->program->teacher) && $major->program->teacher)
                                <span class="badge bg-light text-dark border fw-semibold">
                                    <i class="bx bx-user me-1"></i>{{ $major->program->teacher->name }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 11px; font-style: italic;">Belum Diatur</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted" style="font-size: 11px;">Kepala Bengkel:</span>
                            @if($major->workshopTeacher)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                    <i class="bx bx-wrench me-1"></i>{{ $major->workshopTeacher->name }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 11px; font-style: italic;">Belum Diatur</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="bx bx-folder-open text-muted mb-2" style="font-size: 48px;"></i>
            <h6 class="fw-bold text-muted mb-0">Belum ada data jurusan / konsentrasi keahlian.</h6>
        </div>
    </div>
    @endforelse
</div>

<!-- MODE TABEL BERSIH -->
<div id="view-table-container" class="card border shadow-sm rounded-3 d-none">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center" style="font-size: 11px;">NO</th>
                        <th style="font-size: 11px;">PROGRAM KEAHLIAN</th>
                        <th style="font-size: 11px;">NAMA KONSENTRASI (MAJOR)</th>
                        <th class="text-center" style="font-size: 11px;">KETUA PROGRAM</th>
                        <th class="text-center" style="font-size: 11px;">KABENG</th>
                        <th width="10%" class="text-center" style="font-size: 11px;">KODE</th>
                        @hasanyrole('admin|super-admin')
                        <th width="12%" class="text-center" style="font-size: 11px;">AKSI</th>
                        @endhasanyrole
                    </tr>
                </thead>
                <tbody>
                    @forelse($majors as $index => $major)
                    <tr>
                        <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold text-dark">{{ $major->program->name ?? '-' }}</td>
                        <td class="fw-bold text-dark">{{ $major->name }}</td>
                        <td class="text-center">
                            @if(isset($major->program->teacher) && $major->program->teacher)
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bx bx-user me-1"></i>{{ $major->program->teacher->name }}
                                </span>
                            @else
                                <span class="text-muted small">Belum Diatur</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($major->workshopTeacher)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bx bx-wrench me-1"></i>{{ $major->workshopTeacher->name }}
                                </span>
                            @else
                                <span class="text-muted small">Belum Diatur</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-uppercase">{{ $major->code }}</span>
                        </td>
                        @hasanyrole('admin|super-admin')
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit-major" data-id="{{ $major->id }}" title="Edit Data">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-major" data-id="{{ $major->id }}" data-name="{{ $major->name }}" title="Hapus Data">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                        @endhasanyrole
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Data jurusan tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PAGINASI AJAX -->
@if(method_exists($majors, 'links') && $majors->hasPages())
<div class="ajax-pagination mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">
        Menampilkan <b>{{ $majors->firstItem() }}</b> - <b>{{ $majors->lastItem() }}</b> dari <b>{{ $majors->total() }}</b> jurusan
    </small>
    <div>
        {{ $majors->links() }}
    </div>
</div>
@endif