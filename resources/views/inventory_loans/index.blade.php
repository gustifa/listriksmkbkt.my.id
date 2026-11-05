<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peminjaman Multi-Item Praktikum</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f2f5; padding: 15px; }

        .loan-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 0 auto;
        }

        /* Responsif HP & Desktop */
        @media screen and (max-width: 767px) {
            .loan-container { width: 100%; padding: 15px; }
            .add-box { display: block; }
            .add-box > div { margin-bottom: 10px; width: 100%; }
        }

        @media screen and (min-width: 768px) {
            .loan-container { max-width: 800px; }
            .add-box { display: flex; gap: 10px; align-items: flex-end; }
            .add-box .flex-grow { flex: 1; }
            .add-box .qty-width { width: 120px; }
        }

        .form-title { text-align: center; margin-bottom: 20px; color: #1a202c; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #4a5568; font-size: 14px; }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-add {
            background-color: #38a169;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-add:disabled { background-color: #a0aec0; cursor: not-allowed; }

        /* Tabel Keranjang */
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #edf2f7; color: #2d3748; }

        .btn-delete { background: #e53e3e; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; }

        .btn-submit {
            width: 100%;
            background-color: #3182ce;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="loan-container">
    <h2 class="form-title">Form Peminjaman Multi-Item Praktikum</h2>

    <form id="loanBatchForm">
        <!-- Input Siswa -->
        <div class="form-group">
            <label for="student_id">Pilih Siswa</label>
            <select id="student_id" name="student_id" class="form-control" required>
                <option value="">-- Pilih Siswa --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis ?? 'Siswa' }})</option>
                @endforeach
            </select>
        </div>

        <!-- Input Pilih Bengkel -->
        <div class="form-group">
            <label for="room_id">Pilih Bengkel / Laboratorium Praktikum</label>
            <select id="room_id" name="room_id" class="form-control" required>
                <option value="">-- Pilih Bengkel --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">
                        {{ $room->name }} {{ $room->location ? '('.$room->location.')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Box Pilih & Tambah Barang ke Tabel (Enabled setelah Bengkel Dipilih) -->
        <div class="form-group" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px dashed #cbd5e0;">
            <label style="color: #2b6cb0;">+ Tambahkan Barang ke Daftar</label>
            <div class="add-box">
                <div class="flex-grow">
                    <select id="inventory_select" class="form-control" disabled>
                        <option value="">-- Pilih Bengkel Terlebih Dahulu --</option>
                    </select>
                </div>
                <div class="qty-width">
                    <input type="number" id="temp_quantity" class="form-control" placeholder="Qty" min="1" disabled>
                </div>
                <div>
                    <button type="button" id="btnAddToList" class="btn-add" disabled>+ Tambah</button>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Barang Dipilih -->
        <div class="table-responsive">
            <table id="cartTable">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="emptyRow">
                        <td colspan="4" style="text-align: center; color: #a0aec0;">Belum ada barang yang ditambahkan</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Catatan Praktikum -->
        <div class="form-group" style="margin-top: 15px;">
            <label for="notes">Catatan / Judul Modul Praktikum</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Contoh: Praktikum Pemeliharaan Panel Listrik"></textarea>
        </div>

        <button type="submit" id="btnSubmit" class="btn-submit">Proses Semua Peminjaman</button>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let cart = []; // Array penampung barang sementara

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // 1. Ambil Barang Dinamis Berdasarkan Bengkel yang Dipilih
    $('#room_id').on('change', function() {
        let roomId = $(this).val();
        let inventorySelect = $('#inventory_select');

        // Reset pilihan barang & keranjang saat bengkel berganti
        inventorySelect.empty().append('<option value="">-- Pilih Alat / Bahan --</option>');
        cart = [];
        renderCartTable();
        
        if (!roomId) {
            inventorySelect.prop('disabled', true);
            $('#temp_quantity').prop('disabled', true);
            $('#btnAddToList').prop('disabled', true);
            return;
        }

        inventorySelect.append('<option value="">Memuat barang bengkel...</option>');

        // Ganti bagian AJAX di index.blade.php:
        $.ajax({
            url: "/inventory-loans/get-inventories/" + encodeURIComponent(roomId),
            type: 'GET',
            dataType: 'json',
            success: function(inventories) {
                inventorySelect.empty().append('<option value="">-- Pilih Alat / Bahan --</option>');

                if (inventories.length === 0) {
                    inventorySelect.append('<option value="">(Tidak ada barang / stok habis di bengkel ini)</option>');
                    return;
                }

                inventories.forEach(function(item) {
                    inventorySelect.append(`
                        <option value="${item.id}" 
                                data-stock="${item.quantity}" 
                                data-unit="${item.unit}" 
                                data-type="${item.type}"
                                data-name="${item.name}">
                            [${item.type ? item.type.toUpperCase() : 'BARANG'}] ${item.name} — (Tersedia: ${item.quantity} ${item.unit})
                        </option>
                    `);
                });

                inventorySelect.prop('disabled', false);
                $('#temp_quantity').prop('disabled', false);
                $('#btnAddToList').prop('disabled', false);
            },
            error: function(xhr) {
                console.error("Error Response:", xhr.responseText); // Cek detail error di Console F12
                Swal.fire('Error', 'Gagal memuat data barang dari bengkel ini. (Cek console browser)', 'error');
            }
        });
    });

    // 2. Tambah Barang ke Keranjang
    $('#btnAddToList').on('click', function() {
        let select = $('#inventory_select').find(':selected');
        let itemId = select.val();
        let itemName = select.data('name');
        let itemType = select.data('type');
        let itemUnit = select.data('unit');
        let itemStock = parseInt(select.data('stock'));
        let qty = parseInt($('#temp_quantity').val());

        if (!itemId) {
            Swal.fire('Peringatan', 'Pilih barang terlebih dahulu!', 'warning');
            return;
        }
        if (!qty || qty <= 0) {
            Swal.fire('Peringatan', 'Masukkan jumlah barang yang valid!', 'warning');
            return;
        }
        if (qty > itemStock) {
            Swal.fire('Stok Kurang', `Jumlah dimasukkan (${qty}) melebihi stok (${itemStock} ${itemUnit}).`, 'warning');
            return;
        }

        let existingIndex = cart.findIndex(item => item.id === itemId);
        if (existingIndex > -1) {
            let totalQty = cart[existingIndex].qty + qty;
            if (totalQty > itemStock) {
                Swal.fire('Stok Kurang', `Total permintaan (${totalQty}) melebihi stok (${itemStock} ${itemUnit}).`, 'warning');
                return;
            }
            cart[existingIndex].qty = totalQty;
        } else {
            cart.push({
                id: itemId,
                name: itemName,
                type: itemType,
                unit: itemUnit,
                qty: qty
            });
        }

        renderCartTable();
        $('#temp_quantity').val('');
        $('#inventory_select').val('');
    });

    // 3. Render Tabel Keranjang
    function renderCartTable() {
        let tbody = $('#cartTable tbody');
        tbody.empty();

        if (cart.length === 0) {
            tbody.append('<tr id="emptyRow"><td colspan="4" style="text-align: center; color: #a0aec0;">Belum ada barang yang ditambahkan</td></tr>');
            return;
        }

        cart.forEach((item, index) => {
            let badgeColor = item.type === 'bahan' ? '#ed8936' : '#4299e1';
            let row = `
                <tr>
                    <td><strong>${item.name}</strong></td>
                    <td><span style="background:${badgeColor}; color:white; padding:2px 6px; border-radius:4px; font-size:11px;">${item.type.toUpperCase()}</span></td>
                    <td>${item.qty} ${item.unit}</td>
                    <td><button type="button" class="btn-delete" onclick="removeItem(${index})">Hapus</button></td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // 4. Hapus Item dari Keranjang
    window.removeItem = function(index) {
        cart.splice(index, 1);
        renderCartTable();
    };

    // 5. Submit Keranjang via AJAX
    $('#loanBatchForm').on('submit', function(e) {
        e.preventDefault();

        if (cart.length === 0) {
            Swal.fire('Keranjang Kosong', 'Tambahkan minimal satu barang ke dalam daftar!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Peminjaman',
            text: `Apakah kamu yakin ingin memproses ${cart.length} barang ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3182ce',
            cancelButtonColor: '#e53e3e',
            confirmButtonText: 'Ya, Diproses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Transaksi...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('inventory-loans.store-batch') }}",
                    type: "POST",
                    data: {
                        student_id: $('#student_id').val(),
                        room_id: $('#room_id').val(),
                        notes: $('#notes').val(),
                        items: cart
                    },
                    dataType: "json",
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Update Stok pada Select Dropdown
                            response.updated_stocks.forEach(updated => {
                                let option = $(`#inventory_select option[value="${updated.id}"]`);
                                option.data('stock', updated.new_stock);
                                option.text(`[${updated.type.toUpperCase()}] ${updated.name} — (Tersedia: ${updated.new_stock} ${updated.unit})`);
                            });

                            cart = [];
                            renderCartTable();
                            $('#loanBatchForm')[0].reset();
                            $('#inventory_select').prop('disabled', true);
                            $('#temp_quantity').prop('disabled', true);
                            $('#btnAddToList').prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan pada sistem.';
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>