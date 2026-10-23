@if($classrooms->count() > 0)
    <!-- TAMPILAN CARD -->
    <div id="view-card-container" class="row g-3">
        @foreach($classrooms as $classroom)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden {{ !$classroom->is_active ? 'bg-light opacity-75' : '' }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold text-dark mb-0 me-2">{{ $classroom->name }}</h5>
                            <div class="d-flex align-items-center gap-1">
                                <!-- SWITCH TOGGLE STATUS AKTIF/NONAKTIF -->
                                <div class="form-check form-switch m-0" title="Aktifkan/Nonaktifkan Rombel">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="switch-card-{{ $classroom->id }}" 
                                           onchange="toggleRombelStatus('{{ $classroom->id }}', this.checked)"
                                           {{ $classroom->is_active ? 'checked' : '' }}>
                                </div>
                                <span id="card-badge-count-{{ $classroom->id }}" class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                    <i class="bx bx-user me-1"></i>{{ $classroom->students_count ?? 0 }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-light p-2 rounded-3 mb-3 small">
                            <div class="mb-1 text-truncate">
                                <span class="text-muted d-block">Wali Kelas:</span>
                                <strong class="text-dark">{{ $classroom->homeroomTeacher->name ?? '- Belum ada -' }}</strong>
                            </div>
                            <div class="mb-1 text-truncate">
                                <span class="text-muted d-block">Guru BK:</span>
                                <strong class="text-dark">{{ $classroom->counselingTeacher->name ?? '- Belum ada -' }}</strong>
                            </div>
                            <div class="text-truncate">
                                <span class="text-muted d-block">Ketua Kelas:</span>
                                <strong class="text-dark">{{ $classroom->classLeader->name ?? '- Kosong -' }}</strong>
                            </div>
                        </div>

                        <div class="d-flex gap-1">
                            <!-- TOMBOL SETTING MODAL -->
                            <button type="button" 
                                    class="btn btn-info btn-sm text-white fw-bold flex-grow-1 rounded-3" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#studentsModal{{ $classroom->id }}"
                                    onclick="fetchUnassignedStudents('{{ $classroom->id }}')">
                                <i class="bx bx-cog me-1"></i> Setting
                            </button>
                            
                            <a href="{{ route('classrooms.printAllIdsPdf', $classroom->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3" title="Cetak ID Card">
                                <i class="bx bx-id-card"></i>
                            </a>
                            <a href="{{ route('classrooms.edit', $classroom->id) }}" class="btn btn-outline-warning btn-sm rounded-3" title="Edit Kelas">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" onclick="confirmDelete('{{ $classroom->id }}', '{{ $classroom->name }}')" class="btn btn-outline-danger btn-sm rounded-3" title="Hapus Kelas">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL SETTING SISWA ROMBEL -->
            <div class="modal fade" id="studentsModal{{ $classroom->id }}" tabindex="-1" aria-hidden="true" data-room-id="{{ $classroom->id }}">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0">
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title fw-bold text-white">
                                <i class="bx bx-group me-2"></i>Pengaturan Siswa Rombel - {{ $classroom->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- KOLOM KIRI: SISWA TANPA KELAS -->
                                <div class="col-md-6">
                                    <div class="card border border-2 h-100 rounded-3 shadow-none">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-secondary small">Siswa Tanpa Kelas</span>
                                            <span id="count-unassigned-{{ $classroom->id }}" class="badge bg-secondary rounded-pill">
                                                {{ isset($unassignedStudents) ? count($unassignedStudents) : 0 }}
                                            </span>
                                        </div>
                                        <div class="card-body p-2">
                                            <input type="text" 
                                                   id="input-search-unassigned-{{ $classroom->id }}"
                                                   class="form-control form-control-sm mb-2 search-unassigned-server" 
                                                   data-room-id="{{ $classroom->id }}" 
                                                   placeholder="Cari siswa tanpa kelas...">
                                            
                                            <div id="unassigned-list-{{ $classroom->id }}" class="list-group student-drop-zone p-1" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                                                @forelse($unassignedStudents ?? [] as $student)
                                                    <div class="list-group-item student-card-item rounded-3 mb-1 p-2 border shadow-sm cursor-move" 
                                                         data-student-id="{{ $student->id }}"
                                                         data-search-text="{{ strtolower($student->name . ' ' . ($student->nis ?? '')) }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong class="d-block text-dark small">{{ $student->name }}</strong>
                                                                <small class="text-muted">NIS: {{ $student->nis ?? '-' }}</small>
                                                            </div>
                                                            <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-4 text-muted small">
                                                        Siswa tidak ditemukan.
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div id="unassigned-pagination-{{ $classroom->id }}" class="mt-2 unassigned-pagination" data-room-id="{{ $classroom->id }}">
                                                <!-- Dynamic Pagination via AJAX -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- KOLOM KANAN: SISWA DALAM KELAS INI -->
                                <div class="col-md-6">
                                    <div class="card border border-2 h-100 rounded-3 shadow-none">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-primary small">Siswa Rombel {{ $classroom->name }}</span>
                                            <span id="count-assigned-{{ $classroom->id }}" class="badge bg-primary rounded-pill">{{ $classroom->students->count() }}</span>
                                        </div>
                                        <div class="card-body p-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm mb-2 filter-assigned-client" 
                                                   data-target-list="assigned-list-{{ $classroom->id }}" 
                                                   placeholder="Filter siswa di kelas ini...">
                                            
                                            <div id="assigned-list-{{ $classroom->id }}" class="list-group student-drop-zone p-1" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                                                @foreach($classroom->students as $student)
                                                    <div class="list-group-item student-card-item rounded-3 mb-1 p-2 border shadow-sm cursor-move" 
                                                         data-student-id="{{ $student->id }}"
                                                         data-search-text="{{ strtolower($student->name . ' ' . $student->nis) }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong class="d-block text-dark small">{{ $student->name }}</strong>
                                                                <small class="text-muted">NIS: {{ $student->nis ?? '-' }}</small>
                                                            </div>
                                                            <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- TAMPILAN TABEL -->
    <div id="view-table-container" class="table-responsive bg-white rounded-4 shadow-sm border p-3 d-none">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Status</th>
                    <th>Nama Kelas</th>
                    <th>Wali Kelas</th>
                    <th>Guru BK</th>
                    <th>Ketua Kelas</th>
                    <th class="text-center">Siswa</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classrooms as $classroom)
                    <tr class="{{ !$classroom->is_active ? 'table-light text-muted' : '' }}">
                        <td>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="switch-table-{{ $classroom->id }}" 
                                       onchange="toggleRombelStatus('{{ $classroom->id }}', this.checked)"
                                       {{ $classroom->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="fw-bold text-dark">{{ $classroom->name }}</td>
                        <td>{{ $classroom->homeroomTeacher->name ?? '-' }}</td>
                        <td>{{ $classroom->counselingTeacher->name ?? '-' }}</td>
                        <td>{{ $classroom->classLeader->name ?? '-' }}</td>
                        <td class="text-center">
                            <span id="table-badge-count-{{ $classroom->id }}" class="badge bg-light text-dark border">
                                {{ $classroom->students_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" 
                                        class="btn btn-info text-white" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#studentsModal{{ $classroom->id }}"
                                        onclick="fetchUnassignedStudents('{{ $classroom->id }}')">
                                    <i class="bx bx-cog"></i>
                                </button>
                                <a href="{{ route('classrooms.printAllIdsPdf', $classroom->id) }}" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bx bx-id-card"></i>
                                </a>
                                <a href="{{ route('classrooms.edit', $classroom->id) }}" class="btn btn-outline-warning">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <button type="button" onclick="confirmDelete('{{ $classroom->id }}', '{{ $classroom->name }}')" class="btn btn-outline-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- PAGINASI AJAX -->
    <div class="mt-4 ajax-pagination d-flex justify-content-center">
        {{ $classrooms->links() }}
    </div>
@else
    <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
        <i class="bx bx-folder-open text-muted display-4 mb-2"></i>
        <h6 class="fw-bold text-secondary">Tidak ada data rombongan belajar yang ditemukan.</h6>
    </div>
@endif