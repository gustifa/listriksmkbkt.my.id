@extends('layouts.app')

@section('content')
<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Sistem & Pengaturan</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manajemen Multi-Role User</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body">
            <div class="card-title d-flex align-items-center mb-3">
                <div><i class="bx bx-user-check me-1 font-22 text-primary"></i></div>
                <h5 class="mb-0 text-primary">Daftar Hak Akses & Multi-Role Pengguna</h5>
            </div>
            <hr>

            <div class="table-responsive">
                <table id="userRolesTable" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Identitas Base</th>
                            <th>Role Aktif (Multi-Role)</th>
                            <th>Direct Permissions</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data dimuat via AJAX DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT MULTI-ROLE & PERMISSION -->
<div class="modal fade" id="editUserRoleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">
                    <i class="bx bx-shield-quarter me-1"></i> Atur Hak Akses & Role Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUserRole">
                @csrf
                <input type="hidden" id="userId" name="user_id">
                
                <div class="modal-body">
                    <!-- Info Pengguna -->
                    <div class="alert alert-info py-2 mb-3 d-flex align-items-center">
                        <i class="bx bx-info-circle font-20 me-2"></i>
                        <div>
                            <strong>Pengguna:</strong> <span id="userNameText">-</span>
                        </div>
                    </div>

                    <!-- SCOPE DATA KEJURUAN / BENGKEL -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Program Keahlian (Scope Kaprog)</label>
                            <select class="form-select" id="program_id" name="program_id">
                                <option value="">-- Akses Global / Tanpa Scope --</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">Konsentrasi / Major (Scope Kabeng)</label>
                            <select class="form-select" id="major_id" name="major_id">
                                <option value="">-- Akses Global / Tanpa Scope --</option>
                                @foreach($majors as $maj)
                                    <option value="{{ $maj->id }}">{{ $maj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- PENGATURAN MULTI-ROLE (CHECKBOX) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">
                            <i class="bx bx-key me-1"></i> Pilih Role (Bisa Lebih Dari Satu):
                        </label>
                        <div class="row g-2 p-3 border rounded bg-light" style="max-height: 200px; overflow-y: auto;">
                            @foreach($roles as $role)
                                <div class="col-md-4 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label fw-semibold text-dark" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <!-- PENGATURAN DIRECT PERMISSION EXTRA -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">
                            <i class="bx bx-lock-open me-1"></i> Permission Khusus Tambahan (Direct Permission):
                        </label>
                        <div class="row g-2 p-3 border rounded" style="max-height: 180px; overflow-y: auto;">
                            @foreach($permissions as $perm)
                                <div class="col-md-4 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                        <label class="form-check-label small text-muted" for="perm_{{ $perm->id }}">
                                            {{ $perm->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <i class="bx bx-save me-1"></i> Simpan Hak Akses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 CDN Terbaru -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Setup Header CSRF Token AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 1. Inisialisasi DataTable Data Users
    let table = $('#userRolesTable').DataTable({
        processing: true,
        ajax: '{{ route("admin.user-roles.data") }}',
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name', render: data => `<strong>${data}</strong>` },
            { data: 'email' },
            { data: 'jenis_user', render: data => `<span class="badge bg-secondary">${data}</span>` },
            { 
                data: 'roles', 
                render: function(roles) {
                    if (!roles || !roles.length) return '<span class="text-muted small">Tanpa Role</span>';
                    return roles.map(r => `<span class="badge bg-primary me-1 mb-1">${r}</span>`).join('');
                }
            },
            { 
                data: 'direct_permissions', 
                render: function(perms) {
                    if (!perms || !perms.length) return '<span class="text-muted small">-</span>';
                    return perms.map(p => `<span class="badge bg-info text-dark me-1 mb-1">${p}</span>`).join('');
                }
            },
            {
                data: 'id',
                render: function(id) {
                    return `<button class="btn btn-sm btn-outline-primary btn-edit" data-id="${id}">
                                <i class="bx bx-edit-alt"></i> Kelola Role
                            </button>`;
                }
            }
        ]
    });

    // 2. Buka Modal & Ambil Data Detail User via AJAX
    $(document).on('click', '.btn-edit', function() {
        let userId = $(this).data('id');
        
        Swal.fire({
            title: 'Memuat Data...',
            text: 'Mengambil konfigurasi role pengguna',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `{{ url('/admin/user-roles') }}/${userId}/edit`,
            type: 'GET',
            success: function(res) {
                Swal.close();
                let u = res.user;

                $('#userId').val(u.id);
                $('#userNameText').text(`${u.name} (${u.email})`);
                $('#program_id').val(u.program_id ?? '');
                $('#major_id').val(u.major_id ?? '');

                // Reset Checkboxes
                $('.role-checkbox').prop('checked', false);
                $('.perm-checkbox').prop('checked', false);

                // Set Checkbox Role yang Dimiliki User
                if (u.roles) {
                    u.roles.forEach(roleName => {
                        $(`.role-checkbox[value="${roleName}"]`).prop('checked', true);
                    });
                }

                // Set Checkbox Direct Permission Khusus
                if (u.direct_permissions) {
                    u.direct_permissions.forEach(permName => {
                        $(`.perm-checkbox[value="${permName}"]`).prop('checked', true);
                    });
                }

                $('#editUserRoleModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengambil data user dari server.'
                });
            }
        });
    });

    // 3. Simpan Perubahan Multi-Role via AJAX & SweetAlert2
    $('#formUserRole').on('submit', function(e) {
        e.preventDefault();
        let userId = $('#userId').val();
        let formData = $(this).serialize();

        $('#btnSave').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: `{{ url('/admin/user-roles') }}/${userId}/update`,
            type: 'POST',
            data: formData,
            success: function(res) {
                $('#btnSave').prop('disabled', false).html('<i class="bx bx-save me-1"></i> Simpan Hak Akses');
                $('#editUserRoleModal').modal('hide');

                // Toast Notification SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                $('#btnSave').prop('disabled', false).html('<i class="bx bx-save me-1"></i> Simpan Hak Akses');
                
                let errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan pada sistem.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan!',
                    text: errMsg
                });
            }
        });
    });
});
</script>
@endpush