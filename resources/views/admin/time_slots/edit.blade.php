@section('title', 'Kelola Jam Pelajaran (Time Slots)')

<x-app-layout>
    <div class="page-content">
        <!--breadcrumb-->
        <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3">Master Data</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="p-0 mb-0 breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}"><i class="fas fa-clock me-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Jam Pelajaran (Time Slots)</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="container-fluid py-4">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-business-time me-2"></i> Kelola Jam Pelajaran</h4>
                    <p class="mb-0 text-muted small">Atur slot waktu jam pelajaran dan istirahat harian sekolah.</p>
                </div>
                <button type="button" class="shadow-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTimeSlotModal">
                    <i class="fas fa-plus me-1"></i> Tambah Jam Pelajaran
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">Urutan</th>
                                    <th>Label Jam</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Durasi</th>
                                    <th class="text-center">Tipe</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeSlots as $slot)
                                    @php
                                        $start = \Carbon\Carbon::parse($slot->start_time);
                                        $end = \Carbon\Carbon::parse($slot->end_time);
                                        $duration = $start->diffInMinutes($end);
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold">{{ $slot->sort_order }}</td>
                                        <td class="fw-bold text-dark">{{ $slot->label }}</td>
                                        <td>
                                            <span class="badge bg-light text-primary border border-primary-subtle font-monospace fs-6">
                                                {{ $start->format('H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border border-secondary-subtle font-monospace fs-6">
                                                {{ $end->format('H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><i class="far fa-clock me-1"></i>{{ $duration }} Menit</span>
                                        </td>
                                        <td class="text-center">
                                            @if($slot->type === 'lesson')
                                                <span class="px-3 rounded-pill badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="fas fa-book-open me-1"></i> Jam Pelajaran
                                                </span>
                                            @else
                                                <span class="px-3 rounded-pill badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="fas fa-coffee me-1"></i> Istirahat
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="editTimeSlot({{ json_encode($slot) }})" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('time_slots.destroy', $slot->id) }}" method="POST" id="delete-form-{{ $slot->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete('{{ $slot->id }}')" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-5 text-center text-muted">
                                            <i class="fas fa-clock fa-3x mb-3 text-secondary opacity-50"></i>
                                            <p class="mb-0 fw-bold">Belum Ada Slot Jam Pelajaran</p>
                                            <small>Silakan tambahkan jam pelajaran baru melalui tombol di atas.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TAMBAH JAM PELAJARAN -->
    <div class="modal fade" id="addTimeSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Tambah Jam Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('time_slots.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Label Jam</label>
                            <input type="text" name="label" class="form-control" placeholder="Contoh: Jam 1 (45 Menit) / Istirahat Pertama" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe Slot</label>
                            <select name="type" class="form-select" required>
                                <option value="lesson" selected>Jam Pelajaran</option>
                                <option value="break">Istirahat</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" class="form-control" placeholder="Kosongkan untuk otomatis urutan terakhir">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT JAM PELAJARAN -->
    <div class="modal fade" id="editTimeSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Jam Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Label Jam</label>
                            <input type="text" name="label" id="edit_label" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe Slot</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="lesson">Jam Pelajaran</option>
                                <option value="break">Istirahat</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function editTimeSlot(slot) {
            let updateUrl = "{{ route('time_slots.update', ':id') }}";
            document.getElementById('editForm').action = updateUrl.replace(':id', slot.id);

            document.getElementById('edit_label').value = slot.label;
            document.getElementById('edit_start_time').value = slot.start_time.substring(0, 5);
            document.getElementById('edit_end_time').value = slot.end_time.substring(0, 5);
            document.getElementById('edit_type').value = slot.type;
            document.getElementById('edit_sort_order').value = slot.sort_order;

            var editModal = new bootstrap.Modal(document.getElementById('editTimeSlotModal'));
            editModal.show();
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Time Slot?',
                text: "Jam pelajaran yang dihapus tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
</x-app-layout>