@section('title', 'Atur Hak Akses')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('admin.roles.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Role
                </a>
                <h4 class="mb-0 fw-bold text-primary">Atur Hak Akses: <span class="text-uppercase text-dark">{{ str_replace('_', ' ', $role->name) }}</span></h4>
            </div>
        </div>

        <div class="card border-0 shadow-lg">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-key me-2 text-warning"></i>Daftar Permissions (Hak Akses)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Pilih Semua</button>
                </div>
                
                <div class="card-body p-4 bg-light">
                    <div class="row g-4">
                        @foreach($groupedPermissions as $groupName => $perms)
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white fw-bold text-primary border-bottom-0 pb-0">
                                        {{ $groupName }}
                                    </div>
                                    <div class="card-body">
                                        @foreach($perms as $perm)
                                            <div class="form-check custom-checkbox mb-2">
                                                <input class="form-check-input perm-checkbox" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $perm->name }}" 
                                                       id="perm_{{ $perm->id }}"
                                                       {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}>
                                                <label class="form-check-label cursor-pointer w-100" for="perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer bg-white py-3 text-end">
                    <button type="submit" class="btn btn-success btn-lg fw-bold px-5 shadow-sm">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan Akses
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-checkbox .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
        .cursor-pointer { cursor: pointer; }
    </style>

    @push('scripts')
    <script>
        document.getElementById('selectAll').addEventListener('click', function() {
            let checkboxes = document.querySelectorAll('.perm-checkbox');
            let allChecked = true;
            
            // Cek apakah semua sudah tercentang
            checkboxes.forEach(cb => {
                if(!cb.checked) allChecked = false;
            });

            // Jika semua tercentang, maka uncheck semua. Jika belum, check semua.
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            this.innerText = allChecked ? "Pilih Semua" : "Batalkan Semua";
            this.className = allChecked ? "btn btn-sm btn-outline-primary" : "btn btn-sm btn-outline-danger";
        });
    </script>
    @endpush
</x-app-layout>