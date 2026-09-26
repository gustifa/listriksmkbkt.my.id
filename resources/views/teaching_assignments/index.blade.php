@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Maping</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-user"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Jadwal Mengajar (Mapping)</li>
                </ol>
            </nav>
        </div>
       
    </div>
    <!--end breadcrumb-->
        <div class="container py-4">

            <div class="row mb-4 align-items-center">
                <!-- <div class="col-md-5">
                    <h1 class="mb-0 text-gray-800 h3">Jadwal Mengajar (Mapping)</h1>
                    <p class="text-muted small mb-0">Kartu kendali beban mengajar guru.</p>
                </div> -->
                
                <div class="col-md-8 my-2 my-md-0">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="globalSearchInput" class="form-control border-start-0 ps-0" placeholder="Cari Nama Guru..." autocomplete="off">
                    </div>
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="{{ route('teaching-assignments.create') }}" class="shadow-sm btn btn-primary w-100 w-md-auto">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Baru
                    </a>
                </div>
            </div>

            <ul class="nav nav-tabs mb-4" id="viewTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="card-tab" data-bs-toggle="tab" data-bs-target="#card-view" type="button" role="tab" aria-controls="card-view" aria-selected="true">
                        <i class="fas fa-th-large me-2"></i>Tampilan Card
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab" aria-controls="table-view" aria-selected="false">
                        <i class="fas fa-table me-2"></i>Tampilan Tabel
                    </button>
                </li>
            </ul>

            @php
                // Grouping untuk Card View
                $groupedAssignments = $assignments->groupBy(function($item) {
                    return $item->teacher->id;
                });

                // Palette Warna
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

            <div class="tab-content" id="viewTabsContent">

                <div class="tab-pane fade show active" id="card-view" role="tabpanel" aria-labelledby="card-tab">
                    @if($groupedAssignments->isEmpty())
                        <div class="text-center card py-5 shadow-sm">
                            <div class="card-body">
                                <i class="mb-3 fas fa-clipboard-list fa-3x text-gray-300"></i>
                                <h5 class="text-muted">Belum ada data jadwal mengajar.</h5>
                            </div>
                        </div>
                    @else
                        <div class="row" id="teacherGrid">
                            <div id="noResultCard" class="col-12 d-none">
                                <div class="alert alert-warning text-center">Data tidak ditemukan.</div>
                            </div>

                            @foreach($groupedAssignments as $teacherId => $teacherData)
                                @php
                                    $teacher = $teacherData->first()->teacher;
                                    $countMapel = $teacherData->count();
                                    $theme = $themes[$loop->index % count($themes)];
                                    $teacherName = $teacher->user->name ?? 'Guru Terhapus';
                                @endphp

                                <div class="col-md-6 col-xl-4 mb-4 teacher-card-item" data-name="{{ strtolower($teacherName) }}">
                                    <div class="card shadow-sm h-100 card-colored-top {{ $theme['border'] }}">
                                        
                                        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle {{ $theme['bg'] }} text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </div>
                                                <div>
                                                    <h6 class="m-0 font-weight-bold {{ $theme['text'] }}">{{ $teacherName }}</h6>
                                                    <small class="text-muted">
                                                        {{ optional($teacher)->major ? $teacher->major->code : 'Guru Umum' }}
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
                                                            <div class="fw-bold text-dark">{{ $assignment->subject->name ?? '-' }}</div>
                                                            <div class="small text-muted">
                                                                <i class="fas fa-door-open me-1"></i> {{ $assignment->classroom->name ?? '-' }}
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" class="btn {{ $theme['btn'] }} btn-sm rounded-circle me-1" style="width: 32px; height: 32px; padding: 0;" 
                                                                    data-bs-toggle="modal" data-bs-target="#editMappingModal"
                                                                    data-id="{{ $assignment->id }}"
                                                                    data-teacher-id="{{ $assignment->teacher_id }}"
                                                                    data-subject-id="{{ $assignment->subject_id }}"
                                                                    data-classroom-id="{{ $assignment->classroom_id }}">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </button>

                                                            <form id="delete-form-card-{{ $assignment->id }}" action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('DELETE')
                                                                <button type="button" onclick="confirmDelete('card-{{ $assignment->id }}', '{{ $assignment->subject->name }}')" class="btn {{ $theme['btn'] }} btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;">
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

                <div class="tab-pane fade" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Lengkap</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="text-white bg-dark">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Nama Guru</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Kelas</th>
                                            <th>Jurusan</th>
                                            <th>Tahun Ajaran</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        @forelse($assignments as $key => $assignment)
                                        <tr class="table-row-item" data-name="{{ strtolower($assignment->teacher->user->name ?? '') }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td class="fw-bold">{{ $assignment->teacher->user->name ?? 'Guru Terhapus' }}</td>
                                            <td>
                                                {{ $assignment->subject->name ?? 'Mapel Terhapus' }}
                                                @if(optional($assignment->subject)->major)
                                                    <span class="badge bg-info text-dark">{{ $assignment->subject->major->code }}</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $assignment->classroom->name ?? '-' }}</span></td>
                                            <td>
                                                @if(optional($assignment->teacher)->major)
                                                    <span class="badge bg-primary">{{ $assignment->teacher->major->code }}</span>
                                                @else
                                                    <span class="border badge bg-light text-dark">Umum</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->academic_year ?? '-' }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#editMappingModal"
                                                            data-id="{{ $assignment->id }}"
                                                            data-teacher-id="{{ $assignment->teacher_id }}"
                                                            data-subject-id="{{ $assignment->subject_id }}"
                                                            data-classroom-id="{{ $assignment->classroom_id }}">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <form id="delete-form-table-{{ $assignment->id }}" action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button type="button" onclick="confirmDelete('table-{{ $assignment->id }}', '{{ $assignment->subject->name }}')" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="py-5 text-center text-muted">Belum ada data.</td>
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
    </div>

    <div class="modal fade" id="editMappingModal" tabindex="-1" aria-labelledby="editMappingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editMappingForm" action="#" method="POST">
                    @csrf @method('PUT') 
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editMappingModalLabel"><i class="fas fa-edit me-2"></i>Edit Mapping</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Guru Pengajar</label>
                            <select class="form-select" id="edit_teacher_id" name="teacher_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($allTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user->name }} {{ $teacher->major ? '('.$teacher->major->code.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mata Pelajaran</label>
                            <select class="form-select" id="edit_subject_id" name="subject_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($allSubjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="edit_classroom_id" name="classroom_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($allClassrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Search Logic (Works for both Card & Table)
        const searchInput = document.getElementById('globalSearchInput');
        if(searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const searchText = e.target.value.toLowerCase();
                
                // A. Filter Cards
                const cardItems = document.querySelectorAll('.teacher-card-item');
                let cardHasResult = false;
                cardItems.forEach(item => {
                    const name = item.getAttribute('data-name');
                    if(name.includes(searchText)) {
                        item.classList.remove('d-none');
                        cardHasResult = true;
                    } else {
                        item.classList.add('d-none');
                    }
                });
                const noResultCard = document.getElementById('noResultCard');
                if(noResultCard) noResultCard.classList.toggle('d-none', cardHasResult);

                // B. Filter Table Rows
                const tableRows = document.querySelectorAll('.table-row-item');
                tableRows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    if(name.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // 2. Notifications
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", confirmButtonColor: '#d33' });
        @endif
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
        @endif

        // 3. Modal Edit Logic
        const editModal = document.getElementById('editMappingModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const teacherId = button.getAttribute('data-teacher-id');
                const subjectId = button.getAttribute('data-subject-id');
                const classroomId = button.getAttribute('data-classroom-id');
                
                const form = editModal.querySelector('#editMappingForm');
                form.querySelector('#edit_id').value = id;
                form.querySelector('#edit_teacher_id').value = teacherId;
                form.querySelector('#edit_subject_id').value = subjectId;
                form.querySelector('#edit_classroom_id').value = classroomId;
                
                // Pastikan route 'teaching-assignments.update' ada
                let updateUrl = "{{ route('teaching-assignments.update', ':id') }}";
                form.action = updateUrl.replace(':id', id);
            });
        }
    });

    // 4. Delete Confirmation
    function confirmDelete(uniqueId, mapelName) {
        Swal.fire({
            title: 'Hapus Mapping?',
            text: "Mapel '" + mapelName + "' akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Selector ID dinamis (bisa dari card atau table)
                document.getElementById('delete-form-' + uniqueId).submit();
            }
        })
    }
    </script>

    <style>
        .card-colored-top { border-top-width: 4px !important; border-top-style: solid !important; }
        .card-colored-top:hover { transform: translateY(-5px); transition: transform 0.3s ease; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #4e73df; color: #4e73df; background-color: transparent; border-width: 0 0 3px 0; }
        .nav-tabs .nav-link { border: none; color: #6c757d; }
        .nav-tabs .nav-link:hover { color: #4e73df; }
    </style>
</x-app-layout>