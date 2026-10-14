@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <!-- Tambahkan Resource Select2 & jQuery CDN jika belum di-load global -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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

                        <!-- Select Guru Pengajar -->
                        <div class="mb-3">
                            <label class="form-label d-block">Guru Pengajar
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#teacherModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Guru
                                </button>
                            </label>
                            <select name="teacher_id" id="teacher_select" class="form-select select2-search" required>
                                <option value="">-- Ketik / Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" data-major="{{ $teacher->major_id }}">
                                        {{ $teacher->user->name }}
                                        {{ $teacher->major ? '('.$teacher->major->code.')' : '(Guru Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Mata Pelajaran -->
                        <div class="mb-3">
                            <label class="form-label d-block">Mata Pelajaran
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#subjectModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Mapel
                                </button>
                            </label>
                            <select name="subject_id" id="subject_select" class="form-select select2-search" required>
                                <option value="">-- Ketik / Pilih Mapel --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }}
                                        {{ $subject->major ? '('.$subject->major->code.')' : '(Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Kelas -->
                        <div class="mb-3">
                            <label class="form-label d-block">Kelas
                                <button type="button" class="btn btn-sm btn-outline-info float-end" data-bs-toggle="modal" data-bs-target="#majorModal">
                                    <i class="fas fa-eye"></i> Lihat Semua Jurusan
                                </button>
                            </label>
                            <select name="classroom_id" id="classroom_select" class="form-select select2-search" required>
                                <option value="">-- Ketik / Pilih Kelas --</option>
                                @foreach($classrooms as $class)
                                    <option value="{{ $class->id }}" data-major="{{ $class->major_id }}">
                                        {{ $class->name }}
                                        {{ $class->major ? '('.$class->major->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="major_warning" class="text-danger d-none mt-1 d-block">
                                <i class="fas fa-exclamation-triangle"></i> Peringatan: Jurusan Guru tidak cocok dengan Jurusan Kelas!
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Guru -->
    <div class="modal fade" id="teacherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-chalkboard-teacher me-2"></i>Daftar Semua Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
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

    <!-- Modal Mapel -->
    <div class="modal fade" id="subjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-book-open me-2"></i>Daftar Semua Mata Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
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

    <!-- Modal Jurusan -->
    <div class="modal fade" id="majorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Daftar Semua Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
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

    <!-- JS Select2 & Interaktivitas pencarian -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 dengan Fitur Pencarian Langsung
            $('.select2-search').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Ketik untuk mencari --',
                allowClear: true,
                width: '100%'
            });

            // Logic Validasi Kesesuaian Jurusan
            function checkMajorMatch() {
                const teacherOption = $('#teacher_select').find(':selected');
                const classOption = $('#classroom_select').find(':selected');

                const teacherMajor = teacherOption.data('major');
                const classMajor = classOption.data('major');

                $('#major_warning').addClass('d-none');

                if (teacherMajor && classMajor && teacherMajor !== classMajor) {
                    $('#major_warning').removeClass('d-none');
                }
            }

            // Event saat pilihan Select2 berubah
            $('#teacher_select, #classroom_select').on('change', function() {
                checkMajorMatch();
            });
        });
    </script>
</x-app-layout>