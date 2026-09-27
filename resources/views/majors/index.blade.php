@section('title', 'Data Jurusan')

<x-app-layout>
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Data Jurusan</h4>
            <a href="{{ route('majors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Jurusan
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 text-primary fw-bold">List Jurusan & Konsentrasi Keahlian</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th>Program Keahlian</th>
                                <th>Nama Konsentrasi (Major)</th>
                                <th>Ketua Program</th>
                                <th>Kabeng</th>
                                <th>Kode</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($majors as $index => $major)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $major->program->name ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $major->name }}</strong><br>
                                        <small class="text-muted">ID: {{ $major->code }}</small>
                                    </td>
                                    <td>{{ $major->program->head_of_program ?? '-' }}</td>
                                    <td>{{ $major->workshopTeacher->name ?? '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ $major->code }}</span></td>
                                    <td>
                                        <a href="{{ route('majors.edit', $major->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('majors.destroy', $major->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">Belum ada data jurusan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            @endif

            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.delete-form');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>