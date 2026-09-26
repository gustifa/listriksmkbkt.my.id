@section('title')
    Scan Absensi & Izin
@endsection

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <!-- STATUS PERANGKAT -->
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
                                <label class="small text-muted">Daftarkan perangkat ini ke sistem:</label>
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
                        <small>Gerbang Sekolah / Pos Satpam</small>
                    </div>
                    <div class="text-center card-body bg-light">

                        <!-- PILIHAN MODE SCAN (FITUR BARU) -->
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

                        <!-- MENU PILIH KAMERA -->
                        <div class="mb-3 text-start">
                            <label for="camera-select" class="form-label fw-bold small">Pilih Kamera:</label>
                            <div class="input-group input-group-sm">
                                <select id="camera-select" class="form-select">
                                    <option value="" disabled selected>Memuat kamera...</option>
                                </select>
                                <button class="btn btn-success" id="btn-start"><i class="fas fa-power-off"></i></button>
                                <button class="btn btn-danger" id="btn-stop" disabled><i class="fas fa-stop"></i></button>
                            </div>
                        </div>

                        <!-- AREA SCANNER -->
                        <div id="reader" style="width: 100%; border: 2px solid #ddd; border-radius:8px; min-height: 300px; background: #000;"></div>

                        <div class="mt-3">
                            <p class="text-muted small" id="scan-instruction">Mode Absensi: Scan saat datang & pulang.</p>
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
            const html5QrCode = new Html5Qrcode("reader");
            let isScanning = false;

            // --- 0. Setup Audio ---
            const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
            const audioError = new Audio("{{ asset('audio/error.mp3') }}");

            function playSound(status) {
                let sound = (status === 'success') ? audioSuccess : audioError;
                sound.currentTime = 0;
                sound.play().catch(e => console.warn("Audio blocked"));
            }

            // --- 1. UI Handler Mode ---
            $('input[name="scan_mode"]').change(function() {
                if(this.value === 'daily') {
                    $('#scan-instruction').text("Mode Absensi: Scan saat datang & pulang sekolah.");
                } else {
                    $('#scan-instruction').html("<span class='text-danger fw-bold'>Mode Izin:</span> Scan untuk izin meninggalkan sekolah (Sakit/Dispen/Lainnya).");
                }
            });

            // --- 2. Logic Scan Success ---
            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning) return;
                html5QrCode.pause(); // Pause camera

                let mode = $('input[name="scan_mode"]:checked').val();
                let deviceToken = localStorage.getItem('device_token');

                if (mode === 'daily') {
                    // --- MODE 1: ABSENSI HARIAN ---
                    processDailyAttendance(decodedText, deviceToken);
                } else {
                    // --- MODE 2: IZIN KELUAR ---
                    processPermission(decodedText, deviceToken);
                }
            }

            // --- FUNGSI ABSENSI HARIAN ---
            function processDailyAttendance(nis, token) {
                Swal.fire({
                    title: 'Memproses Absensi...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('daily.store') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis, device_token: token },
                    success: function(res) {
                        playSound(res.status === 'success' ? 'success' : 'error');
                        Swal.fire({
                            title: res.message,
                            text: res.student,
                            icon: res.status === 'success' ? 'success' : 'error',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => safeResume());
                    },
                    error: function(xhr) {
                        handleError(xhr);
                    }
                });
            }

            // --- FUNGSI IZIN KELUAR (FITUR BARU) ---
            function processPermission(nis, token) {
                Swal.fire({
                    title: 'Cek Status Siswa...',
                    didOpen: () => Swal.showLoading()
                });

                // 1. Cek Status Izin Terakhir
                $.ajax({
                    url: "{{ route('izin.check') }}", // Route baru
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis },
                    success: function(res) {
                        if (res.status === 'active_permission') {
                            // SISWA SEDANG DILUAR -> PROSES KEMBALI
                            confirmReturn(res.data);
                        } else if (res.status === 'can_leave') {
                            // SISWA DI SEKOLAH -> PROSES KELUAR
                            inputReason(nis, res.student);
                        } else {
                            // Error (Misal: Belum absen masuk)
                            playSound('error');
                            Swal.fire('Gagal', res.message, 'error').then(() => safeResume());
                        }
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            function inputReason(nis, student) {
                Swal.fire({
                    title: 'Izin Meninggalkan Sekolah',
                    html: `Siswa: <b>${student.name}</b><br>Kelas: ${student.classroom}`,
                    input: 'text',
                    inputLabel: 'Keperluan / Alasan',
                    inputPlaceholder: 'Contoh: Sakit, Dispen Lomba, Urusan Keluarga...',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Izin & Cetak',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) return 'Alasan wajib diisi!'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        savePermission(nis, result.value);
                    } else {
                        safeResume();
                    }
                });
            }

            function savePermission(nis, reason) {
                $.ajax({
                    url: "{{ route('izin.store') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis, reason: reason },
                    success: function(res) {
                        playSound('success');
                        Swal.fire({
                            icon: 'success',
                            title: 'Izin Tercatat!',
                            html: `Silakan ambil surat izin.<br><br>
                                   <a href="/izin/print/${res.id}" target="_blank" class="btn btn-primary btn-lg">
                                   <i class="fas fa-print"></i> CETAK SURAT IZIN</a>`,
                            showConfirmButton: true,
                            confirmButtonText: 'Tutup & Lanjut'
                        }).then(() => safeResume());
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            function confirmReturn(permissionData) {
                Swal.fire({
                    title: 'Siswa Kembali?',
                    html: `Siswa <b>${permissionData.student.name}</b> tercatat izin keluar.<br>
                           Alasan: "${permissionData.reason}"<br>
                           Jam Keluar: ${permissionData.time_out}<br><br>
                           Apakah siswa sudah kembali ke sekolah?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Catat Kembali',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('izin.return') }}",
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}", id: permissionData.id },
                            success: function(res) {
                                playSound('success');
                                Swal.fire('Berhasil', 'Siswa tercatat kembali ke sekolah.', 'success')
                                    .then(() => safeResume());
                            }
                        });
                    } else {
                        safeResume();
                    }
                });
            }

            // --- Helper Functions ---
            function safeResume() {
                try { html5QrCode.resume(); } catch(e){}
            }

            function handleError(xhr) {
                playSound('error');
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Error', msg, 'error').then(() => safeResume());
            }

            // --- SETUP KAMERA (Sama seperti sebelumnya) ---
            Html5Qrcode.getCameras().then(devices => {
                const cameraSelect = $('#camera-select');
                cameraSelect.empty();
                if (devices && devices.length) {
                    devices.forEach(dev => cameraSelect.append(new Option(dev.label || `Kamera ${dev.id}`, dev.id)));
                } else {
                    cameraSelect.append('<option disabled>No camera</option>');
                }
            });

            $('#btn-start').click(() => {
                const id = $('#camera-select').val();
                if(!id) return Swal.fire('Error', 'Pilih kamera!', 'error');
                if(isScanning) return;

                html5QrCode.start(id, { fps: 10, qrbox: 250 }, onScanSuccess)
                .then(() => {
                    isScanning = true;
                    $('#btn-start').prop('disabled', true);
                    $('#btn-stop').prop('disabled', false);
                    $('#camera-select').prop('disabled', true);
                    $('input[name="scan_mode"]').prop('disabled', true); // Lock mode saat scan
                });
            });

            $('#btn-stop').click(() => {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    $('#btn-start').prop('disabled', false);
                    $('#btn-stop').prop('disabled', true);
                    $('#camera-select').prop('disabled', false);
                    $('input[name="scan_mode"]').prop('disabled', false);
                    html5QrCode.clear();
                });
            });

            // --- Logic Registrasi Device (Sama seperti sebelumnya) ---
            // (Kode checkDeviceRegistration, btn-register-device ada di sini, disingkat agar muat)
            // Pastikan Anda menyalin logika localStorage dari kode sebelumnya jika belum ada di file asli.
        });
    </script>
</x-app-layout>
