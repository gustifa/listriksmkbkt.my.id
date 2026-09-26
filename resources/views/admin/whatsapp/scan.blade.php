@section('title', 'Scan: ' . $gateway->name)

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-success">
                            <i class="fab fa-whatsapp me-2"></i> {{ $gateway->name }}
                        </h5>
                        <a href="{{ route('whatsapp.broadcast') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body text-center p-5">
                        
                        <div class="mb-4">
                            <span id="wa-status" class="badge bg-secondary fs-6 px-3 py-2">Memuat Status...</span>
                            <div class="mt-2 text-muted small font-monospace">
                                Session ID: {{ $gateway->session_id }}
                            </div>
                        </div>

                        <!-- Area QR Code -->
                        <div id="qr-container" style="display: none;">
                            <div class="mb-3">
                                <canvas id="qr-canvas"></canvas>
                            </div>
                            <p class="text-muted small">
                                Buka WhatsApp di HP Anda &rarr; Menu &rarr; Perangkat Tertaut &rarr; Tautkan Perangkat
                            </p>
                            <div class="spinner-border text-success spinner-border-sm mt-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted small mt-1">Menunggu scan...</p>
                        </div>

                        <!-- Area Terhubung -->
                        <div id="connected-container" style="display: none;">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success mt-3">WhatsApp Terhubung!</h4>
                            <p class="text-muted mb-1">Gateway siap mengirim pesan.</p>
                            <p id="phone-number" class="fw-bold text-dark font-monospace fs-5"></p>
                        </div>

                        <!-- Area Error -->
                        <div id="error-container" style="display: none;">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-danger fw-bold">Gagal Terhubung ke Node.js</h5>
                            <p class="text-muted small">
                                Pastikan layanan bot WhatsApp (PM2) sudah berjalan di server.
                            </p>
                            <button onclick="location.reload()" class="btn btn-outline-danger btn-sm mt-2">
                                <i class="fas fa-sync me-1"></i> Coba Lagi
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Library QRious untuk generate QR dari text -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    
    <script>
        const SESSION_ID = "{{ $gateway->session_id }}";
        const NODE_API = "http://localhost:3000"; // Sesuaikan jika port Node.js berbeda

        async function checkStatus() {
            try {
                // Panggil API status spesifik untuk session ini
                const response = await fetch(`${NODE_API}/session/status/${SESSION_ID}`);
                
                if (!response.ok) throw new Error("Service Down");
                
                const data = await response.json();
                
                const badge = document.getElementById('wa-status');
                const qrContainer = document.getElementById('qr-container');
                const connectedContainer = document.getElementById('connected-container');
                const errorContainer = document.getElementById('error-container');

                // Reset Tampilan
                errorContainer.style.display = 'none';

                if (data.status === 'connected') {
                    // KONDISI 1: TERHUBUNG
                    badge.className = 'badge bg-success fs-6 px-3 py-2';
                    badge.innerText = 'TERHUBUNG';
                    
                    qrContainer.style.display = 'none';
                    connectedContainer.style.display = 'block';
                    
                    if (data.phone) {
                        document.getElementById('phone-number').innerText = '+' + data.phone;
                    }

                } else if (data.status === 'scan_needed') {
                    // KONDISI 2: BUTUH SCAN
                    badge.className = 'badge bg-warning text-dark fs-6 px-3 py-2';
                    badge.innerText = 'SCAN QR CODE';
                    
                    connectedContainer.style.display = 'none';
                    qrContainer.style.display = 'block';

                    if (data.qr) {
                        new QRious({
                            element: document.getElementById('qr-canvas'),
                            value: data.qr,
                            size: 250,
                            level: 'H'
                        });
                    }

                } else if (data.status === 'connecting') {
                    // KONDISI 3: SEDANG KONEKSI
                    badge.className = 'badge bg-info text-dark fs-6 px-3 py-2';
                    badge.innerText = 'MENGHUBUNGKAN...';
                    
                    qrContainer.style.display = 'none';
                    connectedContainer.style.display = 'none';

                } else {
                    // KONDISI 4: DISCONNECTED / LAINNYA
                    badge.className = 'badge bg-secondary fs-6 px-3 py-2';
                    badge.innerText = 'TERPUTUS / MENUNGGU';
                    
                    // Jika disconnected tapi tidak ada QR, mungkin perlu trigger start ulang session
                    // Tapi di sini kita hanya memantau. Admin bisa hapus & buat baru jika macet total.
                }

            } catch (error) {
                console.error("Gagal koneksi ke WA Service:", error);
                document.getElementById('wa-status').className = 'badge bg-danger fs-6 px-3 py-2';
                document.getElementById('wa-status').innerText = 'SERVICE OFFLINE';
                
                document.getElementById('qr-container').style.display = 'none';
                document.getElementById('connected-container').style.display = 'none';
                document.getElementById('error-container').style.display = 'block';
            }
        }

        // Cek status setiap 3 detik
        const intervalId = setInterval(checkStatus, 3000);
        
        // Cek pertama kali saat load
        checkStatus();

        // Bersihkan interval saat meninggalkan halaman (opsional jika SPA, tapi aman untuk MPA)
        window.addEventListener('beforeunload', () => clearInterval(intervalId));
    </script>
</x-app-layout>