<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Mandiri Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .webcam-box {
            position: relative;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 3/4;
        }
        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        overlay { position: absolute; top:0; left:0; width:100%; height:100%; transform: scaleX(-1); }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="card shadow border-0 text-center">
        <div class="card-header bg-primary text-white py-3 fw-bold">
            <i class="fas fa-user-check me-2"></i> Presensi Mandiri Siswa
        </div>
        <div class="card-body p-3">
            
            <!-- Info Lokasi GPS -->
            <div id="gps-status" class="alert alert-warning py-2 small mb-3">
                <span class="spinner-border spinner-border-sm me-1"></span> Mengunci Lokasi GPS...
            </div>

            <div class="webcam-box mb-3">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="overlay"></canvas>
            </div>

            <button id="btn-scan" class="btn btn-success btn-lg w-100 py-3 fw-bold" disabled>
                <i class="fas fa-camera me-2"></i> SCAN & PRESENSI MASUK
            </button>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentLat = null;
    let currentLng = null;
    const gpsStatus = document.getElementById('gps-status');
    const btnScan = document.getElementById('btn-scan');
    const video = document.getElementById('video');

    // 1. Dapatkan Titik GPS HP Siswa
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                gpsStatus.className = 'alert alert-success py-2 small mb-3';
                gpsStatus.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i> Lokasi Terkunci: ${currentLat.toFixed(5)}, ${currentLng.toFixed(5)}`;
                btnScan.disabled = false;
            },
            (error) => {
                gpsStatus.className = 'alert alert-danger py-2 small mb-3';
                gpsStatus.innerText = "Gagal mengunci lokasi! Aktifkan GPS / Izin Lokasi di HP Anda.";
            },
            { enableHighAccuracy: true }
        );
    } else {
        gpsStatus.innerText = "Perangkat Anda tidak mendukung Geolocation.";
    }

    // 2. Load Model SsdMobilenetv1 & Kamera Front-Facing
    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
    ]).then(() => {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(stream => video.srcObject = stream);
    });

    // 3. Proses Scan & Kirim Koordinat ke Server
    btnScan.addEventListener('click', async () => {
        btnScan.disabled = true;
        
        const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }));
        
        if (!detection) {
            Swal.fire('Wajah Tidak Ditemukan', 'Posisikan wajah Anda pada kamera!', 'warning');
            btnScan.disabled = false;
            return;
        }

        // Screenshot foto saat ini
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const imageBase64 = canvas.toDataURL('image/jpeg');

        Swal.fire({ title: 'Memverifikasi Lokasi & Wajah...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        // Kirim Foto + Koordinat GPS ke Backend
        $.ajax({
            url: "{{ route('student.scan.process') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                latitude: currentLat,
                longitude: currentLng,
                image: imageBase64
            },
            success: (res) => {
                Swal.fire('Berhasil!', res.message, 'success');
                btnScan.disabled = false;
            },
            error: (err) => {
                Swal.fire('Gagal Presensi!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                btnScan.disabled = false;
            }
        });
    });
</script>
</body>
</html>