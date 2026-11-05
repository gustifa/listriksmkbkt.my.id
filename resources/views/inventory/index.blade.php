@section('title', 'Manajemen Inventaris Bengkel & Sarpras')
<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Select2 CSS & Select2 Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-alat { background-color: #0d6efd; color: white; }
        .badge-bahan { background-color: #fd7e14; color: white; }
        .stock-warning { color: #dc3545; font-weight: bold; }
        
        /* Penyesuaian tampilan Select2 agar menyatu dengan Bootstrap 5 */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
        }

        /* --- PENYESUAIAN RESPONSIF TABEL UTAMA --- */
        #inventoryTable {
            width: 100% !important;
            table-layout: auto;
        }
        #inventoryTable th, #inventoryTable td {
            white-space: normal !important;
            word-wrap: break-word;
            vertical-align: middle;
            padding: 0.65rem 0.5rem;
        }

        /* Responsive Modal Dialogs & SweetAlert2 */
        .swal2-popup {
            max-width: 95vw !important;
            width: 600px !important;
            border-radius: 12px !important;
        }
        .swal2-html-container {
            word-break: break-word !important;
            white-space: normal !important;
            max-height: 60vh;
            overflow-y: auto;
            text-align: left !important;
            font-size: 0.9rem !important;
        }

        .modal-dialog {
            max-width: 95vw;
            margin: 0.5rem auto;
        }
        @media (min-width: 768px) {
            .modal-dialog.modal-lg {
                max-width: 850px;
            }
        }

        /* Table Responsiveness for Transactions Modal */
        #transactionsModal .table-responsive {
            max-height: 65vh;
            overflow-y: auto;
        }
        #transactionsModal .table {
            table-layout: fixed;
            width: 100%;
            min-width: 600px;
        }
        #transactionsModal td, #transactionsModal th {
            white-space: normal !important;
            word-wrap: break-word;
            vertical-align: middle;
        }
    </style>

<div class="container-fluid bg-white p-3 p-md-4 rounded shadow-sm">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Pengelolaan Inventaris Bengkel & Sarpras</h3>
            <p class="text-muted small mb-0">Kelola Aset Alat & Bahan Habis Pakai (BHP) Standar Disdik / Sekolah Vokasi</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-primary btn-sm" onclick="openImportModal()">
                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Import Excel
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="exportExcel()">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor Excel (.xlsx)
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="openPrintModal()">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak KIR Ruangan
            </button>
            <button class="btn btn-primary btn-sm" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i> Tambah Inventaris
            </button>
        </div>
    </div>

    <!-- Card Filter & Search -->
    <div class="card card-custom mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filter Bengkel / Ruangan</label>
                    <select id="filter_room" class="form-select select2-filter" onchange="loadData()">
                        <option value="">-- Semua Bengkel --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filter Jenis</label>
                    <select id="filter_type" class="form-select select2-filter" onchange="loadData()">
                        <option value="">-- Semua Jenis --</option>
                        <option value="alat">Alat (Aset Tetap)</option>
                        <option value="bahan">Bahan (Habis Pakai)</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Cari Barang / Kode</label>
                    <div class="input-group">
                        <input type="text" id="search_input" class="form-control" placeholder="Cari nama, kode inventaris, atau merk..." onkeyup="loadData()">
                        <button class="btn btn-outline-secondary" type="button" onclick="loadData()"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="inventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 15%;">Kode / ID</th>
                            <th style="width: 22%;">Nama Barang & Merk</th>
                            <th style="width: 18%;">Bengkel / Ruang</th>
                            <th style="width: 10%;">Jenis</th>
                            <th style="width: 15%;">Stok Tersedia</th>
                            <th style="width: 10%;">Kondisi</th>
                            <th style="width: 8%;">Sumber</th>
                            <th class="text-center pe-3" style="width: 2%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryData">
                        <!-- Content Data di-load via AJAX -->
                    </tbody>
                </table>
            </div>
            <!-- Dynamic Pagination -->
            <div class="p-3 d-flex justify-content-between align-items-center" id="paginationContainer"></div>
        </div>
    </div>
</div>

