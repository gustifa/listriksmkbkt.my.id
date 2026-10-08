<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perekaman Wajah - {{ $student->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .webcam-box { position: relative; width: 100%; max-width: 480px; margin: 0 auto; background: #000; border-radius: 10px; overflow: hidden; }
        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        .preview-img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 2px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-camera me-2"></i> Rekam Wajah: <strong>{{ $student->name }}</strong></span>
                    <a href="{{ route('face.index') }}" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body text-center">
                    <div id="status-msg" class="alert alert-warning py-2 mb-3">Memuat Model SsdMobilenetv1...</div>

                    <div class="webcam-box mb-3">
                        <video id="video" autoplay muted playsinline></video>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Posisi Foto:</label>
                        <select id="sample-label" class="form-select w-50 mx-auto text-center">
                            <option value="Tampak Depan (Netral)">1. Tampak Depan (Netral)</option>
                            <option value="Tersenyum / Ekspresi">2. Tersenyum / Ekspresi</option>
                            <option value="Agak Miring Kiri">3. Agak Miring Kiri</option>
                            <option value="Agak Miring Kanan">4. Agak Miring Kanan</option>
                            <option value="Memakai Aksesoris/Kacamata">5. Memakai Kacamata / Aksesoris</option>
                        </select>
                    </div>

                    <button id="btn-capture" class="btn btn-success btn-lg px-4" disabled>
                        <i class="fas fa-camera me-2"></i> Ambil Sampel Wajah
                    </button>

                    <hr class="my-4">

                    <h5>Sampel Terkumpul (<span id="sample-count">0</span>):</h5>
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
        navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
            .then(stream => {
                video.srcObject = stream;
                statusMsg.className = 'alert alert-success';
                statusMsg.innerText = "Kamera Siap! Posisikan wajah dengan terang.";
                btnCapture.disabled = false;
            });
    }

    btnCapture.addEventListener('click', async () => {
        btnCapture.disabled = true;
        statusMsg.innerText = "Mengekstrak ciri wajah...";

        const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah menghadap kamera dan cahaya cukup.', 'warning');
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
                <small class="d-block text-truncate" style="max-width: 90px;">${label}</small>
            </div>
        `);

        sampleCountText.innerText = collectedDescriptors.length;
        if (collectedDescriptors.length >= 1) btnSave.style.display = 'block';

        Swal.fire({ icon: 'success', title: 'Sampel Berhasil Diambil!', timer: 1000, showConfirmButton: false });
        btnCapture.disabled = false;
        statusMsg.innerText = "Kamera Siap!";
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
            }
        });
    });
</script>
</body>
</html>
