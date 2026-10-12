@section('title')
    Data Guru
@endsection

<x-app-layout>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
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

        <!-- Action Header, Filter & View Switcher -->
        <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="max-width: 400px;">
                <!-- INPUT FILTER NAMA GURU -->
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-secondary"></i></span>
                    <input type="text" id="filter-teacher-name" class="form-control border-start-0 ps-0" placeholder="Filter nama atau NIP guru...">
                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" id="btn-clear-filter" style="display: none;">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('teachers.import') }}" class="shadow-sm btn btn-success">
                    <i class="bx bx-import"></i> Import
                </a>
                <a href="{{ route('teachers.export') }}" class="shadow-sm btn btn-warning">
                    <i class="bx bx-export"></i> Export
                </a>

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
                    $teachers = \App\Models\Teacher::with(['user', 'additionalTask'])->get();
                @endphp

                @forelse($teachers as $t)
                    <div class="col-md-6 col-lg-4 col-xl-3 teacher-card-item" data-name="{{ strtolower($t->user->name ?? '') }}" data-nip="{{ strtolower($t->nip ?? '') }}">
                        <div class="card border-0 shadow-sm rounded-3 teacher-card h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                                <div>
                                    <div class="avatar-lg mx-auto mb-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto" style="width: 64px; height: 64px; font-size: 24px;">
                                            <i class="bx bx-user"></i>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1 teacher-name">{{ $t->user->name ?? '-' }}</h6>
                                    <small class="text-muted d-block mb-2">{{ $t->user->email ?? '-' }}</small>
                                    
                                    <div class="bg-light rounded p-2 mb-3 text-start small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">NIP:</span>
                                            <span class="fw-semibold text-dark">{{ $t->nip ?? '-' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Tugas Tambahan:</span>
                                            <span class="fw-semibold text-dark">{{ $t->additionalTask->name ?? ($t->tugas_tambahan ?? '-') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Jam / Ket:</span>
                                            <span class="badge bg-secondary">
                                                {{ $t->additionalTask->hours ?? ($t->tugas_tambahan_jam ?? 0) }} Jam ({{ $t->additionalTask->type ?? ($t->tugas_tambahan_keterangan ?? 'TTU') }})
                                            </span>
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
                                    <a href="{{ route('teachers.show', $t->id) }}" class="btn btn-sm btn-outline-info w-100 fw-semibold">
                                        <i class="bx bx-show"></i> Detail
                                    </a>
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

                <!-- Pesan jika hasil pencarian tidak ditemukan -->
                <div class="col-12 text-center py-5 text-muted d-none" id="no-filter-result">
                    <i class="bx bx-search-alt fs-1 mb-2 text-secondary"></i>
                    <p class="mb-0 fw-semibold">Guru dengan nama/NIP tersebut tidak ditemukan.</p>
                </div>
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

    <!-- MODAL EDIT DATA GURU LENGKAP -->
    <div class="modal fade" id="modalEditTeacher" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
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
                            <!-- KOLOM KIRI: AKUN & KEPEGAWAIAN -->
                            <div class="col-md-6 border-end">
                                <h6 class="mb-3 fw-bold text-primary"><i class="bx bx-user-pin me-1"></i>Informasi Akun</h6>
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

                                <h6 class="mt-4 mb-3 fw-bold text-primary"><i class="bx bx-briefcase me-1"></i>Kepegawaian & Tugas Tambahan</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">NIP</label>
                                        <input type="text" name="nip" id="edit_nip" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pendidikan Terakhir</label>
                                        <input type="text" name="education_level" id="edit_education_level" class="form-control" placeholder="S1 Pendidikan">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pangkat</label>
                                        <input type="text" name="pangkat" id="edit_pangkat" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Golongan</label>
                                        <input type="text" name="golongan" id="edit_golongan" class="form-control">
                                    </div>
                                    
                                    <!-- SELECT DINAMIS DARI MASTER ADDITIONAL_TASKS -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Tugas Tambahan (Master Data)</label>
                                        <select name="additional_task_id" id="edit_additional_task_id" class="form-select">
                                            <option value="">-- Tidak Ada Tugas Tambahan --</option>
                                            @foreach(\App\Models\AdditionalTask::orderBy('type')->orderBy('name')->get() as $task)
                                                <option value="{{ $task->id }}">
                                                    {{ $task->name }} ({{ $task->hours }} Jam - {{ $task->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- KOLOM KANAN: DATA PROFIL PRIBADI -->
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-bold text-primary"><i class="bx bx-id-card me-1"></i>Data Pribadi</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No. HP / WhatsApp</label>
                                        <input type="text" name="phone" id="edit_phone" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="gender" id="edit_gender" class="form-select">
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tempat Lahir</label>
                                        <input type="text" name="place_of_birth" id="edit_place_of_birth" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" name="date_of_birth" id="edit_date_of_birth" class="form-control">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Alamat Lengkap</label>
                                        <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-save me-1"></i>Simpan Perubahan</button>
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
        // Switcher Tampilan Card vs Tabel
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

        // FUNGSI PENCARIAN REAL-TIME (JQUERY)
        $(document).ready(function() {
            $(document).on('input keyup search', '#filter-teacher-name', function() {
                let keyword = $(this).val().toLowerCase().trim();
                let items = $('.teacher-card-item');
                let visibleCount = 0;

                if (keyword.length > 0) {
                    $('#btn-clear-filter').show();
                } else {
                    $('#btn-clear-filter').hide();
                }

                items.each(function() {
                    let name = ($(this).attr('data-name') || '').toLowerCase();
                    let nip = ($(this).attr('data-nip') || '').toLowerCase();

                    if (name.indexOf(keyword) !== -1 || nip.indexOf(keyword) !== -1) {
                        $(this).removeClass('d-none').show();
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none').hide();
                    }
                });

                if (visibleCount === 0 && items.length > 0) {
                    $('#no-filter-result').removeClass('d-none').show();
                } else {
                    $('#no-filter-result').addClass('d-none').hide();
                }

                if ($.fn.DataTable.isDataTable('#teacher-table')) {
                    $('#teacher-table').DataTable().search(keyword).draw();
                }
            });

            $(document).on('click', '#btn-clear-filter', function() {
                $('#filter-teacher-name').val('').trigger('input');
            });
        });

        // AJAX Fetch Data untuk Modal Edit
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
                    $('#edit_name').val(data.user ? data.user.name : '');
                    $('#edit_email').val(data.user ? data.user.email : '');

                    $('#edit_nip').val(data.nip);
                    $('#edit_phone').val(data.phone);
                    $('#edit_gender').val(data.gender || 'L');
                    $('#edit_place_of_birth').val(data.place_of_birth);
                    $('#edit_date_of_birth').val(data.date_of_birth);
                    $('#edit_education_level').val(data.education_level);
                    $('#edit_pangkat').val(data.pangkat);
                    $('#edit_golongan').val(data.golongan);
                    
                    $('#edit_additional_task_id').val(data.additional_task_id || '');

                    $('#edit_address').val(data.address);
                    
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

        document.addEventListener('DOMContentLoaded', function() {
            switchTeacherView('card');
        });
    </script>
</x-app-layout>