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

                        <div id="reader" style="width: 100%; border: 2px solid #3b82f6; border-radius:12px; min-height: 320px; background: #000; overflow:hidden;"></div>
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
            let isProcessing = false;

            const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
            const audioError = new Audio("{{ asset('audio/error.mp3') }}");

            function playSound(status) {
                let sound = (status === 'success') ? audioSuccess : audioError;
                sound.currentTime = 0;
                sound.play().catch(e => console.warn("Audio blocked"));
            }

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

            function safeResume() {
                isProcessing = false;
                try { if(isScanning) html5QrCode.resume(); } catch(e){}
            }

            function handleError(xhr) {
                playSound('error');
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire({
                    title: 'Error',
                    text: msg,
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => safeResume());
            }

            // --- ON SCAN SUCCESS ---
            function onScanSuccess(decodedText, decodedResult) {
                if (!isScanning || isProcessing) return;
                isProcessing = true;
                
                const capturedImage = captureFace(); 
                html5QrCode.pause();

                let mode = $('input[name="scan_mode"]:checked').val();
                let deviceToken = localStorage.getItem('device_token') || 'GATE-BK';

                if (mode === 'daily') {
                    processDailyAttendance(decodedText, deviceToken, capturedImage);
                } else {
                    processPermission(decodedText, deviceToken, capturedImage);
                }
            }

            // --- 1. PROSES ABSENSI HARIAN ---
            function processDailyAttendance(nis, token, image) {
                Swal.fire({ title: 'Memproses Absensi...', didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ route('daily.store') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis, device_token: token, image: image },
                    success: function(res) {
                        const isSuccess = (res.status === 'success');
                        playSound(isSuccess ? 'success' : 'error');
                        Swal.fire({
                            title: isSuccess ? 'BERHASIL' : 'GAGAL',
                            text: res.message + (res.student ? ' - ' + res.student.name : ''),
                            icon: isSuccess ? 'success' : 'error',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => safeResume());
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            // --- 2. PROSES IZIN (KELUAR/MASUK) ---
            function processPermission(nis, token, image) {
                Swal.fire({ title: 'Cek Status Izin...', didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ route('izin.check') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis },
                    success: function(res) {
                        if (res.status === 'active_permission') {
                            confirmReturn(res.data, image);
                        } else if (res.status === 'can_leave') {
                            inputReason(nis, res.student, image);
                        } else {
                            playSound('error');
                            Swal.fire({ title: 'Gagal', text: res.message, icon: 'error', timer: 3000, showConfirmButton: false })
                                .then(() => safeResume());
                        }
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            function inputReason(nis, student, image) {
                Swal.fire({
                    title: 'Izin Keluar Sekolah',
                    html: `Siswa: <b>${student.name}</b><br>Kelas: ${student.classroom}`,
                    input: 'text',
                    inputLabel: 'Alasan Keluar',
                    inputPlaceholder: 'Contoh: Sakit, Urusan Keluarga...',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan & Keluar',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => { if (!value) return 'Alasan harus diisi!' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        savePermission(nis, result.value, image);
                    } else {
                        safeResume();
                    }
                });
            }

            function savePermission(nis, reason, image) {
                $.ajax({
                    url: "{{ route('izin.store') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", nis: nis, reason: reason, image: image },
                    success: function(res) {
                        playSound('success');
                        // KHUSUS SUKSES IZIN: Tampilkan tombol cetak, JANGAN pakai timer
                        Swal.fire({
                            icon: 'success',
                            title: 'Izin Berhasil!',
                            html: `
                                <div class="mt-2">
                                    <p>Siswa: <b>${res.student_name}</b></p>
                                    <a href="/izin/print/${res.id}" target="_blank" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-print"></i> CETAK SURAT IZIN
                                    </a>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: 'Tutup & Scan Lagi',
                            confirmButtonColor: '#28a745',
                            allowOutsideClick: false
                        }).then(() => safeResume());
                    },
                    error: function(xhr) { handleError(xhr); }
                });
            }

            function confirmReturn(permissionData, image) {
                Swal.fire({
                    title: 'Siswa Kembali?',
                    html: `Siswa: <b>${permissionData.student.name}</b><br>Alasan: ${permissionData.reason}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Masuk',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('izin.return') }}",
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}", id: permissionData.id, image: image },
                            success: function(res) {
                                playSound('success');
                                Swal.fire({ title: 'Berhasil', text: 'Siswa kembali masuk.', icon: 'success', timer: 3000, showConfirmButton: false })
                                    .then(() => safeResume());
                            },
                            error: function(xhr) { handleError(xhr); }
                        });
                    } else {
                        safeResume();
                    }
                });
            }

            // --- KAMERA SETUP ---
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
                    $('#btn-start').prop('disabled', true); $('#btn-stop').prop('disabled', false);
                    $('#device-name-display').text('AKTIF').addClass('text-success');
                });
            });

            $('#btn-stop').click(() => {
                html5QrCode.stop().then(() => {
                    isScanning = false; isProcessing = false;
                    $('#btn-start').prop('disabled', false); $('#btn-stop').prop('disabled', true);
                    $('#device-name-display').text('NON-AKTIF').removeClass('text-success');
                });
            });
        });
    </script>
</x-app-layout>