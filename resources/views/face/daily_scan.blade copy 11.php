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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">

    <title>SISFO SMK | Monitor Gerbang</title>

    <style>
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 15px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); }
        #capture-canvas { display: none; }

        .camera-controls {
            max-width: 640px;
            margin: 0 auto 15px auto;
        }
    </style>
</head>

<body class="bg-light">
    <div class="page-content">
        <div class="row justify-content-center pt-4">
            <div class="col-12 col-md-10 col-lg-8 text-center">
                <div class="shadow card border-0">
                    <div class="text-white card-header bg-success d-flex justify-content-between align-items-center py-3">
                        <span class="fw-bold"><i class="fas fa-camera me-2"></i> MONITOR GERBANG</span>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold">DASHBOARD</a>
                    </div>
                    <div class="card-body">
                        <!-- Mode Absensi -->
                        <div class="mb-3">
                            <div class="btn-group w-75" role="group">
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_harian" value="harian" checked>
                                <label class="btn btn-outline-primary py-2 fw-bold" for="mode_harian">ABSENSI HARIAN</label>
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_izin" value="izin_keluar">
                                <label class="btn btn-outline-warning py-2 fw-bold text-dark" for="mode_izin">IZIN KELUAR</label>
                            </div>
                        </div>

                        <!-- Fitur Pilih Kamera & On/Off Kamera -->
                        <div class="camera-controls">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-video text-secondary"></i></span>
                                <select id="camera-select" class="form-select shadow-none">
                                    <option value="">Mencari Kamera...</option>
                                </select>
                                <button id="btn-toggle-camera" class="btn btn-danger" type="button" title="Matikan / Hidupkan Kamera">
                                    <i id="toggle-icon" class="fas fa-video-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div id="status-loading" class="alert alert-warning py-2 mb-3">
                            <span class="spinner-border spinner-border-sm me-2"></span> Memuat Model Presisi Tinggi...
                        </div>

                        <div class="video-container">
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
        const statusMsg = document.getElementById('status-loading');
        const captureCanvas = document.getElementById('capture-canvas');
        const cameraSelect = document.getElementById('camera-select');
        const btnToggleCamera = document.getElementById('btn-toggle-camera');
        const toggleIcon = document.getElementById('toggle-icon');

        let faceMatcher = null;
        let isProcessing = false;
        let currentStream = null;
        let isCameraOn = true;
        let detectionLoopActive = false;

        // 1. Menggunakan SsdMobilenetv1 untuk Akurasi & Presisi Tinggi
        Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(loadDescriptors);

        async function loadDescriptors() {
            try {
                const response = await fetch("{{ route('face.descriptors.all') }}");
                const data = await response.json();
                if(data.length === 0) {
                    statusMsg.className = 'alert alert-danger';
                    statusMsg.innerText = "Data wajah belum terdaftar!";
                    return;
                }

                const labeledDescriptors = data.map(d => {
                    // Mendukung jika descriptor dikirim sebagai arraytunggal maupun array multidimensi
                    const descriptors = Array.isArray(d.descriptor[0])
                        ? d.descriptor.map(desc => new Float32Array(desc))
                        : [new Float32Array(d.descriptor)];
                    return new faceapi.LabeledFaceDescriptors(d.label, descriptors);
                });

                // Perketat FaceMatcher ke threshold 0.40
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.40);

                statusMsg.className = 'alert alert-success';
                statusMsg.innerText = "Sistem Presisi Tinggi Aktif! Menunggu Wajah...";

                await initCameraDevices();
            } catch (err) {
                statusMsg.className = 'alert alert-danger';
                statusMsg.innerText = "Gagal memuat data wajah.";
            }
        }

        async function initCameraDevices() {
            try {
                const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                tempStream.getTracks().forEach(track => track.stop());

                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');

                cameraSelect.innerHTML = '';
                if (videoDevices.length === 0) {
                    cameraSelect.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                    return;
                }

                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    let label = device.label || `Kamera ${index + 1}`;

                    if (label.toLowerCase().includes('back') || label.toLowerCase().includes('rear')) {
                        label = `📷 Kamera Belakang (${label})`;
                    } else if (label.toLowerCase().includes('front') || label.toLowerCase().includes('facing')) {
                        label = `🤳 Kamera Depan (${label})`;
                    }

                    option.text = label;
                    cameraSelect.appendChild(option);
                });

                startCamera(cameraSelect.value);
            } catch (err) {
                statusMsg.className = 'alert alert-danger';
                statusMsg.innerText = "Izin kamera ditolak atau tidak tersedia.";
            }
        }

        function startCamera(deviceId = null) {
            stopCameraStream();

            const constraints = {
                video: {
                    deviceId: deviceId ? { exact: deviceId } : undefined,
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    frameRate: { ideal: 30, max: 30 }
                }
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    currentStream = stream;
                    video.srcObject = stream;
                    isCameraOn = true;
                    updateCameraButtonUI();
                })
                .catch(err => {
                    statusMsg.className = 'alert alert-danger';
                    statusMsg.innerText = "Kamera gagal diaktifkan.";
                });
        }

        function stopCameraStream() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
        }

        function updateCameraButtonUI() {
            if (isCameraOn) {
                btnToggleCamera.className = 'btn btn-danger';
                toggleIcon.className = 'fas fa-video-slash';
                btnToggleCamera.title = 'Matikan Kamera';
            } else {
                btnToggleCamera.className = 'btn btn-success';
                toggleIcon.className = 'fas fa-video';
                btnToggleCamera.title = 'Hidupkan Kamera';
            }
        }

        cameraSelect.addEventListener('change', () => {
            if (cameraSelect.value) {
                startCamera(cameraSelect.value);
            }
        });

        btnToggleCamera.addEventListener('click', () => {
            if (isCameraOn) {
                stopCameraStream();
                video.srcObject = null;
                isCameraOn = false;
                updateCameraButtonUI();
                statusMsg.className = 'alert alert-secondary';
                statusMsg.innerText = "Kamera Dimatikan.";
            } else {
                startCamera(cameraSelect.value);
                statusMsg.className = 'alert alert-success';
                statusMsg.innerText = "Sistem Presisi Tinggi Aktif! Menunggu Wajah...";
            }
        });

        function takeScreenshot() {
            captureCanvas.width = video.videoWidth || 640;
            captureCanvas.height = video.videoHeight || 480;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
            return captureCanvas.toDataURL('image/jpeg', 0.8);
        }

        // ASYNC LOOP PENDETEKSIAN DENGAN ATURAN KETAT
        video.addEventListener('play', () => {
            const overlay = document.getElementById('overlay');

            if (detectionLoopActive) return;
            detectionLoopActive = true;

            async function processFrame() {
                if (!isCameraOn || !video.srcObject) {
                    detectionLoopActive = false;
                    return;
                }

                const displaySize = { width: video.clientWidth, height: video.clientHeight };
                if (displaySize.width > 0 && displaySize.height > 0) {
                    faceapi.matchDimensions(overlay, displaySize);

                    if (!isProcessing && faceMatcher) {
                        // Menggunakan SsdMobilenetv1Options dengan minConfidence 0.5 (Tinggi Presisi)
                        const detections = await faceapi.detectAllFaces(
                            video,
                            new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 })
                        ).withFaceLandmarks().withFaceDescriptors();

                        const resized = faceapi.resizeResults(detections, displaySize);
                        overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);

                        resized.forEach(det => {
                            const match = faceMatcher.findBestMatch(det.descriptor);

                            // Visualisasi bounding box
                            new faceapi.draw.DrawBox(det.detection.box, { label: match.toString() }).draw(overlay);

                            // Bounding box hijau jika valid (< 0.38), merah jika samar/unknown
                            const isMatched = match.label !== 'unknown' && match.distance < 0.38;

                            if (isMatched) {
                                isProcessing = true;
                                const screenshot = takeScreenshot();
                                const [nis, name] = match.label.split(' - ');
                                handleAction(nis, name, screenshot);
                            }
                        });
                    }
                }

                setTimeout(processFrame, 200);
            }

            processFrame();
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
            Swal.fire({ title: 'Memproses Presisi...', text: name, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ route('daily.store') }}",
                type: "POST",
                data: { nis: nis, mode: 'harian', image: image },
                success: function(res) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: res.message + " (" + name + ")",
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { isProcessing = false; });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || "Gagal Absen";
                    Swal.fire({
                        title: 'Gagal',
                        text: msg,
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => { isProcessing = false; });
                }
            });
        }

        function checkPermission(nis, name, image) {
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
                        Swal.fire({ title: 'Info', text: res.message, icon: 'info', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                    }
                },
                error: function() {
                    Swal.fire({ title: 'Error', text: 'Sistem Error', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                }
            });
        }

        function inputReason(nis, name, image) {
            Swal.fire({
                title: 'Alasan Keluar',
                text: name,
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    savePermission(nis, result.value, image);
                } else {
                    isProcessing = false;
                }
            });
        }

        function savePermission(nis, reason, image) {
            $.ajax({
                url: "{{ route('izin.store') }}",
                type: "POST",
                data: { nis: nis, reason: reason, image: image },
                success: (res) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Izin Disimpan',
                        html: `<a href="{{ url('izin/print') }}/${res.id}" target="_blank" class="btn btn-primary mt-3">CETAK SURAT IZIN</a>`,
                        showConfirmButton: true,
                        confirmButtonText: 'Selesai'
                    }).then(() => isProcessing = false);
                },
                error: function() {
                    Swal.fire({ title: 'Gagal', text: 'Gagal simpan izin', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                }
            });
        }

        function confirmReturn(data, image) {
            Swal.fire({
                title: 'Siswa Kembali?',
                text: `${data.student.name} ingin masuk?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Masuk',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.return') }}",
                        type: "POST",
                        data: { id: data.id, image: image },
                        success: () => {
                            Swal.fire({ title: 'Berhasil', text: 'Siswa masuk kembali', icon: 'success', timer: 2000, showConfirmButton: false }).then(() => isProcessing = false);
                        },
                        error: function() {
                            Swal.fire({ title: 'Gagal', text: 'Gagal update status', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                        }
                    });
                } else {
                    isProcessing = false;
                }
            });
        }
    </script>
</body>
</html>
