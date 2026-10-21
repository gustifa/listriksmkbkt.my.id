<!-- 1. TAMPILAN BENTUK CARD -->
<div id="view-card-container">
    <div class="row g-3">
        @forelse($classrooms as $room)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">{{ $room->name }}</h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#studentsModal{{ $room->id }}">
                                    <i class="fas fa-users me-1"></i> <span id="card-badge-count-{{ $room->id }}">{{ $room->students_count ?? $room->students->count() }}</span>
                                </button>
                            </div>

                            <div class="bg-light rounded p-2 mb-3 small">
                                <div class="mb-2">
                                    <small class="text-muted d-block fw-semibold">Wali Kelas:</small>
                                    @if($room->homeroomTeacher)
                                        <span class="badge bg-primary">{{ $room->homeroomTeacher->name }}</span>
                                    @else
                                        <span class="text-muted italic">- Belum ada -</span>
                                    @endif
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block fw-semibold">Guru BK:</small>
                                    @if($room->counselingTeacher)
                                        <span class="badge bg-info text-dark">{{ $room->counselingTeacher->name }}</span>
                                    @else
                                        <span class="text-muted italic">- Belum ada -</span>
                                    @endif
                                </div>
                                <div>
                                    <small class="text-muted d-block fw-semibold">Ketua Kelas:</small>
                                    @if($room->classLeader)
                                        <span class="fw-bold text-dark"><i class="fas fa-crown text-warning me-1"></i> {{ $room->classLeader->name }}</span>
                                    @else
                                        <span class="text-muted italic">- Kosong -</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-1 justify-content-between pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-info text-white flex-fill" data-bs-toggle="modal" data-bs-target="#officialsModal{{ $room->id }}" title="Setting Kelas">
                                <i class="fas fa-user-cog me-1"></i> Setting
                            </button>
                            <a href="{{ route('classrooms.print_ids', $room->id) }}" class="btn btn-sm btn-dark" target="_blank" title="Cetak ID Card"><i class="fas fa-id-card"></i></a>
                            <a href="{{ route('classrooms.edit', $room->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Nama"><i class="bx bx-message-square-edit"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')"><i class="bx bx-message-square-x"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <p class="mb-0">Data kelas tidak ditemukan.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- 2. TAMPILAN BENTUK TABEL -->
<div id="view-table-container" class="d-none">
    <div class="border-0 shadow card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover table-striped">
                    <thead class="text-center table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Guru BK</th>
                            <th>Ketua Kelas</th>
                            <th width="10%">Siswa</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $key => $room)
                            <tr>
                                <td class="text-center">{{ method_exists($classrooms, 'firstItem') ? $classrooms->firstItem() + $key : $key + 1 }}</td>
                                <td class="text-center fw-bold">{{ $room->name }}</td>
                                <td>
                                    @if($room->homeroomTeacher)
                                        <span class="badge bg-primary">{{ $room->homeroomTeacher->name }}</span>
                                    @else
                                        <span class="text-muted small text-italic">- Belum ada -</span>
                                    @endif
                                </td>
                                <td>
                                    @if($room->counselingTeacher)
                                        <span class="badge bg-info text-dark">{{ $room->counselingTeacher->name }}</span>
                                    @else
                                        <span class="text-muted small text-italic">- Belum ada -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($room->classLeader)
                                        <i class="fas fa-crown text-warning me-1"></i><span class="fw-bold text-dark">{{ $room->classLeader->name }}</span>
                                    @else
                                        <span class="text-muted small text-italic">- Kosong -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#studentsModal{{ $room->id }}">
                                        <i class="fas fa-users me-1"></i> <span id="table-badge-count-{{ $room->id }}">{{ $room->students_count ?? $room->students->count() }}</span>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="text-white btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#officialsModal{{ $room->id }}" title="Atur Wali Kelas & BK"><i class="fas fa-user-cog"></i></button>
                                        <a href="{{ route('classrooms.print_ids', $room->id) }}" class="text-white btn btn-sm btn-dark" target="_blank" title="Cetak ID Card"><i class="fas fa-id-card"></i></a>
                                        <a href="{{ route('classrooms.edit', $room->id) }}" class="text-white btn btn-sm btn-warning" title="Edit Nama"><i class="bx bx-message-square-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')"><i class="bx bx-message-square-x"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">Data kelas tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- LINK PAGINASI UTAMA -->
