<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Wajah Mandiri - SISFO SMK</title>

    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }
        .webcam-box {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 3/4;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 4px solid #fff;
        }
        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #overlay { position: absolute; top:0; left:0; width:100%; height:100%; transform: scaleX(-1); }
        #capture-canvas { display: none; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 text-center">

                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">
                        <h5 class="fw-bold text-primary mb-1"><i class="fas fa-user-check me-2"></i>Absensi Mandiri</h5>
                        <p class="text-muted small mb-0">{{ $student->name }} ({{$student->nis }})</p>
                    </div>
                </div>

                <!-- Status Geolocation & Jarak -->
                <div id="geo-status" class="alert alert-info py-2 small fw-bold mb-3">
                    <i class="fas fa-spinner fa-spin me-1"></i> Mengambil Lokasi GPS...
                </div>

                <div class="webcam-box mb-3">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                </div>

                <canvas id="capture-canvas"></canvas>

                <button id="btn-scan" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow" disabled>
                    <i class="fas fa-camera me-2"></i> SCAN WAJAH SEKARANG
                </button>

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
        const captureCanvas = document.getElementById('capture-canvas');
        const geoStatus = document.getElementById('geo-status');
        const btnScan = document.getElementById('btn-scan');

        let userLat = null;
        let userLng = null;
        let faceDescriptor = null;
        let isModelLoaded = false;

        // 1. Dapatkan Geolocation HP
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;
                    geoStatus.className = 'alert alert-success py-2 small fw-bold mb-3';
                    geoStatus.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i> Lokasi GPS Terdeteksi!`;
                    checkReady();
                },
                (err) => {
                    geoStatus.className = 'alert alert-danger py-2 small fw-bold mb-3';
                    geoStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Akses GPS Ditolak! Harap aktifkan Lokasi/GPS HP Anda.`;
                },
                { enableHighAccuracy: true }
            );
        } else {
            geoStatus.innerHTML = 'Browser Anda tidak mendukung Geolocation.';
        }

        // 2. Load Model Face API
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(() => {
            isModelLoaded = true;
            startCamera();
            checkReady();
        });

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 480, height: 640 } })
                .then(stream => { video.srcObject = stream; })
                .catch(err => {
                    Swal.fire('Error', 'Gagal membuka kamera HP.', 'error');
                });
        }

        function checkReady() {
            if (userLat && userLng && isModelLoaded) {
                btnScan.disabled = false;
            }
        }

        function takeScreenshot() {
            captureCanvas.width = video.videoWidth || 480;
            captureCanvas.height = video.videoHeight || 640;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
            return captureCanvas.toDataURL('image/jpeg', 0.8);
        }

        btnScan.addEventListener('click', async () => {
            btnScan.disabled = true;
            Swal.fire({ title: 'Mendeteksi Wajah...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const detections = await faceapi.detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
            ).withFaceLandmarks().withFaceDescriptor();

            if (!detections) {
                Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah Anda terlihat jelas pada kamera!', 'warning');
                btnScan.disabled = false;
                return;
            }

            const imageBase64 = takeScreenshot();

            // Kirim Data Absensi ke Server
            $.ajax({
                url: "{{ route('student.scan.store') }}",
                type: "POST",
                data: {
                    latitude: userLat,
                    longitude: userLng,
                    image: imageBase64
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal Melakukan Absensi.';
                    Swal.fire('Gagal Absen', msg, 'error');
                    btnScan.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
