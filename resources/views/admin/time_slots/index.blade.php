@section('title', 'Kelola Jam Pelajaran')

<x-app-layout>
    <div class="page-content">
        <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3">Master Data</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="p-0 mb-0 breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="fas fa-clock me-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Jam Pelajaran (Time Slots)</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="py-4 container-fluid">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-clock me-2"></i> Pengaturan Jam Pelajaran</h4>
                    <p class="mb-0 text-muted small">Kelola urutan dan durasi jam pelajaran/istirahat sekolah.</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTimeSlotModal">
                    <i class="fas fa-plus me-1"></i> Tambah Jam Pelajaran
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="border-0 shadow card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Urutan</th>
                                    <th>Label</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th>Tipe</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeSlots as $slot)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $slot->sort_order }}</span></td>
                                        <td class="fw-bold">{{ $slot->label }}</td>
                                        <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                        <td>
                                            @if($slot->type === 'lesson')
                                                <span class="badge bg-primary"><i class="fas fa-book-open me-1"></i> Jam KBM</span>
                                            @else
                                                <span class="badge bg-danger"><i class="fas fa-coffee me-1"></i> Istirahat</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning me-1" 
                                                onclick="editSlot({{ json_encode($slot) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                onclick="deleteSlot('{{ route('time_slots.destroy', $slot->id) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada data jam pelajaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH JAM -->
    <div class="modal fade" id="createTimeSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('time_slots.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="text-white modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-1"></i> Tambah Jam Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label / Nama Jam</label>
                        <input type="text" name="label" class="form-control" placeholder="Contoh: Jam 1, Jam 2, atau Istirahat I" required>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Tipe Slot</label>
                            <select name="type" class="form-select" required>
                                <option value="lesson">Jam KBM (Pelajaran)</option>
                                <option value="break">Jam Istirahat</option>
                            </select>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" class="form-control" value="0" placeholder="1, 2, 3...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT JAM -->
    <div class="modal fade" id="editTimeSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="text-white modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-edit me-1"></i> Edit Jam Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label / Nama Jam</label>
                        <input type="text" name="label" id="edit_label" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Mulai</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Jam Selesai</label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Tipe Slot</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="lesson">Jam KBM (Pelajaran)</option>
                                <option value="break">Jam Istirahat</option>
                            </select>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label fw-bold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORM HAPUS TERSEMBUNYI -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('editTimeSlotModal'));

        function editSlot(slot) {
            let updateUrl = "{{ route('time_slots.update', ':id') }}";
            document.getElementById('editForm').action = updateUrl.replace(':id', slot.id);

            document.getElementById('edit_label').value = slot.label;
            document.getElementById('edit_start_time').value = slot.start_time.substring(0, 5);
            document.getElementById('edit_end_time').value = slot.end_time.substring(0, 5);
            document.getElementById('edit_type').value = slot.type;
            document.getElementById('edit_sort_order').value = slot.sort_order;

            editModal.show();
        }

        function deleteSlot(url) {
            Swal.fire({
                title: 'Hapus Jam Pelajaran?',
                text: "Data yang dihapus tidak bisa dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = url;
                    deleteForm.submit();
                }
            });
        }
    </script>
</x-app-layout>