<div class="d-flex justify-content-center align-items-center mt-4 ajax-pagination">
    {{ $classrooms->links() }}
</div>

@foreach($classrooms as $room)
    @php
        // Set path paginasi khusus untuk ID kelas ini saat pertama render
        $roomUnassignedStudents = clone $unassignedStudents;
        $roomUnassignedStudents->setPath(route('classrooms.unassigned_students', $room->id));
    @endphp

    <div class="modal fade" id="studentsModal{{ $room->id }}" tabindex="-1" aria-hidden="true" data-room-id="{{ $room->id }}">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="min-height: 580px;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-users-cog me-2"></i> Kelola Siswa - {{ $room->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Geser (drag & drop) nama siswa dari daftar kiri ke kanan untuk memasukkan ke kelas, atau sebaliknya.
                    </p>

                    <div class="row g-3">
                        <!-- KOLOM KIRI: SISWA TANPA KELAS -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm custom-fixed-card">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <i class="fas fa-user-plus text-warning me-2"></i>Siswa Tanpa Kelas
                                        </h6>
                                        <span class="badge bg-secondary rounded-pill" id="count-unassigned-{{ $room->id }}">{{ $roomUnassignedStudents->total() }}</span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" id="input-search-unassigned-{{ $room->id }}" class="form-control border-start-0 search-unassigned-server" placeholder="Cari Nama, NIS, NISN..." data-room-id="{{ $room->id }}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="card-body p-2 custom-fixed-body d-flex flex-column justify-content-between">
                                    <div id="unassigned-list-{{ $room->id }}" class="drag-container list-group list-group-flush flex-grow-1" data-room-id="{{ $room->id }}">
                                        @include('classrooms.partials.unassigned_list_items', ['students' => $roomUnassignedStudents, 'classroomId' => $room->id])
                                    </div>

                                    <div id="unassigned-pagination-{{ $room->id }}" class="unassigned-pagination mt-2 d-flex justify-content-center" data-room-id="{{ $room->id }}">
                                        @if($roomUnassignedStudents->hasPages())
                                            {{ $roomUnassignedStudents->links('pagination::bootstrap-4') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: SISWA ROMBEL -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm custom-fixed-card">
                                <div class="card-header bg-primary text-white py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0 text-white">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Siswa Rombel {{ $room->name }}
                                        </h6>
                                        <span class="badge bg-white text-primary rounded-pill" id="count-assigned-{{ $room->id }}">{{ $room->students->count() }}</span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0 filter-assigned-client" placeholder="Cari Nama, NIS, NISN..." data-target-list="assigned-list-{{ $room->id }}">
                                    </div>
                                </div>
                                <div class="card-body p-2 custom-fixed-body">
                                    <div id="assigned-list-{{ $room->id }}" class="drag-container list-group list-group-flush h-100" data-room-id="{{ $room->id }}">
                                        @foreach($room->students as $student)
                                            @php
                                                $searchText = strtolower($student->name . ' ' . ($student->nis ?? '') . ' ' . ($student->nisn ?? ''));
                                            @endphp
                                            <div class="list-group-item list-group-item-action rounded mb-2 border border-primary-subtle shadow-sm style-drag-item d-flex justify-content-between align-items-center p-2 student-card-item" data-student-id="{{ $student->id }}" data-search-text="{{ $searchText }}">
                                                <div>
                                                    <i class="fas fa-grip-vertical text-muted me-2"></i>
                                                    <span class="fw-bold text-dark small">{{ $student->name }}</span><br>
                                                    <small class="text-muted ms-4">
                                                        NIS: {{ $student->nis ?? '-' }}
                                                        @if(!empty($student->nisn)) | NISN: {{ $student->nisn }} @endif
                                                    </small>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge {{ $student->face_descriptor ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="fas {{ $student->face_descriptor ? 'fa-smile' : 'fa-user-slash' }}"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Selesai</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    .custom-fixed-card {
        height: 440px !important;
        display: flex;
        flex-direction: column;
    }
    .custom-fixed-body {
        height: 360px !important;
        overflow-y: auto !important;
        flex: 1 1 auto;
    }
    .style-drag-item { cursor: grab; }
    .style-drag-item:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.4; background-color: #e2e8f0 !important; }
</style>