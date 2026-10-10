<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perekaman Wajah - {{ $student->name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }

        .webcam-box {
            position: relative;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 4/3;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
        }

        /* BINGKAI LINGKARAN PANDUAN WAJAH */
        .face-guide-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 58%;
            height: 75%;
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

        .preview-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }

        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header { flex-direction: column; gap: 8px; text-align: center; }
            .webcam-box { aspect-ratio: 3/4; }
            .face-guide-overlay { width: 68%; height: 65%; }
            .preview-img { width: 70px; height: 70px; }
            #mode-model-select { font-size: 0.85rem; }
        }

        @supports (padding: max(0px)) {
            body {
                padding-left: min(0px, env(safe-area-inset-left));
                padding-right: min(0px, env(safe-area-inset-right));
                padding-bottom: min(0px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="fas fa-camera me-2"></i> Rekam Wajah: {{ $student->name }}</span>
                    <a href="{{ route('face.index') }}" class="btn btn-sm btn-light fw-bold">Kembali</a>
                </div>
                <div class="card-body text-center p-3 p-md-4">
                    <!-- Pilihan Mode AI -->
                    <div class="mb-3 w-100 w-md-75 mx-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-white" title="Pilihan Performa AI"><i class="fas fa-microchip text-primary"></i></span>
                            <select id="mode-model-select" class="form-select shadow-none fw-bold">
                                <option value="tiny">⚡ Mode Ringan / Cepat (Rekomendasi HP)</option>
                                <option value="ssd">🎯 Mode Presisi Tinggi (Perangkat Bagus)</option>
                            </select>
                        </div>
                    </div>

                    <div id="status-msg" class="alert alert-warning py-2 mb-3 small">
                        <span class="spinner-border spinner-border-sm me-1"></span> Memuat Model AI...
                    </div>

                    <div class="webcam-box mb-3">
                        <video id="video" autoplay muted playsinline></video>
                        <div class="face-guide-overlay" id="face-guide" data-status="Posisikan Wajah Di Dalam Lingkaran"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Posisi Foto / Instruksi Saat Ini:</label>
                        <select id="sample-label" class="form-select w-100 w-md-50 mx-auto text-center shadow-none fw-bold text-primary">
                            <option value="Tampak Depan (Netral)">1. Tampak Depan (Netral)</option>
                            <option value="Tersenyum / Ekspresi">2. Tersenyum / Ekspresi</option>
                            <option value="Agak Miring Kiri">3. Agak Miring Kiri</option>
                            <option value="Agak Miring Kanan">4. Agak Miring Kanan</option>
                            <option value="Memakai Aksesoris/Kacamata">5. Memakai Kacamata / Aksesoris</option>
                        </select>
                    </div>

                    <div class="alert alert-info py-2 my-3 small">
                        <i class="fas fa-magic me-1"></i> <strong>Scan Otomatis Aktif:</strong> Posisikan wajah di lingkaran & tahan posisi selama 1 detik.
                    </div>

                    <hr class="my-3">

                    <h6 class="fw-bold">Sampel Terkumpul (<span id="sample-count">0</span>):</h6>
                    <div id="preview-list" class="d-flex justify-content-center gap-2 flex-wrap mb-3"></div>

                    <button id="btn-save" class="btn btn-primary w-100 py-2 fw-bold" style="display: none;">
                        <i class="fas fa-save me-2"></i> Simpan Semua Sampel Wajah
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const video = document.getElementById('video');
    const statusMsg = document.getElementById('status-msg');
    const btnSave = document.getElementById('btn-save');
    const previewList = document.getElementById('preview-list');
    const sampleCountText = document.getElementById('sample-count');
    const modeModelSelect = document.getElementById('mode-model-select');
    const sampleLabelSelect = document.getElementById('sample-label');
    const faceGuide = document.getElementById('face-guide');

    let rawDescriptors = [];
    let collectedDescriptors = [];
    let collectedLabels = [];
    let currentStream = null;
    let currentModelType = 'tiny';
    let isAutoScanning = false;
    let scanHoldTimer = null;
    let detectionLoopActive = false;

    detectOptimalModelMode();

    function detectOptimalModelMode() {
        if (navigator.connection) {
            const conn = navigator.connection;
            if (conn.saveData || conn.effectiveType === '2g' || conn.effectiveType === '3g') {
                currentModelType = 'tiny';
            } else if (conn.effectiveType === '4g') {
                currentModelType = modeModelSelect.value;
            }
        } else {
            currentModelType = modeModelSelect.value;
        }

        modeModelSelect.value = currentModelType;
        loadAIModels(currentModelType);
    }

    async function loadAIModels(modelType) {
        statusMsg.className = 'alert alert-warning py-2 mb-3 small';
        statusMsg.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Memuat Model AI (${modelType === 'tiny' ? 'Mode Ringan' : 'Mode Presisi'})...`;

        try {
            if (modelType === 'tiny') {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri("{{ asset('models') }}"),
                    faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
                    faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
                ]);
            } else {
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
                    faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
                    faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
                ]);
            }

            if (!currentStream) {
                startWebcam();
            } else {
                statusMsg.className = 'alert alert-success py-2 mb-3 small';
                statusMsg.innerText = `Kamera Siap! Posisikan wajah di dalam lingkaran.`;
            }
        } catch (err) {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal memuat model AI dari server.";
        }
    }

    modeModelSelect.addEventListener('change', () => {
        currentModelType = modeModelSelect.value;
        loadAIModels(currentModelType);
    });

    function startWebcam() {
        const idealWidth = currentModelType === 'tiny' ? 480 : 640;
        const idealHeight = currentModelType === 'tiny' ? 360 : 480;

        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: "user", 
                width: { ideal: idealWidth }, 
                height: { ideal: idealHeight } 
            } 
        })
        .then(stream => {
            currentStream = stream;
            video.srcObject = stream;
            statusMsg.className = 'alert alert-success py-2 mb-3 small';
            statusMsg.innerText = `Kamera Siap! Posisikan wajah di dalam lingkaran.`;
        })
        .catch(() => {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal mengakses kamera. Berikan izin akses peramban.";
        });
    }

    // LOOP PENIMBANGAN DAN SCAN OTOMATIS
    video.addEventListener('play', () => {
        if (detectionLoopActive) return;
        detectionLoopActive = true;

        async function processAutoScan() {
            if (!video.srcObject) {
                detectionLoopActive = false;
                return;
            }

            if (!isAutoScanning && collectedLabels.length < 5) {
                let detection;
                if (currentModelType === 'tiny') {
                    detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                } else {
                    detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                }

                if (detection) {
                    faceGuide.classList.add('detected');
                    faceGuide.setAttribute('data-status', 'Wajah Pas! Tahan 1 Detik...');

                    if (!scanHoldTimer) {
                        scanHoldTimer = setTimeout(() => {
                            captureSampleAutomatically(detection);
                        }, 1200); // Tahan posisi selama 1.2 detik untuk auto-capture
                    }
                } else {
                    faceGuide.classList.remove('detected');
                    faceGuide.setAttribute('data-status', 'Posisikan Wajah Di Dalam Lingkaran');
                    if (scanHoldTimer) {
                        clearTimeout(scanHoldTimer);
                        scanHoldTimer = null;
                    }
                }
            }

            const interval = currentModelType === 'tiny' ? 250 : 150;
            setTimeout(processAutoScan, interval);
        }

        processAutoScan();
    });

    async function captureSampleAutomatically(detection) {
        isAutoScanning = true;
        if (scanHoldTimer) { clearTimeout(scanHoldTimer); scanHoldTimer = null; }

        const selectedLabel = sampleLabelSelect.value;

        // Validasi 1: Label sudah diambil sebelumnya
        if (collectedLabels.includes(selectedLabel)) {
            faceGuide.classList.remove('detected');
            faceGuide.setAttribute('data-status', 'Ubah Posisi / Pilihan instruksi!');
            setTimeout(() => { isAutoScanning = false; }, 2000);
            return;
        }

        // Validasi 2: Cek variasi Euclidean distance geometri wajah
        const newDescriptor = detection.descriptor;
        for (let i = 0; i < rawDescriptors.length; i++) {
            const distance = faceapi.euclideanDistance(newDescriptor, rawDescriptors[i]);
            if (distance < 0.12) {
                faceGuide.classList.remove('detected');
                faceGuide.setAttribute('data-status', 'Ubah Sudut / Ekspresi Wajah Anda!');
                setTimeout(() => { isAutoScanning = false; }, 2000);
                return;
            }
        }

        // Lolos Validasi & Ambil Sampel
        rawDescriptors.push(newDescriptor);
        collectedDescriptors.push(JSON.stringify(Array.from(newDescriptor)));
        collectedLabels.push(selectedLabel);

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 480;
        canvas.height = video.videoHeight || 360;
        canvas.getContext('2d').drawImage(video, 0, 0);

        previewList.insertAdjacentHTML('beforeend', `
            <div class="text-center">
                <img src="${canvas.toDataURL('image/jpeg', 0.6)}" class="preview-img mb-1">
                <small class="d-block text-truncate fw-bold" style="max-width: 80px;">${selectedLabel}</small>
            </div>
        `);

        sampleCountText.innerText = collectedDescriptors.length;

        // Pindah otomatis ke instruksi berikutnya
        const availableOptions = Array.from(sampleLabelSelect.options).map(opt => opt.value);
        const nextOption = availableOptions.find(optVal => !collectedLabels.includes(optVal));
        if (nextOption) {
            sampleLabelSelect.value = nextOption;
        }

        if (collectedDescriptors.length >= 1) btnSave.style.display = 'block';

        Swal.fire({
            icon: 'success',
            title: 'Sampel Ditangkap!',
            text: selectedLabel,
            timer: 1000,
            showConfirmButton: false
        }).then(() => {
            faceGuide.classList.remove('detected');
            faceGuide.setAttribute('data-status', 'Posisikan Wajah Di Dalam Lingkaran');
            isAutoScanning = false;
        });
    }

    btnSave.addEventListener('click', () => {
        const uniqueLabelsCount = new Set(collectedLabels).size;
        if (uniqueLabelsCount < 3) {
            Swal.fire({
                icon: 'error',
                title: 'Variasi Wajah Kurang',
                text: 'Wajib mengambil minimal 3 posisi wajah yang berbeda!'
            });
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: "{{ route('face.store') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                student_id: "{{ $student->id }}",
                descriptors: collectedDescriptors,
                labels: collectedLabels
            },
            success: (res) => {
                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                    window.location.href = "{{ route('face.index') }}";
                });
            },
            error: (err) => {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal menyimpan data.', 'error');
            }
        });
    });
</script>
</body>
</html>