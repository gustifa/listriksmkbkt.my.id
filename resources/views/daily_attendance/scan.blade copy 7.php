@section('title', 'Scan Absensi & Izin')

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
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#deviceConfig">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                    <div class="collapse" id="deviceConfig">
                        <div class="bg-white card-footer">
                            <div id="info-registered-device">
                                <small class="text-muted d-block">Token Perangkat:</small>
                                <code id="display-token" class="text-primary">-</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="shadow card border-bottom-primary">
                    <div class="text-center text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-qrcode me-2"></i> SCANNER GERBANG</h4>
                        <small>SMK N 1 BUKITTINGGI</small>
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

                        <div id="reader" style="width: 100%; border: 2px solid #3b82f6; border-radius:12px; min-height: 320px; background: #000; overflow: hidden;"></div>
                        <canvas id="capture-canvas" style="display:none;"></canvas>

                        <div class="mt-3">
                            <p class="text-muted small" id="scan-instruction">Arahkan QR Code ke kotak kamera.</p>
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
            let isProcessing = false; // Mencegah double scan

            // Audio Indicators
            const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
            const audioError = new Audio("{{ asset('audio/error.mp3') }}");

            function playSound(status) {
                let sound = (status === 'success') ? audioSuccess : audioError;
                sound.currentTime = 0;
                sound.play().catch(e => console.warn("Audio blocked by browser"));
            }

            // Fungsi Ambil Foto (Capture Face)
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

            // Callback saat QR Terdeteksi
            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning || isProcessing) return;

                isProcessing = true; // Kunci proses agar tidak menscan ulang saat modal muncul
                const capturedImage = captureFace();
                html5QrCode.pause(); // Jeda kamera

                let mode = $('input[name="scan_mode"]:checked').val();
                let deviceToken = localStorage.getItem('device_token') || 'GUEST-DEVICE';

                // Tampilkan Loading
                Swal.fire({
                    title: 'Memproses Data...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                // Kirim Data via AJAX
                $.ajax({
                    url: mode === 'daily' ? "{{ route('daily.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nis: decodedText,
                        device_token: deviceToken,
                        image: capturedImage
                    },
                    success: function(res) {
                        const isSuccess = (res.status === 'success');
                        playSound(isSuccess ? 'success' : 'error');

                        Swal.fire({
                            title: isSuccess ? 'BERHASIL!' : 'GAGAL!',
                            text: res.message + (res.student ? ' - ' + res.student.name : ''),
                            icon: isSuccess ? 'success' : 'error',
                            timer: 3000, // Hilang otomatis dalam 3 detik
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(() => {
                            isProcessing = false;
                            safeResume();
                        });
                    },
                    error: function(xhr) {
                        playSound('error');
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                        
                        Swal.fire({
                            title: 'ERROR!',
                            text: msg,
                            icon: 'error',
                            timer: 3000, // Error juga hilang otomatis dalam 3 detik
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(() => {
                            isProcessing = false;
                            safeResume();
                        });
                    }
                });
            }

            function safeResume() {
                try { 
                    if (isScanning) html5QrCode.resume(); 
                } catch(e) { console.warn("Resume failed", e); }
            }

            // Inisialisasi Kamera
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = $('#camera-select');
                if (devices && devices.length) {
                    devices.forEach(dev => cameraSelect.append(new Option(dev.label || `Kamera ${dev.id}`, dev.id)));
                }
            }).catch(err => console.error("Gagal ambil kamera", err));

            // Tombol Kontrol
            $('#btn-start').click(() => {
                const cameraId = $('#camera-select').val();
                if(!cameraId) return Swal.fire('Error', 'Silakan pilih kamera terlebih dahulu!', 'error');

                html5QrCode.start(cameraId, { fps: 15, qrbox: 250 }, onScanSuccess)
                    .then(() => {
                        isScanning = true;
                        $('#btn-start').prop('disabled', true);
                        $('#btn-stop').prop('disabled', false);
                        $('#device-name-display').text('AKTIF').addClass('text-success');
                    })
                    .catch(err => Swal.fire('Gagal', 'Kamera tidak dapat diakses.', 'error'));
            });

            $('#btn-stop').click(() => {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    isProcessing = false;
                    $('#btn-start').prop('disabled', false);
                    $('#btn-stop').prop('disabled', true);
                    $('#device-name-display').text('NON-AKTIF').removeClass('text-success');
                });
            });
        });
    </script>
</x-app-layout>