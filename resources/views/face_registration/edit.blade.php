<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Sampel - {{ $student->name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }

        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header { flex-direction: column; gap: 8px; text-align: center; }
            .table-responsive { font-size: 0.875rem; }
            .btn-add-sample { width: 100%; }
        }

        @supports (padding: max(0px)) {
            body {
                padding-left: min(0px, env(safe-area-inset-left));
                padding-right: min(0px, env(safe-area-inset-right));
                padding-bottom: min(0px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-3 py-md-4">
    <div class="card shadow border-0">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold fs-6 fs-md-5">
                <i class="fas fa-id-card me-2"></i> Sampel Wajah: {{ $student->name }}
            </h5>
            <a href="{{ route('face.index') }}" class="btn btn-sm btn-dark fw-bold">Kembali</a>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Label Sampel</th>
                            <th>Tanggal Rekam</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->faceDescriptors as $index =>$item)
                        <tr id="row-{{ $item->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-primary fs-6">{{ $item->label ?? 'Sampel ' . ($index+1) }}</span></td>
                            <td>{{ $item->created_at->format('d M Y, H:i') }} WIB</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger btn-delete w-100 w-md-auto" data-id="{{ $item->id }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada sampel data wajah yang tersimpan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <a href="{{ route('face.create', $student->id) }}" class="btn btn-success btn-add-sample">
                    <i class="fas fa-plus me-1"></i> Tambah Sampel Baru
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
            text: "Penghapusan sampel dapat menurunkan akurasi scan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/face-registration/destroy/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $(`#row-${id}`).remove();
                        Swal.fire('Terhapus!', res.message, 'success');
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Gagal menghapus sampel.', 'error');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
