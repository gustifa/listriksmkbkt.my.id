<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Wajah Mandiri - SISFO SMK</title>

    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
        #student-map {
            height: 220px;
            width: 100%;
            border-radius: 15px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
            border: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 text-center">

                <!-- CARD HEADER DENGAN TOMBOL KEMBALI KE DASHBOARD -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div class="text-start">
                            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-user-check me-2"></i>Absensi Mandiri</h5>
                            <small class="text-muted">{{ $student->name }} ({{$student->nis }})</small>
                        </div>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                            <i class="fas fa-arrow-left me-1"></i> Dashboard
                        </a>
                    </div>
                </div>

                <!-- Status Geolocation & Jarak -->
                <div id="geo-status" class="alert alert-info py-2 small fw-bold mb-3">
                    <i class="fas fa-spinner fa-spin me-1"></i> Mengambil Lokasi GPS...
                </div>

                <!-- TAMPILAN MAP DENGAN GEOFENCING (READ-ONLY) -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 text-start">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center px-2 mb-2">
                            <span class="fw-bold small text-secondary"><i class="fas fa-map-marked-alt text-primary me-1"></i> Posisi Anda & Area Sekolah</span>
                            <span id="distance-badge" class="badge bg-secondary small">Menghitung Jarak...</span>
                        </div>
                        <div id="student-map"></div>
                    </div>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
        const distanceBadge = document.getElementById('distance-badge');

        // KOORDINAT PUSAT & RADIUS SEKOLAH (Diambil dari database)
        const schoolLat = parseFloat("{{ $setting->latitude ?? '-0.30512300' }}");
        const schoolLng = parseFloat("{{ $setting->longitude ?? '100.36912300' }}");
        const schoolRadius = parseInt("{{ $setting->radius_meters ?? 100 }}");

        let userLat = null;
        let userLng = null;
        let isModelLoaded = false;
        let studentMap = null;
        let userMarker = null;

        // Inisialisasi Map Terkunci (Read-Only)
        function initStudentMap() {
            studentMap = L.map('student-map', {
                zoomControl: false,
                dragging: false,
                touchZoom: false,
                doubleClickZoom: false,
                scrollWheelZoom: false,
                boxZoom: false,
                keyboard: false
            }).setView([schoolLat, schoolLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(studentMap);

            // 1. Lingkaran Radius Sekolah
            L.circle([schoolLat, schoolLng], {
                color: '#28a745',
                fillColor: '#28a745',
                fillOpacity: 0.2,
                radius: schoolRadius
            }).addTo(studentMap);

            // 2. Marker Titik Sekolah
            L.marker([schoolLat, schoolLng]).addTo(studentMap)
                .bindPopup('<b>Titik Sekolah</b>').openPopup();
        }

        initStudentMap();

        // Formula Haversine (Hitung Jarak)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return Math.round(R * c);
        }

        // 1. Dapatkan Geolocation HP Siswa
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (pos) => {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;

                    const distance = calculateDistance(userLat, userLng, schoolLat, schoolLng);
                    distanceBadge.innerText = `Jarak: ${distance} Meter`;

                    if (!userMarker) {
                        userMarker = L.circleMarker([userLat, userLng], {
                            radius: 8,
                            fillColor: '#0d6efd',
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(studentMap).bindPopup('<b>Lokasi Anda</b>');
                    } else {
                        userMarker.setLatLng([userLat, userLng]);
                    }

                    const bounds = L.latLngBounds([
                        [schoolLat, schoolLng],
                        [userLat, userLng]
                    ]);
                    studentMap.fitBounds(bounds, { padding: [30, 30] });

                    if (distance <= schoolRadius) {
                        geoStatus.className = 'alert alert-success py-2 small fw-bold mb-3';
                        geoStatus.innerHTML = `<i class="fas fa-check-circle me-1"></i> Lokasi Valid! Anda berada ${distance}m dari sekolah.`;
                        distanceBadge.className = 'badge bg-success small';
                    } else {
                        geoStatus.className = 'alert alert-danger py-2 small fw-bold mb-3';
                        geoStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Di Luar Area! Jarak Anda ${distance}m (Batas: ${schoolRadius}m).`;
                        distanceBadge.className = 'badge bg-danger small';
                    }

                    checkReady(distance <= schoolRadius);
                },
                (err) => {
                    geoStatus.className = 'alert alert-danger py-2 small fw-bold mb-3';
                    geoStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Akses GPS Ditolak! Harap aktifkan Lokasi/GPS HP Anda.`;
                    distanceBadge.className = 'badge bg-danger small';
                    distanceBadge.innerText = 'GPS Terkunci';
                },
                { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
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
            checkReady(true);
        });

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 480, height: 640 } })
                .then(stream => { video.srcObject = stream; })
                .catch(err => {
                    Swal.fire('Error', 'Gagal membuka kamera HP.', 'error');
                });
        }

        function checkReady(isWithinDistance = true) {
            if (userLat && userLng && isModelLoaded && isWithinDistance) {
                btnScan.disabled = false;
            } else {
                btnScan.disabled = true;
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
