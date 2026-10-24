@extends('layouts.app')

@push('css')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold text-dark">Data Program Keahlian</h4>
        <button type="button" class="btn btn-primary shadow-sm" id="btnAddProgram">
            <i class="bx bx-plus me-1"></i> Tambah Program
        </button>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">List Program Keahlian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-programs" class="table table-hover table-bordered align-middle w-100">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Program Keahlian</th>
                            <th>Kode</th>
                            <th>Ketua Program (Penilai)</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORM PROGRAM (CREATE / EDIT) -->
<div class="modal fade" id="modalProgram" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalProgramTitle">Tambah Program Keahlian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProgram">
                @csrf
                <input type="hidden" id="program_id" name="program_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Program Keahlian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Teknik Ketenagakerjaan" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Program <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="Contoh: TKT" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ketua Program (Penilai)</label>
                        <select class="form-select select2" id="program_teacher_id" name="program_teacher_id" style="width: 100%;">
                            <option value="">-- Pilih Ketua Program --</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Inisialisasi Select2
    function initSelect2() {
        $('#program_teacher_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalProgram'),
            placeholder: '-- Pilih Ketua Program --',
            allowClear: true
        });
    }

    // 1. DataTables AJAX Load
    let table = $('#table-programs').DataTable({
        processing: true,
        ajax: '{{ route("programs.index") }}',
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
            { data: 'name', render: data => `<strong>${data}</strong>` },
            { data: 'code', render: data => `<span class="badge bg-secondary">${data}</span>`, className: 'text-center' },
            { 
                data: 'teacher', 
                render: teacher => teacher ? `<span class="text-dark fw-semibold">${teacher.name}</span>` : '<span class="text-muted small">Belum Diatur</span>' 
            },
            {
                data: 'id',
                className: 'text-center',
                render: function(id) {
                    return `
                        <button class="btn btn-sm btn-warning text-white btn-edit" data-id="${id}" title="Edit">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${id}" title="Hapus">
                            <i class="bx bx-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // 2. Open Modal Tambah (Fetch Data Guru Terbaru via AJAX)
    $('#btnAddProgram').on('click', function() {
        $('#formProgram')[0].reset();
        $('#program_id').val('');

        Swal.fire({ title: 'Memuat Data Guru...', didOpen: () => Swal.showLoading() });

        $.get('{{ route("programs.available-teachers") }}', function(teachers) {
            Swal.close();
            
            let selectGuru = $('#program_teacher_id');
            selectGuru.empty().append('<option value="">-- Pilih Ketua Program --</option>');
            
            teachers.forEach(t => {
                selectGuru.append(`<option value="${t.id}">${t.name}</option>`);
            });

            selectGuru.val('').trigger('change');
            $('#modalProgramTitle').text('Tambah Program Keahlian');
            $('#modalProgram').modal('show');
            initSelect2();
        }).fail(function() {
            Swal.fire('Gagal!', 'Gagal mengambil data guru dari server.', 'error');
        });
    });

    // 3. Open Modal Edit via AJAX
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');

        Swal.fire({ title: 'Memuat Data...', didOpen: () => Swal.showLoading() });

        $.get(`{{ url('/program/edit') }}/${id}`, function(res) {
            Swal.close();
            $('#program_id').val(res.program.id);
            $('#name').val(res.program.name);
            $('#code').val(res.program.code);

            // Populate Dropdown Guru
            let selectGuru = $('#program_teacher_id');
            selectGuru.empty().append('<option value="">-- Pilih Ketua Program --</option>');
            res.teachers.forEach(t => {
                let selected = (t.id === res.program.program_teacher_id) ? 'selected' : '';
                selectGuru.append(`<option value="${t.id}" ${selected}>${t.name}</option>`);
            });

            selectGuru.val(res.program.program_teacher_id).trigger('change');

            $('#modalProgramTitle').text('Edit Program Keahlian');
            $('#modalProgram').modal('show');
            initSelect2();
        }).fail(function() {
            Swal.fire('Gagal!', 'Gagal mengambil data dari server.', 'error');
        });
    });

    // 4. Submit Form (Store / Update) via AJAX
    $('#formProgram').on('submit', function(e) {
        e.preventDefault();
        let id = $('#program_id').val();
        let url = id ? `{{ url('/program') }}/${id}` : '{{ route("programs.store") }}';
        let method = id ? 'PUT' : 'POST';

        $('#btnSave').prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#btnSave').prop('disabled', false).text('Simpan');
                $('#modalProgram').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                $('#btnSave').prop('disabled', false).text('Simpan');
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });

    // 5. Hapus Data via AJAX & SweetAlert2
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Program Keahlian ini akan dihapus dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/program') }}/${id}`,
                    type: 'DELETE',
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush