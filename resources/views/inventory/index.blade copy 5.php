@section('title', 'Inventaris Bengkel')

<x-app-layout>
    <div class="page-content">
        <div class="row">
            <div class="col-12">

                <!-- HEADER & FILTER -->
                <div class="mb-4 shadow-sm card">
                    <div class="card-body">
                        <div class="d-md-flex justify-content-between align-items-center">
                            <h5 class="mb-3 mb-md-0 fw-bold text-primary">
                                <i class="fas fa-tools me-2"></i> Inventaris Alat & Barang Bengkel
                            </h5>

                            <div class="gap-2 d-flex">
                                <!-- TOMBOL DATA PEMINJAMAN -->
                                <button class="text-white btn btn-info" onclick="showLoansModal()">
                                    <i class="fas fa-list-alt me-1"></i> Data Peminjaman
                                </button>

                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                    <i class="fas fa-plus me-1"></i> Tambah Barang
                                </button>
                            </div>
                        </div>
                        <hr>
                        <form action="{{ route('inventory.index') }}" method="GET" class="row g-2">
                            <div class="col-md-4">
                                <select name="room_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Ruangan / Bengkel --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Kategori --</option>
                                    <option value="alat" {{ request('category') == 'alat' ? 'selected' : '' }}>Alat (Aset Tetap)</option>
                                    <option value="bahan" {{ request('category') == 'bahan' ? 'selected' : '' }}>Bahan (Habis Pakai)</option>
                                    <option value="mesin" {{ request('category') == 'mesin' ? 'selected' : '' }}>Mesin</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('inventory.index') }}" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="shadow card">
                    <div class="p-0 card-body">
                        @if(session('success'))
                            <div class="m-3 alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="m-3 alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table mb-0 align-middle table-hover table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Lokasi (Bengkel)</th>
                                        <th class="text-center">Jml</th>
                                        <th class="text-center">Kondisi</th>
                                        <th class="text-center" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventories as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $inventories->firstItem() + $index }}</td>
                                            <td>
                                                <span class="badge bg-secondary font-monospace">{{ $item->code }}</span><br>
                                                <small class="text-muted">{{ ucfirst($item->category) }}</small>
                                            </td>
                                            <td class="fw-bold">
                                                {{ $item->name }}
                                                <div class="small text-muted fw-normal">{{ $item->brand ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ $item->room->name ?? 'Tidak diketahui' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-dark">{{ $item->quantity }}</span>
                                                <small class="text-muted">{{ $item->unit }}</small>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $condClass = match($item->condition) {
                                                        'baik' => 'bg-success',
                                                        'rusak_ringan' => 'bg-warning text-dark',
                                                        'rusak_berat' => 'bg-danger',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $condClass }}">
                                                    {{ str_replace('_', ' ', ucfirst($item->condition)) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <!-- Tombol Pinjam -->
                                                    <button class="text-white btn btn-sm btn-info" title="Pinjam Barang"
                                                        onclick="borrowInventory('{{ $item->id }}', '{{ $item->name }}', '{{ $item->quantity }}')">
                                                        <i class="fas fa-hand-holding"></i>
                                                    </button>


                                                    <button class="btn btn-sm btn-warning" title="Edit" onclick="editInventory({{ json_encode($item) }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <form action="{{ route('inventory.barcode', $item->id) }}" method="GET" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-primary" title="qrcode"><i class="fas fa-qrcode"></i></button>
                                                    </form>
                                                    <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-5 text-center text-muted">
                                                <i class="mb-3 fas fa-box-open fa-3x"></i><br>
                                                Belum ada data inventaris.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $inventories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH/EDIT BARANG -->
    <div class="modal fade" id="addInventoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="text-white modal-header bg-primary">
                    <h5 class="modal-title" id="modalTitle">Tambah Inventaris Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory.store') }}" method="POST" id="inventoryForm">
                    @csrf
                    <div id="methodField"></div> <!-- Untuk Method PUT saat Edit -->

                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Barang</label>
                                <input type="text" name="name" id="name" class="form-control" required placeholder="Contoh: Tang Kombinasi">
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Kode Barang</label>
                                <input type="text" name="code" id="code" class="form-control" required placeholder="Auto/Manual">
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Merk/Brand</label>
                                <input type="text" name="brand" id="brand" class="form-control" placeholder="Opsional">
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Lokasi Ruangan/Bengkel</label>
                                <select name="room_id" id="room_id" class="form-select" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="alat">Alat</option>
                                    <option value="bahan">Bahan</option>
                                    <option value="mesin">Mesin</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label fw-bold">Kondisi</label>
                                <select name="condition" id="condition" class="form-select" required>
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Jumlah</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" required min="0">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Satuan</label>
                                <input type="text" name="unit" id="unit" class="form-control" placeholder="Pcs, Unit, Set" value="pcs">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label fw-bold">Tanggal Pengadaan</label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL FORM PINJAM -->
    <div class="modal fade" id="borrowModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="text-white modal-header bg-info">
                    <h5 class="modal-title"><i class="fas fa-hand-holding me-2"></i> Pinjam Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory-loan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="inventory_id" id="borrow_inventory_id">

                    <div class="modal-body">
                        <div class="border alert alert-light">
                            <strong>Barang:</strong> <span id="borrow_item_name"></span><br>
                            <small class="text-muted">Stok Tersedia: <span id="borrow_max_qty"></span></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_name" class="form-control" placeholder="Nama Siswa / Guru" required>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Jumlah Pinjam <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Tanggal Pinjam</label>
                                <input type="datetime-local" name="loan_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Keperluan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="text-white btn btn-info">Simpan Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DAFTAR PEMINJAMAN AKTIF & RIWAYAT -->
    <div class="modal fade" id="loansModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="text-white modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-list-alt me-2"></i> Riwayat Peminjaman Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-0 modal-body">
                    <div id="loansLoading" class="p-4 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data peminjaman...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Nama Peminjam</th>
                                    <th>Barang</th>
                                    <th class="text-center">Jml</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Status / Catatan</th>
                                    <th class="text-center pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="loansTableBody">
                                <!-- Data akan diisi via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function editInventory(item) {
            // Ubah Title & Action URL
            document.getElementById('modalTitle').innerText = 'Edit Inventaris';
            document.getElementById('inventoryForm').action = `/inventory/${item.id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('btnSubmit').innerText = 'Update';

            // Isi Form
            document.getElementById('name').value = item.name;
            document.getElementById('code').value = item.code;
            document.getElementById('brand').value = item.brand;
            document.getElementById('room_id').value = item.room_id;
            document.getElementById('category').value = item.category;
            document.getElementById('condition').value = item.condition;
            document.getElementById('quantity').value = item.quantity;
            document.getElementById('unit').value = item.unit;
            document.getElementById('purchase_date').value = item.purchase_date;
            document.getElementById('description').value = item.description;

            // Buka Modal
            var myModal = new bootstrap.Modal(document.getElementById('addInventoryModal'));
            myModal.show();
        }

        function borrowInventory(id, name, maxQty) {
            document.getElementById('borrow_inventory_id').value = id;
            document.getElementById('borrow_item_name').innerText = name;
            document.getElementById('borrow_max_qty').innerText = maxQty;

            var myModal = new bootstrap.Modal(document.getElementById('borrowModal'));
            myModal.show();
        }

        // --- FUNGSI LOAD DATA PEMINJAMAN VIA AJAX ---
        function showLoansModal() {
            var myModal = new bootstrap.Modal(document.getElementById('loansModal'));
            myModal.show();
            loadLoans();
        }

        function loadLoans() {
            const tableBody = document.getElementById('loansTableBody');
            const loading = document.getElementById('loansLoading');

            tableBody.innerHTML = '';
            loading.style.display = 'block';

            fetch('/inventory-loan/active')
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    if (data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-muted">Belum ada riwayat peminjaman.</td></tr>';
                        return;
                    }

                    data.forEach(loan => {
                        // Tentukan Status dan Tombol Aksi
                        let statusBadge = '';
                        let actionButtons = '';

                        // LOGIKA TOMBOL CETAK BUKTI
                        let printTitle = 'Cetak Bukti Peminjaman';
                        let printBtnClass = 'btn-secondary';

                        if (loan.status === 'kembali') {
                            printTitle = 'Cetak Bukti Pengembalian';
                            printBtnClass = 'btn-primary'; // Warna biru jika sudah kembali
                        }

                        // Link Cetak Bukti
                        const printBtn = `
                            <a href="/inventory-loan/${loan.id}/print" target="_blank" class="text-white btn btn-sm ${printBtnClass} me-1" title="${printTitle}">
                                <i class="fas fa-print"></i>
                            </a>
                        `;

                        if (loan.status === 'dipinjam') {
                            statusBadge = '<span class="badge bg-warning text-dark">Dipinjam</span>';
                            // Tombol Kembali
                            actionButtons = `
                                ${printBtn}
                                <button class="text-white btn btn-sm btn-success" onclick="returnItem('${loan.id}', '${loan.borrower_name}')" title="Kembalikan">
                                    <i class="fas fa-undo"></i>
                                </button>
                            `;
                        } else {
                            statusBadge = '<span class="badge bg-success">Kembali</span>';
                            actionButtons = printBtn;
                        }

                        const row = `
                            <tr>
                                <td class="ps-3 fw-bold">
                                    ${loan.borrower_name}<br>
                                    ${statusBadge}
                                </td>
                                <td>${loan.inventory ? loan.inventory.name : 'Item dihapus'}</td>
                                <td class="text-center fw-bold">${loan.quantity}</td>
                                <td>
                                    <small class="d-block text-muted">Pinjam:</small>
                                    ${new Date(loan.loan_date).toLocaleString('id-ID')}
                                    ${loan.return_date ? '<small class="d-block text-success">Kembali: ' + new Date(loan.return_date).toLocaleString('id-ID') + '</small>' : ''}
                                </td>
                                <td><small class="text-muted">${loan.notes || '-'}</small></td>
                                <td class="text-center pe-3">
                                    <div class="btn-group">
                                        ${actionButtons}
                                    </div>
                                </td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    loading.style.display = 'none';
                    tableBody.innerHTML = '<tr><td colspan="6" class="py-3 text-center text-danger">Gagal memuat data. Pastikan route /inventory-loan/active ada.</td></tr>';
                });
        }

        // --- FIX: FUNGSI PENGEMBALIAN BARANG DENGAN TEXTAREA ---
        function returnItem(id, borrowerName) {
            Swal.fire({
                title: 'Konfirmasi Pengembalian',
                html: `Barang dipinjam oleh: <b>${borrowerName}</b><br><br>Masukkan catatan kondisi barang (Opsional):`,
                icon: 'question',
                input: 'textarea', // Gunakan textarea agar bisa input banyak baris
                inputPlaceholder: 'Contoh: Barang kondisi baik / Ada lecet...',
                inputAttributes: {
                    'aria-label': 'Catatan kondisi barang'
                },
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Terima Barang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const notes = result.value;

                    // Loading state
                    Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});

                    fetch(`/inventory-loan/${id}/return`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // PENTING agar dikenali sebagai AJAX
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ notes: notes })
                    })
                    .then(async response => {
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            data = { message: 'Terjadi kesalahan pada respon server.' };
                        }

                        if (response.ok) {
                            Swal.fire('Berhasil!', data.message || 'Barang telah dikembalikan.', 'success');
                            loadLoans(); // Reload tabel modal
                        } else {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
                    });
                }
            });
        }

        // Reset modal saat ditutup (agar kembali ke mode tambah)
        document.getElementById('addInventoryModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('inventoryForm').reset();
            document.getElementById('inventoryForm').action = "{{ route('inventory.store') }}";
            document.getElementById('methodField').innerHTML = '';
            document.getElementById('modalTitle').innerText = 'Tambah Inventaris Baru';
            document.getElementById('btnSubmit').innerText = 'Simpan';
        });
    </script>
    @endpush
</x-app-layout>
