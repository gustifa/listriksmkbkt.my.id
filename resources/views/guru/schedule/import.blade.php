@section('title')
   Import Jadwal Pelajaran
@endsection


    <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fas fa-book me-2"></i> Master Data Mata Pelajaran</h3>
        <a href="{{ route('subjects.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Mapel
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">List Mata Pelajaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="10%">No</th>
                            <th>Nama Mata Pelajaran</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $key => $subject)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $subject->name }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mapel {{ $subject->name }}? Data jadwal terkait mungkin akan terpengaruh.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <img src="https://img.icons8.com/ios/100/cccccc/books.png" width="60" class="mb-3 opacity-50">
                                    <p class="mb-0">Belum ada data mata pelajaran.</p>
                                    <small>Silakan klik tombol "Tambah Mapel" untuk memulai.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-app-layout>