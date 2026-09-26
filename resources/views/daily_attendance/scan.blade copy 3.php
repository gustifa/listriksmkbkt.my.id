@section('title')
    Scan Datang dan Pulang
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="shadow card border-bottom-primary">
                    <div class="text-center text-white card-header bg-primary">
                        <h4 class="mb-0"><i class="fas fa-school me-2"></i> SCANNER GERBANG</h4>
                        <small>Absensi Harian (Datang & Pulang)</small>
                    </div>
                    <div class="text-center card-body bg-light">

                        <!-- Info Autoplay -->
                        <div id="audio-alert" class="mb-2 alert alert-warning small" style="display:none;">
                            <i class="fas fa-volume-mute"></i> Klik di mana saja pada halaman ini agar suara notifikasi aktif.
                        </div>

                        <!-- MENU PILIH KAMERA (FITUR BARU) -->
                        <div class="mb-3 text-start">
                            <label for="camera-select" class="form-label fw-bold">Pilih Kamera:</label>
                            <div class="input-group">
                                <select id="camera-select" class="form-select">
                                    <option value="" disabled selected>Memuat kamera...</option>
                                </select>
                                <button class="btn btn-success" id="btn-start">
                                    <i class="fas fa-power-off"></i> Mulai
                                </button>
                                <button class="btn btn-danger" id="btn-stop" disabled>
                                    <i class="fas fa-stop"></i> Stop
                                </button>
                            </div>
                            <small class="text-muted">*Pastikan izin kamera diberikan.</small>
                        </div>

                        <!-- AREA SCANNER -->
                        <div id="reader" style="width: 100%; border: 1px solid #ccc; min-height: 300px; background: #000;"></div>

                        <div class="mt-4">
                            <p class="text-muted small">Scan QR Siswa saat tiba dan saat pulang.</p>
                        </div>

                        <!-- Tombol Test Suara -->
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="playSound('success')">
                                <i class="fas fa-volume-up"></i> Test Suara
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Load Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // --- VARIABEL GLOBAL ---
            const html5QrCode = new Html5Qrcode("reader");
            let isScanning = false;

            // --- 1. SETUP AUDIO MP3 ---
            const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
            const audioError = new Audio("{{ asset('audio/error.mp3') }}");

            function playSound(status) {
                let sound = (status === 'success') ? audioSuccess : audioError;
                sound.currentTime = 0;
                sound.play().catch(error => {
                    console.warn("Autoplay dicegah:", error);
                    $('#audio-alert').show();
                });
            }
            window.playSound = playSound;

            // --- 2. SETUP KAMERA (FITUR BARU) ---
            // Mendapatkan daftar kamera yang tersedia
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = $('#camera-select');
                cameraSelect.empty(); // Kosongkan opsi loading

                if (devices && devices.length) {
                    devices.forEach((device, index) => {
                        // Tambahkan opsi ke dropdown
                        const option = $('<option></option>').attr('value', device.id).text(device.label || `Kamera ${index + 1}`);
                        cameraSelect.append(option);
                    });
                } else {
                    cameraSelect.append('<option disabled>Tidak ada kamera ditemukan</option>');
                }
            }).catch(err => {
                console.error('Error mendapatkan kamera:', err);
                $('#camera-select').html('<option disabled>Gagal memuat kamera</option>');
            });

            // --- 3. LOGIC TOMBOL MULAI & STOP ---
            $('#btn-start').click(function() {
                const cameraId = $('#camera-select').val();

                if (!cameraId) {
                    Swal.fire('Error', 'Silakan pilih kamera terlebih dahulu!', 'error');
                    return;
                }

                if (isScanning) return; // Cegah double click

                // Konfigurasi Scanner
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                };

                // Mulai Scanning
                html5QrCode.start(
                    cameraId,
                    config,
                    onScanSuccess, // Fungsi saat sukses scan
                    (errorMessage) => {
                        // console.log(errorMessage); // Ignore error scanning frames
                    }
                ).then(() => {
                    isScanning = true;
                    $('#btn-start').prop('disabled', true);
                    $('#btn-stop').prop('disabled', false);
                    $('#camera-select').prop('disabled', true);
                }).catch(err => {
                    console.error("Gagal memulai kamera", err);
                    Swal.fire('Gagal', 'Tidak dapat mengakses kamera.', 'error');
                });
            });

            $('#btn-stop').click(function() {
                if (!isScanning) return;

                html5QrCode.stop().then(() => {
                    isScanning = false;
                    $('#btn-start').prop('disabled', false);
                    $('#btn-stop').prop('disabled', true);
                    $('#camera-select').prop('disabled', false);
                    // Bersihkan area
                    html5QrCode.clear();
                }).catch(err => {
                    console.log("Gagal stop scanner", err);
                });
            });

            // --- 4. LOGIC SAAT QR TERBACA (AJAX) ---
            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning) return;

                // Pause sementara agar tidak scan berulang kali cepat
                html5QrCode.pause();

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mencatat kehadiran...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('daily.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nis: decodedText
                    },
                    success: function(res) {
                        if(res.status == 'success') {
                            playSound('success');
                            let color = res.type == 'in' ? '#28a745' : '#17a2b8';

                            Swal.fire({
                                title: res.message,
                                text: res.student,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                                background: '#fff',
                                iconColor: color
                            }).then(() => {
                                // Resume scanning setelah alert tutup
                                try { html5Qrcode.resume(); } catch(e){}
                            });
                        } else {
                            playSound('error');
                            Swal.fire({
                                title: 'Gagal',
                                text: res.message,
                                icon: 'error',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                try { html5Qrcode.resume(); } catch(e){}
                            });
                        }
                    },
                    error: function(xhr) {
                        playSound('error');
                        console.error("Full Error Log:", xhr);

                        let msg = 'Terjadi kesalahan pada server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            msg = "Siswa tidak ditemukan atau Route salah.";
                        }

                        Swal.fire({
                            title: 'Error Sistem',
                            text: msg,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            try { html5Qrcode.resume(); } catch(e){}
                        });
                    }
                });
            }
        });
    </script>

</x-app-layout>
