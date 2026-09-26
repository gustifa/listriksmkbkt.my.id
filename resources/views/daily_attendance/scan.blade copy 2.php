@section('title')
    Scan Datang dan Pulang
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <!-- STATUS PERANGKAT (FITUR BARU) -->
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
                            <!-- Form Registrasi (Jika belum ada token) -->
                            <div id="form-register-device" style="display: none;">
                                <label class="small text-muted">Daftarkan perangkat ini ke sistem:</label>
                                <div class="mt-1 input-group input-group-sm">
                                    <input type="text" id="input-device-name" class="form-control" placeholder="Contoh: POS SATPAM 1">
                                    <button class="btn btn-primary" id="btn-register-device">Simpan</button>
                                </div>
                            </div>

                            <!-- Info Terdaftar (Jika sudah ada token) -->
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
                        <h4 class="mb-0"><i class="fas fa-school me-2"></i> SCANNER GERBANG</h4>
                        <small>Absensi Harian (Datang & Pulang)</small>
                    </div>
                    <div class="text-center card-body bg-light">

                        <!-- Info Autoplay -->
                        <div id="audio-alert" class="mb-2 alert alert-warning small" style="display:none;">
                            <i class="fas fa-volume-mute"></i> Klik di mana saja pada halaman ini agar suara notifikasi aktif.
                        </div>

                        <!-- MENU PILIH KAMERA -->
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

            // --- 0. LOGIC REGISTRASI PERANGKAT ---
            function checkDeviceRegistration() {
                const token = localStorage.getItem('device_token');
                const name = localStorage.getItem('device_name');

                if (token && name) {
                    // SUDAH TERDAFTAR
                    $('#device-name-display').text(name).removeClass('text-danger').addClass('text-success');
                    $('#form-register-device').hide();
                    $('#info-registered-device').show();
                    $('#display-token').text(token);
                } else {
                    // BELUM TERDAFTAR
                    $('#device-name-display').text('Belum Terdaftar').removeClass('text-success').addClass('text-danger');
                    $('#form-register-device').show();
                    $('#info-registered-device').hide();
                    $('#deviceConfig').collapse('show'); // Buka menu setting otomatis jika belum terdaftar
                }
            }

            // Init Cek Status
            checkDeviceRegistration();

            // Handle Register Button
            $('#btn-register-device').click(function() {
                const name = $('#input-device-name').val();
                if(!name) {
                    Swal.fire('Error', 'Nama perangkat tidak boleh kosong', 'warning');
                    return;
                }

                // Kirim ke API Laravel untuk dapat token
                $.ajax({
                    url: "{{ url('/api/device/register') }}", // Pastikan route API ini ada
                    type: "POST",
                    data: {
                        device_name: name,
                        _token: "{{ csrf_token() }}" // CSRF jika lewat web routes, atau abaikan jika murni API stateless
                    },
                    success: function(res) {
                        if(res.success) {
                            localStorage.setItem('device_token', res.data.device_token);
                            localStorage.setItem('device_name', res.data.device_name);
                            Swal.fire('Berhasil', 'Perangkat berhasil didaftarkan!', 'success');
                            checkDeviceRegistration();
                            $('#deviceConfig').collapse('hide');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire('Gagal', 'Gagal mendaftarkan perangkat. Cek koneksi.', 'error');
                    }
                });
            });

            // Handle Reset Button
            $('#btn-reset-device').click(function() {
                Swal.fire({
                    title: 'Hapus Registrasi?',
                    text: "Perangkat ini tidak akan dikenali lagi oleh sistem.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        localStorage.removeItem('device_token');
                        localStorage.removeItem('device_name');
                        checkDeviceRegistration();
                    }
                });
            });


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

            // --- 2. SETUP KAMERA ---
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = $('#camera-select');
                cameraSelect.empty();

                if (devices && devices.length) {
                    devices.forEach((device, index) => {
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

                if (isScanning) return;

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                };

                html5QrCode.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    (errorMessage) => { }
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
                    html5QrCode.clear();
                }).catch(err => {
                    console.log("Gagal stop scanner", err);
                });
            });

            // --- 4. LOGIC SAAT QR TERBACA (AJAX) ---
            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning) return;

                html5QrCode.pause();

                // Ambil token dari localstorage (opsional, jika ingin dikirim ke server untuk validasi)
                const deviceToken = localStorage.getItem('device_token');

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
                        nis: decodedText,
                        device_token: deviceToken // Kirim token perangkat (jika backend membutuhkannya)
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
                                try { html5QrCode.resume(); } catch(e){}
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
                                try { html5QrCode.resume(); } catch(e){}
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
