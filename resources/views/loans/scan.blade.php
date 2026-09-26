@section('title', 'Scan Peminjaman Barang')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-purple text-white text-center">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i> Scan Barcode Barang</h5>
                        <small>Arahkan kamera ke QR Code Inventaris untuk meminjam</small>
                    </div>
                    <div class="card-body text-center">
                        
                        <!-- Area Kamera -->
                        <div id="reader" style="width: 100%; border-radius: 10px; overflow: hidden;"></div>
                        
                        <div class="mt-3">
                            <p class="text-muted small">Pastikan pencahayaan cukup terang.</p>
                            <a href="{{ route('loans.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-history me-1"></i> Lihat Riwayat Peminjaman Saya
                            </a>
                        </div>

                        <!-- Manual Input (Fallback jika kamera rusak) -->
                        <div class="mt-4 pt-3 border-top">
                            <label class="form-label fw-bold">Input Kode Manual</label>
                            <div class="input-group">
                                <input type="text" id="manual_code" class="form-control" placeholder="Contoh: INV-001">
                                <button class="btn btn-primary" onclick="processLoan($('#manual_code').val())">
                                    <i class="fas fa-check"></i> Pinjam
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Library HTML5-QRCode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // --- 1. SETUP SCANNER ---
        const html5QrCode = new Html5Qrcode("reader");
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            // Hentikan scan sejenak agar tidak double submit
            html5QrCode.pause(); 
            
            // Proses Peminjaman
            processLoan(decodedText);
        };

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        // Mulai kamera (kamera belakang prefered)
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
        .catch(err => {
            console.error(err);
            $('#reader').html('<div class="alert alert-danger">Kamera tidak terdeteksi. Silakan gunakan input manual.</div>');
        });

        // --- 2. LOGIKA API ---
        function processLoan(code) {
            if(!code) {
                Swal.fire('Error', 'Kode barang tidak boleh kosong', 'warning');
                return;
            }

            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang mengecek ketersediaan barang...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('loans.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    barcode: code
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `Anda berhasil meminjam: <br><strong>${response.item_name}</strong><br><small>Sisa stok: ${response.remaining_stock}</small>`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Resume scanning jika pakai kamera
                        try { html5QrCode.resume(); } catch(e){}
                        $('#manual_code').val(''); // Reset manual input
                    });
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    }).then(() => {
                        // Resume scanning
                        try { html5QrCode.resume(); } catch(e){}
                    });
                }
            });
        }
    </script>
    <style>
        .bg-purple { background-color: #6f42c1; }
    </style>
    @endpush
</x-app-layout>