@section('title')
    Registrasi Wajah: {{ $student->name }}
@endsection

<x-app-layout>
<div class="page-content">
    <div class="row justify-content-center pt-2">
        <div class="col-12 col-md-10 col-lg-8">
            <!-- Card Utama disesuaikan seperti UI rujukan -->
            <div class="register-card shadow-sm border-0">
                <div class="register-card-header text-white fw-bold py-3 px-4">
                    Registrasi Wajah: {{ $student->name }}
                </div>

                <div class="card-body text-center p-4">
                    <!-- Dropdown Pilihan Kamera Rapi di Tengah -->
                    <div class="camera-select-wrapper">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-camera text-secondary"></i></span>
                            <select class="form-select shadow-none" id="cameraSelect">
                                <option value="" selected>Mencari kamera...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Loading Model AI -->
                    <div id="loading" class="alert alert-info py-2 mb-3 small">
                        <span class="spinner-border spinner-border-sm me-2"></span> Memuat model AI... Harap tunggu.
                    </div>

                    <!-- Video Container Membulat (Rounded) -->
                    <div class="video-container shadow-sm">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                    </div>

                    <!-- Tombol Aksi Simpan & Batal persis seperti gambar rujukan -->
                    <div class="btn-action-group">
                        <button id="btn-save" class="btn-save-face" disabled>
                            <i class="fas fa-check-circle me-1"></i>
                            <span id="btn-text">Mencari Wajah...</span>
                        </button>
                        <a href="{{ route('face.index', ['classroom_id' => $student->classroom_id]) }}" class="btn-cancel">Batal</a>
                    </div>

                    <!-- Teks Petunjuk Bawah -->
                    <p class="instruction-text">
                        Pastikan wajah terlihat jelas, tidak memakai masker, dan pencahayaan cukup.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STYLES KHUSUS MENGACU TAMPILAN GAMBAR RUJUKAN -->
