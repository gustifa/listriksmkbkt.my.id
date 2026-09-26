@section('title', 'Data Ruangan & Bengkel')

<x-app-layout>
    <div class="page-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i> Data Ruangan / Bengkel</h5>
                        <button class="btn btn-light btn-sm fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                            <i class="fas fa-plus me-1"></i> Tambah Ruangan
                        </button>
                    </div>
                    <div class="card-body">

                        <!-- Alert -->
                        {{-- @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif --}}

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered table-striped table-hover" id="dataTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="10%">Kode</th>
                                        <th>Nama Ruangan</th>
                                        <th width="15%">Jenis</th>
                                        <th width="15%">Lokasi</th>
                                        <th width="10%" class="text-center">Kapasitas</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rooms as $index => $room)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><span class="badge bg-secondary font-monospace">{{ $room->code }}</span></td>
                                            <td class="fw-bold">{{ $room->name }}</td>
                                            <td>
                                                @php
                                                    $badges = [
                                                        'teori' => 'bg-info text-dark',
                                                        'labor' => 'bg-warning text-dark',
                                                        'bengkel' => 'bg-danger',
                                                        'lapangan' => 'bg-success',
                                                        'lainnya' => 'bg-secondary'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $badges[$room->type] ?? 'bg-secondary' }}">
                                                    {{ ucfirst($room->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $room->location ?? '-' }}</td>
                                            <td class="text-center">{{ $room->capacity ?? 0 }} Siswa</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning me-1"
                                                    onclick="editRoom('{{ $room->id }}', '{{ $room->name }}', '{{ $room->code }}', '{{ $room->type }}', '{{ $room->capacity }}', '{{ $room->location }}', '{{ $room->description }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-4 text-center text-muted">Belum ada data ruangan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="text-white modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Tambah Ruangan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('rooms.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3 col-md-8">
                                <label class="form-label fw-bold">Nama Ruangan <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Bengkel Listrik 1" required>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" placeholder="BL-01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Ruangan <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="teori">Kelas Teori</option>
                                <option value="bengkel">Bengkel / Workshop</option>
                                <option value="labor">Laboratorium Komputer/IPA</option>
                                <option value="lapangan">Lapangan Olahraga</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Lokasi</label>
                                <input type="text" name="location" class="form-control" placeholder="Gedung A, Lt 2">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Kapasitas (Siswa)</label>
                                <input type="number" name="capacity" class="form-control" placeholder="36">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
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

    <!-- MODAL EDIT -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3 col-md-8">
                                <label class="form-label fw-bold">Nama Ruangan <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="edit_code" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Ruangan <span class="text-danger">*</span></label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="teori">Kelas Teori</option>
                                <option value="bengkel">Bengkel / Workshop</option>
                                <option value="labor">Laboratorium Komputer/IPA</option>
                                <option value="lapangan">Lapangan Olahraga</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Lokasi</label>
                                <input type="text" name="location" id="edit_location" class="form-control">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Kapasitas (Siswa)</label>
                                <input type="number" name="capacity" id="edit_capacity" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editRoom(id, name, code, type, capacity, location, description) {
            // Set Action URL
            let url = "{{ route('rooms.update', ':id') }}";
            url = url.replace(':id', id);
            document.getElementById('editForm').action = url;

            // Set Values
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_location').value = location;
            document.getElementById('edit_description').value = description;

            // Show Modal
            var myModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
            myModal.show();
        }
    </script>
    @endpush
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Cek apakah ada session 'success' yang dikirim dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000 // Notifikasi hilang otomatis setelah 2 detik
            });
        @endif

        // Opsional: Cek jika ada error validasi
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif
    </script>

</x-app-layout>
