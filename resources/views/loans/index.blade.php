@section('title', 'Riwayat Peminjaman')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i> Peminjaman Saya</h5>
                        <a href="{{ route('loans.scan') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-qrcode me-1"></i> Scan Peminjaman Baru
                        </a>
                    </div>
                    <div class="card-body">
                        
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Kode</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Status</th>
                                        <th>Tanggal Kembali</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loans as $loan)
                                    <tr>
                                        <td class="fw-bold">{{ $loan->inventory->name ?? 'Barang Dihapus' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $loan->inventory->code ?? '-' }}</span></td>
                                        <td>{{ $loan->borrow_date->format('d M Y, H:i') }}</td>
                                        <td>
                                            @if($loan->status == 'borrowed')
                                                <span class="badge bg-warning text-dark">Sedang Dipinjam</span>
                                            @elseif($loan->status == 'returned')
                                                <span class="badge bg-success">Dikembalikan</span>
                                            @else
                                                <span class="badge bg-danger">Hilang</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $loan->return_date ? $loan->return_date->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($loan->status == 'borrowed')
                                                <form action="{{ route('loans.return', $loan->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Apakah Anda yakin sudah mengembalikan barang ini?')">
                                                        <i class="fas fa-undo me-1"></i> Kembalikan
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-check text-success"></i> Selesai
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Anda belum memiliki riwayat peminjaman.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $loans->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>