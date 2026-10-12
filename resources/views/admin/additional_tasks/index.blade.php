@section('title', 'Master Tugas Tambahan')

<x-app-layout>
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Master Data</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tugas Tambahan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary mb-0"><i class="bx bx-briefcase me-2"></i>Kelola Tugas Tambahan</h4>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddTasks">
                <i class="bx bx-plus me-1"></i> Tambah Master Tugas
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Tugas Tambahan</th>
                                <th>Ekuivalensi Jam (JJP)</th>
                                <th>Kategori</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $item->name }}</td>
                                    <td><span class="badge bg-primary fs-6">{{ $item->hours }} Jam</span></td>
                                    <td>
                                        <span class="badge {{ $item->type === 'TTU' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $item->type }} ({{ $item->type === 'TTU' ? 'Utama' : 'Ekstra' }})
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editTask('{{ $item->id }}', '{{ $item->name }}', '{{ $item->hours }}', '{{ $item->type }}')">
                                            <i class="bx bx-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('additional-tasks.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf 
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i> Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada master tugas tambahan yang dikelola.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH MASTER TUGAS -->
    <div class="modal fade" id="modalAddTasks" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('additional-tasks.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Tambah Master Tugas Tambahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tugas Tambahan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kepala Bengkel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Jam (JJP)</label>
                        <input type="number" min="0" name="hours" class="form-control" value="2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="type" class="form-select">
                            <option value="TTU">TTU (Tugas Tambahan Utama)</option>
                            <option value="TTE" selected>TTE (Tugas Tambahan Ekstra)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT MASTER TUGAS -->
    <div class="modal fade" id="modalEditTask" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditTask" method="POST" class="modal-content">
                @csrf 
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">Edit Master Tugas Tambahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tugas Tambahan</label>
                        <input type="text" id="edit_task_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Jam (JJP)</label>
                        <input type="number" min="0" id="edit_task_hours" name="hours" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select id="edit_task_type" name="type" class="form-select">
                            <option value="TTU">TTU (Tugas Tambahan Utama)</option>
                            <option value="TTE">TTE (Tugas Tambahan Ekstra)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editTask(id, name, hours, type) {
            $('#edit_task_name').val(name);
            $('#edit_task_hours').val(hours);
            $('#edit_task_type').val(type);
            $('#formEditTask').attr('action', "{{ url('admin/additional-tasks') }}/" + id);
            $('#modalEditTask').modal('show');
        }
    </script>
</x-app-layout>