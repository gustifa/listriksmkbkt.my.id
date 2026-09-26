@section('title', 'Tambah Jadwal Mengajar')
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
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label">Guru Pengajar</label>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#teacherModal">
                                    <i class="fas fa-eye me-1"></i> Lihat Semua Guru
                                </button>
                            </div>
                            <select name="teacher_id" id="teacher_select" class="form-control select2" required>
                                <option value="" data-major="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" 
                                            data-major="{{ $teacher->major_id ?? '' }}">
                                        {{ $teacher->name }} 
                                        {{ $teacher->major ? '('.$teacher->major->code.')' : '(Guru Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label">Mata Pelajaran</label>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#subjectModal">
                                    <i class="fas fa-eye me-1"></i> Lihat Semua Mapel
                                </button>
                            </div>
                            <select name="subject_id" id="subject_select" class="form-control select2" required>
                                <option value="" data-major="">-- Pilih Mapel --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" 
                                            data-major="{{ $subject->major_id ?? '' }}">
                                        {{ $subject->name }}
                                        {{ $subject->major ? '('.$subject->major->code.')' : '(Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label">Kelas</label>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#majorModal">
                                    <i class="fas fa-eye me-1"></i> Lihat Semua Jurusan
                                </button>
                            </div>
                            <select name="classroom_id" id="classroom_select" class="form-control select2" required>
                                <option value="" data-major="" class="class-option">-- Pilih Kelas --</option>
                                @foreach($classrooms as $class)
                                    <option value="{{ $class->id }}" 
                                            data-major="{{ $class->major_id }}" 
                                            class="class-option"
                                            style="display:none;">
                                        {{ $class->name }}
                                        {{ $class->major ? '('.$class->major->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="major_warning" class="text-danger d-none">
                                <i class="fas fa-exclamation-triangle"></i> Peringatan: Guru/Mapel hanya bisa mengajar di Kelas Jurusan yang sesuai!
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modal Guru --}}
    <div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="teacherModalLabel"><i class="fas fa-chalkboard-teacher me-2"></i> Daftar Semua Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Guru</th>
                                    <th>NIP</th>
                                    <th>Jurusan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teachers as $index => $teacher)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $teacher->name }}</td>
                                        <td>{{ $teacher->nip ?? '-' }}</td>
                                        <td>{{ $teacher->major->name ?? 'Umum' }}</td>
                                        <td>
                                            <button 
                                                type="button" 
                                                class="btn btn-success btn-sm select-teacher-btn" 
                                                data-id="{{ $teacher->id }}" 
                                                data-major="{{ $teacher->major_id ?? '' }}"
                                            >
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modal Mata Pelajaran --}}
    <div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="subjectModalLabel"><i class="fas fa-book-open me-2"></i> Daftar Semua Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Mapel</th>
                                    <th>Kode</th>
                                    <th>Jurusan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $index => $subject)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->code ?? '-' }}</td>
                                        <td>{{ $subject->major->name ?? 'Umum' }}</td>
                                        <td>
                                            <button 
                                                type="button" 
                                                class="btn btn-success btn-sm select-subject-btn" 
                                                data-id="{{ $subject->id }}" 
                                                data-major="{{ $subject->major_id ?? '' }}"
                                            >
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modal Jurusan --}}
    <div class="modal fade" id="majorModal" tabindex="-1" aria-labelledby="majorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="majorModalLabel"><i class="fas fa-graduation-cap me-2"></i> Daftar Semua Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
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
            </div>
        </div>
    </div>
    <script>
        const teacherSelect = document.getElementById('teacher_select');
        const subjectSelect = document.getElementById('subject_select');
        const classroomSelect = document.getElementById('classroom_select');
        const warningMsg = document.getElementById('major_warning');
        const classOptions = document.querySelectorAll('.class-option');

        function updateSelect2(elementId, value) {
            const $element = $(`#${elementId}`);
            if ($.fn.select2) {
                // Set nilai, trigger change untuk JS listener, dan perbarui tampilan Select2
                $element.val(value).trigger('change'); 
                $element.select2('val', value).trigger('change');
            } else {
                $element.val(value).trigger('change');
            }
        }

        // =======================================================
        // CORE FILTERING LOGIC (Dipertahankan)
        // =======================================================
        function filterClassrooms() {
            const selectedTeacherOption = teacherSelect.options[teacherSelect.selectedIndex];
            const teacherMajorId = selectedTeacherOption.getAttribute('data-major');
            
            const selectedSubjectOption = subjectSelect.options[subjectSelect.selectedIndex];
            const subjectMajorId = selectedSubjectOption.getAttribute('data-major');
            
            let targetMajorId = null;
            let isFiltered = false;
            
            warningMsg.classList.add('d-none');
            
            if (teacherMajorId) {
                targetMajorId = teacherMajorId;
            }
            
            if (subjectMajorId && !targetMajorId) {
                targetMajorId = subjectMajorId;
            }
            
            if (teacherMajorId && subjectMajorId && teacherMajorId !== subjectMajorId) {
                warningMsg.textContent = 'Peringatan: Jurusan Guru tidak cocok dengan Jurusan Mata Pelajaran! Mapping ini mungkin tidak valid.';
                warningMsg.classList.remove('d-none');
            }


            const currentClassId = classroomSelect.value;
            updateSelect2('classroom_select', ''); 

            // Hancurkan Select2 sebelum memanipulasi DOM untuk menghindari masalah
            if ($.fn.select2) {
                $('#classroom_select').select2('destroy');
            }

            classOptions.forEach(option => {
                const classMajorId = option.getAttribute('data-major');
                
                if (option.value === "") {
                    option.style.display = 'block'; 
                    return; 
                }

                if (!targetMajorId || classMajorId === targetMajorId) {
                    option.style.display = 'block';
                    isFiltered = true;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Re-inisialisasi Select2
            if ($.fn.select2) {
                $('#classroom_select').select2({
                    // Opsi Select2 Anda
                });
            }

            if (targetMajorId && currentClassId) {
                const retainedOption = document.querySelector(`#classroom_select option[value="${currentClassId}"]`);
                if (retainedOption && retainedOption.style.display === 'block') {
                    updateSelect2('classroom_select', currentClassId);
                }
            }
            
            if (targetMajorId && !isFiltered) {
                warningMsg.textContent = 'Peringatan: Tidak ada Kelas yang tersedia untuk Jurusan yang dipilih!';
                warningMsg.classList.remove('d-none');
            }
        }
        
        // =======================================================
        // EVENT LISTENERS & INITIALIZATION (DENGAN EVENT DELEGATION)
        // =======================================================
        
        $(document).ready(function() {
            
            // Inisialisasi Select2 (Harus di DOM Ready)
            if ($.fn.select2) {
                $('.select2').select2({
                    // Opsi Select2 Anda
                });
            }
            
            // --- PERBAIKAN UTAMA: Event Delegation untuk Tombol Pilih ---
            
            // Select Guru dari Modal (Menggunakan Delegation pada document)
            $(document).on('click', '.select-teacher-btn', function() {
                const teacherId = $(this).data('id');
                updateSelect2('teacher_select', teacherId);
                $('#teacherModal').modal('hide');
                filterClassrooms(); 
            });

            // Select Mapel dari Modal (Menggunakan Delegation pada document)
            $(document).on('click', '.select-subject-btn', function() {
                const subjectId = $(this).data('id');
                updateSelect2('subject_select', subjectId);
                $('#subjectModal').modal('hide');
                filterClassrooms(); 
            });

            // Listener perubahan langsung pada select (Tetap diperlukan)
            teacherSelect.addEventListener('change', filterClassrooms);
            subjectSelect.addEventListener('change', filterClassrooms);
            
            // Panggil filterClassrooms saat halaman dimuat pertama kali
            filterClassrooms(); 
        });

    </script>
</x-app-layout>