@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
        <div class="container py-4">
            <div class="shadow card">
                <div class="text-white card-header bg-primary">
                    <h5 class="mb-0">Tambah Jadwal Mengajar (Mapping)</h5>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teaching-assignments.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label d-block">Guru Pengajar
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#teacherModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Guru
                                </button>
                            </label>
                            <select name="teacher_id" id="teacher_select" class="form-control select2" required>
                                <option value="" data-major="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" data-major="{{ $teacher->major_id }}">
                                        {{ $teacher->user->name }}
                                        {{ $teacher->major ? '('.$teacher->major->code.')' : '(Guru Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Mata Pelajaran
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#subjectModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Mapel
                                </button>
                            </label>
                            <select name="subject_id" class="form-control select2" required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }}
                                        {{ $subject->major ? '('.$subject->major->code.')' : '(Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Kelas
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#majorModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Jurusan
                                </button>
                            </label>
                            <select name="classroom_id" id="classroom_select" class="form-control select2" required>
                                <option value="" data-major="">-- Pilih Kelas --</option>
                                @foreach($classrooms as $class)
                                    <option value="{{ $class->id }}" data-major="{{ $class->major_id }}">
                                        {{ $class->name }}
                                        {{ $class->major ? '('.$class->major->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="major_warning" class="text-danger d-none">
                                <i class="fas fa-exclamation-triangle"></i> Peringatan: Jurusan Guru tidak cocok dengan Jurusan Kelas!
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="teacherModalLabel"><i class="fas fa-chalkboard-teacher"></i> Daftar Semua Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Guru</th>
                                    <th>NIP/ID</th>
                                    <th>Jurusan Khusus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teachers as $index => $teacher)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $teacher->user->name }}</td>
                                    <td>{{ $teacher->nip ?? '-' }}</td>
                                    <td>{{ $teacher->major->name ?? 'Umum' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="subjectModalLabel"><i class="fas fa-book-open"></i> Daftar Semua Mata Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Mapel</th>
                                    <th>Kode Mapel</th>
                                    <th>Jurusan Khusus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $index => $subject)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $subject->name }}</td>
                                    <td>{{ $subject->code ?? '-' }}</td>
                                    <td>{{ $subject->major->name ?? 'Umum' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="majorModal" tabindex="-1" aria-labelledby="majorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="majorModalLabel"><i class="fas fa-graduation-cap"></i> Daftar Semua Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Jurusan</th>
                                    <th>Kode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($majors as $index => $major)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $major->name }}</td>
                                    <td>{{ $major->code }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logic JS Sederhana untuk Validasi Jurusan di Frontend (Tidak Berubah)
        const teacherSelect = document.getElementById('teacher_select');
        const classroomSelect = document.getElementById('classroom_select');
        const warningMsg = document.getElementById('major_warning');

        function checkMajorMatch() {
            // Ambil option yang dipilih
            const selectedTeacher = teacherSelect.options[teacherSelect.selectedIndex];
            const selectedClass = classroomSelect.options[classroomSelect.selectedIndex];

            const teacherMajor = selectedTeacher.getAttribute('data-major');
            const classMajor = selectedClass.getAttribute('data-major');

            // Reset warning
            warningMsg.classList.add('d-none');

            // Logic: Jika Guru punya jurusan, DAN Kelas punya jurusan, DAN beda => Warning
            if (teacherMajor && classMajor && teacherMajor && classMajor && teacherMajor !== classMajor) {
                warningMsg.classList.remove('d-none');
            }
        }

        teacherSelect.addEventListener('change', checkMajorMatch);
        classroomSelect.addEventListener('change', checkMajorMatch);
        
        // Inisialisasi Select2 jika Anda menggunakannya
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

</x-app-layout>