<!-- MODAL FORM (TAMBAH & EDIT) -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Barang Inventaris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="inventoryForm">
                <input type="hidden" id="inventory_id" name="inventory_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Kode Inventaris</label>
                            <div class="input-group">
                                <input type="text" id="code" name="code" class="form-control" placeholder="Contoh: ALT-ITL-2026-0001" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="autoGenerateCode()" title="Generate Kode Otomatis">
                                    <i class="bi bi-magic me-1"></i> Auto
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nama Barang</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Multitester Digital Sanwa" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Bengkel / Ruangan</label>
                            <select id="room_id" name="room_id" class="form-select select2-modal" onchange="autoGenerateCodeIfNew()" required>
                                <option value="">-- Pilih Bengkel --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Jenis Barang</label>
                            <select id="type" name="type" class="form-select select2-modal" onchange="autoGenerateCodeIfNew()" required>
                                <option value="alat">Alat (Pinjam & Kembali)</option>
                                <option value="bahan">Bahan (Habis Pakai)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Kategori</label>
                            <input type="text" id="category" name="category" class="form-control" placeholder="Elektronika, Komputer..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Merk / Tipe / Spesifikasi</label>
                            <input type="text" id="brand_model" name="brand_model" class="form-control" placeholder="Contoh: CD800a / 220V">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Jumlah Stok</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Stok Minimal (Alert)</label>
                            <input type="number" id="min_quantity" name="min_quantity" class="form-control" min="0" value="5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Satuan</label>
                            <input type="text" id="unit" name="unit" class="form-control" placeholder="pcs, meter, roll, set..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Kondisi Barang</label>
                            <select id="condition" name="condition" class="form-select select2-modal" required>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Sumber Dana</label>
                            <select id="funding_source" name="funding_source" class="form-select select2-modal" required>
                                <option value="BOS">BOS</option>
                                <option value="DAK">DAK</option>
                                <option value="APBD_SUMBAR">APBD Sumbar</option>
                                <option value="APBN">APBN</option>
                                <option value="HIBAH">Hibah / Komite</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun Pengadaan</label>
                            <input type="number" id="acquisition_year" name="acquisition_year" class="form-control" placeholder="2026">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Keterangan / Catatan Tambahan</label>
                            <input type="text" id="description" name="description" class="form-control" placeholder="Posisi lemari, rak, atau spesifikasi unik">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSave" class="btn btn-primary">Simpan Inventaris</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Import Data Inventaris via Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Gunakan format berkas Excel sesuai dengan standar kolom sistem.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Berkas Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" id="file_excel" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="small text-muted">Belum punya formatnya?</span>
                        <a href="{{ route('inventories.download-template') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Unduh Template Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Unggah & Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL HISTORI TRANSAKSI (INVENTORY TRANSACTIONS) -->
<div class="modal fade" id="transactionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="transactionsModalTitle"><i class="bi bi-clock-history me-2"></i>Histori Transaksi Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 16%;">Tanggal</th>
                                <th style="width: 12%;">Tipe</th>
                                <th style="width: 12%;">Jumlah</th>
                                <th style="width: 15%;">Sumber Dana</th>
                                <th style="width: 22%;">Petugas</th>
                                <th class="pe-3" style="width: 23%;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsData">
                            <!-- Data transaksi di-load via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MUTASI / PINDAH RUANGAN -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-arrows-move me-2"></i>Mutasi Barang / Pindah Bengkel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="transferForm">
                <input type="hidden" id="transfer_inventory_id" name="transfer_inventory_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Barang</label>
                        <input type="text" id="transfer_item_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Bengkel Asal</label>
                            <input type="text" id="transfer_source_room" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Stok Saat Ini</label>
                            <input type="text" id="transfer_max_qty" class="form-control bg-light" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Bengkel / Ruangan Tujuan</label>
                        <select id="target_room_id" name="target_room_id" class="form-select select2-transfer-modal" required>
                            <option value="">-- Pilih Bengkel Tujuan --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jumlah Stok Dipindahkan</label>
                        <input type="number" id="transfer_quantity" name="quantity" class="form-control" min="1" required>
                        <div class="form-text text-muted">Bisa memindahkan sebagian atau seluruh stok.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan / Catatan Pemindahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Penataan ulang meja lab, hibah antar bengkel, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-send me-1"></i> Proses Mutasi Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let modal;
let importModal;
let transactionsModal;
let transferModal;