<style>
    /* Card utama */
    .register-card {
        border-radius: 10px;
        background-color: #fff;
        overflow: hidden;
    }

    /* Header Biru Cerah */
    .register-card-header {
        background-color: #0084ff;
        font-size: 1.05rem;
    }

    /* Wrapper Pilihan Kamera di Tengah */
    .camera-select-wrapper {
        max-width: 340px;
        margin: 0 auto 15px auto;
    }

    .camera-select-wrapper .input-group-text {
        border-right: none;
    }

    .camera-select-wrapper select {
        border-left: none;
        font-size: 0.9rem;
    }

    /* Container Frame Video */
    .video-container {
        position: relative;
        width: 100%;
        max-width: 680px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        aspect-ratio: 4 / 3;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scaleX(-1); /* Mirroring video agar natural */
    }

    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1); /* Mirroring canvas sinkron dengan video */
    }

    /* Tombol Aksi di Bawah Video */
    .btn-action-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-save-face {
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-save-face:hover:not(:disabled) {
        background-color: #218838;
        color: white;
    }

    .btn-save-face:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-cancel:hover {
        background-color: #5a6268;
        color: white;
    }

    /* Teks Petunjuk */
    .instruction-text {
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 15px;
        margin-bottom: 5px;
    }
</style>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    const video = document.getElementById('video');
    const btnSave = document.getElementById('btn-save');
    const btnText = document.getElementById('btn-text');
    const cameraSelect = document.getElementById('cameraSelect');
    const loadingMsg = document.getElementById('loading');

    let detectedDescriptor = null;
    let currentStream = null;
    let isProcessingFrame = false;

    // 1. Load Model AI yang Ringan saja (Hapus SsdMobilenetv1 untuk cegah LAG)
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]).then(() => {
        loadingMsg.classList.remove('alert-info');
        loadingMsg.classList.add('alert-success');
        loadingMsg.innerHTML = '<i class="fas fa-check"></i> Model AI Siap. Mengakses kamera...';
        startVideo();
    }).catch(err => {
        console.error("Gagal muat model:", err);
        loadingMsg.classList.remove('alert-info');
        loadingMsg.classList.add('alert-danger');
        loadingMsg.innerHTML = '<b>Error:</b> Gagal memuat file model AI dari folder /models.';
    });

    // 2. Mendapatkan Daftar Kamera Tersedia
    async function getCameras() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;

        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');

            cameraSelect.innerHTML = '';

            if (videoDevices.length === 0) {
                const opt = document.createElement('option');
                opt.text = "Tidak ada kamera ditemukan";
                cameraSelect.add(opt);
                return;
            }

            videoDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                let label = device.label || `Kamera ${index + 1}`;

                if (label.toLowerCase().includes('back') || label.toLowerCase().includes('rear')) {
                    label = `📷 Kamera Belakang (${label})`;
                } else if (label.toLowerCase().includes('front') || label.toLowerCase().includes('facing')) {
                    label = `🤳 Kamera Depan (${label})`;
                }

                option.text = label;
                cameraSelect.add(option);
            });

            if (currentStream) {
                const track = currentStream.getVideoTracks()[0];
                const settings = track.getSettings();
                if (settings.deviceId) {
                    cameraSelect.value = settings.deviceId;
                }
            }
        } catch (err) {
            console.error("Error enumerate devices:", err);
        }
    }

    // 3. Menjalankan Kamera dengan Reselusi Teroptimasi (640x480)
    function startVideo(deviceId = null) {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }

        const constraints = {
            video: {
                deviceId: deviceId ? { exact: deviceId } : undefined,
                width: { ideal: 640 },
                height: { ideal: 480 },
                frameRate: { ideal: 30, max: 30 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                currentStream = stream;
                video.srcObject = stream;
                loadingMsg.style.display = 'none';

                if (cameraSelect.options.length <= 1) {
                    getCameras();
                }
            })
            .catch(err => {
                console.error(err);
                loadingMsg.classList.remove('alert-success');
                loadingMsg.classList.add('alert-danger');
                loadingMsg.style.display = 'block';
                loadingMsg.innerHTML = `<b>Gagal Akses Kamera:</b> ${err.name}. Pastikan izin kamera telah diberikan.`;
            });
    }

    cameraSelect.addEventListener('change', function() {
        if (this.value) {
            startVideo(this.value);
        }
    });

    // 4. Deteksi Wajah Asinkronus Tanpa Penumpukan Frame (Lancar & Ringan)
    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');

        async function processDetection() {
            if (video.paused || video.ended) return;

            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            if (displaySize.width > 0 && displaySize.height > 0) {

                if (canvas.width !== displaySize.width || canvas.height !== displaySize.height) {
                    faceapi.matchDimensions(canvas, displaySize);
                }

                if (!isProcessingFrame) {
                    isProcessingFrame = true;

                    // TinyFaceDetector dengan inputSize 160 sangat cepat & tidak patah-patah
                    const detections = await faceapi.detectSingleFace(
                        video,
                        new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 })
                    ).withFaceLandmarks().withFaceDescriptor();

                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    if (detections) {
                        const resizedDetections = faceapi.resizeResults(detections, displaySize);
                        faceapi.draw.drawDetections(canvas, resizedDetections);

                        detectedDescriptor = detections.descriptor;
                        btnSave.disabled = false;
                        btnText.innerText = 'Wajah Terdeteksi - Klik untuk Simpan';
                    } else {
                        detectedDescriptor = null;
                        btnSave.disabled = true;
                        btnText.innerText = 'Mencari Wajah...';
                    }

                    isProcessingFrame = false;
                }
            }

            // Jeda 150ms antar frame deteksi agar pergerakan video tetap mulus
            setTimeout(processDetection, 150);
        }

        processDetection();
    });

    // 5. Simpan Data Wajah via AJAX
    btnSave.addEventListener('click', () => {
        if (!detectedDescriptor) return;

        btnSave.disabled = true;

        Swal.fire({
            title: 'Menyimpan Wajah...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const descriptorArray = Array.from(detectedDescriptor);

        $.ajax({
            url: "{{ route('face.store', $student->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                descriptor: JSON.stringify(descriptorArray)
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Wajah berhasil didaftarkan.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('face.index', ['classroom_id' => $student->classroom_id]) }}";
                });
            },
            error: function(xhr) {
                console.error(xhr);
                btnSave.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Gagal menyimpan data wajah. Silakan coba lagi.'
                });
            }
        });
    });
</script>
</x-app-layout>
