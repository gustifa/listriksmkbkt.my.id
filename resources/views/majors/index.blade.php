@extends('layouts.app')

@push('css')
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-dark">Data Jurusan</h4>
        <button type="button" class="btn btn-primary shadow-sm" id="btnAddMajor">
            <i class="bx bx-plus me-1"></i> Tambah Jurusan
        </button>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 text-primary fw-bold">List Jurusan & Konsentrasi Keahlian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-majors" class="table table-bordered table-hover align-middle w-100">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Program Keahlian</th>
                            <th>Nama Konsentrasi (Major)</th>
                            <th>Ketua Program</th>
                            <th>Kabeng</th>
                            <th width="10%">Kode</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FORM JURUSAN (CREATE / EDIT) -->
<div class="modal fade" id="modalMajor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalMajorTitle">Tambah Jurusan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMajor">
                @csrf
                <input type="hidden" id="major_id" name="major_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Program Keahlian <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="program_id" name="program_id" style="width: 100%;" required>
                                <option value="">-- Pilih Program Keahlian --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="Contoh: TITL" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Konsentrasi / Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: TEKNIK INSTALASI TENAGA LISTRIK" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kepala Bengkel (Kabeng)</label>
                        <select class="form-select select2" id="workshop_teacher_id" name="workshop_teacher_id" style="width: 100%;">
                            <option value="">-- Pilih Kepala Bengkel --</option>
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

    function initSelect2() {
        $('#program_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalMajor'),
            placeholder: '-- Pilih Program Keahlian --',
            allowClear: true
        });

        $('#workshop_teacher_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalMajor'),
            placeholder: '-- Pilih Kepala Bengkel --',
            allowClear: true
        });
    }

    // 1. DataTables AJAX Load dengan Auto-Merge Cell (Rowspan)
    let table = $('#table-majors').DataTable({
        processing: true,
        ajax: '{{ route("majors.index") }}',
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center align-middle' },
            { 
                data: 'program', 
                className: 'align-middle text-center fw-bold text-dark',
                render: program => (program && program.name) ? program.name : '-' 
            },
            { 
                data: 'name', 
                className: 'align-middle',
                render: (name, type, row) => `<strong>${name}</strong><br><small class="text-muted">ID: ${row.code}</small>` 
            },
            { 
                data: 'program', 
                className: 'align-middle text-center',
                render: program => (program && program.teacher) ? `<span class="text-dark fw-semibold">${program.teacher.name}</span>` : '<span class="text-muted small">Belum Diatur</span>' 
            },
            { 
                data: 'workshop_teacher', 
                className: 'align-middle text-center',
                render: teacher => (teacher && teacher.name) ? `<span class="text-dark fw-semibold">${teacher.name}</span>` : '<span class="text-muted small">Belum Diatur</span>' 
            },
            { 
                data: 'code', 
                className: 'text-center align-middle',
                render: code => `<span class="badge bg-secondary">${code}</span>` 
            },
            {
                data: 'id',
                className: 'text-center align-middle',
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
        ],
        // Penggabungan otomatis sel Program Keahlian & Ketua Program yang sama
        drawCallback: function(settings) {
            let api = this.api();
            let rows = api.rows({ page: 'current' }).nodes();
            let lastProgram = null;
            let programCell = null;
            let teacherCell = null;
            let rowspan = 1;

            api.column(1, { page: 'current' }).data().each(function(program, i) {
                let progName = (program && program.name) ? program.name : '-';

                if (lastProgram === progName) {
                    rowspan++;
                    $(programCell).attr('rowspan', rowspan);
                    $(teacherCell).attr('rowspan', rowspan);
                    $(rows[i]).find('td:eq(1)').hide(); // Sembunyikan sel duplikat Program Keahlian
                    $(rows[i]).find('td:eq(3)').hide(); // Sembunyikan sel duplikat Ketua Program
                } else {
                    lastProgram = progName;
                    rowspan = 1;
                    programCell = $(rows[i]).find('td:eq(1)');
                    teacherCell = $(rows[i]).find('td:eq(3)');
                }
            });
        }
    });

    // 2. Open Modal Tambah (Fetch Data Guru Kabeng Terbaru via AJAX)
    $('#btnAddMajor').on('click', function() {
        $('#formMajor')[0].reset();
        $('#major_id').val('');
        $('#program_id').val('').trigger('change');

        Swal.fire({ title: 'Memuat Data...', didOpen: () => Swal.showLoading() });

        $.get('{{ route("majors.available-teachers") }}', function(teachers) {
            Swal.close();
            
            let selectGuru = $('#workshop_teacher_id');
            selectGuru.empty().append('<option value="">-- Pilih Kepala Bengkel --</option>');
            
            teachers.forEach(t => {
                selectGuru.append(`<option value="${t.id}">${t.name}</option>`);
            });

            selectGuru.val('').trigger('change');
            $('#modalMajorTitle').text('Tambah Jurusan / Konsentrasi');
            $('#modalMajor').modal('show');
            initSelect2();
        }).fail(function() {
            Swal.fire('Gagal!', 'Gagal mengambil data dari server.', 'error');
        });
    });

    // 3. Open Modal Edit via AJAX
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');

        Swal.fire({ title: 'Memuat Data...', didOpen: () => Swal.showLoading() });

        $.get(`{{ url('/majors') }}/${id}/edit`, function(res) {
            Swal.close();
            $('#major_id').val(res.major.id);
            $('#name').val(res.major.name);
            $('#code').val(res.major.code);
            $('#program_id').val(res.major.program_id).trigger('change');

            let selectGuru = $('#workshop_teacher_id');
            selectGuru.empty().append('<option value="">-- Pilih Kepala Bengkel --</option>');
            res.teachers.forEach(t => {
                let selected = (t.id === res.major.workshop_teacher_id) ? 'selected' : '';
                selectGuru.append(`<option value="${t.id}" ${selected}>${t.name}</option>`);
            });

            selectGuru.val(res.major.workshop_teacher_id).trigger('change');

            $('#modalMajorTitle').text('Edit Jurusan / Konsentrasi');
            $('#modalMajor').modal('show');
            initSelect2();
        }).fail(function() {
            Swal.fire('Gagal!', 'Gagal mengambil data dari server.', 'error');
        });
    });

    // 4. Submit Form (Store / Update) via AJAX
    $('#formMajor').on('submit', function(e) {
        e.preventDefault();
        let id = $('#major_id').val();
        let url = id ? `{{ url('/majors') }}/${id}` : '{{ route("majors.store") }}';
        let method = id ? 'PUT' : 'POST';

        $('#btnSave').prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#btnSave').prop('disabled', false).text('Simpan');
                $('#modalMajor').modal('hide');
                
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
        let deleteUrl = '{{ route("majors.index") }}' + '/' + id;

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Jurusan ini akan dihapus dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
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
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush