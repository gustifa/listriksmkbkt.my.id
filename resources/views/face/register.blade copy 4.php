@section('title')
    Registrasi Wajah: {{ $student->name }}
@endsection

<x-app-layout>
<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="border-0 shadow card">
                <div class="py-3 text-white card-header bg-primary">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2"></i> Registrasi Wajah: {{ $student->name }}</h5>
                </div>
                <div class="text-center card-body bg-light">

                    <div id="loading" class="py-2 alert alert-warning small">
                        <span class="spinner-border spinner-border-sm me-2"></span> Memuat AI Ringan... Mohon tunggu sebentar.
                    </div>

                    <div class="mb-3 d-flex justify-content-center">
                        <div class="w-auto input-group input-group-sm">
                            <label class="bg-white input-group-text" for="cameraSelect"><i class="fas fa-camera"></i></label>
                            <select class="form-select" id="cameraSelect" style="max-width: 250px;">
                                <option value="" selected>Mencari kamera...</option>
                            </select>
                        </div>
                    </div>

                    <div class="shadow-sm video-container">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                    </div>

                    <div class="mt-4">
                        <button id="btn-save" class="py-3 mb-2 shadow-sm btn btn-success btn-lg w-100" disabled>
                            <i class="fas fa-search"></i> Mencari Wajah...
                        </button>
                        <a href="{{ route('face.index', ['classroom_id' => $student->classroom_id]) }}" class="btn btn-outline-secondary w-100">Batal</a>
                    </div>

                    <p class="mt-3 text-muted small"><i class="fas fa-info-circle me-1"></i> Tips: Pastikan wajah terlihat jelas dan pencahayaan terang.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .video-container {
        position: relative;
        width: 100%;
        max-width: 480px; /* Ukuran ideal untuk proses AI di HP */
        margin: 0 auto;
        border-radius: 15px;
        overflow: hidden;
        background: #000;
        aspect-ratio: 4/3;
        border: 4px solid #fff;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scaleX(-1); /* Mirror effect */
    }

    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1);
    }

    /* Animasi tombol saat aktif */
    .btn-success { transition: all 0.3s ease; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const video = document.getElementById('video');
    const btnSave = document.getElementById('btn-save');
    const cameraSelect = document.getElementById('cameraSelect');
    const loadingMsg = document.getElementById('loading');

    let detectedDescriptor = null;
    let currentStream = null;

    // 1. Load Model AI (Hanya muat yang diperlukan saja)
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]).then(() => {
        loadingMsg.classList.replace('alert-warning', 'alert-success');
        loadingMsg.innerHTML = '<i class="fas fa-check"></i> AI Siap. Mengaktifkan kamera...';
        startVideo();
    });

    // 2. Fungsi Daftar Kamera
    async function getCameras() {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');

        cameraSelect.innerHTML = videoDevices.map((dev, i) =>
            `<option value="${dev.deviceId}">${dev.label || 'Kamera ' + (i+1)}</option>`
        ).join('');
    }

    // 3. Fungsi Start Video (Optimasi Resolusi)
    function startVideo(deviceId = null) {
        if (currentStream) currentStream.getTracks().forEach(track => track.stop());

        // Resolusi rendah (480x360) membuat deteksi AI jauh lebih ringan di HP
        const constraints = {
            video: {
                deviceId: deviceId ? { exact: deviceId } : undefined,
                width: { ideal: 480 },
                height: { ideal: 360 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                currentStream = stream;
                video.srcObject = stream;
                loadingMsg.style.display = 'none';
                if (cameraSelect.options.length <= 1) getCameras();
            })
            .catch(err => {
                loadingMsg.className = 'alert alert-danger';
                loadingMsg.innerHTML = `<b>Gagal:</b> ${err.name}. Pastikan izin kamera aktif dan gunakan HTTPS.`;
            });
    }

    cameraSelect.addEventListener('change', () => startVideo(cameraSelect.value));

    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');
        const displaySize = { width: video.clientWidth, height: video.clientHeight };
        faceapi.matchDimensions(canvas, displaySize);

        // Interval deteksi (600ms) agar baterai HP tidak boros dan HP tidak panas
        setInterval(async () => {
            // Tiny Face Detector Options dengan inputSize 160 (Sangat Cepat)
            const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            if (detections) {
                const resized = faceapi.resizeResults(detections, displaySize);
                faceapi.draw.drawDetections(canvas, resized);

                detectedDescriptor = detections.descriptor;
                btnSave.disabled = false;
                btnSave.className = 'btn btn-success btn-lg w-100 mb-2 py-3';
                btnSave.innerHTML = '<i class="fas fa-check-circle"></i> Simpan Wajah Sekarang';
            } else {
                detectedDescriptor = null;
                btnSave.disabled = true;
                btnSave.className = 'btn btn-secondary btn-lg w-100 mb-2 py-3';
                btnSave.innerHTML = '<i class="fas fa-search"></i> Mencari Wajah...';
            }
        }, 600);
    });

    // 4. Kirim Data via AJAX
    btnSave.addEventListener('click', () => {
        if(!detectedDescriptor) return;

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('face.store', $student->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                descriptor: JSON.stringify(Array.from(detectedDescriptor))
            },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Registrasi Berhasil!', timer: 1500, showConfirmButton: false })
                .then(() => {
                    window.location.href = "{{ route('students.index', ['classroom_id' => $student->classroom_id]) }}";
                });
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Gagal Simpan', text: 'Koneksi terputus atau server error.' });
            }
        });
    });
</script>
@endpush
</x-app-layout>
