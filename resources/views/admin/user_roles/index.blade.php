@extends('layouts.app') {{-- sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="page-content">
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
                            <th>Nama User</th>
                            <th>Email</th>
                            <th>Identitas</th>
                            <th>Roles (Multi-Role)</th>
                            <th>Direct Permissions</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT MULTI-ROLE & PERMISSION -->
<div class="modal fade" id="editUserRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalTitle">Atur Multi-Role & Hak Akses User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUserRole">
                @csrf
                <input type="hidden" id="userId" name="user_id">
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bx bx-info-circle me-1"></i> <strong id="userNameText">User Name</strong>
                    </div>

                    <!-- SCOPE KEJURUAN / BENGKEL -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Program Keahlian (Kaprog Scope)</label>
                            <select class="form-select" id="program_id" name="program_id">
                                <option value="">-- Tanpa Scope (Global) --</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Konsentrasi / Major (Kabeng Scope)</label>
                            <select class="form-select" id="major_id" name="major_id">
                                <option value="">-- Tanpa Scope (Global) --</option>
                                @foreach($majors as $maj)
                                    <option value="{{ $maj->id }}">{{ $maj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- PENGATURAN MULTI-ROLE (CHECKBOXES) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Pilih Role User (Bisa Lebih Dari Satu):</label>
                        <div class="row g-2 p-2 border rounded bg-light">
                            @foreach($roles as $role)
                                <div class="col-md-4 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}">
                                        <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">
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
                        <label class="form-label fw-bold text-secondary">Permission Tambahan Khusus (Optional Direct Permission):</label>
                        <div class="row g-2 p-2 border rounded" style="max-height: 180px; overflow-y: auto;">
                            @foreach($permissions as $perm)
                                <div class="col-md-4 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                        <label class="form-check-label small" for="perm_{{ $perm->id }}">
                                            {{ $perm->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
{{-- Library SweetAlert2 Terakhir & DataTables --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // 1. Load Data Users via AJAX
    let table = $('#userRolesTable').DataTable({
        ajax: '{{ route("admin.user-roles.data") }}',
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name', render: data => `<strong>${data}</strong>` },
            { data: 'email' },
            { data: 'jenis_user', render: data => `<span class="badge bg-secondary">${data}</span>` },
            { 
                data: 'roles', 
                render: function(roles) {
                    if (!roles.length) return '<span class="text-muted small">No Role</span>';
                    return roles.map(r => `<span class="badge bg-primary me-1">${r}</span>`).join('');
                }
            },
            { 
                data: 'direct_permissions', 
                render: function(perms) {
                    if (!perms.length) return '<span class="text-muted small">-</span>';
                    return perms.map(p => `<span class="badge bg-info text-dark me-1">${p}</span>`).join('');
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

    // 2. Open Modal & Load Single User Data
    $(document).on('click', '.btn-edit', function() {
        let userId = $(this).data('id');
        
        Swal.fire({
            title: 'Memuat Data...',
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

                // Check Assigned Roles
                u.roles.forEach(roleName => {
                    $(`.role-checkbox[value="${roleName}"]`).prop('checked', true);
                });

                // Check Direct Permissions
                u.direct_permissions.forEach(permName => {
                    $(`.perm-checkbox[value="${permName}"]`).prop('checked', true);
                });

                $('#editUserRoleModal').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Tidak dapat mengambil data user dari server.'
                });
            }
        });
    });

    // 3. Submit Form Update via AJAX & SweetAlert2
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

                // Toast Notification SweetAlert2 Terbaru
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
                $('#btnSave').prop('disabled', false).html('<i class="bx bx-save me-1"></i> Simpan Hak Akses');
                
                let errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
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