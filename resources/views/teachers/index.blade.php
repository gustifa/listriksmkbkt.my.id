@section('title')
    Data Guru
@endsection

<x-app-layout>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        /* Desain tombol aksi agar rapi */
        .order-actions a, .order-actions button {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .table-responsive {
            padding: 10px 0;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .teacher-card {
            transition: all 0.2s ease-in-out;
        }
        .teacher-card:hover {
            transform: translateY(-3px);
        }
    </style>

    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Mapping</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('/admin/dashboard')}}"><i class="bx bx-user"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Guru</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Action Header & View Switcher -->
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <a href="{{ route('teachers.import') }}" class="shadow-sm btn btn-success">
                    <i class="bx bx-import"></i> Import
                </a>
                <a href="{{ route('teachers.export') }}" class="shadow-sm btn btn-warning">
                    <i class="bx bx-export"></i> Export
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- TOGGLE SWITCHER VIEW (CARD VS TABEL) -->
                <div class="btn-group bg-white p-1 rounded-3 border shadow-sm" role="group" aria-label="View Switcher">
                    <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0 active" id="btn-view-card" onclick="switchTeacherView('card')">
                        <i class="bx bx-grid-alt me-1"></i> Card
                    </button>
                    <button type="button" class="btn btn-sm btn-white text-dark fw-bold border-0" id="btn-view-table" onclick="switchTeacherView('table')">
                        <i class="bx bx-list-ul me-1"></i> Tabel
                    </button>
                </div>

                <a href="{{ url('teachers/add') }}" class="shadow-sm btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Guru
                </a>
            </div>
        </div>

        <!-- 1. TAMPILAN BENTUK CARD -->
        <div id="view-card-container">
            <div class="row g-3" id="teacher-card-list">
                @php
                    $teachers = \App\Models\Teacher::with('user')->get();
                @endphp

                @forelse($teachers as $t)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-3 teacher-card h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div>
                                    <div class="avatar-lg mx-auto mb-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto" style="width: 64px; height: 64px; font-size: 24px;">
                                            <i class="bx bx-user"></i>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">{{ $t->user->name ?? '-' }}</h6>
                                    <small class="text-muted d-block mb-2">{{ $t->user->email ?? '-' }}</small>
                                    
                                    <div class="bg-light rounded p-2 mb-3 text-start small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">NIP:</span>
                                            <span class="fw-semibold text-dark">{{ $t->nip ?? '-' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">No. HP:</span>
                                            <span class="fw-semibold text-dark">{{ $t->phone ?? '-' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Gender:</span>
                                            <span class="badge {{ ($t->gender ?? 'L') === 'L' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }}">
                                                {{ ($t->gender ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-semibold btn-edit" data-id="{{ $t->id }}">
                                        <i class="bx bx-edit"></i> Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bx bx-folder-open fs-1 mb-2"></i>
                        <p>Belum ada data guru yang terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. TAMPILAN BENTUK TABEL -->
        <div id="view-table-container" class="d-none">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table align-middle table-striped table-bordered w-100', 'id' => 'teacher-table']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT DATA GURU -->
    <div class="modal fade" id="modalEditTeacher" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bx bx-edit me-2"></i>Edit Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditTeacher" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="mb-3 fw-bold text-primary">Informasi Akun</h6>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email (Login)</label>
                                    <input type="email" name="email" id="edit_email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                    <input type="password" name="password" class="form-control" placeholder="******">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-bold text-primary">Data Profil</h6>
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" id="edit_nip" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. HP</label>
                                    <input type="text" name="phone" id="edit_phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="gender" id="edit_gender" class="form-select">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT SECTION -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        // Function Switcher Tampilan Card vs Tabel
        function switchTeacherView(mode) {
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

        // AJAX Edit Data Guru (Berlaku untuk Card & Tabel)
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let btn = $(this);
            
            let urlAction = "{{ route('teachers.edit.json', ':id') }}";
            urlAction = urlAction.replace(':id', id);

            btn.prop('disabled', true);

            $.ajax({
                url: urlAction,
                type: "GET",
                success: function(data) {
                    $('#edit_name').val(data.user.name);
                    $('#edit_email').val(data.user.email);
                    $('#edit_nip').val(data.nip);
                    $('#edit_phone').val(data.phone);
                    $('#edit_gender').val(data.gender || 'L');
                    
                    let updateUrl = "{{ url('teachers') }}/" + id;
                    $('#formEditTeacher').attr('action', updateUrl);
                    
                    $('#modalEditTeacher').modal('show');
                    btn.prop('disabled', false);
                },
                error: function(xhr) {
                    console.error("URL yang dicoba: " + urlAction);
                    alert("Gagal mengambil data guru.");
                    btn.prop('disabled', false);
                }
            });
        });

        // Set Default View ke Card saat halaman pertama dibuka
        document.addEventListener('DOMContentLoaded', function() {
            switchTeacherView('card');
        });
    </script>
</x-app-layout>