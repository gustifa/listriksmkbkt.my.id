<x-app-layout>
    <div class="page-content">
        <div class="row">
            <!-- AREA KAMERA & SCANNER -->
            <div class="col-md-7">
                <div class="border-0 shadow-lg card">
                    <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scanner MBG (Makan Bergizi Gratis)</h5>
                        <div class="badge bg-warning text-dark" id="jam-digital">00:00:00</div>
                    </div>
                    <div class="p-0 text-center card-body bg-dark rounded-bottom position-relative">

                        <!-- Video Preview -->
                        <div id="reader" style="width: 100%; min-height: 400px;"></div>

                        <!-- Overlay Loading -->
                        <div id="loading-scan" class="position-absolute top-50 start-50 translate-middle d-none">
                            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status"></div>
                            <div class="mt-2 text-white fw-bold">Memproses...</div>
                        </div>

                    </div>
                    <div class="p-3 card-footer">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="fas fa-keyboard"></i></span>
                            <input type="text" id="manual-nis" class="text-center form-control fw-bold" placeholder="Ketik NIS lalu Tekan Enter (Jika Scanner Gagal)" autofocus>
                            <button class="btn btn-primary" onclick="processManual()">KIRIM</button>
                        </div>
                        <small class="mt-2 text-muted d-block">*Pastikan kamera aktif dan browser mengizinkan akses kamera.</small>
                    </div>
                </div>
            </div>

            <!-- AREA STATUS & RIWAYAT TERAKHIR -->
            <div class="col-md-5">
                <!-- HASIL SCAN TERAKHIR -->
                <div class="mb-4 shadow-sm card">
                    <div class="text-center card-body" id="result-container">
                        <div class="mb-3">
                            <i class="mb-3 fas fa-utensils fa-4x text-muted"></i>
                            <h4 class="text-muted">Siap Memindai...</h4>
                            <p class="text-muted">Arahkan Kartu Pelajar ke Kamera atau Ketik NIS.</p>
                        </div>
                    </div>
                </div>

                <!-- HISTORY HARI INI -->
                <div class="shadow-sm card">
                    <div class="text-white card-header bg-success">
                        <h6 class="mb-0"><i class="fas fa-history me-2"></i>5 Pengambil Terakhir</h6>
                    </div>
                    <div class="p-0 card-body">
                        <ul class="list-group list-group-flush" id="recent-list">
                            <!-- List item will be appended here by JS -->
                            <li class="py-3 text-center list-group-item text-muted small">Belum ada data hari ini.</li>
                        </ul>
                    </div>
                    <div class="text-center card-footer">
                        <a href="{{ route('admin.mbg.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua Laporan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Video & Canvas for Face Snapshot (Evidence) -->
    <video id="evidence-video" autoplay playsinline class="d-none"></video>
    <canvas id="evidence-canvas" class="d-none"></canvas>

    @push('scripts')
    <!-- Library Barcode Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- 1. SETUP KAMERA & SCANNER ---
        const beepSuccess = new Audio("{{ asset('assets/audio/beep-success.mp3') }}"); // Opsional
        const beepError = new Audio("{{ asset('assets/audio/beep-error.mp3') }}");     // Opsional

        // Setup Html5QrcodeScanner
        const html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Setup Evidence Camera (Kamera kedua/background untuk foto wajah saat scan)
        const evidenceVideo = document.getElementById('evidence-video');
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { evidenceVideo.srcObject = stream; })
            .catch(err => console.error("Gagal akses kamera bukti:", err));

        // --- 2. LOGIKA SCAN BERHASIL ---
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return; // Cegah double scan cepat
            isProcessing = true;
            processAttendance(decodedText, 'barcode');
        }

        function onScanFailure(error) {
            // Biarkan kosong agar tidak spam console log
        }

        function processManual() {
            const nis = document.getElementById('manual-nis').value;
            if(!nis) return;
            isProcessing = true;
            processAttendance(nis, 'manual');
        }

        // Listener Enter pada input manual
        document.getElementById('manual-nis').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') processManual();
        });

        // --- 3. KIRIM DATA KE SERVER ---
        function processAttendance(nis, method) {
            // Tampilkan Loading
            document.getElementById('loading-scan').classList.remove('d-none');

            // Ambil Snapshot Wajah sebagai Bukti
            const canvas = document.getElementById('evidence-canvas');
            canvas.width = evidenceVideo.videoWidth;
            canvas.height = evidenceVideo.videoHeight;
            canvas.getContext('2d').drawImage(evidenceVideo, 0, 0);
            const imageBase64 = canvas.toDataURL('image/jpeg', 0.8);

            // AJAX Request
            fetch("{{ route('admin.mbg.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    nis: nis,
                    method: method,
                    image: imageBase64 // Kirim foto bukti
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading-scan').addClass('d-none');

                if (data.status === 'success') {
                    showSuccess(data);
                    document.getElementById('manual-nis').value = ''; // Reset input
                } else {
                    showError(data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire("Error System", "Terjadi kesalahan koneksi.", "error");
            })
            .finally(() => {
                document.getElementById('loading-scan').classList.add('d-none');
                // Beri jeda 2 detik sebelum bisa scan lagi
                setTimeout(() => { isProcessing = false; }, 2000);
            });
        }

        // --- 4. TAMPILAN HASIL ---
        function showSuccess(data) {
            // Play Sound (Jika ada file audionya)
            // beepSuccess.play();

            // Update Tampilan Besar
            const html = `
                <div class="border-0 shadow-sm alert alert-success animate__animated animate__bounceIn">
                    <div class="mb-2 display-1"><i class="fas fa-check-circle text-success"></i></div>
                    <h2 class="fw-bold text-success">BERHASIL!</h2>
                    <h4 class="text-dark">${data.student.name}</h4>
                    <div class="mt-2 badge bg-primary fs-5">${data.student.nis}</div>
                    <p class="mt-3 text-muted">Silahkan ambil makan siang.</p>
                </div>
            `;
            document.getElementById('result-container').innerHTML = html;

            // Tambahkan ke List History di Kanan
            const list = document.getElementById('recent-list');
            const newItem = `
                <li class="list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeInLeft">
                    <div>
                        <div class="fw-bold text-dark">${data.student.name}</div>
                        <small class="text-muted">${data.time} • ${data.student.nis}</small>
                    </div>
                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                </li>
            `;
            // Hapus "Belum ada data" jika ada
            if(list.innerText.includes('Belum ada data')) list.innerHTML = '';
            list.insertAdjacentHTML('afterbegin', newItem);

            // Limit list cuma 5
            if(list.children.length > 5) list.lastElementChild.remove();
        }

        function showError(data) {
            // beepError.play();

            const html = `
                <div class="border-0 shadow-sm alert alert-danger animate__animated animate__shakeX">
                    <div class="mb-2 display-1"><i class="fas fa-times-circle text-danger"></i></div>
                    <h2 class="fw-bold text-danger">DITOLAK!</h2>
                    <h5 class="text-dark fw-bold">${data.data ? data.data.name : 'Data Tidak Ditemukan'}</h5>
                    <p class="mt-2 fs-5">${data.message}</p>
                </div>
            `;
            document.getElementById('result-container').innerHTML = html;
        }

        // Jam Digital
        setInterval(() => {
            const now = new Date();
            document.getElementById('jam-digital').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);

    </script>
    @endpush
</x-app-layout>
