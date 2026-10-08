<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan Wajah Otomatis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .webcam-box {
            position: relative;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 3/4;
            border: 3px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); -webkit-transform: scaleX(-1); }
        #overlay { position: absolute; top:0; left:0; width:100%; height:100%; transform: scaleX(-1); -webkit-transform: scaleX(-1); }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 text-center">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="fas fa-camera me-2"></i> Scan Presensi Otomatis</span>
                    <a href="{{ route('student.face.index') }}" class="btn btn-sm btn-light fw-bold text-primary">Kelola Wajah</a>
                </div>
                <div class="card-body p-3">
                    
                    <!-- Status GPS -->
                    <div id="gps-status" class="alert alert-warning py-2 small mb-3">
                        <span class="spinner-border spinner-border-sm me-1"></span> Mengunci Lokasi GPS HP...
                    </div>

                    <!-- Status Face Recognition -->
                    <div id="status-msg" class="alert alert-info py-2 mb-3 small">
                        Menginisialisasi kamera & data wajah Anda...
                    </div>

                    <div class="webcam-box mb-3">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                    </div>

                    <canvas id="capture-canvas" style="display: none;"></canvas>
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
    const overlay = document.getElementById('overlay');
    const statusMsg = document.getElementById('status-msg');
    const gpsStatus = document.getElementById('gps-status');
    const captureCanvas = document.getElementById('capture-canvas');

    let faceMatcher = null;
    let currentLat = null;
    let currentLng = null;
    let isProcessing = false;
    let detectionLoopActive = false;

    // 1. Kunci Lokasi GPS HP
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                gpsStatus.className = 'alert alert-success py-2 small mb-3';
                gpsStatus.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i> GPS Terkunci (${currentLat.toFixed(4)}, ${currentLng.toFixed(4)})`;
            },
            (err) => {
                gpsStatus.className = 'alert alert-danger py-2 small mb-3';
                gpsStatus.innerText = "Gagal mengunci GPS! Mohon izinkan akses lokasi di HP Anda.";
            },
            { enableHighAccuracy: true }
        );
    }

    // 2. Load Model & Data Descriptor Siswa
    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
    ]).then(loadStudentDescriptors);

    async function loadStudentDescriptors() {
        try {
            const response = await fetch("{{ route('student.descriptors') }}");
            const data = await response.json();

            if (!data.descriptors || data.descriptors.length === 0) {
                statusMsg.className = 'alert alert-danger py-2 mb-3 small';
                statusMsg.innerText = "Anda belum mendaftarkan data wajah!";
                return;
            }

            const floatDescriptors = data.descriptors.map(d => new Float32Array(d));
            const labeledDescriptor = new faceapi.LabeledFaceDescriptors(data.label, floatDescriptors);

            // Threshold presisi tinggi
            faceMatcher = new faceapi.FaceMatcher([labeledDescriptor], 0.40);

            statusMsg.className = 'alert alert-success py-2 mb-3 small';
            statusMsg.innerText = "Sistem Aktif! Hahapkan wajah Anda ke kamera.";

            startCamera();
        } catch (err) {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal memuat data wajah dari server.";
        }
    }

    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } } })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(() => {
                statusMsg.className = 'alert alert-danger py-2 mb-3 small';
                statusMsg.innerText = "Izin kamera HP ditolak.";
            });
    }

    function takeScreenshot() {
        captureCanvas.width = video.videoWidth || 640;
        captureCanvas.height = video.videoHeight || 480;
        const ctx = captureCanvas.getContext('2d');
        ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
        return captureCanvas.toDataURL('image/jpeg', 0.8);
    }

    // 3. ASYNC LOOP PENDETEKSIAN OTOMATIS (AUTO-SCAN)
    video.addEventListener('play', () => {
        if (detectionLoopActive) return;
        detectionLoopActive = true;

        async function processFrame() {
            if (!video.srcObject) {
                detectionLoopActive = false;
                return;
            }

            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            if (displaySize.width > 0 && displaySize.height > 0) {
                faceapi.matchDimensions(overlay, displaySize);

                if (!isProcessing && faceMatcher) {
                    const detections = await faceapi.detectAllFaces(
                        video,
                        new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 })
                    ).withFaceLandmarks().withFaceDescriptors();

                    const resized = faceapi.resizeResults(detections, displaySize);
                    overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);

                    resized.forEach(det => {
                        const match = faceMatcher.findBestMatch(det.descriptor);
                        new faceapi.draw.DrawBox(det.detection.box, { label: match.toString() }).draw(overlay);

                        // EKSKUSI OTOMATIS: Jika cocok (match < 0.38) & GPS sudah terkunci
                        if (match.label !== 'unknown' && match.distance < 0.38) {
                            if (!currentLat || !currentLng) {
                                statusMsg.className = 'alert alert-warning py-2 mb-3 small';
                                statusMsg.innerText = "Wajah cocok! Menunggu lokasi GPS terkunci...";
                                return;
                            }

                            // Kunci proses agar tidak terjadi request ganda
                            isProcessing = true;
                            const screenshot = takeScreenshot();
                            submitAttendanceAuto(screenshot);
                        }
                    });
                }
            }

            // Loop per 200ms
            setTimeout(processFrame, 200);
        }

        processFrame();
    });

    // 4. Kirim Data Presensi ke Backend
    function submitAttendanceAuto(imageBase64) {
        Swal.fire({ title: 'Memverifikasi Presensi...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: "{{ route('student.scan.process') }}",
            type: "POST",
            data: {
                latitude: currentLat,
                longitude: currentLng,
                image: imageBase64
            },
            success: (res) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Presensi Berhasil!',
                    text: res.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    // Jeda 5 detik sebelum dapat melakukan scan lagi
                    setTimeout(() => { isProcessing = false; }, 5000);
                });
            },
            error: (err) => {
                let msg = err.responseJSON?.message || 'Gagal menyimpan presensi.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Presensi',
                    text: msg,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    setTimeout(() => { isProcessing = false; }, 3000);
                });
            }
        });
    }
</script>
</body>
</html>