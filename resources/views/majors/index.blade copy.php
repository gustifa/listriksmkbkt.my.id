@section('title', 'Jurusan')
<x-app-layout>
    <div class="page-content">       
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('majors.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i> Tambah Jurusan
                    </a>
                </div>

                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">List Jurusan</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table table-hover table-bordered align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th width="10%">No</th>
                                        <th>Nama Jurusan</th>
                                        <th>Kode</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($majors as $key => $major)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td class="fw-bold text-dark">{{ $major->name }}</td>
                                            <td class="fw-bold text-dark">{{ $major->code }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('majors.edit', $major->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">Edit
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <!-- Form Hapus dengan SweetAlert -->
                                                    <form id="delete-form-{{ $major->id }}" action="{{ route('majors.destroy', $major->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger" title="Hapus" onclick="confirmDelete('{{ $major->id }}', '{{ $major->name }}')">
                                                            <i class="fas fa-trash"></i>Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <img src="https://img.icons8.com/ios/100/cccccc/books.png" width="60" class="mb-3 opacity-50">
                                                <p class="mb-0">Belum ada data Jurusan.</p>
                                                <small>Silakan klik tombol "Tambah Jurusan" untuk memulai.</small>
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
        // Cek apakah ada session 'success' yang dikirim dari controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000 // Notifikasi hilang otomatis setelah 2 detik
            });
        @endif

        // Opsional: Cek jika ada error validasi
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
                        text: "Mata pelajaran " + name + " akan dihapus! Data jadwal yang menggunakan mapel ini mungkin akan terpengaruh.",
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