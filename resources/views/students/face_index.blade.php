<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Wajah Saya</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header { flex-direction: column; gap: 8px; text-align: center; }
            .btn-add { width: 100%; }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-3 py-md-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold fs-6 fs-md-5">
                <i class="fas fa-user-circle me-2"></i> Data Sampel Wajah Saya
            </h5>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-bold">Dashboard</a>
        </div>
        <div class="card-body p-3 p-md-4">
            
            <div class="alert alert-info py-2 small mb-3">
                <i class="fas fa-info-circle me-1"></i> Daftarkan minimal 3 sampel wajah (misal: depan, miring, senyum) agar proses scan presensi makin akurat.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Label Sampel</th>
                            <th>Tanggal Pendaftaran</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($descriptors as $index =>$item)
                        <tr id="row-{{ $item->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-primary fs-6">{{ $item->label ?? 'Sampel ' . ($index+1) }}</span></td>
                            <td>{{ $item->created_at->format('d M Y, H:i') }} WIB</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger btn-delete w-100" data-id="{{ $item->id }}">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block text-warning"></i>
                                Anda belum mendaftarkan data wajah.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <a href="{{ route('student.face.register') }}" class="btn btn-success btn-add py-2 fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Sampel Wajah Baru
                </a>
            </div>

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
            text: "Sampel yang dihapus dapat mengurangi presisi deteksi wajah Anda.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/student/face/destroy/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $(`#row-${id}`).remove();
                        Swal.fire('Terhapus!', res.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        });
    });
</script>
</body>
</html>