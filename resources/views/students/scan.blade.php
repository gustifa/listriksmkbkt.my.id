<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Mandiri - SISFO SMK</title>

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

        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); -webkit-transform: scaleX(-1); }
        #overlay { position: absolute; top:0; left:0; width:100%; height:100%; transform: scaleX(-1); -webkit-transform: scaleX(-1); }
        #capture-canvas { display: none; }

        .face-guide-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 65%;
            height: 68%;
            transform: translate(-50%, -50%);
            border: 3px dashed rgba(255, 255, 255, 0.85);
            border-radius: 50%;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 5;
        }

        .face-guide-overlay.detected {
            border: 4px solid #198754 !important;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.25), 0 0 15px #198754 !important;
        }

        .face-guide-overlay::after {
            content: attr(data-status);
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 0.8rem;
            font-weight: bold;
            background: rgba(0,0,0,0.75);
            padding: 3px 12px;
            border-radius: 12px;
            white-space: nowrap;
        }

        #student-map {
            height: 180px;
            width: 100%;
            border-radius: 15px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
            border: 2px solid #dee2e6;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.85rem;
            padding: 8px 6px;
        }

        .sample-badge-item {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 6px 10px;
            background-color: #fff;
        }

        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .webcam-box { aspect-ratio: 3/4; }
            .face-guide-overlay { width: 70%; height: 62%; }
            #mode-model-select { font-size: 0.85rem; }
            .nav-pills .nav-link { font-size: 0.78rem; }
        }
    </style>
