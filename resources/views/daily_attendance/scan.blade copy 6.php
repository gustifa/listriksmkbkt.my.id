@section('title')
    Scan Absensi & Izin
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="mb-3 shadow-sm card border-left-info">
                    <div class="py-2 card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold d-block">STATUS PERANGKAT</small>
                            <span id="device-name-display" class="fw-bold text-dark">Memeriksa...</span>
                        </div>
                        <button id="btn-device-settings" class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#deviceConfig">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                    <div class="collapse" id="deviceConfig">
                        <div class="bg-white card-footer">
                            <div id="form-register-device" style="display: none;">
                                <label class="small text-muted">Daftarkan perangkat ini:</label>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="input-device-name" class="form-control" placeholder="Contoh: POS SATPAM 1">
                                    <button class="btn btn-primary" id="btn-register-device">Simpan</button>
                                </div>
                            </div>
                            <div id="info-registered-device" style="display: none;">
                                <div class="py-2 mb-2 alert alert-success small">
                                    <i class="fas fa-check-circle"></i> Perangkat Terdaftar.
                                </div>
                                <small class="text-muted d-block">Token: <code id="display-token">-</code></small>
                                <button class="mt-2 btn btn-sm btn-danger w-100" id="btn-reset-device">
                                    <i class="fas fa-trash"></i> Hapus Registrasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shadow card border-bottom-primary">
                    <div class="text-center text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-qrcode me-2"></i> SCANNER UTAMA</h4>
                        <small>Gerbang SMK N 1 Bukittinggi</small>
                    </div>
                    <div class="text-center card-body bg-light">
                        <div class="mb-4 btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="scan_mode" id="mode_daily" value="daily" checked>
                            <label class="btn btn-outline-primary fw-bold" for="mode_daily">
                                <i class="fas fa-user-clock me-1"></i> Absensi Harian
                            </label>
                            <input type="radio" class="btn-check" name="scan_mode" id="mode_permit" value="permit">
                            <label class="btn btn-outline-warning fw-bold text-dark" for="mode_permit">
                                <i class="fas fa-door-open me-1"></i> Izin Keluar
                            </label>
                        </div>

                        <div class="mb-3 text-start">
                            <label for="camera-select" class="form-label fw-bold small">Pilih Kamera:</label>
                            <div class="input-group input-group-sm">
                                <select id="camera-select" class="form-select"></select>
                                <button class="btn btn-success" id="btn-start"><i class="fas fa-power-off"></i></button>
                                <button class="btn btn-danger" id="btn-stop" disabled><i class="fas fa-stop"></i></button>
                            </div>
                        </div>

                        <div id="reader" style="width: 100%; border: 2px solid #ddd; border-radius:8px; min-height: 300px; background: #000;"></div>
                        <canvas id="capture-canvas" style="display:none;"></canvas>

                        <div class="mt-3">
                            <p class="text-muted small" id="scan-instruction">Mode Absensi: Scan saat datang & pulang.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const html5QrCode = new Html5Qrcode("reader");
            let isScanning = false;
            const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
            const audioError = new Audio("{{ asset('audio/error.mp3') }}");

            function playSound(status) {
                let sound = (status === 'success') ? audioSuccess : audioError;
                sound.currentTime = 0;
                sound.play().catch(e => console.warn("Audio blocked"));
            }

            // Capture Wajah dari Video Stream
            function captureFace() {
                const video = document.querySelector('#reader video');
                const canvas = document.getElementById('capture-canvas');
                if (video) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    return canvas.toDataURL('image/jpeg', 0.7);
                }
                return null;
            }

            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning) return;

                // Ambil foto tepat saat QR terdeteksi
                const capturedImage = captureFace();
                html5QrCode.pause();

                let mode = $('input[name="scan_mode"]:checked').val();
                let deviceToken = localStorage.getItem('device_token');

                if (mode === 'daily') {
                    processDailyAttendance(decodedText, deviceToken, capturedImage);
                } else {
                    processPermission(decodedText, deviceToken, capturedImage);
                }
            }

            function processDailyAttendance(nis, token, image) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ route('daily.store') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis, device_token: token, image: image },
                    success: function(res) {
                        playSound(res.status === 'success' ? 'success' : 'error');
                        Swal.fire({
                            title: res.message,
                            text: res.student?.name || '',
                            icon: res.status === 'success' ? 'success' : 'error',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => safeResume());
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            // Fungsi Helper Kamera (Start/Stop/Resume)
            function safeResume() { try { html5QrCode.resume(); } catch(e){} }
            function handleError(xhr) {
                playSound('error');
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Error', msg, 'error').then(() => safeResume());
            }

            // Setup Kameralist
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = $('#camera-select');
                if (devices && devices.length) {
                    devices.forEach(dev => cameraSelect.append(new Option(dev.label || `Kamera ${dev.id}`, dev.id)));
                }
            });

            $('#btn-start').click(() => {
                const id = $('#camera-select').val();
                if(!id) return Swal.fire('Error', 'Pilih kamera!', 'error');
                html5QrCode.start(id, { fps: 15, qrbox: 250 }, onScanSuccess).then(() => {
                    isScanning = true;
                    $('#btn-start').prop('disabled', true);
                    $('#btn-stop').prop('disabled', false);
                });
            });

            $('#btn-stop').click(() => {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    $('#btn-start').prop('disabled', false);
                    $('#btn-stop').prop('disabled', true);
                });
            });
        });
    </script>
</x-app-layout>
