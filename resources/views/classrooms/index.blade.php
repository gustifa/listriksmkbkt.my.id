@section('title')
    Setting Kelas
@endsection

<x-app-layout>
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Maping</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Rombongan Belajar</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- LOGIC: Ambil Data Guru & Filter Wali Kelas --}}
        @php
            $allTeachers = \App\Models\Teacher::orderBy('name')->get();
            $takenHomeroomIds = \App\Models\Classroom::whereNotNull('homeroom_teacher_id')->pluck('homeroom_teacher_id')->toArray();
        @endphp

        <!-- Top Actions Bar: Form Cari & Switcher Mode -->
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Form Pencarian -->
            <form action="{{ route('classrooms.index') }}" method="GET" class="flex-grow-1 me-2" style="max-width: 400px;">
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama Kelas..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-dark"><i class="bx bx-search"></i> Cari</button>
                </div>
            </form>

            <!-- Toggle Switcher View: Card vs Tabel -->
            <div class="btn-group bg-white p-1 rounded-3 border shadow-sm" role="group" aria-label="View Switcher">
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 active" id="btn-view-card" onclick="switchClassroomView('card')">
                    <i class="bx bx-grid-alt me-1"></i> Card
                </button>
                <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0" id="btn-view-table" onclick="switchClassroomView('table')">
                    <i class="bx bx-list-ul me-1"></i> Tabel
                </button>
            </div>
        </div>

        <!-- Menampilkan Error Validasi -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- 1. TAMPILAN BENTUK CARD -->
        <div id="view-card-container">
            <div class="row g-3">
                @forelse($classrooms as $key => $room)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Header Card Nama Kelas & Badge Siswa -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold text-dark mb-0">{{ $room->name }}</h5>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#studentsModalCard{{ $room->id }}">
                                            <i class="fas fa-users me-1"></i> {{ $room->students->count() }}
                                        </button>
                                    </div>

                                    <!-- Detail Perangkat Kelas -->
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

                                <!-- Tombol Aksi Card -->
                                <div class="d-flex gap-1 justify-content-between pt-2 border-top">
                                    <button type="button" class="btn btn-sm btn-info text-white flex-fill" data-bs-toggle="modal" data-bs-target="#officialsModalCard{{ $room->id }}" title="Setting Kelas">
                                        <i class="fas fa-user-cog me-1"></i> Setting
                                    </button>
                                    <a href="{{ route('classrooms.print_ids', $room->id) }}" class="btn btn-sm btn-dark" target="_blank" title="Cetak ID Card">
                                        <i class="fas fa-id-card"></i>
                                    </a>
                                    <a href="{{ route('classrooms.edit', $room->id) }}" class="btn btn-sm btn-warning text-white" title="Edit Nama">
                                        <i class="bx bx-message-square-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')">
                                        <i class="bx bx-message-square-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL DAFTAR SISWA CARD -->
                    <div class="modal fade" id="studentsModalCard{{ $room->id }}" tabindex="-1" aria-hidden="true">
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

                                                        <form id="remove-student-card-form-{{ $student->id }}" action="{{ route('students.remove_class', $student->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" title="Keluarkan dari Kelas" onclick="confirmRemoveStudentCard('{{ $student->id }}', '{{ $student->name }}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-4 text-center text-muted">
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

                    <!-- MODAL SETTING PERANGKAT KELAS CARD -->
                    <div class="modal fade" id="officialsModalCard{{ $room->id }}" tabindex="-1" aria-hidden="true">
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
                                    <input type="hidden" name="name" value="{{ $room->name }}">

                                    <div class="modal-body text-start">
                                        <!-- WALI KELAS -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Wali Kelas</label>
                                            <select name="homeroom_teacher_id" class="form-select">
                                                <option value="">-- Pilih Wali Kelas --</option>
                                                @foreach($allTeachers as $teacher)
                                                    @if(!in_array($teacher->id, $takenHomeroomIds) || $room->homeroom_teacher_id == $teacher->id)
                                                        <option value="{{ $teacher->id }}" {{ $room->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- GURU BK -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Guru BK</label>
                                            <select name="counseling_teacher_id" class="form-select">
                                                <option value="">-- Pilih Guru BK --</option>
                                                @foreach($allTeachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ $room->counseling_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- KETUA KELAS -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Ketua Kelas</label>
                                            <select name="class_leader_id" class="form-select">
                                                <option value="">-- Pilih Ketua Kelas --</option>
                                                @foreach($room->students as $student)
                                                    <option value="{{ $student->id }}" {{ $room->class_leader_id == $student->id ? 'selected' : '' }}>
                                                        {{ $student->name }}
                                                    </option>
                                                @endforeach
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
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <p class="mb-0">Data kelas belum tersedia.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. TAMPILAN BENTUK TABEL -->
        <div id="view-table-container" class="d-none">
            <div class="border-0 shadow card">
                <div class="card-body">
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
                                                <button type="button" class="text-white btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#officialsModal{{ $room->id }}" title="Atur Wali Kelas & BK">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <a href="{{ route('classrooms.print_ids', $room->id) }}" class="text-white btn btn-sm btn-dark" target="_blank" title="Cetak ID Card Se-Kelas">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                                <a href="{{ route('classrooms.edit', $room->id) }}" class="text-white btn btn-sm btn-warning" title="Edit Nama">
                                                    <i class="bx bx-message-square-edit"></i>
                                                </a>
                                                <form id="delete-form-{{ $room->id }}" action="{{ route('classrooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $room->id }}', '{{ $room->name }}')">
                                                        <i class="bx bx-message-square-x"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- MODAL SETTING PERANGKAT KELAS TABEL -->
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
                                                            <input type="hidden" name="name" value="{{ $room->name }}">

                                                            <div class="modal-body text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Wali Kelas</label>
                                                                    <select name="homeroom_teacher_id" class="form-select">
                                                                        <option value="">-- Pilih Wali Kelas --</option>
                                                                        @foreach($allTeachers as $teacher)
                                                                            @if(!in_array($teacher->id, $takenHomeroomIds) || $room->homeroom_teacher_id == $teacher->id)
                                                                                <option value="{{ $teacher->id }}" {{ $room->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                                    {{ $teacher->name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Guru BK</label>
                                                                    <select name="counseling_teacher_id" class="form-select">
                                                                        <option value="">-- Pilih Guru BK --</option>
                                                                        @foreach($allTeachers as $teacher)
                                                                            <option value="{{ $teacher->id }}" {{ $room->counseling_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                                {{ $teacher->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Ketua Kelas</label>
                                                                    <select name="class_leader_id" class="form-select">
                                                                        <option value="">-- Pilih Ketua Kelas --</option>
                                                                        @foreach($room->students as $student)
                                                                            <option value="{{ $student->id }}" {{ $room->class_leader_id == $student->id ? 'selected' : '' }}>
                                                                                {{ $student->name }}
                                                                            </option>
                                                                        @endforeach
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
                                            </div>
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
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT SECTION -->
    <script>
        // Function Switcher Tampilan Card vs Tabel
        function switchClassroomView(mode) {
            const cardContainer = document.getElementById('view-card-container');
            const tableContainer = document.getElementById('view-table-container');
            const btnCard = document.getElementById('btn-view-card');
            const btnTable = document.getElementById('btn-view-table');

            if (mode === 'card') {
                cardContainer.classList.remove('d-none');
                tableContainer.classList.add('d-none');
                btnCard.classList.add('active', 'bg-primary', 'text-white');
                btnTable.classList.remove('active', 'bg-primary', 'text-white');
            } else {
                tableContainer.classList.remove('d-none');
                cardContainer.classList.add('d-none');
                btnTable.classList.add('active', 'bg-primary', 'text-white');
                btnCard.classList.remove('active', 'bg-primary', 'text-white');
            }
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Kelas?',
                text: "Hapus kelas " + name + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function confirmRemoveStudent(id, name) {
            Swal.fire({
                title: 'Keluarkan Siswa?',
                text: "Keluarkan " + name + " dari kelas?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('remove-student-form-' + id).submit();
                }
            });
        }

        function confirmRemoveStudentCard(id, name) {
            Swal.fire({
                title: 'Keluarkan Siswa?',
                text: "Keluarkan " + name + " dari kelas?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('remove-student-card-form-' + id).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            switchClassroomView('card');
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: {!! json_encode(session('success')) !!}, timer: 2000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: {!! json_encode(session('error')) !!} });
        @endif
    </script>
</x-app-layout>