</head>
<body>
    <div class="container py-3 py-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 text-center">

                <!-- CARD HEADER -->
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

                <!-- 3 NAV PILLS TAB -->
                <ul class="nav nav-pills nav-fill mb-3 bg-white p-1 rounded-3 shadow-sm" id="methodTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="face-tab" data-bs-toggle="pill" data-bs-target="#method-face" type="button" role="tab">
                            <i class="fas fa-smile me-1"></i> Scan Wajah
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-warning" id="nis-tab" data-bs-toggle="pill" data-bs-target="#method-nis" type="button" role="tab">
                            <i class="fas fa-id-card me-1"></i> Input NIS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-info" id="registered-tab" data-bs-toggle="pill" data-bs-target="#method-registered" type="button" role="tab">
                            <i class="fas fa-user-shield me-1"></i> Data Wajah
                        </button>
                    </li>
                </ul>

                <!-- STATUS GEOLOCATION & MAP -->
                <div id="geo-status" class="alert alert-info py-2 small fw-bold mb-3 shadow-sm">
                    <i class="fas fa-spinner fa-spin me-1"></i> Mengambil Lokasi GPS...
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-3 text-start">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center px-2 mb-2">
                            <span class="fw-bold small text-secondary"><i class="fas fa-map-marked-alt text-primary me-1"></i> Posisi Anda & Area Sekolah</span>
                            <span id="distance-badge" class="badge bg-secondary small">Menghitung Jarak...</span>
                        </div>
                        <div id="student-map"></div>
                    </div>
                </div>

                <!-- TAMPILAN KAMERA (TETAP TAMPIL SAAT TAB SCAN/NIS AKTIF) -->
                <div id="camera-wrapper" class="webcam-box mb-3">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                    <div class="face-guide-overlay" id="face-guide" data-status="Memuat Kamera..."></div>
                </div>

                <canvas id="capture-canvas"></canvas>

                <!-- ISI TAB CONTENT -->
                <div class="tab-content" id="methodTabContent">
                    
                    <!-- TAB 1: SCAN WAJAH OTOMATIS -->
                    <div class="tab-pane fade show active" id="method-face" role="tabpanel">
                        <div class="input-group mb-3 shadow-sm rounded-3">
                            <span class="input-group-text bg-white" title="Pilihan Performa AI"><i class="fas fa-microchip text-primary"></i></span>
                            <select id="mode-model-select" class="form-select shadow-none fw-bold">
                                <option value="tiny">⚡ Mode Ringan / Cepat (Rekomendasi HP)</option>
                                <option value="ssd">🎯 Mode Presisi Tinggi (Perangkat Bagus)</option>
                            </select>
                        </div>
                        <button id="btn-scan-face" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow mb-3" disabled>
                            <i class="fas fa-camera me-2"></i> ABSEN VIA SCAN WAJAH
                        </button>
                    </div>

                    <!-- TAB 2: INPUT NIS MANUAL (CADANGAN) -->
                    <div class="tab-pane fade" id="method-nis" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 text-start bg-white">
                            <label class="form-label fw-bold text-secondary small"><i class="fas fa-lock text-danger me-1"></i> NIS Siswa Terkunci:</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light"><i class="fas fa-id-badge text-primary"></i></span>
                                <input type="text" id="input-nis" class="form-control fw-bold fs-5 text-secondary bg-light" value="{{ $student->nis }}" readonly>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.78rem;">
                                <i class="fas fa-info-circle text-warning me-1"></i> Swafoto bukti kehadiran akan otomatis dikirim bersamaan dengan NIS Anda.
                            </small>
                        </div>
                        <button id="btn-scan-nis" class="btn btn-warning btn-lg w-100 rounded-pill fw-bold shadow mb-3 text-dark" disabled>
                            <i class="fas fa-paper-plane me-2"></i> ABSEN VIA NIS & FOTO
                        </button>
                    </div>

                    <!-- TAB 3: DATA WAJAH TERDAFTAR -->
                    <div class="tab-pane fade" id="method-registered" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3 text-start bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-user-check text-success me-1"></i> Status Pendaftaran Wajah</h6>
                                @php
                                    // Misal variabel $faceDescriptors dikirim dari Controller
                                    $sampleCount = count($student->faceDescriptors ?? []);
                                @endphp
                                <span class="badge {{ $sampleCount >= 3 ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $sampleCount }} / 3 Sampel Terkumpul
                                </span>
                            </div>

                            @if($sampleCount >= 3)
                                <div class="alert alert-success py-2 small mb-3">
                                    <i class="fas fa-check-circle me-1"></i> Data wajah Anda sudah lengkap & aktif untuk absensi otomatis.
                                </div>
                            @else
                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Sampel wajah Anda kurang dari 3! Disarankan daftar ulang agar AI mengenali wajah Anda.
                                </div>
                            @endif

                            <div class="d-grid gap-2 mb-2">
                                <a href="{{ route('student.face.register') }}" class="btn btn-outline-primary btn-sm fw-bold rounded-3">
                                    <i class="fas fa-plus-circle me-1"></i> Daftar / Perbarui Sampel Wajah
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        const btnScanFace = document.getElementById('btn-scan-face');
        const btnScanNis = document.getElementById('btn-scan-nis');
        const cameraWrapper = document.getElementById('camera-wrapper');
        const distanceBadge = document.getElementById('distance-badge');
        const modeModelSelect = document.getElementById('mode-model-select');
        const faceGuide = document.getElementById('face-guide');

        const schoolLat = parseFloat("{{ $setting->latitude ?? '-0.30512300' }}");
        const schoolLng = parseFloat("{{ $setting->longitude ?? '100.36912300' }}");
        const schoolRadius = parseInt("{{ $setting->radius_meters ?? 100 }}");

        let userLat = null;
        let userLng = null;
        let isModelLoaded = false;
        let studentMap = null;
        let userMarker = null;
        let currentModelType = 'tiny';
        let isWithinDistance = false;
        let isProcessing = false;

        initApp();

        async function initApp() {
            initStudentMap();
            initGeolocation();
            detectOptimalModelMode();

            await Promise.all([
                startCamera(),
                loadAIModels(currentModelType)
            ]);
        }

        // CONTROL TAMPILAN KAMERA SAAT BUKA TAB DATA WAJAH
        document.getElementById('face-tab').addEventListener('click', () => { cameraWrapper.style.display = 'block'; checkReady(); });
        document.getElementById('nis-tab').addEventListener('click', () => { cameraWrapper.style.display = 'block'; checkReady(); });
        document.getElementById('registered-tab').addEventListener('click', () => { cameraWrapper.style.display = 'none'; });

        function initStudentMap() {
            studentMap = L.map('student-map', {
                zoomControl: false, dragging: false, touchZoom: false, doubleClickZoom: false, scrollWheelZoom: false, boxZoom: false, keyboard: false
            }).setView([schoolLat, schoolLng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(studentMap);
            L.circle([schoolLat, schoolLng], { color: '#28a745', fillColor: '#28a745', fillOpacity: 0.2, radius: schoolRadius }).addTo(studentMap);
            L.marker([schoolLat, schoolLng]).addTo(studentMap).bindPopup('<b>Titik Sekolah</b>').openPopup();
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            return Math.round(R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))));
        }

        function initGeolocation() {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (pos) => {
                        userLat = pos.coords.latitude;
                        userLng = pos.coords.longitude;
                        const distance = calculateDistance(userLat, userLng, schoolLat, schoolLng);
                        distanceBadge.innerText = `Jarak: ${distance} Meter`;

                        if (!userMarker) {
                            userMarker = L.circleMarker([userLat, userLng], { radius: 8, fillColor: '#0d6efd', color: '#ffffff', weight: 2, fillOpacity: 0.9 }).addTo(studentMap);
                        } else {
                            userMarker.setLatLng([userLat, userLng]);
                        }

                        studentMap.fitBounds(L.latLngBounds([[schoolLat, schoolLng], [userLat, userLng]]), { padding: [30, 30] });

                        if (distance <= schoolRadius) {
                            geoStatus.className = 'alert alert-success py-2 small fw-bold mb-3 shadow-sm';
                            geoStatus.innerHTML = `<i class="fas fa-check-circle me-1"></i> Lokasi Valid! Anda berada ${distance}m dari sekolah.`;
                            distanceBadge.className = 'badge bg-success small';
                            isWithinDistance = true;
                        } else {
                            geoStatus.className = 'alert alert-danger py-2 small fw-bold mb-3 shadow-sm';
                            geoStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Di Luar Area! Jarak Anda ${distance}m (Batas: ${schoolRadius}m).`;
                            distanceBadge.className = 'badge bg-danger small';
                            isWithinDistance = false;
                        }
                        checkReady();
                    },
                    () => {
                        geoStatus.className = 'alert alert-danger py-2 small fw-bold mb-3 shadow-sm';
                        geoStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> GPS Ditolak! Harap aktifkan Lokasi HP.`;
                        isWithinDistance = false;
                        checkReady();
                    },
                    { enableHighAccuracy: true, maximumAge: 10000, timeout: 8000 }
                );
            }
        }

        function detectOptimalModelMode() {
            if (navigator.connection && (navigator.connection.saveData || navigator.connection.effectiveType === '2g' || navigator.connection.effectiveType === '3g')) {
                currentModelType = 'tiny';
            } else {
                currentModelType = modeModelSelect.value;
            }
            modeModelSelect.value = currentModelType;
        }

        async function loadAIModels(modelType) {
            isModelLoaded = false;
            checkReady();
            try {
                const modelPath = "{{ asset('models') }}";
                if (modelType === 'tiny') {
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
                        faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                        faceapi.nets.faceRecognitionNet.loadFromUri(modelPath)
                    ]);
                } else {
                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri(modelPath),
                        faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                        faceapi.nets.faceRecognitionNet.loadFromUri(modelPath)
                    ]);
                }
                isModelLoaded = true;
                faceGuide.setAttribute('data-status', 'Posisikan Wajah Di Dalam Lingkaran');
                checkReady();
            } catch (err) {
                faceGuide.setAttribute('data-status', 'Model AI Gagal Dimuat');
            }
        }

        function startCamera() {
            return navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 480 }, height: { ideal: 360 }, frameRate: { ideal: 15, max: 24 } },
                audio: false
            })
            .then(stream => { video.srcObject = stream; })
            .catch(() => {
                faceGuide.setAttribute('data-status', 'Gagal Buka Kamera');
            });
        }

        function checkReady() {
            if (userLat && userLng && isWithinDistance && !isProcessing) {
                btnScanNis.disabled = false;
                btnScanFace.disabled = !isModelLoaded;
            } else {
                btnScanFace.disabled = true;
                btnScanNis.disabled = true;
            }
        }

        function takeScreenshot() {
            captureCanvas.width = 320;
            captureCanvas.height = 240;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
            return captureCanvas.toDataURL('image/jpeg', 0.5);
        }

        // SUBMIT SCAN WAJAH
        btnScanFace.addEventListener('click', async () => {
            if (isProcessing) return;
            isProcessing = true;
            Swal.fire({ title: 'Memproses Absen Wajah...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            let detection;
            if (currentModelType === 'tiny') {
                detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 }))
                    .withFaceLandmarks().withFaceDescriptor();
            } else {
                detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks().withFaceDescriptor();
            }

            if (!detection) {
                Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah berada di lingkaran panduan. Jika tetap gagal, silakan gunakan tab "Input NIS".', 'warning');
                isProcessing = false;
                checkReady();
                return;
            }

            sendAttendanceData({ method: 'face' });
        });

        // SUBMIT FALLBACK NIS MANUAL
        btnScanNis.addEventListener('click', () => {
            if (isProcessing) return;
            isProcessing = true;
            Swal.fire({ title: 'Memproses Absen NIS...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            sendAttendanceData({ method: 'nis' });
        });

        function sendAttendanceData(extraData) {
            const imageBase64 = takeScreenshot();

            const payload = Object.assign({
                latitude: userLat,
                longitude: userLng,
                image: imageBase64
            }, extraData);

            $.ajax({
                url: "{{ route('student.scan.store') }}",
                type: "POST",
                data: payload,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Absen!',
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
                    isProcessing = false;
                    checkReady();
                }
            });
        }
    </script>
</body>
</html>