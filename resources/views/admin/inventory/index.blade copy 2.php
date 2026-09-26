@section('title', 'Inventaris Barang & Stok')

<x-app-layout>
    <div class="py-4 page-content">
        {{-- Header Halaman --}}
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-boxes me-2"></i>Inventaris Barang & Stok
                </h4>
                <p class="mb-0 text-muted small">Manajemen aset dan mutasi stok bengkel TITL</p>
            </div>
            <div class="gap-2 d-flex">
                <a href="{{ route('admin.inventory.history') }}" class="shadow-sm btn btn-outline-secondary">
                    <i class="fas fa-history me-1"></i> Riwayat
                </a>
                <button class="shadow-sm btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-excel me-1"></i> Import
                </button>
                <button class="shadow-sm btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus me-1"></i> Tambah Barang
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 border-0 shadow-sm alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 border-0 shadow-sm alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Gagal!</strong> Periksa kembali file/inputan Anda.
                <ul class="mt-1 mb-0 small">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tabel Utama --}}
        <div class="border-0 shadow-lg card">
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 text-uppercase small fw-bold" style="width: 15%">Kode</th>
                                <th class="py-3 text-uppercase small fw-bold" style="width: 35%">Nama Barang</th>
                                <th class="py-3 text-center text-uppercase small fw-bold" style="width: 25%">Sisa Stok</th>
                                <th class="py-3 text-center pe-4 text-uppercase small fw-bold" style="width: 25%">Aksi (In/Out)</th>
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
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $item->description ?? '-' }}</small>
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
                                <tr>
                                    <td colspan="4" class="py-5 text-center text-muted">
                                        <i class="mb-3 fas fa-box-open fa-3x d-block text-light"></i>
                                        Belum ada data barang di inventaris.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($items->hasPages())
                <div class="py-3 bg-white border-0 card-footer">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- DataLists untuk Autocomplete --}}
    <datalist id="unitList">
        <option value="Pcs"><option value="Meter"><option value="Roll"><option value="Box"><option value="Kg">
    </datalist>
    <datalist id="fundingSources">
        <option value="Dana BOS"><option value="Dana Komite"><option value="Bantuan Pemerintah"><option value="Hibah">
    </datalist>

    {{-- MODALS SECTION --}}

    {{-- 1. Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="border-0 shadow-lg modal-content">
                <form action="{{ route('admin.inventory.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="text-white modal-header bg-success">
                        <h5 class="modal-title fw-bold"><i class="fas fa-file-excel me-2"></i>Import Data</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="border-0 shadow-sm alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i> Pastikan file Excel sesuai dengan template sistem.
                            <a href="{{ route('admin.inventory.template') }}" class="mt-1 fw-bold d-block text-decoration-none">Unduh Template Disini</a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">PILIH FILE EXCEL/CSV</label>
                            <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                        </div>
                    </div>
                    <div class="border-0 modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 btn btn-success">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Modal Tambah Barang --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="border-0 shadow-lg modal-content">
                <form action="{{ route('admin.inventory.item.store') }}" method="POST">
                    @csrf
                    <div class="text-white modal-header bg-primary">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Master Barang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">KODE BARANG</label>
                                <input type="text" name="code" class="form-control" required placeholder="Cth: TITL-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">SATUAN (UNIT)</label>
                                <input type="text" name="unit" list="unitList" class="form-control" required placeholder="Pcs/Meter/Pek">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">NAMA BARANG</label>
                                <input type="text" name="name" class="form-control" required placeholder="Cth: Kabel NYM 2x1.5mm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">STOK AWAL</label>
                                <input type="number" name="initial_stock" value="0" min="0" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">SUMBER DANA</label>
                                <input type="text" name="funding_source" list="fundingSources" class="form-control" placeholder="Cth: Dana BOS 2026">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">KETERANGAN / DESKRIPSI</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Detail lokasi rak atau spesifikasi..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="border-0 modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 btn btn-primary">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. Modal Transaksi --}}
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="border-0 shadow-lg modal-content">
                <form action="{{ route('admin.inventory.transaction.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" id="transItemId">
                    <input type="hidden" name="type" id="transType">

                    <div class="modal-header" id="transHeader">
                        <h5 class="text-white modal-title fw-bold" id="transModalTitle">Transaksi Barang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">BARANG TERPILIH</label>
                            <input type="text" id="transItemName" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="mb-3 row g-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">JUMLAH (<span id="transItemUnit">Qty</span>)</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">TANGGAL</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                            </div>
                        </div>

                        <div id="inContainer" style="display: none;">
                            <div class="p-3 mb-3 border rounded bg-success-subtle border-success-subtle">
                                <label class="form-label fw-bold small text-success">SUMBER DANA / TAHUN</label>
                                <input type="text" name="funding_source" id="transFunding" list="fundingSources" class="form-control border-success">
                            </div>
                        </div>

                        <div id="outContainer" style="display: none;">
                            <div class="p-3 mb-3 border rounded bg-danger-subtle border-danger-subtle">
                                <label class="form-label fw-bold small text-danger">NAMA PENERIMA / PENGAMBIL</label>
                                <input type="text" name="receiver" id="transReceiver" class="form-control border-danger" placeholder="Nama Guru / Siswa">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted">CATATAN TAMBAHAN</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="border-0 modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="transSubmitBtn" class="px-4 btn">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Gunakan Bootstrap Modal API agar konsisten dengan template lain
        function openTransactionModal(itemId, itemName, itemUnit, type) {
            document.getElementById('transItemId').value = itemId;
            document.getElementById('transItemName').value = itemName + ' (' + itemUnit + ')';
            document.getElementById('transItemUnit').innerText = itemUnit;
            document.getElementById('transType').value = type;

            const header = document.getElementById('transHeader');
            const title = document.getElementById('transModalTitle');
            const btn = document.getElementById('transSubmitBtn');
            const inDiv = document.getElementById('inContainer');
            const outDiv = document.getElementById('outContainer');

            if(type === 'in') {
                header.className = 'modal-header bg-success text-white';
                title.innerHTML = '<i class="fas fa-arrow-circle-down me-2"></i>Barang Masuk (+)';
                btn.className = 'btn btn-success px-4';
                inDiv.style.display = 'block';
                outDiv.style.display = 'none';
                document.getElementById('transFunding').required = true;
                document.getElementById('transReceiver').required = false;
            } else {
                header.className = 'modal-header bg-danger text-white';
                title.innerHTML = '<i class="fas fa-arrow-circle-up me-2"></i>Barang Keluar (-)';
                btn.className = 'btn btn-danger px-4';
                inDiv.style.display = 'none';
                outDiv.style.display = 'block';
                document.getElementById('transFunding').required = false;
                document.getElementById('transReceiver').required = true;
            }

            var modal = new bootstrap.Modal(document.getElementById('transactionModal'));
            modal.show();
        }
    </script>
    @endpush
</x-app-layout>
