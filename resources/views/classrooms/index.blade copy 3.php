@section('title')
    Setting Kelas
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h3 class="fw-bold text-primary"><i class="fas fa-school me-2"></i> Master Data Kelas</h3>
            <a href="{{ route('classrooms.create') }}" class="shadow-sm btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Kelas
            </a>
        </div>

        {{-- LOGIC: Ambil Data Guru & Filter Wali Kelas untuk Modal --}}
        @php
            // Ambil semua guru untuk dropdown (Wali Kelas & BK)
            $allTeachers = \App\Models\Teacher::orderBy('name')->get();

            // Ambil ID guru yang sudah menjadi wali kelas di kelas manapun
            // Ini digunakan untuk memfilter dropdown agar satu guru tidak memegang 2 kelas
            $takenHomeroomIds = \App\Models\Classroom::whereNotNull('homeroom_teacher_id')->pluck('homeroom_teacher_id')->toArray();
        @endphp

        <div class="border-0 shadow card">
            <div class="card-body">

                <!-- Form Pencarian -->
                <form action="{{ route('classrooms.index') }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama Kelas..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-dark"><i class="bx bx-search"></i> Cari</button>
                    </div>
                </form>

                <!-- Menampilkan Error Validasi (jika ada duplicate entry dari modal) -->
                <!-- @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif -->

                <div class="table-responsive">
                    <table id="example" class="table align-middle table-hover table-striped">
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
                                    <td class="text-center">{{ $classrooms->firstItem() + $key }}</td>
                                    <td class="text-center fw-bold">{{ $room->name }}</td>

                                    {{-- Kolom Wali Kelas --}}
                                    <td>
                                        @if($room->homeroomTeacher)
                                            <span class="badge bg-primary">{{ $room->homeroomTeacher->user->name }}</span>
                                        @else
                                            <span class="text-muted small text-italic">- Belum ada -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Guru BK --}}
                                    <td>
                                        @if($room->counselingTeacher)
                                            <span class="badge bg-info text-dark">{{ $room->counselingTeacher->user->name }}</span>
                                        @else
                                            <span class="text-muted small text-italic">- Belum ada -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Ketua Kelas --}}
                                    <td>
                                        @if($room->classLeader)
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-crown text-warning me-1"></i>
                                                <span class="fw-bold text-dark">{{ $room->classLeader->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted small text-italic">- Kosong -</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <!-- TOMBOL PEMICU MODAL LIHAT SISWA -->
                                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#studentsModal{{ $room->id }}">
                                            <i class="fas fa-users me-1"></i> {{ $room->students->count() }}
                                        </button>

                                        <!-- MODAL DAFTAR SISWA -->
                                        <div class="modal fade" id="studentsModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="fas fa-user-graduate me-2"></i> Siswa Kelas {{ $room->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        @if($room->students->count() > 0)
                                                            <div class="list-group list-group-flush">
                                                                @foreach($room->students as $student)
                                                                    <div class="p-2 list-group-item d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <span class="fw-bold">{{ $student->name }}</span><br>
                                                                            <small class="text-muted">NIS: {{ $student->nis }}</small>
                                                                        </div>
                                                                        <div class="gap-2 d-flex align-items-center">
                                                                            @if($student->face_descriptor)
                                                                                <span class="badge bg-success" title="Wajah Terdaftar"><i class="fas fa-smile"></i></span>
                                                                            @else
                                                                                <span class="badge bg-secondary" title="Belum Rekam Wajah"><i class="fas fa-user-slash"></i></span>
                                                                            @endif

                                                                            <!-- Form remove student -->
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
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- Tombol Setting -->
                                            <button type="button" class="text-white btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#officialsModal{{ $room->id }}" title="Atur Wali Kelas & BK">
                                                <i class="fas fa-user-cog"></i>
                                            </button>

                                            <!-- Tombol Edit -->
                                            <a href="{{ route('classrooms.edit', $room->id) }}" class="text-white btn btn-sm btn-warning" title="Edit Nama">
                                                <i class="bx bx-message-square-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form id="delete-form-{{ $room->id }}" action="{{ route('classrooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')">
                                                    <i class="bx bx-message-square-x"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- MODAL SETTING PERANGKAT KELAS -->
                                        <div class="modal fade" id="officialsModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="text-white modal-header bg-primary">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="fas fa-chalkboard-teacher me-2"></i> Setting Kelas {{ $room->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <form action="{{ route('classrooms.update', $room->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <!-- Kirim nama kelas agar validasi unique tidak error -->
                                                        <input type="hidden" name="name" value="{{ $room->name }}">

                                                        <div class="modal-body text-start">

                                                            <!-- WALI KELAS (FILTERED) -->
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Wali Kelas</label>
                                                                <select name="homeroom_teacher_id" class="form-select select2-modal">
                                                                    <option value="">-- Pilih Wali Kelas --</option>
                                                                    @foreach($allTeachers as $teacher)
                                                                        {{--
                                                                            LOGIKA FILTER:
                                                                            Tampilkan Guru JIKA:
                                                                            1. ID Guru ini TIDAK ADA di daftar 'takenHomeroomIds'
                                                                            2. ATAU ID Guru ini ADALAH Wali Kelas saat ini (agar selected value tetap muncul)
                                                                        --}}
                                                                        @if(!in_array($teacher->id, $takenHomeroomIds) || $room->homeroom_teacher_id == $teacher->id)
                                                                            <option value="{{ $teacher->id }}" {{ $room->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                                {{ $teacher->user->name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                <div class="form-text text-muted small">
                                                                    *Hanya menampilkan guru yang belum menjadi wali kelas.
                                                                </div>
                                                            </div>

                                                            <!-- GURU BK -->
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Guru BK</label>
                                                                <select name="counseling_teacher_id" class="form-select select2-modal">
                                                                    <option value="">-- Pilih Guru BK --</option>
                                                                    @foreach($allTeachers as $teacher)
                                                                        <option value="{{ $teacher->id }}" {{ $room->counseling_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                            {{ $teacher->user->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <!-- KETUA KELAS -->
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Ketua Kelas</label>
                                                                <select name="class_leader_id" class="form-select select2-modal">
                                                                    <option value="">-- Pilih Ketua Kelas --</option>
                                                                    @foreach($room->students as $student)
                                                                        <option value="{{ $student->id }}" {{ $room->class_leader_id == $student->id ? 'selected' : '' }}>
                                                                            {{ $student->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="form-text text-muted small">Hanya siswa dari kelas ini.</div>
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
                                        <!-- END MODAL SETTING -->

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-5 text-center text-muted">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</x-app-layout>
