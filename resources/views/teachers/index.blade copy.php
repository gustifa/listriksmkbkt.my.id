@section('title')
   Data Guru
@endsection
<x-app-layout>
    <div class="page-content">

            <div class="mb-4 d-flex justify-content-between align-items-center">

                <div>
                    <a href="{{ route('teachers.import') }}" class="shadow-sm btn btn-success">
                        <i class="bx bx-import"></i> Import
                    </a>

                    <a href="{{ route('teachers.export') }}" class="shadow-sm btn btn-warning">
                        <i class="bx bx-export"></i> Export
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="border-0 shadow card">
                <div class="card-body">

                    <!-- Form Pencarian -->
                    <form action="{{ route('teachers.index') }}" method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama, NIP, atau Email..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>NIP</th>
                                    <th>L/P</th>
                                    <th>Email (Login)</th>
                                    <th>No. HP</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachers as $teacher)
                                    <tr>
                                        <td class="fw-bold">{{ $teacher->user->name ?? '-' }}</td>
                                        <td>{{ $teacher->nip ?? '-' }}</td>
                                        <td>{{ $teacher->gender ?? '-' }}</td>
                                        <td>{{ $teacher->user->email ?? '-' }}</td>
                                        <td>{{ $teacher->phone ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                 <a href="{{ route('teachers.show', $teacher->id) }}" class="text-white btn btn-sm btn-success" title="Detail">
                                                    <i class="bx bx-info-circle"></i>
                                                </a>
                                                <a href="{{ route('teachers.edit', $teacher->id) }}" class="text-white btn btn-sm btn-warning" title="Edit">
                                                    <i class="bx bx-message-square-edit"></i>
                                                </a>
                                                <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus guru ini? Akun login juga akan terhapus.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="bx bx-message-square-x"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-muted">
                                            Data guru belum tersedia. Silakan Import Data terlebih dahulu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $teachers->withQueryString()->links() }}
                    </div>
                </div>
            </div>

    </div>
</x-app-layout>
