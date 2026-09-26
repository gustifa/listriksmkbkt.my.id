@section('title', 'Riwayat Transaksi & Mutasi Barang')

<x-app-layout>
    <div class="py-4 page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-history me-2"></i>Riwayat Transaksi & Mutasi Barang
                </h4>
                <p class="mb-0 text-muted small">Catatan keluar masuk barang bengkel TITL</p>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="shadow-sm btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Master Barang
            </a>
        </div>

        <div class="border-0 shadow-lg card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-uppercase small fw-bold" style="width: 150px;">Tanggal</th>
                                <th class="py-3 text-uppercase small fw-bold">Barang</th>
                                <th class="py-3 text-center text-uppercase small fw-bold">Status</th>
                                <th class="py-3 text-center text-uppercase small fw-bold">Jumlah</th>
                                <th class="py-3 text-uppercase small fw-bold">Detail Mutasi</th>
                                <th class="py-3 pe-4 text-uppercase small fw-bold">Pencatat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($transactions as $trx)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }} WIB</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $trx->item->name }}</div>
                                        <span class="border badge bg-light text-dark" style="font-size: 0.7rem;">
                                            <i class="fas fa-tag me-1 text-muted"></i>{{ $trx->item->code }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($trx->type == 'in')
                                            <span class="px-3 py-2 badge bg-success-subtle text-success rounded-pill">
                                                <i class="fas fa-arrow-down me-1"></i> Masuk
                                            </span>
                                        @else
                                            <span class="px-3 py-2 badge bg-danger-subtle text-danger rounded-pill">
                                                <i class="fas fa-arrow-up me-1"></i> Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold fs-6 {{ $trx->type == 'in' ? 'text-success' : 'text-danger' }}">
                                            {{ $trx->type == 'in' ? '+' : '-' }}{{ $trx->quantity }}
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $trx->item->unit ?? 'Pcs' }}</small>
                                    </td>
                                    <td>
                                        @if($trx->type == 'in')
                                            <div class="small">
                                                <span class="text-muted">Sumber:</span> <strong>{{ $trx->funding_source ?? '-' }}</strong><br>
                                                <span class="text-muted">Tahun:</span> <strong>{{ $trx->year ?? '-' }}</strong>
                                            </div>
                                        @else
                                            <div class="small">
                                                <span class="text-muted">Pengambil:</span> <strong class="text-primary">{{ $trx->receiver ?? '-' }}</strong>
                                            </div>
                                        @endif

                                        @if($trx->notes)
                                            <div class="p-2 mt-1 italic rounded bg-light border-start border-3 border-secondary small">
                                                <i class="fas fa-info-circle me-1"></i> "{{ $trx->notes }}"
                                            </div>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex align-items-center">
                                            <div class="text-white bg-secondary rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 25px; height: 25px; font-size: 0.7rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span class="small fw-medium">{{ $trx->user->name ?? 'Sistem' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-5 text-center">
                                        <i class="mb-3 fas fa-history fa-3x text-light d-block"></i>
                                        <p class="mb-0 text-muted">Belum ada riwayat transaksi barang.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="py-3 bg-white border-0 card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $transactions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