$(document).ready(function() {
    modal = new bootstrap.Modal(document.getElementById('inventoryModal'));
    importModal = new bootstrap.Modal(document.getElementById('importModal'));
    transactionsModal = new bootstrap.Modal(document.getElementById('transactionsModal'));
    transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
    
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('.select2-filter').select2({ theme: 'bootstrap-5', width: '100%' });
    $('.select2-modal').select2({ theme: 'bootstrap-5', dropdownParent: $('#inventoryModal'), width: '100%' });
    $('.select2-transfer-modal').select2({ theme: 'bootstrap-5', dropdownParent: $('#transferModal'), width: '100%' });

    loadData();

    $('#inventoryForm').on('submit', function(e) {
        e.preventDefault();
        
        let id = $('#inventory_id').val();
        let url = id ? `/inventories/${id}` : "{{ route('inventories.store') }}";
        let method = id ? "PUT" : "POST";

        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: url,
            type: method,
            data: $('#inventoryForm').serialize(),
            dataType: "json",
            success: function(response) {
                Swal.close();
                if(response.success) {
                    modal.hide();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, timer: 1800, showConfirmButton: false });
                    loadData();
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan pada sistem.';
                Swal.fire('Gagal!', errorMsg, 'error');
            }
        });
    });

    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        Swal.fire({
            title: 'Mengunggah & Mengimpor Data...',
            text: 'Harap tunggu hingga proses selesai.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('inventories.import-excel') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(response) {
                Swal.close();
                if (response.success) {
                    importModal.hide();
                    $('#importForm')[0].reset();
                    Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: response.message });
                    loadData();
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = xhr.responseJSON?.message || 'Gagal mengimpor file Excel.';
                Swal.fire('Gagal Import!', errorMsg, 'error');
            }
        });
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#transfer_inventory_id').val();

        Swal.fire({ title: 'Memproses Mutasi Barang...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: `/inventories/${id}/transfer`,
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                Swal.close();
                if (response.success) {
                    transferModal.hide();
                    Swal.fire({ icon: 'success', title: 'Mutasi Berhasil!', text: response.message });
                    loadData();
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = xhr.responseJSON?.message || 'Gagal memindahkan barang.';
                Swal.fire('Gagal Mutasi!', errorMsg, 'error');
            }
        });
    });
});

function loadData(page = 1) {
    let roomId = $('#filter_room').val();
    let type = $('#filter_type').val();
    let search = $('#search_input').val();

    $.ajax({
        url: `{{ route('inventories.data') }}?page=${page}&room_id=${roomId}&type=${type}&search=${search}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let tbody = $('#inventoryData');
            tbody.empty();

            if (response.data.length === 0) {
                tbody.append('<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data inventaris ditemukan.</td></tr>');
                $('#paginationContainer').empty();
                return;
            }

            response.data.forEach(item => {
                let badgeType = item.type === 'bahan' ? '<span class="badge badge-bahan">BAHAN</span>' : '<span class="badge badge-alat">ALAT</span>';
                let isWarning = (item.type === 'bahan' && item.quantity <= item.min_quantity);
                let stockDisplay = isWarning 
                    ? `<span class="stock-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>${item.quantity} ${item.unit} (Hampir Habis)</span>` 
                    : `${item.quantity} ${item.unit}`;

                // DROPDOWN MENU AKSI (SATU BUTTON DROPDOWN)
                let row = `
                    <tr>
                        <td class="ps-3"><span class="fw-bold text-dark">${item.code}</span></td>
                        <td>
                            <div class="fw-bold">${item.name}</div>
                            <small class="text-muted">${item.brand_model ?? '-'}</small>
                        </td>
                        <td>${item.room ? item.room.name : '-'}</td>
                        <td>${badgeType}</td>
                        <td>${stockDisplay}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">${item.condition.replace('_', ' ')}</span></td>
                        <td><span class="badge bg-secondary">${item.funding_source}</span></td>
                        <td class="text-center pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-menu-item dropdown-item" href="javascript:void(0)" onclick="openTransferModal('${item.id}', '${item.name}', '${item.room ? item.room.name : '-'}', ${item.quantity}, '${item.unit}')">
                                            <i class="bi bi-arrows-move me-2 text-warning"></i> Mutasi Barang
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-menu-item dropdown-item" href="javascript:void(0)" onclick="showTransactions('${item.id}')">
                                            <i class="bi bi-clock-history me-2 text-info"></i> Histori Stok
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-menu-item dropdown-item" href="javascript:void(0)" onclick="editModal('${item.id}')">
                                            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Barang
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-menu-item dropdown-item text-danger" href="javascript:void(0)" onclick="deleteItem('${item.id}', '${item.name}')">
                                            <i class="bi bi-trash me-2 text-danger"></i> Hapus Barang
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            renderPagination(response);
        }
    });
}

function autoGenerateCode() {
    let roomId = $('#room_id').val();
    let type = $('#type').val();

    $.ajax({
        url: `{{ route('inventories.generate-code') }}?room_id=${roomId}&type=${type}`,
        type: 'GET',
        success: function(res) {
            if (res.success) {
                $('#code').val(res.code);
            }
        }
    });
}

function autoGenerateCodeIfNew() {
    if (!$('#inventory_id').val()) {
        autoGenerateCode();
    }
}

function openTransferModal(id, name, roomName, quantity, unit) {
    $('#transferForm')[0].reset();
    $('#transfer_inventory_id').val(id);
    $('#transfer_item_name').val(name);
    $('#transfer_source_room').val(roomName);
    $('#transfer_max_qty').val(`${quantity} ${unit}`);
    $('#transfer_quantity').val(quantity).attr('max', quantity);

    $('.select2-transfer-modal').val('').trigger('change');
    transferModal.show();
}

function openAddModal() {
    $('#inventoryForm')[0].reset();
    $('#inventory_id').val('');
    $('.select2-modal').val('').trigger('change');
    $('#modalTitle').text('Tambah Barang Inventaris Baru');
    
    autoGenerateCode();
    modal.show();
}

function openImportModal() {
    $('#importForm')[0].reset();
    importModal.show();
}

function editModal(id) {
    $.ajax({
        url: `/inventories/${id}`,
        type: 'GET',
        success: function(res) {
            if(res.success) {
                let d = res.data;
                $('#inventory_id').val(d.id);
                $('#code').val(d.code);
                $('#name').val(d.name);
                $('#category').val(d.category);
                $('#brand_model').val(d.brand_model);
                $('#quantity').val(d.quantity);
                $('#min_quantity').val(d.min_quantity);
                $('#unit').val(d.unit);
                $('#acquisition_year').val(d.acquisition_year);
                $('#description').val(d.description);

                $('#room_id').val(d.room_id).trigger('change');
                $('#type').val(d.type).trigger('change');
                $('#condition').val(d.condition).trigger('change');
                $('#funding_source').val(d.funding_source).trigger('change');

                $('#modalTitle').text('Edit Data Inventaris');
                modal.show();
            }
        }
    });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Barang?',
        text: `Apakah Anda yakin ingin menghapus '${name}' dari inventaris?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/inventories/${id}`,
                type: 'DELETE',
                success: function(res) {
                    if(res.success) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        loadData();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error saat menghapus data.', 'error');
                }
            });
        }
    });
}

function showTransactions(id) {
    Swal.fire({
        title: 'Memuat Histori Transaksi...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: `/inventories/${id}/transactions`,
        type: 'GET',
        success: function(res) {
            Swal.close();
            if (res.success) {
                $('#transactionsModalTitle').html(`<i class="bi bi-clock-history me-2"></i>Histori Transaksi: ${res.item_name}`);
                let tbody = $('#transactionsData');
                tbody.empty();

                if (res.transactions.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat transaksi untuk barang ini.</td></tr>');
                } else {
                    res.transactions.forEach(t => {
                        let date = t.date ? t.date : new Date(t.created_at).toLocaleDateString('id-ID');
                        let badgeType = (t.type === 'in' || t.type === 'masuk') 
                            ? `<span class="badge bg-success">+ MASUK</span>` 
                            : `<span class="badge bg-danger">- KELUAR</span>`;
                        let userText = t.user ? t.user.name : (t.receiver ?? 'Sistem/Admin');

                        let row = `
                            <tr>
                                <td class="ps-3">${date}</td>
                                <td>${badgeType}</td>
                                <td class="fw-bold ${t.type === 'in' || t.type === 'masuk' ? 'text-success' : 'text-danger'}">
                                    ${t.type === 'in' || t.type === 'masuk' ? '+' : '-'}${t.quantity}
                                </td>
                                <td><span class="badge bg-secondary">${t.funding_source ?? '-'}</span></td>
                                <td>${userText}</td>
                                <td class="pe-3">${t.notes ?? '-'}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
                transactionsModal.show();
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Gagal!', 'Tidak dapat memuat data transaksi.', 'error');
        }
    });
}

