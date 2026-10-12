<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Wajah Mandiri</title>
    
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

        /* CARD ILUSTRASI VISUAL */
        .guide-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            transition: all 0.2s ease-in-out;
        }

        .guide-card.active {
            border-color: #0d6efd;
            background-color: #e7f1ff;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
        }

        .guide-svg {
            width: 50px;
            height: 50px;
            margin: 0 auto 5px auto;
            display: block;
        }

        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header { flex-direction: column; gap: 8px; text-align: center; }
            .webcam-box { aspect-ratio: 3/4; }
            .face-guide-overlay { width: 68%; height: 65%; }
            .preview-img { width: 70px; height: 70px; }
            .guide-svg { width: 40px; height: 40px; }
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
        <div class="col-12 col-md-9">

            <!-- ALERT WARNING DARI MIDDLEWARE (JIKA KOSONG) -->
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="fas fa-camera me-2"></i> Pendaftaran Wajah Saya</span>
                    <a href="{{ route('student.face.index') }}" class="btn btn-sm btn-light fw-bold">Kembali</a>
                </div>
                <div class="card-body text-center p-3 p-md-4">
                    
                    <!-- PANDUAN GAMBAR CONTOH POSISI WAJAH -->
                    <div class="card border-0 bg-light p-3 mb-3 text-start">
                        <h6 class="fw-bold text-dark mb-3 text-center">
                            <i class="fas fa-user-circle text-primary me-1"></i> Contoh Posisi Wajah Yang Diperlukan (Min. 3 Posisi):
                        </h6>
                        <div class="row g-2 text-center justify-content-center">
                            <div class="col-4 col-md-2">
                                <div class="guide-card active">
                                    <svg class="guide-svg" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" fill="#e2e8f0" stroke="#0d6efd" stroke-width="3"/>
                                        <circle cx="35" cy="40" r="5" fill="#334155"/>
                                        <circle cx="65" cy="40" r="5" fill="#334155"/>
                                        <path d="M 38 65 Q 50 70 62 65" stroke="#334155" stroke-width="3" fill="none"/>
                                    </svg>
                                    <span class="d-block fw-bold small text-truncate">1. Depan</span>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="guide-card">
                                    <svg class="guide-svg" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" fill="#e2e8f0" stroke="#94a3b8" stroke-width="3"/>
                                        <circle cx="25" cy="40" r="5" fill="#334155"/>
                                        <circle cx="55" cy="40" r="5" fill="#334155"/>
                                        <path d="M 28 65 Q 40 70 52 65" stroke="#334155" stroke-width="3" fill="none"/>
                                    </svg>
                                    <span class="d-block fw-bold small text-truncate">2. Miring Kiri</span>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="guide-card">
                                    <svg class="guide-svg" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" fill="#e2e8f0" stroke="#94a3b8" stroke-width="3"/>
                                        <circle cx="45" cy="40" r="5" fill="#334155"/>
                                        <circle cx="75" cy="40" r="5" fill="#334155"/>
                                        <path d="M 48 65 Q 60 70 72 65" stroke="#334155" stroke-width="3" fill="none"/>
                                    </svg>
                                    <span class="d-block fw-bold small text-truncate">3. Miring Kanan</span>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="guide-card">
                                    <svg class="guide-svg" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" fill="#e2e8f0" stroke="#94a3b8" stroke-width="3"/>
                                        <circle cx="35" cy="40" r="5" fill="#334155"/>
                                        <circle cx="65" cy="40" r="5" fill="#334155"/>
                                        <path d="M 30 60 Q 50 80 70 60" stroke="#334155" stroke-width="3" fill="none"/>
                                    </svg>
                                    <span class="d-block fw-bold small text-truncate">4. Tersenyum</span>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="guide-card">
                                    <svg class="guide-svg" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" fill="#e2e8f0" stroke="#94a3b8" stroke-width="3"/>
                                        <rect x="25" y="32" width="20" height="15" rx="3" stroke="#334155" stroke-width="3" fill="none"/>
                                        <rect x="55" y="32" width="20" height="15" rx="3" stroke="#334155" stroke-width="3" fill="none"/>
                                        <line x1="45" y1="38" x2="55" y2="38" stroke="#334155" stroke-width="3"/>
                                        <path d="M 38 65 Q 50 70 62 65" stroke="#334155" stroke-width="3" fill="none"/>
                                    </svg>
                                    <span class="d-block fw-bold small text-truncate">5. Kacamata</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILIHAN MODE AI -->
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

                    <!-- TOMBOL MANUAL & OPSI SCAN OTOMATIS -->
                    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 mb-3">
                        <button id="btn-capture" class="btn btn-success btn-lg px-4 py-2 fw-bold w-100 w-sm-auto" disabled>
                            <i class="fas fa-camera me-2"></i> Ambil Sampel Wajah
                        </button>
                    </div>

                    <div class="alert alert-info py-2 my-3 small">
                        <i class="fas fa-info-circle me-1"></i> Anda wajib mengambil <strong>minimal 3 sampel foto wajah yang berbeda</strong> agar tombol simpan aktif. Auto-scan aktif saat posisi ditahan 1 detik.
                    </div>

                    <hr class="my-3">

                    <h6 class="fw-bold">Sampel Terkumpul (<span id="sample-count">0</span>/3):</h6>
                    <div id="preview-list" class="d-flex justify-content-center gap-2 flex-wrap mb-3"></div>

                    <!-- TOMBOL SIMPAN (MATI JIKA BELUM 3) -->
                    <button id="btn-save" class="btn btn-secondary w-100 py-2 fw-bold" disabled>
                        <i class="fas fa-save me-2"></i> Simpan Sampel Wajah (<span id="save-count-info">0/3</span>)
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
    const btnCapture = document.getElementById('btn-capture');
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
    let isProcessing = false;
    let scanHoldTimer = null;
    let detectionLoopActive = false;
    let lastDetection = null;

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
        btnCapture.disabled = true;
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
                btnCapture.disabled = false;
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
            btnCapture.disabled = false;
        })
        .catch(() => {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal mengakses kamera. Mohon izinkan akses kamera di browser HP/Laptop Anda.";
        });
    }

    // LOOP DETEKSI REAL-TIME DI LATAR BELAKANG
    video.addEventListener('play', () => {
        if (detectionLoopActive) return;
        detectionLoopActive = true;

        async function processFrame() {
            if (!video.srcObject) {
                detectionLoopActive = false;
                return;
            }

            if (!isProcessing && collectedLabels.length < 5) {
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

                lastDetection = detection;

                if (detection) {
                    faceGuide.classList.add('detected');
                    faceGuide.setAttribute('data-status', 'Wajah Pas! Klik Tombol / Tahan 1 Detik');

                    // Auto-capture jika ditahan 1.2 detik
                    if (!scanHoldTimer) {
                        scanHoldTimer = setTimeout(() => {
                            captureSample(detection);
                        }, 1200);
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
            setTimeout(processFrame, interval);
        }

        processFrame();
    });

    // PENGAMBILAN SAMPEL MANUAL VIA TOMBOL
    btnCapture.addEventListener('click', async () => {
        if (isProcessing) return;
        btnCapture.disabled = true;

        if (!lastDetection) {
            statusMsg.innerText = "Mengekstrak fitur wajah secara presisi...";
            if (currentModelType === 'tiny') {
                lastDetection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
            } else {
                lastDetection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
            }
        }

        if (!lastDetection) {
            Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah berada di dalam lingkaran panduan & pencahayaan cukup terang.', 'warning');
            btnCapture.disabled = false;
            return;
        }

        captureSample(lastDetection);
    });

    // FUNGSI UTAMA PENANGKAPAN SAMPEL WAJAH
    function captureSample(detection) {
        isProcessing = true;
        if (scanHoldTimer) { clearTimeout(scanHoldTimer); scanHoldTimer = null; }

        const selectedLabel = sampleLabelSelect.value;

        // Validasi 1: Label sudah pernah diambil
        if (collectedLabels.includes(selectedLabel)) {
            Swal.fire('Sudut Wajah Sudah Terdaftar', `Anda sudah mengambil sampel "${selectedLabel}". Silakan pilih instruksi posisi lain!`, 'warning');
            btnCapture.disabled = false;
            isProcessing = false;
            return;
        }

        // Validasi 2: Cek variasi Euclidean distance geometri wajah (mencegah foto identik)
        const newDescriptor = detection.descriptor;
        for (let i = 0; i < rawDescriptors.length; i++) {
            const distance = faceapi.euclideanDistance(newDescriptor, rawDescriptors[i]);
            if (distance < 0.12) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Posisi Wajah Terlalu Sama',
                    text: 'Sistem mendeteksi posisi/ekspresi wajah Anda belum berubah. Miringkan kepala atau ubah ekspresi!'
                });
                btnCapture.disabled = false;
                isProcessing = false;
                return;
            }
        }

        // Lolos Validasi
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

        const count = collectedDescriptors.length;
        sampleCountText.innerText = count;
        $('#save-count-info').text(`${count}/3`);

        // VALIDASI TOMBOL SIMPAN AKTIF APABILA SUDAH MINIMAL 3 SAMPEL
        if (count >= 3) {
            btnSave.classList.remove('btn-secondary');
            btnSave.classList.add('btn-primary');
            btnSave.disabled = false;
        } else {
            btnSave.classList.remove('btn-primary');
            btnSave.classList.add('btn-secondary');
            btnSave.disabled = true;
        }

        // Pindah otomatis pilihan dropdown ke posisi berikutnya yang belum terambil
        const availableOptions = Array.from(sampleLabelSelect.options).map(opt => opt.value);
        const nextOption = availableOptions.find(optVal => !collectedLabels.includes(optVal));
        if (nextOption) {
            sampleLabelSelect.value = nextOption;
        }

        Swal.fire({
            icon: 'success',
            title: `Sampel Ke-${count} Ditangkap!`,
            text: count < 3 ? `Ambil ${3 - count} sampel lagi untuk mengaktifkan tombol simpan.` : 'Syarat minimal 3 sampel terpenuhi! Silakan simpan.',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            faceGuide.classList.remove('detected');
            faceGuide.setAttribute('data-status', 'Posisikan Wajah Di Dalam Lingkaran');
            btnCapture.disabled = false;
            isProcessing = false;
        });
    }

    // SIMPAN KE DATABASE
    btnSave.addEventListener('click', () => {
        if (collectedDescriptors.length < 3) {
            Swal.fire('Sampel Kurang', 'Anda wajib mengambil minimal 3 foto sampel wajah!', 'warning');
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: "{{ route('student.face.store') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                descriptors: collectedDescriptors,
                labels: collectedLabels
            },
            success: (res) => {
                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                    window.location.href = "{{ route('student.face.index') }}";
                });
            },
            error: (err) => {
                let msg = err.responseJSON?.errors?.descriptors?.[0] || err.responseJSON?.message || 'Gagal menyimpan sampel.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });
</script>
</body>
</html>