@section('title', 'Form Peminjaman Alat & Bahan')
<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .select2-container--bootstrap-5 .select2-selection { border-radius: 0.375rem; }
        .form-label.required::after { content: " *"; color: #dc3545; }
    </style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-custom">
                <div class="card-header bg-primary text-white py-3 rounded-top">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-box-arrow-right me-2"></i>Form Peminjaman Alat & Bahan Praktikum
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="loanForm">
                        @csrf
                        <div class="row g-3">
                            <!-- Field Selection Bengkel -->
                            <div class="col-12">
                                <label class="form-label required fw-semibold">Bengkel / Ruangan Praktikum</label>
                                @if(isset($activeRoomId) && $activeRoomId)
                                    <input type="hidden" id="room_id" name="room_id" value="{{ $activeRoomId }}">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-primary"><i class="bi bi-geo-alt-fill"></i></span>
                                        <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $activeRoomName }} (Terdeteksi Jam PBM Aktif)" readonly>
                                    </div>
                                    <div class="form-text text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i> Bengkel terkunci otomatis berdasarkan jadwal praktikum Anda saat ini.
                                    </div>
                                @else
                                    <select id="room_id" name="room_id" class="form-select select2" onchange="loadRoomItems()" required>
                                        <option value="">-- Pilih Bengkel / Ruangan --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Pilih ruangan tempat praktikum Anda berlangsung.</div>
                                @endif
                            </div>

                            <!-- Field Selection Barang (Alat/Bahan) -->
                            <div class="col-md-8">
                                <label class="form-label required fw-semibold">Alat / Bahan Praktikum</label>
                                <select id="inventory_id" name="inventory_id" class="form-select select2" onchange="updateStockInfo()" required disabled>
                                    <option value="">-- Pilih Bengkel Terlebih Dahulu --</option>
                                </select>
                            </div>

                            <!-- Field Jumlah Peminjaman -->
                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">Jumlah Pinjam</label>
                                <div class="input-group">
                                    <input type="number" id="amount" name="amount" class="form-control" min="1" value="1" required>
                                    <span class="input-group-text" id="unit_badge">Satuan</span>
                                </div>
                                <div class="form-text text-muted" id="stock_info_text">Maksimal stok tersedia: -</div>
                            </div>

                            <!-- Field Input Manual Barcode (Alternatif Scan/Ketik) -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kode Barang / Barcode (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-barcode-scan"></i></span>
                                    <input type="text" id="barcode" name="barcode" class="form-control" placeholder="Scan barcode barang di sini jika ada..." onchange="selectByBarcode()">
                                </div>
                            </div>

                            <!-- Field Catatan / Keperluan -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan / Peruntukan Job sheet</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Contoh: Praktikum Jobsheet 3 Instalasi Penerangan..."></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                            </a>
                            <button type="submit" id="btnSubmitLoan" class="btn btn-primary px-4">
                                <i class="bi bi-send-fill me-1"></i> Ajukan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let roomItemsData = [];

$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Trigger pemuatan barang jika room_id sudah terisi (otomatis PBM atau dipillih)
    if ($('#room_id').val()) {
        loadRoomItems();
    }

    // Event listener saat user memilih barang dari Select2
    $('#inventory_id').on('change', function() {
        updateStockInfo();
    });

    // Form Submit Handler via AJAX
    $('#loanForm').on('submit', function(e) {
        e.preventDefault();

        let inventoryId = $('#inventory_id').val();
        let barcodeVal = $('#barcode').val().trim();

        // Validasi: harus ada salah satu yang terisi (inventory_id atau barcode)
        if (!inventoryId && !barcodeVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Silakan pilih Alat/Bahan Praktikum terlebih dahulu dari daftar!',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        Swal.fire({
            title: 'Memproses Peminjaman...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('loans.store') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('loans.index') }}";
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memproses peminjaman.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Meminjam',
                    text: errorMsg
                });
            }
        });
    });
});

function loadRoomItems() {
    let roomId = $('#room_id').val();
    let itemSelect = $('#inventory_id');

    if (!roomId) {
        itemSelect.empty().append('<option value="">-- Pilih Bengkel Terlebih Dahulu --</option>').trigger('change').prop('disabled', true);
        return;
    }

    itemSelect.empty().append('<option value="">-- Memuat daftar barang... --</option>').trigger('change').prop('disabled', true);

    $.ajax({
        url: `{{ route('loans.get-items-by-room') }}?room_id=${roomId}`,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            itemSelect.empty();
            if (res.success && res.data.length > 0) {
                roomItemsData = res.data;
                itemSelect.append('<option value="">-- Pilih Alat / Bahan Praktikum --</option>');
                
                res.data.forEach(item => {
                    let badgeType = item.type === 'bahan' ? '[BHP]' : '[ALAT]';
                    let optionText = `${item.code} - ${item.name} ${badgeType} (Tersedia: ${item.quantity} ${item.unit})`;
                    itemSelect.append(`<option value="${item.id}" data-code="${item.code}">${optionText}</option>`);
                });

                itemSelect.prop('disabled', false).trigger('change');
            } else {
                itemSelect.append('<option value="">Tidak ada alat/bahan tersedia di bengkel ini</option>').trigger('change').prop('disabled', true);
            }
        },
        error: function() {
            itemSelect.empty().append('<option value="">Gagal memuat data barang</option>').trigger('change').prop('disabled', true);
        }
    });
}

function updateStockInfo() {
    let selectedId = $('#inventory_id').val();
    let selectedItem = roomItemsData.find(item => item.id == selectedId);

    if (selectedItem) {
        $('#unit_badge').text(selectedItem.unit ?? 'pcs');
        $('#amount').attr('max', selectedItem.quantity);
        $('#stock_info_text').html(`Maksimal stok tersedia: <b class="text-primary">${selectedItem.quantity} ${selectedItem.unit}</b>`);
        
        // Auto-fill input barcode sesuai kode barang terpilih
        $('#barcode').val(selectedItem.code);
    } else {
        $('#unit_badge').text('Satuan');
        $('#amount').removeAttr('max');
        $('#stock_info_text').text('Maksimal stok tersedia: -');
        $('#barcode').val('');
    }
}

function selectByBarcode() {
    let barcodeInput = $('#barcode').val().trim();
    if (!barcodeInput) return;

    let matchedItem = roomItemsData.find(item => item.code.toLowerCase() === barcodeInput.toLowerCase());
    if (matchedItem) {
        $('#inventory_id').val(matchedItem.id).trigger('change');
    }
}
</script>
</x-app-layout>