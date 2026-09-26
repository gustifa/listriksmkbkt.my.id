@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
        <div class="container py-4">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0 text-gray-800 h3">Jadwal Mengajar (Mapping)</h1>
                    <p class="text-muted small">Kartu kendali beban mengajar guru.</p>
                </div>
                <a href="{{ route('teaching-assignments.create') }}" class="shadow-sm btn btn-primary">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Mapping Baru
                </a>
            </div>

            @php
                // Kelompokkan data assignment berdasarkan ID Guru
                $groupedAssignments = $assignments->groupBy(function($item) {
                    return $item->teacher->id;
                });

                // Palette Warna-warni untuk Card
                $themes = [
                    ['border' => 'border-primary', 'text' => 'text-primary', 'bg' => 'bg-primary', 'btn' => 'btn-outline-primary'],
                    ['border' => 'border-success', 'text' => 'text-success', 'bg' => 'bg-success', 'btn' => 'btn-outline-success'],
                    ['border' => 'border-info',    'text' => 'text-info',    'bg' => 'bg-info',    'btn' => 'btn-outline-info'],
                    ['border' => 'border-warning', 'text' => 'text-warning', 'bg' => 'bg-warning', 'btn' => 'btn-outline-warning'],
                    ['border' => 'border-danger',  'text' => 'text-danger',  'bg' => 'bg-danger',  'btn' => 'btn-outline-danger'],
                    ['border' => 'border-secondary','text'=> 'text-secondary','bg'=> 'bg-secondary','btn'=> 'btn-outline-secondary'],
                    ['border' => 'border-dark',    'text' => 'text-dark',    'bg' => 'bg-dark',    'btn' => 'btn-outline-dark'],
                ];
            @endphp

            @if($groupedAssignments->isEmpty())
                <div class="text-center card py-5 shadow-sm">
                    <div class="card-body">
                        <i class="mb-3 fas fa-clipboard-list fa-3x text-gray-300"></i>
                        <h5 class="text-muted">Belum ada data jadwal mengajar.</h5>
                        <p class="mb-0 text-muted">Silakan tambahkan mapping baru.</p>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($groupedAssignments as $teacherId => $teacherData)
                        @php
                            $teacher = $teacherData->first()->teacher;
                            $countMapel = $teacherData->count();
                            // Pilih tema berdasarkan urutan loop
                            $theme = $themes[$loop->index % count($themes)];
                        @endphp

                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card shadow-sm h-100 card-colored-top {{ $theme['border'] }}">
                                
                                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle {{ $theme['bg'] }} text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div>
                                            <h6 class="m-0 font-weight-bold {{ $theme['text'] }}">{{ $teacher->user->name ?? 'Guru Terhapus' }}</h6>
                                            <small class="text-muted">
                                                @if(optional($teacher)->major)
                                                    Jurusan: {{ $teacher->major->code }}
                                                @else
                                                    Guru Umum
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $theme['bg'] }}">{{ $countMapel }} Mapel</span>
                                </div>

                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @foreach($teacherData as $assignment)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                
                                                <div>
                                                    <div class="fw-bold text-dark">
                                                        {{ $assignment->subject->name ?? '-' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-door-open me-1"></i> {{ $assignment->classroom->name ?? '-' }}
                                                        @if(optional($assignment->subject)->major)
                                                            <span class="badge bg-light text-dark border ms-1">{{ $assignment->subject->major->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center">
                                                    
                                                    <button type="button" 
                                                            class="btn {{ $theme['btn'] }} btn-sm rounded-circle me-1" 
                                                            style="width: 32px; height: 32px; padding: 0; display:inline-flex; align-items:center; justify-content:center;" 
                                                            title="Edit"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editMappingModal"
                                                            data-id="{{ $assignment->id }}"
                                                            data-teacher-id="{{ $assignment->teacher_id }}"
                                                            data-subject-id="{{ $assignment->subject_id }}"
                                                            data-classroom-id="{{ $assignment->classroom_id }}">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>

                                                    <form id="delete-form-{{ $assignment->id }}" action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="confirmDelete('{{ $assignment->id }}', '{{ $assignment->subject->name }}')" class="btn {{ $theme['btn'] }} btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="card-footer bg-light text-end">
                                    <small class="text-muted">TA: {{ $teacherData->first()->academic_year ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <div class="modal fade" id="editMappingModal" tabindex="-1" aria-labelledby="editMappingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <form id="editMappingForm" action="#" method="POST">
                    @csrf
                    @method('PUT') 

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editMappingModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Mapping Pembelajaran
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3">
                            <label for="edit_teacher_id" class="form-label fw-bold">Guru Pengajar</label>
                            <select class="form-select" id="edit_teacher_id" name="teacher_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($allTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->user->name }} {{ $teacher->major ? '('.$teacher->major->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_subject_id" class="form-label fw-bold">Mata Pelajaran</label>
                            <select class="form-select" id="edit_subject_id" name="subject_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($allSubjects as $subject)
                                    <option value="{{ $subject->id }}">
                                        {{ $subject->name }} {{ $subject->major ? '('.$subject->major->code.')' : '(Umum)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_classroom_id" class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="edit_classroom_id" name="classroom_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($allClassrooms as $classroom)
                                    <option value="{{ $classroom->id }}">
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i> Perubahan akan langsung tersimpan ke database.
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // A. Notifikasi SweetAlert dari Session
        @if(session('error'))
            Swal.fire({ 
                icon: 'error', 
                title: 'Gagal!', 
                text: "{{ session('error') }}", 
                confirmButtonColor: '#d33' });
        @endif

        @if(session('success'))
            Swal.fire({ 
                icon: 'success', 
                title: 'Berhasil!', 
                text: "{{ session('success') }}", 
                timer: 2000, 
                showConfirmButton: false });
        @endif

        // B. Logic Modal Edit (Populate Data & URL)
        const editModal = document.getElementById('editMappingModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', event => {
                // 1. Ambil tombol yang diklik
                const button = event.relatedTarget;
                
                // 2. Ambil data dari tombol
                const id = button.getAttribute('data-id');
                const teacherId = button.getAttribute('data-teacher-id');
                const subjectId = button.getAttribute('data-subject-id');
                const classroomId = button.getAttribute('data-classroom-id');
                
                // 3. Isi form dalam modal
                const form = editModal.querySelector('#editMappingForm');
                form.querySelector('#edit_id').value = id;
                form.querySelector('#edit_teacher_id').value = teacherId;
                form.querySelector('#edit_subject_id').value = subjectId;
                form.querySelector('#edit_classroom_id').value = classroomId;
                
                // 4. Ubah Action Form menjadi route('assignments.update', id)
                // Kita gunakan placeholder ':id' lalu replace
                let updateUrl = "{{ route('teaching-assignments.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', id);
                
                form.action = updateUrl;
            });
        }
    });

    // C. Logic Konfirmasi Hapus
    function confirmDelete(id, mapelName) {
        Swal.fire({
            title: 'Hapus Mapping?',
            text: "Mapel '" + mapelName + "' akan dihapus dari guru ini.",
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
        })
    }
    </script>
    
    <style>
        .card-colored-top {
            border-top-width: 4px !important;
            border-top-style: solid !important;
        }
        .card-colored-top:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
    </style>
</x-app-layout>