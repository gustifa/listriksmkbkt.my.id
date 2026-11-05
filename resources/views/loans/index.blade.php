@section('title', 'Riwayat Peminjaman Alat & Bahan')
<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-borrowed { background-color: #ffc107; color: #000; font-weight: 600; }
        .badge-returned { background-color: #198754; color: #fff; font-weight: 600; }
        .table th, .table td { vertical-align: middle; }
    </style>

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Peminjaman Alat & Bahan</h3>
            <p class="text-muted small mb-0">Daftar transaksi peminjaman barang praktikum bengkel sekolah</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('loans.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Pinjam Barang Baru
            </a>
        </div>
    </div>

    <!-- Alert Success / Error (Session Flash) -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Table Card -->
    <div class="card card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 5%;">#</th>
                            <th style="width: 14%;">Tgl Pinjam</th>
                            <th style="width: 18%;">Peminjam</th>
                            <th style="width: 22%;">Nama Barang & Kode</th>
                            <th style="width: 16%;">Bengkel / Ruangan</th>
                            <th style="width: 8%;">Jumlah</th>
                            <th style="width: 10%;">Status</th>
                            <th class="text-center pe-3" style="width: 7%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $index => $loan)
                            @php
                                $user = Auth::user();
                                $canProcessReturn = false;

                                // Pengecekan Otorisasi Pengembalian
                                if ($user->hasAnyRole(['super_admin', 'admin', 'kabeng', 'kaprog', 'toolman'])) {
                                    $canProcessReturn = true;
                                } elseif ($user->hasRole('guru') && optional($user->teacher)->id === $loan->teacher_id) {
                                    $canProcessReturn = true;
                                } elseif ($user->hasRole('siswa') && optional($user->student)->id === $loan->student_id) {
                                    $canProcessReturn = true;
                                }
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted">{{ $loans->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($loan->borrow_date)->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($loan->borrow_date)->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    @if($loan->student)
                                        <div class="fw-bold text-dark">{{ $loan->student->name }}</div>
                                        <small class="badge bg-info text-dark">Siswa / {{ $loan->student->classroom->name ?? '-' }}</small>
                                    @elseif($loan->teacher)
                                        <div class="fw-bold text-dark">{{ $loan->teacher->name }}</div>
                                        <small class="badge bg-secondary">Guru / Instruktur</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $loan->inventory->name ?? 'Barang Dihapus' }}</div>
                                    <small class="badge bg-light text-dark border">{{ $loan->inventory->code ?? '-' }}</small>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt me-1 text-primary"></i>
                                    {{ $loan->inventory->room->name ?? '-' }}
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $loan->amount ?? 1 }}</span> 
                                    <small class="text-muted">{{ $loan->inventory->unit ?? 'pcs' }}</small>
                                </td>
                                <td>
                                    @if($loan->status === 'borrowed')
                                        <span class="badge badge-borrowed px-2 py-1">
                                            <i class="bi bi-clock-history me-1"></i> Dipinjam
                                        </span>
                                    @else
                                        <span class="badge badge-returned px-2 py-1">
                                            <i class="bi bi-check2-circle me-1"></i> Dikembalikan
                                        </span>
                                        @if($loan->return_date)
                                            <div class="small text-muted mt-1">
                                                {{ \Carbon\Carbon::parse($loan->return_date)->translatedFormat('d M Y H:i') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @if($loan->status === 'borrowed')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('loans.print', $loan->id) }}" target="_blank">
                                                        <i class="bi bi-printer me-2 text-primary"></i> Cetak Struk Pinjam
                                                    </a>
                                                </li>
                                                @if($canProcessReturn)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-success" href="javascript:void(0)" onclick="confirmReturn('{{ $loan->id }}', '{{ $loan->inventory->name ?? 'Barang' }}')">
                                                            <i class="bi bi-box-arrow-in-left me-2"></i> Kembalikan Barang
                                                        </a>
                                                    </li>
                                                @endif
                                            @else
                                                <!-- Opsi Cetak Setelah Pengembalian -->
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('loans.print', $loan->id) }}" target="_blank">
                                                        <i class="bi bi-printer me-2 text-secondary"></i> Struk Peminjaman
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="{{ route('loans.print-return', $loan->id) }}" target="_blank">
                                                        <i class="bi bi-printer-fill me-2"></i> Cetak Bukti Kembali
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <!-- Form Hidden Pengembalian -->
                                    @if($loan->status === 'borrowed' && $canProcessReturn)
                                        <form id="return-form-{{ $loan->id }}" action="{{ route('loans.return', $loan->id) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada riwayat peminjaman barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($loans->hasPages())
                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <span class="small text-muted">
                        Menampilkan {{ $loans->firstItem() }} - {{ $loans->lastItem() }} dari total {{ $loans->total() }} transaksi
                    </span>
                    <div>
                        {{ $loans->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmReturn(loanId, itemName) {
    Swal.fire({
        title: 'Kembalikan Barang?',
        text: `Apakah Anda yakin ingin mengembalikan '${itemName}' ke stok bengkel?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Kembalikan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Pengembalian...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            document.getElementById(`return-form-${loanId}`).submit();
        }
    });
}
</script>
</x-app-layout>