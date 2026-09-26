@section('title', 'Master Jenis Pelanggaran')

<x-app-layout>
    <div class="page-content">
        <!-- Header -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-exclamation-circle me-2"></i>Jenis Pelanggaran</h4>
                <p class="mb-0 text-muted">Kelola daftar pelanggaran dan bobot poin siswa.</p>
            </div>
            <a href="{{ route('admin.violation-types.create') }}" class="shadow-sm btn btn-primary fw-bold">
                <i class="fas fa-plus me-1"></i> Tambah Data
            </a>
        </div>

        <!-- Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="border-0 shadow-lg card">
            <div class="py-3 bg-white card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mt-2 mb-0 fw-bold text-dark">Daftar Pelanggaran</h6>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('admin.violation-types.index') }}" method="GET" class="gap-2 d-flex">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama pelanggaran..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="40%">Nama Pelanggaran</th>
                                <th class="text-center" width="15%">Kategori</th>
                                <th class="text-center" width="15%">Bobot Poin</th>
                                @role('admin')
                                <th class="text-center" width="15%">Aksi</th>
                                @endrole
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violationTypes as $index => $item)
                                @php
                                    $badgeClass = match($item->category) {
                                        'ringan' => 'bg-success',
                                        'sedang' => 'bg-warning text-dark',
                                        'berat'  => 'bg-danger',
                                        default  => 'bg-secondary'
                                    };
                                @endphp
                            <tr>
                                <td class="ps-4">{{ $violationTypes->firstItem() + $index }}</td>
                                <td class="fw-bold text-dark">{{ $item->name }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} rounded-pill text-uppercase px-3">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger">{{ $item->points }} Poin</span>
                                </td>
                                @role('admin')
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.violation-types.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.violation-types.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endrole
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-5 text-center text-muted">
                                    <img src="https://illustrations.popsy.co/gray/question-mark.svg" class="mb-3 opacity-50 w-25" alt="Empty">
                                    <br>Belum ada data pelanggaran.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white card-footer">
                {{ $violationTypes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
