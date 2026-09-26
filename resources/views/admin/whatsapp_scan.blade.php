@section('title', 'Scan WhatsApp Bot')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-top border-4 border-success">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-success">
                            <i class="fab fa-whatsapp me-2"></i> Koneksi WhatsApp Gateway
                        </h5>
                    </div>
                    <div class="card-body text-center p-5">
                        
                        <!-- Status Badge -->
                        <div class="mb-4">
                            Status: <span id="wa-status" class="badge bg-secondary">Memuat...</span>
                        </div>

                        <!-- Area QR Code -->
                        <div id="qr-container" style="display: none;">
                            <p class="text-muted small mb-3">Scan kode QR di bawah ini menggunakan WhatsApp di HP Anda (Menu > Perangkat Tertaut).</p>
                            <div class="d-flex justify-content-center">
                                <!-- Canvas untuk QR Code -->
                                <canvas id="qr-canvas"></canvas>
                            </div>
                            <p class="text-muted small mt-3" id="qr-countdown">Merefresh QR code...</p>
                        </div>

                        <!-- Area Terhubung -->
                        <div id="connected-container" style="display: none;">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success">WhatsApp Terhubung!</h4>
                            <p class="text-muted">Sistem siap mengirim notifikasi otomatis.</p>
                            
                            <button onclick="checkStatus()" class="btn btn-outline-success mt-3">
                                <i class="fas fa-sync"></i> Cek Koneksi Lagi
                            </button>
                        </div>

                        <!-- Area Error -->
                        <div id="error-container" style="display: none;">
                            <div class="mb-3">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-danger">Gagal Terhubung ke Service Node.js</h5>
                            <p class="text-muted small">Pastikan `wa-bot` di PM2 sudah berjalan (Online).</p>
                            <p class="small bg-light p-2 rounded">http://localhost:3000/status</p>
                            <button onclick="location.reload()" class="btn btn-primary btn-sm">Refresh Halaman</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Library QRious untuk generate QR dari text -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    
    <script>
        const NODE_API = "http://localhost:3000"; // Sesuaikan port Node.js Anda

        function updateStatusUI(status) {
            const statusBadge = document.getElementById('wa-status');
            const qrContainer = document.getElementById('qr-container');
            const connectedContainer = document.getElementById('connected-container');
            const errorContainer = document.getElementById('error-container');

            // Reset UI
            qrContainer.style.display = 'none';
            connectedContainer.style.display = 'none';
            errorContainer.style.display = 'none';

            if (status === 'connected') {
                statusBadge.className = 'badge bg-success';
                statusBadge.innerText = 'TERHUBUNG';
                connectedContainer.style.display = 'block';
            } else if (status === 'scan_needed' || status === 'disconnected') {
                statusBadge.className = 'badge bg-warning text-dark';
                statusBadge.innerText = 'MENUNGGU SCAN';
                qrContainer.style.display = 'block';
            } else {
                statusBadge.className = 'badge bg-secondary';
                statusBadge.innerText = 'UNKNOWN';
            }
        }

        async function checkStatus() {
            try {
                // 1. Panggil API Node.js
                const response = await fetch(`${NODE_API}/qr`);
                if (!response.ok) throw new Error("Service Down");
                
                const data = await response.json();
                
                updateStatusUI(data.status);

                // 2. Jika ada QR string, render ke canvas
                if (data.qr && data.status !== 'connected') {
                    new QRious({
                        element: document.getElementById('qr-canvas'),
                        value: data.qr,
                        size: 250,
                        level: 'H' // High correction level
                    });
                }

            } catch (error) {
                console.error("Gagal koneksi ke WA Service:", error);
                document.getElementById('wa-status').className = 'badge bg-danger';
                document.getElementById('wa-status').innerText = 'SERVICE OFFLINE';
                document.getElementById('qr-container').style.display = 'none';
                document.getElementById('connected-container').style.display = 'none';
                document.getElementById('error-container').style.display = 'block';
            }
        }

        // Cek status setiap 3 detik
        setInterval(checkStatus, 3000);
        
        // Cek pertama kali saat load
        checkStatus();
    </script>
</x-app-layout>