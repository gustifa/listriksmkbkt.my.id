@section('title', 'Program Keahlian')

<x-app-layout>
    <div class="page-content">       
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold text-dark">Data Program Keahlian</h4>
            <a href="{{ route('programs.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> Tambah Program
            </a>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">List Program Keahlian</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-programs" class="table table-hover table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Program Keahlian</th>
                                <th>Kode</th>
                                <th>Ketua Program (Penilai)</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programs as $key => $program)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $program->name }}</td>
                                    <td class="text-center fw-bold">{{ $program->code }}</td>
                                    <td>
                                        {{-- Asumsi Anda memiliki relasi 'teacher' di model Program --}}
                                        {{ $program->teacher->name ?? 'Belum Diatur' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('programs.edit', $program->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Form Hapus dengan SweetAlert -->
                                            <form id="delete-form-{{ $program->id }}" action="{{ route('programs.destroy', $program->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $program->id }}', '{{ $program->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <img src="https://img.icons8.com/ios/100/cccccc/books.png" width="60" class="mb-3 opacity-50">
                                        <p class="mb-0">Belum ada data Program Keahlian.</p>
                                        <small>Silakan klik tombol "Tambah Program" untuk memulai.</small>
                                    </td>
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
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Mohon periksa kembali inputan Anda.',
            });
        @endif

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Program Keahlian " + name + " akan dihapus. Data yang terhubung mungkin akan ikut terdampak.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
</x-app-layout>