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
            .webcam-box { aspect-ratio: 3/4; } /* Memanjang vertikal di HP */
            .preview-img { width: 70px; height: 70px; }
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
                    <div id="status-msg" class="alert alert-warning py-2 mb-3 small">Memuat Model SsdMobilenetv1...</div>

                    <div class="webcam-box mb-3">
                        <video id="video" autoplay muted playsinline></video>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Posisi Foto / Instuksi:</label>
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

                    <hr class="my-4">

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

    let collectedDescriptors = [];
    let collectedLabels = [];

    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
        faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
    ]).then(startWebcam);

    function startWebcam() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } } })
            .then(stream => {
                video.srcObject = stream;
                statusMsg.className = 'alert alert-success py-2 mb-3 small';
                statusMsg.innerText = "Kamera Siap! Posisikan wajah dengan terang.";
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

        const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah menghadap kamera dan pencahayaan terang.', 'warning');
            btnCapture.disabled = false;
            statusMsg.innerText = "Kamera Siap!";
            return;
        }

        const descriptorArray = Array.from(detection.descriptor);
        const label = $('#sample-label').val();

        collectedDescriptors.push(JSON.stringify(descriptorArray));
        collectedLabels.push(label);

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        previewList.insertAdjacentHTML('beforeend', `
            <div class="text-center">
                <img src="${canvas.toDataURL('image/jpeg')}" class="preview-img mb-1">
                <small class="d-block text-truncate" style="max-width: 80px;">${label}</small>
            </div>
        `);

        sampleCountText.innerText = collectedDescriptors.length;
        if (collectedDescriptors.length >= 1) btnSave.style.display = 'block';

        Swal.fire({ icon: 'success', title: 'Sampel Ditangkap!', timer: 1000, showConfirmButton: false });
        btnCapture.disabled = false;
        statusMsg.innerText = "Kamera Siap! Ambil posisi lain.";
    });

    btnSave.addEventListener('click', () => {
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
