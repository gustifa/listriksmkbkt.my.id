@section('title', 'Manajemen Role & Permission')

<x-app-layout>
    <div class="page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Pengaturan Role & Akses</h4>
                <p class="mb-0 text-muted">Kelola jenis pengguna dan hak akses aplikasi.</p>
            </div>
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fas fa-plus me-1"></i> Tambah Role
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="20%">Nama Role</th>
                                <th width="15%" class="text-center">Jumlah Pengguna</th>
                                <th width="45%">Hak Akses (Permissions)</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $index => $role)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-dark text-uppercase">{{ str_replace('_', ' ', $role->name) }}</span>
                                    <small class="d-block text-muted font-monospace">{{ $role->name }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3">{{ $role->users_count }} Akun</span>
                                </td>
                                <td>
                                    @if($role->name == 'super_admin')
                                        <span class="badge bg-danger">ALL PERMISSIONS (Bypass)</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($role->permissions->take(5) as $perm)
                                                <span class="badge bg-light text-dark border">{{ $perm->name }}</span>
                                            @empty
                                                <span class="text-muted small fst-italic">Belum ada akses diset</span>
                                            @endforelse
                                            
                                            @if($role->permissions->count() > 5)
                                                <span class="badge bg-secondary">+{{ $role->permissions->count() - 5 }} lainnya</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($role->name !== 'super_admin')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm">
                                            <i class="fas fa-user-cog me-1"></i> Atur Akses
                                        </a>
                                        @if(!in_array($role->name, ['admin', 'guru', 'siswa']))
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus role ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus Role"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-secondary disabled"><i class="fas fa-lock me-1"></i> Locked</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ROLE -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Jenis Role Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: koordinator_pkl">
                            <div class="form-text text-muted">Gunakan huruf kecil dan garis bawah (underscore) jika lebih dari satu kata.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>