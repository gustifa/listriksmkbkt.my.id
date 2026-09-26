@section('title')
   Registrasi Wajah: {{ $student->name }}
@endsection

<x-app-layout>
<div class="page-content">
    <div class="row justify-content-center">
        <!-- Menggunakan kolom responsif (Full di HP, 8 kolom di Desktop) -->
        <div class="col-12 col-md-10 col-lg-8">
            <div class="shadow card">
                <div class="text-white card-header bg-primary">
                    Registrasi Wajah: <strong>{{ $student->name }}</strong>
                </div>
                <div class="text-center card-body">

                    <!-- Loading Indicator -->
                    <div id="loading" class="alert alert-info">
                        <span class="spinner-border spinner-border-sm me-2"></span> Sedang memuat model AI... Harap tunggu.
                    </div>

                    <!-- FITUR BARU: PILIHAN KAMERA -->
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="w-auto input-group">
                            <label class="bg-white input-group-text" for="cameraSelect"><i class="fas fa-camera"></i></label>
                            <select class="form-select form-select-sm" id="cameraSelect" style="max-width: 250px;">
                                <option value="" selected>Mencari kamera...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Video Preview Container Responsif -->
                    <div class="shadow-sm video-container d-inline-block position-relative">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="overlay"></canvas>
                    </div>

                    <div class="mt-3">
                        <button id="btn-save" class="btn btn-success btn-lg" disabled>
                            <i class="fas fa-save"></i> Simpan Wajah Ini
                        </button>
                        <a href="{{ route('students.index', ['classroom_id' => $student->classroom_id]) }}" class="btn btn-secondary btn-lg">Batal</a>
                    </div>

                    <p class="mt-2 text-muted small">Pastikan wajah terlihat jelas, tidak memakai masker, dan pencahayaan cukup.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STYLE KHUSUS UNTUK RESPONSIVE VIDEO -->
<style>
    /* Agar video menyesuaikan lebar container namun tetap menjaga rasio */
    .video-container {
        position: relative;
        width: 100%;
        max-width: 640px; /* Maksimal lebar di desktop */
        margin: 0 auto;
        border-radius: 10px;
        overflow: hidden;
        background: #000;
        aspect-ratio: 4/3; /* Menjaga rasio 4:3 */
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Memenuhi container */
        display: block;
        transform: scaleX(-1); /* Efek cermin (mirror) agar natural saat registrasi */
    }

    /* Balikkan canvas juga agar sesuai dengan video yang dimirror */
    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1);
    }
</style>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Load Face API dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const video = document.getElementById('video');
    const btnSave = document.getElementById('btn-save');
    const cameraSelect = document.getElementById('cameraSelect');
    const loadingMsg = document.getElementById('loading');

    let detectedDescriptor = null;
    let currentStream = null; // Menyimpan stream aktif

    // 1. Load Model AI
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/models') // Lebih akurat dari tiny
    ]).then(() => {
        loadingMsg.classList.remove('alert-info');
        loadingMsg.classList.add('alert-success');
        loadingMsg.innerHTML = '<i class="fas fa-check"></i> Model AI Siap. Mengakses kamera...';
        startVideo();
    });

    // 2. Fungsi Mendapatkan Daftar Kamera
    async function getCameras() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;

        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');

            cameraSelect.innerHTML = '<option value="" disabled>Pilih Kamera</option>';

            if (videoDevices.length === 0) {
                const opt = document.createElement('option');
                opt.text = "Tidak ada kamera ditemukan";
                cameraSelect.add(opt);
                return;
            }

            videoDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Kamera ${index + 1}`;
                cameraSelect.add(option);
            });

            // Set select value ke kamera yang sedang aktif
            if (currentStream) {
                const track = currentStream.getVideoTracks()[0];
                const settings = track.getSettings();
                if (settings.deviceId) {
                    cameraSelect.value = settings.deviceId;
                }
            }
        } catch (err) {
            console.error("Error enumerating devices:", err);
        }
    }

    // 3. Fungsi Start Video dengan opsi Device ID
    function startVideo(deviceId = null) {
        // Stop stream sebelumnya jika ada
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }

        // Config video constraints
        // Gunakan width/height ideal untuk performa deteksi wajah yang baik
        const constraints = {
            video: {
                deviceId: deviceId ? { exact: deviceId } : undefined,
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                currentStream = stream;
                video.srcObject = stream;
                loadingMsg.style.display = 'none';

                // Isi dropdown kamera jika belum ada isinya (pertama kali load)
                if (cameraSelect.options.length <= 1) {
                    getCameras();
                }
            })
            .catch(err => {
                console.error(err);
                loadingMsg.classList.remove('alert-success');
                loadingMsg.classList.add('alert-danger');
                loadingMsg.innerHTML = `<b>Gagal Akses Kamera:</b> ${err.name}. Pastikan izin kamera diberikan dan menggunakan HTTPS/Localhost.`;
            });
    }

    // Event Listener Ganti Kamera
    cameraSelect.addEventListener('change', function() {
        if (this.value) {
            startVideo(this.value);
        }
    });

    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');

        // Fungsi responsive canvas
        function adjustCanvasSize() {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(canvas, displaySize);
            return displaySize;
        }

        let displaySize = adjustCanvasSize();

        // Resize canvas saat ukuran layar berubah
        window.addEventListener('resize', () => {
            displaySize = adjustCanvasSize();
        });

        setInterval(async () => {
            // Pastikan ukuran canvas sinkron sebelum deteksi
            const currentDisplaySize = { width: video.clientWidth, height: video.clientHeight };
            if (canvas.width !== currentDisplaySize.width || canvas.height !== currentDisplaySize.height) {
                 faceapi.matchDimensions(canvas, currentDisplaySize);
            }

            // Deteksi wajah (Single Face)
            const detections = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options())
                .withFaceLandmarks()
                .withFaceDescriptor();

            // Clear canvas
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            if (detections) {
                const resizedDetections = faceapi.resizeResults(detections, currentDisplaySize);

                // Gambar kotak di wajah
                faceapi.draw.drawDetections(canvas, resizedDetections);

                // Simpan descriptor sementara
                detectedDescriptor = detections.descriptor;

                // Update tombol
                btnSave.disabled = false;
                btnSave.classList.remove('btn-secondary');
                btnSave.classList.add('btn-success');
                btnSave.innerHTML = '<i class="fas fa-check-circle"></i> Wajah Terdeteksi - Klik untuk Simpan';
            } else {
                // Reset jika wajah hilang
                detectedDescriptor = null;
                btnSave.disabled = true;
                btnSave.classList.remove('btn-success');
                btnSave.classList.add('btn-secondary');
                btnSave.innerHTML = '<i class="fas fa-search"></i> Mencari Wajah...';
            }
        }, 500); // Scan tiap 500ms
    });

    // Simpan ke Database via AJAX
    btnSave.addEventListener('click', () => {
        if(!detectedDescriptor) return;

        // Tampilkan loading saat proses simpan
        Swal.fire({
            title: 'Menyimpan Wajah...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Convert Float32Array ke Array biasa agar bisa jadi JSON
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
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Redirect kembali ke daftar siswa di kelas tersebut
                    window.location.href = "{{ route('students.index', ['classroom_id' => $student->classroom_id]) }}";
                });
            },
            error: function(xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menyimpan data wajah. Silakan coba lagi.'
                });
            }
        });
    });
</script>
</x-app-layout>
