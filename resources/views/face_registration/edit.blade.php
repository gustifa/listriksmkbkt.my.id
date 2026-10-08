<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Sampel - {{ $student->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i> Sampel Wajah: {{ $student->name }}</h5>
            <a href="{{ route('face.index') }}" class="btn btn-sm btn-dark">Kembali</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Label Sampel</th>
                        <th>Tanggal Tambah</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->faceDescriptors as $index =>$item)
                    <tr id="row-{{ $item->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td><span class="badge bg-primary fs-6">{{ $item->label ?? 'Sampel ' . ($index+1) }}</span></td>
                        <td>{{ $item->created_at->format('d M Y, H:i') }} WIB</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $item->id }}">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada sampel data wajah yang tersimpan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <a href="{{ route('face.create', $student->id) }}" class="btn btn-success mt-2">
                <i class="fas fa-plus me-1"></i> Tambah Sampel
            </a>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.btn-delete').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus sampel ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/face-registration/destroy/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $(`#row-${id}`).remove();
                        Swal.fire('Terhapus!', res.message, 'success');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
