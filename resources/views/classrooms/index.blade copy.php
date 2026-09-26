@section('title')
   Setting Kelas
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <!-- <h3 class="fw-bold text-primary"><i class="fas fa-school me-2"></i> Master Data Kelas</h3> -->
            <a href="{{ route('classrooms.create') }}" class="shadow-sm btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Kelas
            </a>
        </div>

        <!-- Alert dari Controller (Fallback jika SweetAlert JS gagal) -->
        {{-- @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif --}}

        <div class="border-0 shadow card">
            <div class="card-body">

                <!-- Form Pencarian -->
                <form action="{{ route('classrooms.index') }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama Kelas..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-dark"><i class="bx bx-search"></i> Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle table-hover table-striped">
                        <thead class="text-center table-dark">
                            <tr>
                                <th width="10%">No</th>
                                <th>Nama Kelas</th>
                                <th width="20%">Jumlah Siswa</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classrooms as $key => $room)
                                <tr>
                                    <td class="text-center">{{ $classrooms->firstItem() + $key }}</td>
                                    <td class="text-center fw-bold">{{ $room->name }}</td>
                                    <td class="text-center">
                                        <!-- TOMBOL PEMICU MODAL LIHAT SISWA -->
                                        <button type="button" class="btn btn-outline-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#studentsModal{{ $room->id }}">
                                            <i class="fas fa-users me-1"></i> {{ $room->students->count() }} Siswa
                                        </button>

                                        <!-- MODAL DAFTAR SISWA -->
                                        <div class="modal fade" id="studentsModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="fas fa-user-graduate me-2"></i> Siswa Kelas {{ $room->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        @if($room->students->count() > 0)
                                                            <div class="list-group list-group-flush">
                                                                @foreach($room->students as $student)
                                                                    <div class="p-2 list-group-item d-flex justify-content-between align-items-center">
                                                                        <!-- Info Siswa -->
                                                                        <div>
                                                                            <span class="fw-bold">{{ $student->name }}</span>
                                                                            <br>
                                                                            <small class="text-muted">NIS: {{ $student->nis }}</small>
                                                                        </div>

                                                                        <!-- Badge & Action -->
                                                                        <div class="gap-2 d-flex align-items-center">
                                                                            <!-- Status Wajah -->
                                                                            @if($student->face_descriptor)
                                                                                <span class="badge bg-success" title="Wajah Terdaftar"><i class="fas fa-smile"></i></span>
                                                                            @else
                                                                                <span class="badge bg-secondary" title="Belum Rekam Wajah"><i class="fas fa-user-slash"></i></span>
                                                                            @endif

                                                                            <!-- Tombol Keluarkan Siswa (Unassign) -->
                                                                            <!-- Asumsi Route: route('students.remove_class', $id) -->
                                                                            <form id="remove-student-form-{{ $student->id }}" action="{{ route('students.remove_class', $student->id) }}" method="POST">
                                                                                @csrf
                                                                                @method('PATCH')
                                                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" title="Keluarkan dari Kelas" onclick="confirmRemoveStudent('{{ $student->id }}', '{{ $student->name }}')">
                                                                                    <i class="fas fa-times"></i>
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="py-4 text-center text-muted">
                                                                <img src="https://img.icons8.com/ios/50/cccccc/empty-box.png" class="mb-2" width="50">
                                                                <p class="mb-0">Belum ada siswa di kelas ini.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END MODAL -->

                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('classrooms.edit', $room->id) }}" class="text-white btn btn-sm btn-warning" title="Edit">
                                                <i class="bx bx-message-square-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus dengan SweetAlert -->
                                            <form id="delete-form-{{ $room->id }}" action="{{ route('classrooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')">
                                                    <i class="bx bx-message-square-x"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-5 text-center text-muted">
                                        <img src="https://img.icons8.com/ios/100/cccccc/classroom.png" width="60" class="mb-3 opacity-50">
                                        <p class="mb-0">Data kelas belum tersedia.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <!-- <div class="mt-3">
                    {{ $classrooms->withQueryString()->links() }}
                </div> -->
            </div>
        </div>
    </div>


</x-app-layout>
