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
    </style>

    <div class="page-content">
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

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('teachers.import') }}" class="shadow-sm btn btn-success">
                    <i class="bx bx-import"></i> Import
                </a>
                <a href="{{ route('teachers.export') }}" class="shadow-sm btn btn-warning">
                    <i class="bx bx-export"></i> Export
                </a>
            </div>
            <a href="{{ url('teachers/add') }}" class="shadow-sm btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Guru
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table align-middle table-striped table-bordered w-100', 'id' => 'teacher-table']) }}
                </div>
            </div>
        </div>
    </div>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let btn = $(this);
            
            // 1. Buat URL menggunakan route name laravel
            let urlAction = "{{ route('teachers.edit.json', ':id') }}";
            urlAction = urlAction.replace(':id', id); // Mengganti :id dengan ID guru

            btn.prop('disabled', true);

            $.ajax({
                url: urlAction,
                type: "GET",
                success: function(data) {
                    // 2. Isi form modal
                    $('#edit_name').val(data.user.name);
                    $('#edit_email').val(data.user.email);
                    $('#edit_nip').val(data.nip);
                    $('#edit_phone').val(data.phone);
                    
                    // 3. Atur URL untuk form Update
                    let updateUrl = "{{ url('teachers') }}/" + id;
                    $('#formEditTeacher').attr('action', updateUrl);
                    
                    $('#modalEditTeacher').modal('show');
                    btn.prop('disabled', false);
                },
                error: function(xhr) {
                    // Jika error, tampilkan URL yang gagal di console untuk pengecekan
                    console.error("URL yang dicoba: " + urlAction);
                    alert("Gagal mengambil data. Error: 404 (URL tidak ditemukan)");
                    btn.prop('disabled', false);
                }
            });
        });
        // $(document).on('click', '.btn-edit', function() {
        //     let id = $(this).data('id');
        //     let btn = $(this);
            
        //     // Membuat URL dinamis dari nama Route Laravel
        //     let urlAction = "{{ route('teachers.edit.json', ':id') }}";
        //     urlAction = urlAction.replace(':id', id); 

        //     // Visual feedback saat loading
        //     btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        //     $.ajax({
        //         url: urlAction,
        //         type: "GET",
        //         dataType: "JSON",
        //         success: function(data) {
        //             // Isi form modal
        //             $('#edit_name').val(data.user.name);
        //             $('#edit_email').val(data.user.email);
        //             $('#edit_nip').val(data.nip);
        //             $('#edit_phone').val(data.phone);
        //             $('#edit_gender').val(data.gender);
                    
        //             // Update Action URL Form untuk proses Update (PUT)
        //             let updateUrl = "{{ url('teachers') }}/" + id;
        //             $('#formEditTeacher').attr('action', updateUrl);
                    
        //             $('#modalEditTeacher').modal('show');
        //             btn.prop('disabled', false).html('<i class="bx bx-message-square-edit"></i>');
        //         },
        //         error: function(xhr) {
        //             console.error("Detail Error:", xhr.responseText);
        //             alert("Gagal mengambil data. Error: " + xhr.status + " (" + xhr.statusText + ")");
        //             btn.prop('disabled', false).html('<i class="bx bx-message-square-edit"></i>');
        //         }
        //     });
        // });

    </script>
</x-app-layout>