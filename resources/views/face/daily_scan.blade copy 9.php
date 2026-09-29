<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png')}}" type="image/png"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">

    <title>SISFO SMK | Monitor Gerbang (Mobile Optimized)</title>

    <style>
        .video-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* Tanda respon hijau pada kamera saat wajah terdeteksi */
        .video-container.active-detect {
            border-color: #198754 !important;
            box-shadow: 0 0 20px rgba(25, 135, 84, 0.8) !important;
        }

        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); }
        #capture-canvas { display: none; }
        .btn-group .btn { font-size: 0.85rem; padding: 10px 5px; }
    </style>
</head>

<body class="bg-light">
    <div class="page-content">
        <div class="row justify-content-center pt-2">
            <div class="col-12 col-md-8 text-center">
                <div class="shadow card border-0">
                    <div class="text-white card-header bg-success d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold small"><i class="fas fa-bolt me-1"></i> GERBANG SMK</span>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold py-0">DASHBOARD</a>
                    </div>
                    <div class="card-body px-2">

                        <!-- Pilihan Kamera & Tombol Toggle Kamera -->
                        <div class="mb-2 d-flex justify-content-center align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-video"></i></span>
                                <select id="camera-select" class="form-select border-start-0 shadow-none">
                                    <option value="">Mencari Kamera...</option>
                                </select>
                            </div>
                            <!-- Tombol Matikan / Hidupkan Kamera -->
                            <button id="btn-toggle-cam" class="btn btn-sm btn-danger text-nowrap fw-bold" type="button" disabled>
                                <i class="fas fa-video-slash me-1"></i> Matikan Kamera
                            </button>
                        </div>

                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_harian" value="harian" checked>
                                <label class="btn btn-outline-primary fw-bold" for="mode_harian">HARIAN</label>
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_izin" value="izin_keluar">
                                <label class="btn btn-outline-warning fw-bold text-dark" for="mode_izin">IZIN</label>
                            </div>
                        </div>

                        <div id="status-loading" class="alert alert-warning py-1 mb-2 small">
                            <span class="spinner-border spinner-border-sm me-1"></span> Loading AI Models...
                        </div>

                        <!-- Kontainer Kamera dengan Efek Border Hijau -->
                        <div class="video-container" id="video-box">
                            <video id="video" autoplay muted playsinline></video>
                            <canvas id="overlay"></canvas>
                        </div>

                        <canvas id="capture-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        const video = document.getElementById('video');
        const cameraSelect = document.getElementById('camera-select');
        const statusMsg = document.getElementById('status-loading');
        const captureCanvas = document.getElementById('capture-canvas');
        const btnToggleCam = document.getElementById('btn-toggle-cam');
        const videoBox = document.getElementById('video-box');

        let faceMatcher = null;
        let isProcessing = false;
        let currentStream = null;
        let isCameraOn = false;

        // Inisialisasi Toast SweetAlert
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        // Efek Suara Bip Sintetis
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playBeep(type = 'success') {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            if (type === 'success') {
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
            } else {
                osc.frequency.setValueAtTime(300, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
            }
        }

        // Fungsi Tanda Respon Hijau
        function triggerSuccessUI() {
            playBeep('success');
            videoBox.classList.add('active-detect');
        }

        function resetSuccessUI() {
            videoBox.classList.remove('active-detect');
            isProcessing = false;
        }

        // 1. LOAD MODEL VERSI TINY DENGAN VALIDASI SWEETALERT
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(initSystem).catch(err => {
            console.error("Gagal memuat berkas model AI:", err);
            statusMsg.className = 'alert alert-danger py-1 small';
            statusMsg.innerText = "Error: Berkas model AI tidak ditemukan.";

            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Model AI',
                text: 'Berkas model AI tidak dapat diakses di folder public/models.',
                confirmButtonColor: '#dc3545'
            });
        });

        async function initSystem() {
            try {
                try {
                    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    tempStream.getTracks().forEach(track => track.stop());
                } catch (camErr) {
                    console.warn("Akses kamera belum disetujui:", camErr);
                }

                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');

                cameraSelect.innerHTML = '';
                if (videoDevices.length === 0) {
                    cameraSelect.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                    btnToggleCam.disabled = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kamera Tidak Ditemukan',
                        text: 'Sistem tidak dapat menemukan webcam.',
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    videoDevices.forEach((d, i) => {
                        const opt = document.createElement('option');
                        opt.value = d.deviceId;
                        opt.text = d.label || `Kamera ${i + 1}`;
                        cameraSelect.appendChild(opt);
                    });
                    btnToggleCam.disabled = false;
                }

                const response = await fetch("{{ route('face.descriptors.all') }}");
                if (!response.ok) {
                    throw new Error(`Gagal menghubungi server (Status: ${response.status}).`);
                }
                const data = await response.json();

                if (!Array.isArray(data) || data.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Wajah Kosong',
                        text: 'Belum ada data wajah siswa yang terdaftar di database.',
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    const labeledDescriptors = data.map(d => {
                        let rawDescriptor = d.descriptor;
                        if (typeof rawDescriptor === 'string') {
                            rawDescriptor = JSON.parse(rawDescriptor);
                        }
                        return new faceapi.LabeledFaceDescriptors(
                            d.label,
                            [new Float32Array(rawDescriptor)]
                        );
                    });

                    if (labeledDescriptors.length > 0) {
                        faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.45);
                    }
                }

                statusMsg.className = 'alert alert-success py-1 small';
                statusMsg.innerText = "Sistem Siap!";

                if (cameraSelect.value) {
                    startCamera(cameraSelect.value);
                }
            } catch (err) {
                console.error("Detail Error Init System:", err);
                statusMsg.className = 'alert alert-danger py-1 small';
                statusMsg.innerText = `Error: ${err.message || "Gagal muat AI."}`;
            }
        }

        cameraSelect.addEventListener('change', () => {
            if (isCameraOn && cameraSelect.value) {
                startCamera(cameraSelect.value);
            }
        });

        btnToggleCam.addEventListener('click', () => {
            if (isCameraOn) {
                stopCamera();
            } else {
                if (cameraSelect.value) {
                    startCamera(cameraSelect.value);
                }
            }
        });

        function startCamera(deviceId) {
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());
            const constraints = { video: { deviceId: deviceId ? { exact: deviceId } : undefined, width: { ideal: 640 }, height: { ideal: 480 } } };

            navigator.mediaDevices.getUserMedia(constraints).then(s => {
                currentStream = s;
                video.srcObject = s;
                isCameraOn = true;

                btnToggleCam.className = 'btn btn-sm btn-danger text-nowrap fw-bold';
                btnToggleCam.innerHTML = '<i class="fas fa-video-slash me-1"></i> Matikan Kamera';

                statusMsg.className = 'alert alert-success py-1 small';
                statusMsg.innerText = "Sistem Siap!";
            }).catch(err => {
                console.error("Gagal membuka stream kamera:", err);
            });
        }

        function stopCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            video.srcObject = null;
            isCameraOn = false;

            const overlay = document.getElementById('overlay');
            if (overlay) {
                const ctx = overlay.getContext('2d');
                ctx.clearRect(0, 0, overlay.width, overlay.height);
            }

            btnToggleCam.className = 'btn btn-sm btn-success text-nowrap fw-bold';
            btnToggleCam.innerHTML = '<i class="fas fa-video me-1"></i> Hidupkan Kamera';

            statusMsg.className = 'alert alert-secondary py-1 small';
            statusMsg.innerText = "Kamera Dimatikan.";
        }

        function takeScreenshot() {
            captureCanvas.width = 240;
            captureCanvas.height = 180;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, 240, 180);
            return captureCanvas.toDataURL('image/jpeg', 0.3);
        }

        video.addEventListener('play', () => {
            const overlay = document.getElementById('overlay');
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(overlay, displaySize);

            setInterval(async () => {
                if(isProcessing || !faceMatcher || !isCameraOn) return;

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 }))
                    .withFaceLandmarks().withFaceDescriptors();

                const resized = faceapi.resizeResults(detections, displaySize);
                overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);

                resized.forEach(det => {
                    const match = faceMatcher.findBestMatch(det.descriptor);
                    if (match.label !== 'unknown' && match.distance < 0.45) {
                        isProcessing = true;

                        // Menyalakan Tanda Respon Hijau & Suara Bip
                        triggerSuccessUI();

                        const screenshot = takeScreenshot();
                        const [nis, name] = match.label.split(' - ');
                        handleAction(nis, name, screenshot);
                    }
                });
            }, 500);
        });

        function handleAction(nis, name, image) {
            let mode = $('input[name="mode_absen"]:checked').val();
            if (mode === 'izin_keluar') {
                checkPermission(nis, name, image);
            } else {
                submitAttendance(nis, name, image);
            }
        }

        function submitAttendance(nis, name, image) {
            Toast.fire({
                icon: 'info',
                title: `Memproses: ${name}`
            });

            $.ajax({
                url: "{{ route('daily.store') }}",
                type: "POST",
                data: { nis: nis, mode: 'harian', image: image },
                success: function(res) {
                    Toast.fire({
                        icon: 'success',
                        title: `Absen Berhasil!\nSelamat Datang, ${name}`
                    });
                    setTimeout(resetSuccessUI, 1200);
                },
                error: function(xhr) {
                    playBeep('error');
                    let msg = xhr.responseJSON?.message || "Gagal melakukan absensi.";
                    Toast.fire({
                        icon: 'error',
                        title: msg
                    });
                    setTimeout(resetSuccessUI, 1500);
                }
            });
        }

        function checkPermission(nis, name, image) {
            Swal.fire({
                title: 'Memeriksa Izin...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('izin.check') }}",
                type: "POST",
                data: { nis: nis },
                success: function(res) {
                    if (res.status === 'active_permission') {
                        confirmReturn(res.data, image);
                    } else if (res.status === 'can_leave') {
                        inputReason(nis, name, image);
                    } else {
                        Swal.fire({
                            title: 'Informasi',
                            text: res.message,
                            icon: 'info',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(resetSuccessUI);
                    }
                },
                error: function(xhr) {
                    playBeep('error');
                    let msg = xhr.responseJSON?.message || "Gagal memeriksa status izin.";
                    Swal.fire({ title: 'Gagal', text: msg, icon: 'error', confirmButtonColor: '#dc3545' })
                        .then(resetSuccessUI);
                }
            });
        }

        function inputReason(nis, name, image) {
            Swal.fire({
                title: 'Alasan Keluar Sekolah',
                html: `Siswa: <b>${name}</b> (${nis})`,
                input: 'text',
                inputPlaceholder: 'Ketik alasan izin keluar...',
                showCancelButton: true,
                confirmButtonText: 'Simpan & Cetak',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#dc3545',
                allowOutsideClick: false,
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'Alasan keluar sekolah wajib diisi!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan Izin...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: "{{ route('izin.store') }}",
                        type: "POST",
                        data: { nis: nis, reason: result.value, image: image },
                        success: (res) => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Izin Disimpan',
                                html: `<p>Izin keluar untuk <b>${name}</b> berhasil dibuat.</p><a href="{{ url('izin/print') }}/${res.id}" target="_blank" class="btn btn-primary mt-2"><i class="fas fa-print"></i> CETAK SURAT IZIN</a>`,
                                showConfirmButton: true,
                                confirmButtonText: 'Selesai'
                            }).then(resetSuccessUI);
                        },
                        error: (xhr) => {
                            playBeep('error');
                            let msg = xhr.responseJSON?.message || "Gagal menyimpan izin.";
                            Swal.fire({ title: 'Gagal', text: msg, icon: 'error', confirmButtonColor: '#dc3545' })
                                .then(resetSuccessUI);
                        }
                    });
                } else {
                    resetSuccessUI();
                }
            });
        }

        function confirmReturn(data, image) {
            Swal.fire({
                title: 'Konfirmasi Kembali',
                html: `Siswa <b>${data.student.name}</b> tercatat sedang izin keluar.<br>Apakah siswa sudah kembali ke sekolah?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kembali',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#dc3545',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: "{{ route('izin.return') }}",
                        type: "POST",
                        data: { id: data.id, image: image },
                        success: () => {
                            Swal.fire({
                                title: 'Selesai',
                                text: `Status izin ${data.student.name} telah ditutup.`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(resetSuccessUI);
                        },
                        error: (xhr) => {
                            playBeep('error');
                            let msg = xhr.responseJSON?.message || "Gagal memperbarui status kembali.";
                            Swal.fire({ title: 'Gagal', text: msg, icon: 'error', confirmButtonColor: '#dc3545' })
                                .then(resetSuccessUI);
                        }
                    });
                } else {
                    resetSuccessUI();
                }
            });
        }
    </script>
</body>
</html>