function openPrintModal() {
    let roomId = $('#filter_room').val();

    if (!roomId) {
        Swal.fire({
            title: 'Pilih Ruangan Terlebih Dahulu!',
            text: 'Silakan pilih Bengkel/Ruangan pada dropdown filter sebelum mencetak KIR.',
            icon: 'warning',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    let roomName = $('#filter_room option:selected').text();

    Swal.fire({
        title: 'Cetak KIR Ruangan?',
        text: `Apakah Anda ingin mencetak dokumen Laporan KIR untuk: "${roomName}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Cetak PDF',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/inventories/print-by-room/${roomId}`, '_blank');
        }
    });
}

function exportExcel() {
    let roomId = $('#filter_room').val();
    let url = "{{ route('inventories.export-excel') }}";
    if (roomId) {
        url += "?room_id=" + encodeURIComponent(roomId);
    }
    window.location.href = url;
}

function renderPagination(data) {
    let container = $('#paginationContainer');
    container.empty();
    
    let info = `<span class="small text-muted">Menampilkan ${data.from ?? 0} - ${data.to ?? 0} dari total ${data.total} data</span>`;
    
    let links = '<ul class="pagination pagination-sm mb-0">';
    data.links.forEach(link => {
        if (link.url) {
            let active = link.active ? 'active' : '';
            let pageNum = link.url.split('page=')[1];
            links += `<li class="page-item ${active}"><a class="page-link" href="javascript:void(0)" onclick="loadData(${pageNum})">${link.label}</a></li>`;
        }
    });
    links += '</ul>';

    container.append(info + links);
}
</script>
</x-app-layout>