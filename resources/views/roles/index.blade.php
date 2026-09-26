<x-app-layout>
    <div class="page-content">
        <div class="container mt-5">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h3>Manajemen Role & Hak Akses</h3>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">+ Tambah Role</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="shadow card">
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Role</th>
                                <th>Hak Akses (Permissions)</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $key => $role)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="fw-bold text-uppercase">{{ $role->name }}</td>
                                <td>
                                    @foreach($role->permissions as $perm)
                                        <span class="badge bg-info text-dark">{{ $perm->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                    @if($role->name != 'admin')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus role ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
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
</x-app-layout>
