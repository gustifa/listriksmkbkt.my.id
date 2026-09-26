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

                        <!-- 1. Pilih Guru -->
                        <div class="mb-3">
                            <label class="form-label">Guru Pengajar</label>
                            <select name="teacher_id" id="teacher_select" class="form-control select2" required>
                                <option value="" data-major="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <!-- Simpan ID Jurusan di atribut data-major untuk validasi JS -->
                                    <option value="{{ $teacher->id }}" data-major="{{ $teacher->major_id }}">
                                        {{ $teacher->user->name }}
                                        {{ $teacher->major ? '('.$teacher->major->code.')' : '(Guru Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Pilih Mata Pelajaran -->
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran</label>
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

                        <!-- 3. Pilih Kelas -->
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
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
        <script>
            // Logic JS Sederhana untuk Validasi Jurusan di Frontend
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
                if (teacherMajor && classMajor && teacherMajor !== classMajor) {
                    warningMsg.classList.remove('d-none');
                }
            }

            teacherSelect.addEventListener('change', checkMajorMatch);
            classroomSelect.addEventListener('change', checkMajorMatch);
        </script>

</x-app-layout>
