<x-app-layout>
    <div class="page-content">
        <div class="row">
            <!-- AREA KAMERA & SCANNER -->
            <div class="col-md-7">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scanner MBG (Ambil & Kembali)</h5>
                        <div class="badge bg-warning text-dark fs-6" id="jam-digital">00:00:00</div>
                    </div>
                    <div class="card-body text-center bg-black p-0 position-relative" style="min-height: 400px;">
                        
                        <!-- Video Preview -->
                        <div id="reader" style="width: 100%; height: 100%;"></div>
                        
                        <!-- Overlay Loading -->
                        <div id="loading-scan" class="position-absolute top-50 start-50 translate-middle d-none">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"></div>
                            <h5 class="text-white mt-3 fw-bold text-shadow">Memproses Data...</h5>
                        </div>

                    </div>
                    <div class="card-footer p-3 bg-light">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-keyboard text-muted"></i></span>
                            <input type="text" id="manual-nis" class="form-control fw-bold text-center border-start-0" placeholder="Ketik NIS lalu Tekan Enter" autofocus>
                            <button class="btn btn-primary px-4" onclick="processManual()">
                                <i class="fas fa-paper-plane me-2"></i>KIRIM
                            </button>
                        </div>
                        <div class="d-flex justify-content-between mt-2 px-1">
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Scan 1: Ambil Makan</small>
                            <small class="text-muted"><i class="fas fa-check-circle me-1"></i> Scan 2: Kembali Wadah</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AREA STATUS & RIWAYAT TERAKHIR -->
            <div class="col-md-5">
                <!-- HASIL SCAN TERAKHIR -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body text-center p-4" id="result-container">
                        <div class="py-4">
                            <i class="fas fa-camera-retro fa-4x text-muted mb-3 opacity-50"></i>
                            <h4 class="text-muted fw-bold">Siap Memindai...</h4>
                            <p class="text-muted mb-0">Arahkan Kartu Pelajar atau Wajah ke Kamera</p>
                        </div>
                    </div>
                </div>

                <!-- HISTORY HARI INI -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Aktivitas Terakhir</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="recent-list">
                            @php
                                $recents = \App\Models\MbgAttendance::with('student')
                                            ->whereDate('date', date('Y-m-d'))
                                            ->orderBy('updated_at', 'desc') // Sort by latest update (take or return)
                                            ->take(5)
                                            ->get();
                            @endphp

                            @forelse($recents as $row)
                                <li class="list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeInLeft">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $row->student->name ?? 'Siswa' }}</div>
                                        <small class="text-muted">
                                            @if($row->status == 'returned')
                                                <i class="fas fa-check-double text-success me-1"></i> Kembali: {{ \Carbon\Carbon::parse($row->returned_at)->format('H:i') }}
                                            @else
                                                <i class="fas fa-utensils text-primary me-1"></i> Ambil: {{ \Carbon\Carbon::parse($row->taken_at)->format('H:i') }}
                                            @endif
                                            • {{ $row->student->nis ?? '-' }}
                                        </small>
                                    </div>
                                    @if($row->status == 'returned')
                                        <span class="badge bg-success rounded-pill">SELESAI</span>
                                    @else
                                        <span class="badge bg-primary rounded-pill">MAKAN</span>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted small py-4">Belum ada aktivitas hari ini.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Video & Canvas for Face Snapshot -->
    <video id="evidence-video" autoplay playsinline class="d-none"></video>
    <canvas id="evidence-canvas" class="d-none"></canvas>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- SETUP ---
        const html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        const evidenceVideo = document.getElementById('evidence-video');
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => { evidenceVideo.srcObject = stream; })
                .catch(err => console.log("Kamera bukti error:", err));
        }

        // --- PROCESS ---
        let isProcessing = false;

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;
            processAttendance(decodedText, 'barcode');
        }
        function onScanFailure(error) {}

        function processManual() {
            const nis = document.getElementById('manual-nis').value;
            if(!nis) { Swal.fire("Info", "Masukkan NIS!", "info"); return; }
            isProcessing = true;
            processAttendance(nis, 'manual');
        }

        document.getElementById('manual-nis').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') processManual();
        });

        function processAttendance(nis, method) {
            document.getElementById('loading-scan').classList.remove('d-none');
            
            // Snapshot
            let imageBase64 = null;
            try {
                if(evidenceVideo.srcObject && evidenceVideo.videoWidth > 0) {
                    const canvas = document.getElementById('evidence-canvas');
                    canvas.width = evidenceVideo.videoWidth;
                    canvas.height = evidenceVideo.videoHeight;
                    canvas.getContext('2d').drawImage(evidenceVideo, 0, 0);
                    imageBase64 = canvas.toDataURL('image/jpeg', 0.6);
                }
            } catch(e) {}

            fetch("{{ route('admin.mbg.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ nis: nis, method: method, image: imageBase64 })
            })
            .then(async response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) return response.json();
                else throw new Error("Server Error (HTML Response).");
            })
            .then(data => {
                if (data.status === 'success') {
                    showSuccess(data);
                    document.getElementById('manual-nis').value = '';
                    document.getElementById('manual-nis').focus();
                } else {
                    showError(data);
                }
            })
            .catch(error => {
                Swal.fire("Gagal", error.message, "error");
            })
            .finally(() => {
                document.getElementById('loading-scan').classList.add('d-none');
                setTimeout(() => { isProcessing = false; }, 2000); // 2s delay
            });
        }

        // --- UI ---
        function showSuccess(data) {
            // Tentukan Warna & Ikon berdasarkan Tipe (TAKE/RETURN)
            let colorClass = data.type === 'RETURN' ? 'success' : 'primary';
            let iconClass = data.type === 'RETURN' ? 'fa-check-double' : 'fa-utensils';
            let titleText = data.type === 'RETURN' ? 'DIKEMBALIKAN' : 'SELAMAT MAKAN';
            let badgeText = data.type === 'RETURN' ? 'SELESAI' : 'MAKAN';

            const html = `
                <div class="alert alert-${colorClass} border-0 shadow-sm animate__animated animate__zoomIn">
                    <div class="display-1 mb-2"><i class="fas ${iconClass} text-${colorClass}"></i></div>
                    <h2 class="fw-bold text-${colorClass}">${titleText}</h2>
                    <h4 class="text-dark fw-bold">${data.student.name}</h4>
                    <div class="badge bg-dark fs-5 mt-2">${data.student.nis}</div>
                    <p class="mt-3 text-muted fw-bold">${data.message}</p>
                </div>
            `;
            document.getElementById('result-container').innerHTML = html;

            // Add History
            const list = document.getElementById('recent-list');
            const newItem = `
                <li class="list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeInLeft bg-light-subtle">
                    <div>
                        <div class="fw-bold text-dark">${data.student.name}</div>
                        <small class="text-muted">
                             <i class="fas ${iconClass} text-${colorClass} me-1"></i> ${data.time} • ${data.student.nis}
                        </small>
                    </div>
                    <span class="badge bg-${colorClass} rounded-pill">${badgeText}</span>
                </li>
            `;
            if(list.innerText.includes('Belum ada')) list.innerHTML = '';
            list.insertAdjacentHTML('afterbegin', newItem);
            if(list.children.length > 5) list.lastElementChild.remove();
        }

        function showError(data) {
            const html = `
                <div class="alert alert-danger border-0 shadow-sm animate__animated animate__shakeX">
                    <div class="display-1 mb-2"><i class="fas fa-times-circle text-danger"></i></div>
                    <h2 class="fw-bold text-danger">GAGAL!</h2>
                    <h5 class="text-dark fw-bold">${data.data ? data.data.name : 'NIS Tidak Dikenal'}</h5>
                    <p class="mt-2 fs-5 text-danger">${data.message}</p>
                </div>
            `;
            document.getElementById('result-container').innerHTML = html;
        }

        setInterval(() => {
            document.getElementById('jam-digital').innerText = new Date().toLocaleTimeString('id-ID');
        }, 1000);
    </script>
    @endpush
</x-app-layout>