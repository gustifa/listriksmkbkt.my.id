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

        .preview-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }

        @media (max-width: 575.98px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header { flex-direction: column; gap: 10px; text-align: center; }
            .header-nav-btns { width: 100%; justify-content: center; }
            .webcam-box { aspect-ratio: 3/4; }
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

            <!-- ALERT WARNING DARI MIDDLEWARE (JIKA KOSONG) -->
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow border-0">
                <!-- HEADER DENGAN TOMBOL LOGOUT & KEMBALI -->
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold"><i class="fas fa-camera me-2"></i> Pendaftaran Wajah Saya</span>
                    
                    <div class="header-nav-btns d-flex gap-2">
                        <a href="{{ route('student.face.index') }}" class="btn btn-sm btn-light fw-bold text-primary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <!-- FORM LOGOUT -->
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger fw-bold">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body text-center p-3 p-md-4">
                    
                    <!-- PETUNJUK SYARAT MINIMAL 3 SAMPEL -->
                    <div class="alert alert-info py-2 mb-3 small">
                        <i class="fas fa-info-circle me-1"></i> Anda wajib mengambil <strong>minimal 3 sampel foto wajah</strong> (misal: Depan, Senyum, Miring) agar tombol simpan aktif.
                    </div>

                    <div id="status-msg" class="alert alert-warning py-2 mb-3 small">Memuat Model SsdMobilenetv1...</div>

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

                    <hr class="my-4">

                    <h6 class="fw-bold">Sampel Terkumpul (<span id="sample-count">0</span>/3):</h6>
                    <div id="preview-list" class="d-flex justify-content-center gap-2 flex-wrap mb-3"></div>

                    <!-- TOMBOL SIMPAN -->
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
                statusMsg.innerText = "Kamera Siap! Posisikan wajah Anda pada tempat terang.";
                btnCapture.disabled = false;
            })
            .catch(() => {
                statusMsg.className = 'alert alert-danger py-2 mb-3 small';
                statusMsg.innerText = "Gagal mengakses kamera. Mohon izinkan akses kamera di browser HP/Laptop Anda.";
            });
    }

    btnCapture.addEventListener('click', async () => {
        btnCapture.disabled = true;
        statusMsg.innerText = "Mengekstrak fitur wajah...";

        const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            Swal.fire('Wajah Tidak Terdeteksi', 'Pastikan wajah terlihat jelas dan lokasi penerangan cukup.', 'warning');
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

        const count = collectedDescriptors.length;
        sampleCountText.innerText = count;
        $('#save-count-info').text(`${count}/3`);

        if (count >= 3) {
            btnSave.classList.remove('btn-secondary');
            btnSave.classList.add('btn-primary');
            btnSave.disabled = false;
        } else {
            btnSave.classList.remove('btn-primary');
            btnSave.classList.add('btn-secondary');
            btnSave.disabled = true;
        }

        Swal.fire({ 
            icon: 'success', 
            title: `Sampel Ke-${count} Ditangkap!`, 
            text: count < 3 ? `Ambil ${3 - count} sampel lagi untuk mengaktifkan tombol simpan.` : 'Syarat minimal 3 sampel terpenuhi! Silakan simpan.',
            timer: 1500, 
            showConfirmButton: false 
        });

        btnCapture.disabled = false;
        statusMsg.innerText = "Kamera Siap! Posisikan ekspresi/posisi lain.";
    });

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