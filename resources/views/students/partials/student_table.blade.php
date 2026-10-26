<style>
    .custom-table thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Style Interaktif Header Sort */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
    }
    .sortable-header:hover {
        background: #e2e8f0 !important;
    }

    .custom-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
    }

    .nis-badge {
        background: #e2e8f0;
        color: #334155;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
    }

    .btn-action-dropdown {
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<div class="table-responsive" style="min-height: 320px; overflow-y: visible;">
    <table class="table custom-table table-hover align-middle mb-0 w-100">
        <thead>
            <tr>
                @php
                    $currentSort = request('sort_by', 'name');
                    $currentOrder = request('sort_order', 'asc');
                    
                    function getSortIcon($column, $currentSort, $currentOrder) {
                        if ($currentSort !== $column) {
                            return '<i class="bx bx-hash text-muted opacity-50 ms-1"></i>';
                        }
                        return $currentOrder === 'asc' 
                            ? '<i class="bx bx-sort-up text-primary ms-1 fs-6"></i>' 
                            : '<i class="bx bx-sort-down text-primary ms-1 fs-6"></i>';
                    }
                @endphp

                <!-- Kolom NIS -->
                <th width="12%" class="sortable-header btn-sort" data-column="nis">
                    <div class="d-flex align-items-center">
                        NIS {!! getSortIcon('nis', $currentSort, $currentOrder) !!}
                    </div>
                </th>

                <!-- Kolom Nama Siswa -->
                <th width="30%" class="sortable-header btn-sort" data-column="name">
                    <div class="d-flex align-items-center">
                        NAMA SISWA {!! getSortIcon('name', $currentSort, $currentOrder) !!}
                    </div>
                </th>

                <!-- Kolom Kelas -->
                <th width="15%" class="sortable-header btn-sort" data-column="classroom_id">
                    <div class="d-flex align-items-center">
                        KELAS {!! getSortIcon('classroom_id', $currentSort, $currentOrder) !!}
                    </div>
                </th>

                <th width="15%">NO. HP ORTU</th>
                <th width="15%" class="text-center">STATUS WAJAH</th>
                <th width="13%" class="text-center">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr>
                <td><span class="nis-badge">{{ $student->nis }}</span></td>
                <td class="fw-bold text-dark">{{ $student->name ?? $student->nama }}</td>
                <td>
                    <span class="badge bg-light text-dark border">
                        {{ $student->classroom->name ?? $student->classroom->nama_kelas ?? '-' }}
                    </span>
                </td>
                <td>{{ $student->parent_phone ?? $student->phone ?? '-' }}</td>
                <td class="text-center">
                    @if($student->has_face || (isset($student->faceDescriptor) && $student->faceDescriptor))
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                            <i class='bx bx-check-circle me-1'></i> Terdaftar
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2">
                            <i class='bx bx-x-circle me-1'></i> Belum Ada
                        </span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="dropdown position-static">
                        <button class="btn btn-light border btn-sm dropdown-toggle btn-action-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Aksi
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('students.show', $student->id) }}">
                                    <i class='bx bx-show text-info me-2'></i> Detail Siswa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ url('/admin/scan-wajah/' . $student->id) }}">
                                    <i class='bx bx-camera text-primary me-2'></i> Scan / Register Wajah
                                </a>
                            </li>

                            @hasanyrole('admin|super-admin')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('students.edit', $student->id) }}">
                                    <i class='bx bx-edit text-warning me-2'></i> Edit Data
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-danger btn-delete-swal" data-id="{{ $student->id }}" data-name="{{ $student->name ?? $student->nama }}">
                                    <i class='bx bx-trash me-2'></i> Hapus Siswa
                                </button>
                            </li>
                            @endhasanyrole
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class='bx bx-search-alt fs-1 d-block mb-2 text-secondary'></i>
                    Data siswa tidak ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Footer Pagination -->
<div class="px-3 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">
        Menampilkan <b>{{ $students->firstItem() ?? 0 }}</b> - <b>{{ $students->lastItem() ?? 0 }}</b> dari <b>{{ $students->total() }}</b> data
    </small>
    <div>
        {{ $students->links() }}
    </div>
</div>