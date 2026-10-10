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
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Posisi Foto / Instruksi:</label>
                        <select id="sample-label" class="form-select w-100 w-md-50 mx-auto text-center shadow-none">
                            <option value="Tampak Depan (Netral)">1. Tampak Depan (Netral)</option>
                            <option value="Tersenyum / Ekspresi">2. Tersenyum / Ekspresi</option>
                            <option value="Agak Miring Kiri">3. Agak Miring Kiri</option>
                            <option value="Agak Miring Kanan">4. Agak Miring Kanan</option>
                            <option value="Memakai Aksesoris/Kacamata">5. Memakai Kacamata / Aksesoris</option>
                        </select>
                    </div>

                    <button id="btn-capture" class="btn btn-success btn-lg w-100 w-md-auto px-4 py-2" disabled>
                        <i class="fas fa-camera me-2"></i> Ambil Sampel Wajah
                    </button>

                    <div class="alert alert-info py-2 my-3 small">
                        <i class="fas fa-info-circle me-1"></i> Wajib mengambil minimal <strong>3 sudut/posisi wajah berbeda</strong>.
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
    const btnCapture = document.getElementById('btn-capture');
    const btnSave = document.getElementById('btn-save');
    const previewList = document.getElementById('preview-list');
    const sampleCountText = document.getElementById('sample-count');
    const modeModelSelect = document.getElementById('mode-model-select');
    const sampleLabelSelect = document.getElementById('sample-label');

    let rawDescriptors = []; // Array berupa Float32Array untuk komparasi jarak geometri
    let collectedDescriptors = [];
    let collectedLabels = [];
    let currentStream = null;
    let currentModelType = 'tiny';

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
                statusMsg.innerText = `Kamera Siap! (${modelType === 'tiny' ? 'Mode Ringan' : 'Mode Presisi'})`;
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
            statusMsg.innerText = `Kamera Siap! (${currentModelType === 'tiny' ? 'Mode Ringan' : 'Mode Presisi'})`;
            btnCapture.disabled = false;
        })
        .catch(() => {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal mengakses kamera. Berikan izin akses peramban.";
        });
    }

    btnCapture.addEventListener('click', async () => {
        btnCapture.disabled = true;
        statusMsg.innerText = "Mengekstrak fitur wajah...";

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

        if (!detection) {
            Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah menghadap kamera dan pencahayaan terang.', 'warning');
            btnCapture.disabled = false;
            statusMsg.innerText = "Kamera Siap!";
            return;
        }

        const selectedLabel = sampleLabelSelect.value;

        // VALIDASI 1: Cek apakah label ini sudah pernah diambil sebelumnya
        if (collectedLabels.includes(selectedLabel)) {
            Swal.fire('Sudut Wajah Sudah Terdaftar', `Anda sudah mengambil sampel "${selectedLabel}". Silakan pilih instruksi posisi lain pada dropdown!`, 'warning');
            btnCapture.disabled = false;
            statusMsg.innerText = "Kamera Siap!";
            return;
        }

        // VALIDASI 2: Cek Jarak Geometri Descriptor (Pencegahan Foto Identik/Tampak Depan Terus)
        const newDescriptor = detection.descriptor;
        for (let i = 0; i < rawDescriptors.length; i++) {
            const distance = faceapi.euclideanDistance(newDescriptor, rawDescriptors[i]);
            // Jarak < 0.12 menunjukkan foto hampir 100% identik dalam sudut & ekspresi yang sama
            if (distance < 0.12) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Posisi Wajah Terlalu Sama',
                    text: 'Sistem mendeteksi posisi/ekspresi wajah Anda tidak berubah dari sampel sebelumnya. Miringkan kepala atau ubah ekspresi sesuai instruksi!'
                });
                btnCapture.disabled = false;
                statusMsg.innerText = "Kamera Siap!";
                return;
            }
        }

        // Simpan data jika lolos semua validasi
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

        // Auto-shift dropdown ke posisi berikutnya yang belum diambil
        const availableOptions = Array.from(sampleLabelSelect.options).map(opt => opt.value);
        const nextOption = availableOptions.find(optVal => !collectedLabels.includes(optVal));
        if (nextOption) {
            sampleLabelSelect.value = nextOption;
        }

        if (collectedDescriptors.length >= 1) btnSave.style.display = 'block';

        Swal.fire({ icon: 'success', title: 'Sampel Ditangkap!', text: selectedLabel, timer: 1200, showConfirmButton: false });
        btnCapture.disabled = false;
        statusMsg.innerText = "Kamera Siap! Ambil posisi lain.";
    });

    btnSave.addEventListener('click', () => {
        // VALIDASI 3: Memastikan minimal diambil 3 sudut/variasi berbeda sebelum bisa disimpan
        const uniqueLabelsCount = new Set(collectedLabels).size;
        if (uniqueLabelsCount < 3) {
            Swal.fire({
                icon: 'error',
                title: 'Variasi Wajah Kurang',
                text: 'Untuk akurasi sistem, Anda wajib mengambil minimal 3 posisi wajah yang berbeda (misal: Tampak Depan, Miring Kiri, Miring Kanan)!'
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