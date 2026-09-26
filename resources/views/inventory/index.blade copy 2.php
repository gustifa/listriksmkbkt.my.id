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

    <!-- MODAL PEMINJAMAN (BARU) -->
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

    @push('scripts')
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
