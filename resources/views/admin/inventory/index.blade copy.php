@section('title', 'Inventaris Barang & Stok')

<x-app-layout>
    <div class="py-4 page-content">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-boxes me-2"></i>Inventaris Barang & Stok
                </h4>
                <p class="mb-0 text-muted small">Manajemen aset dan stok barang bengkel TITL</p>
            </div>
            <div class="gap-2 d-flex">
                <a href="{{ route('admin.inventory.history') }}" class="shadow-sm btn btn-outline-secondary">
                    <i class="fas fa-history me-1"></i> Riwayat
                </a>
                <button class="shadow-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus me-1"></i> Tambah Barang
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="border-0 shadow-sm alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tabel Master Barang --}}
        <div class="border-0 shadow-lg card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-uppercase small fw-bold">Kode</th>
                                <th class="py-3 text-uppercase small fw-bold">Nama Barang</th>
                                <th class="py-3 text-center text-uppercase small fw-bold">Sisa Stok</th>
                                <th class="py-3 text-center pe-4 text-uppercase small fw-bold">Aksi (In/Out)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="border badge bg-light text-dark fw-medium">{{ $item->code }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->name }}</div>
                                        <small class="text-muted">{{ $item->description ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="px-3 py-2 badge rounded-pill {{ $item->stock > 5 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                            <strong class="fs-6">{{ $item->stock }}</strong> {{ $item->unit }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="shadow-sm btn-group">
                                            <button onclick="openTransactionModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->unit }}', 'in')"
                                                class="px-3 btn btn-sm btn-success">
                                                <i class="fas fa-arrow-down me-1"></i> Masuk
                                            </button>
                                            <button onclick="openTransactionModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->unit }}', 'out')"
                                                class="px-3 btn btn-sm btn-danger" {{ $item->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-up me-1"></i> Keluar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-5 text-center text-muted">Belum ada data barang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Master Barang --}}
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="border-0 shadow-lg modal-content">
                <form action="{{ route('admin.inventory.item.store') }}" method="POST">
                    @csrf
                    <div class="text-white modal-header bg-primary">
                        <h5 class="mb-0 modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Barang Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Kode Barang</label>
                                <input type="text" name="code" value="{{ $autoCode }}" readonly class="form-control bg-light fw-bold text-primary">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Satuan (Unit)</label>
                                <input type="text" name="unit" class="form-control" required placeholder="Pcs/Meter">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Nama Barang</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Stok Awal</label>
                                <input type="number" name="initial_stock" value="0" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Tahun Perolehan</label>
                                <input type="number" name="year" value="{{ date('Y') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Keterangan</label>
                                <textarea name="description" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 btn btn-primary">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Transaksi Masuk/Keluar --}}
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="border-0 shadow-lg modal-content">
                <form action="{{ route('admin.inventory.transaction.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" id="transItemId">
                    <input type="hidden" name="type" id="transType">

                    <div id="transHeader" class="text-white modal-header">
                        <h5 class="mb-0 modal-title fw-bold" id="transModalTitle">Transaksi Barang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nama Barang</label>
                            <input type="text" id="transItemName" class="form-control bg-light fw-bold text-dark" readonly>
                        </div>
                        <div class="mb-3 row g-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Jumlah (<span id="transItemUnitDisplay">Qty</span>)</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Tanggal</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                            </div>
                        </div>
                        <div id="inFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase text-success">Sumber Dana</label>
                                <input type="text" name="funding_source" class="form-control border-success">
                            </div>
                        </div>
                        <div id="outFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase text-danger">Nama Penerima/Pengambil</label>
                                <input type="text" name="receiver" class="form-control border-danger">
                            </div>
                        </div>
                        <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Tahun Perolehan / Keluar</label>
                                <input type="number" name="year" value="{{ date('Y') }}" class="form-control">
                            </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted text-uppercase">Catatan</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="transSubmitBtn" class="px-4 btn">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openTransactionModal(itemId, itemName, itemUnit, type) {
            document.getElementById('transItemId').value = itemId;
            document.getElementById('transItemName').value = itemName;
            document.getElementById('transItemUnitDisplay').innerText = itemUnit;
            document.getElementById('transType').value = type;

            const header = document.getElementById('transHeader');
            const title = document.getElementById('transModalTitle');
            const btn = document.getElementById('transSubmitBtn');
            const inFields = document.getElementById('inFields');
            const outFields = document.getElementById('outFields');

            if(type === 'in') {
                header.className = 'modal-header bg-success text-white';
                title.innerHTML = '<i class="fas fa-arrow-circle-down me-2"></i>Barang Masuk';
                btn.className = 'btn btn-success px-4';
                inFields.style.display = 'block';
                outFields.style.display = 'none';
            } else {
                header.className = 'modal-header bg-danger text-white';
                title.innerHTML = '<i class="fas fa-arrow-circle-up me-2"></i>Barang Keluar';
                btn.className = 'btn btn-danger px-4';
                inFields.style.display = 'none';
                outFields.style.display = 'block';
            }

            var myModal = new bootstrap.Modal(document.getElementById('transactionModal'));
            myModal.show();
        }
    </script>
    @endpush
</x-app-layout>
