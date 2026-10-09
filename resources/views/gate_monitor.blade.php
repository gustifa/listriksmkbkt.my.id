<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">

    <title>SISFO SMK | Monitor Gerbang Presisi</title>

    <style>
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 15px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-mirror {
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
        }

        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
        }

        .preview-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <h4 class="fw-bold mb-3"><i class="fas fa-door-open text-primary me-2"></i>Monitor Gerbang Presisi</h4>

                <div id="status-msg" class="alert alert-info py-2 mb-3 small fw-semibold">
                    <i class="fas fa-spinner fa-spin me-2"></i>Memuat Model Cepat (Tiny Face Detector)...
                </div>

                <div class="video-container mb-3">
                    <span id="detection-status" class="badge bg-secondary status-badge">Menunggu Kamera...</span>
                    <video id="video" class="video-mirror" autoplay muted playsinline></video>
                    <canvas id="overlay"></canvas>
                </div>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <button id="btn-switch-camera" class="btn btn-outline-secondary btn-sm fw-semibold" type="button">
                        <i class="fas fa-sync-alt me-1"></i> Ganti Kamera (<span id="camera-label">Depan</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const statusMsg = document.getElementById('status-msg');
        const detectionStatus = document.getElementById('detection-status');
        const btnSwitchCamera = document.getElementById('btn-switch-camera');
        const cameraLabel = document.getElementById('camera-label');

        let labeledDescriptors = [];
        let faceMatcher = null;
        let isProcessing = false;
        let currentStream = null;
        let currentFacingMode = "user";
        let lastDetectionTime = 0;
        const DETECTION_INTERVAL = 300; // Hanya lakukan deteksi setiap 300ms agar sangat ringan

        // 1. Muat Model Ringan (TinyFaceDetector jauh lebih cepat dari SsdMobilenetv1)
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(async () => {
            await loadStudentDescriptors();
            startWebcam();
        }).catch(err => {
            statusMsg.className = 'alert alert-danger py-2 mb-3 small';
            statusMsg.innerText = "Gagal memuat model deteksi wajah.";
        });

        // 2. Ambil data sampel wajah siswa dari server
        async function loadStudentDescriptors() {
            try {
                statusMsg.innerText = "Mengunduh database sampel wajah...";
                const res = await $.ajax({
                    url: "{{ route('face.descriptors.all') }}",
                    type: "GET",
                    dataType: "json"
                });

                if (res.length > 0) {
                    labeledDescriptors = res.map(item => {
                        const descriptors = item.descriptors.map(d => new Float32Array(JSON.parse(d)));
                        return new faceapi.LabeledFaceDescriptors(item.name + " | " + item.student_id, descriptors);
                    });

                    // Matcher dengan ambang batas (threshold) 0.5 untuk kecepatan & akurasi
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
                    statusMsg.className = 'alert alert-success py-2 mb-3 small';
                    statusMsg.innerText = "Database Wajah Siap! Sistem Siap Digunakan.";
                } else {
                    statusMsg.className = 'alert alert-warning py-2 mb-3 small';
                    statusMsg.innerText = "Belum ada sampel wajah siswa terdaftar.";
                }
            } catch (e) {
                statusMsg.className = 'alert alert-danger py-2 mb-3 small';
                statusMsg.innerText = "Gagal mengambil data dari server.";
            }
        }

        // 3. Jalankan Kamera
        function startWebcam() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }

            if (currentFacingMode === "user") {
                video.classList.add('video-mirror');
                cameraLabel.innerText = "Depan";
            } else {
                video.classList.remove('video-mirror');
                cameraLabel.innerText = "Belakang";
            }

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: currentFacingMode,
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            }).then(stream => {
                currentStream = stream;
                video.srcObject = stream;
                detectionStatus.className = "badge bg-success status-badge";
                detectionStatus.innerText = "Kamera Aktif";
            }).catch(() => {
                detectionStatus.className = "badge bg-danger status-badge";
                detectionStatus.innerText = "Akses Kamera Ditolak";
            });
        }

        btnSwitchCamera.addEventListener('click', () => {
            currentFacingMode = (currentFacingMode === "user") ? "environment" : "user";
            startWebcam();
        });

        // 4. Loop Deteksi Wajah dengan TinyFaceDetector Ter-Throttle
        video.addEventListener('play', () => {
            const displaySize = { width: video.clientWidth || 640, height: video.clientHeight || 480 };
            faceapi.matchDimensions(overlay, displaySize);

            async function processFrame(now) {
                if (video.paused || video.ended) return;

                // Batasi eksekusi hanya setiap 300ms (THROTTLE)
                if (now - lastDetectionTime >= DETECTION_INTERVAL && !isProcessing) {
                    lastDetectionTime = now;

                    // Menggunakan TinyFaceDetectorOptions dengan inputSize kecil (224) untuk kecepatan maksimal
                    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });

                    const detections = await faceapi.detectAllFaces(video, options)
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    const resizedDetections = faceapi.resizeResults(detections, displaySize);

                    const ctx = overlay.getContext('2d');
                    ctx.clearRect(0, 0, overlay.width, overlay.height);

                    if (resizedDetections.length > 0 && faceMatcher) {
                        resizedDetections.forEach(detection => {
                            const result = faceMatcher.findBestMatch(detection.descriptor);
                            const box = detection.detection.box;

                            // Koreksi posisi canvas jika kamera depan di-mirror
                            if (currentFacingMode === "user") {
                                box.x = displaySize.width - box.x - box.width;
                            }

                            const drawBox = new faceapi.draw.DrawBox(box, {
                                label: result.toString(),
                                boxColor: result.label.includes('unknown') ? 'red' : 'green'
                            });
                            drawBox.draw(overlay);

                            // Jika wajah teridentifikasi
                            if (!result.label.includes('unknown') && !isProcessing) {
                                const studentData = result.label.split(" | ");
                                const studentName = studentData[0];
                                const studentId = studentData[1];

                                processAttendance(studentId, studentName);
                            }
                        });
                    }
                }

                requestAnimationFrame(processFrame);
            }

            requestAnimationFrame(processFrame);
        });

        // 5. Proses Kirim Presensi ke Server
        function processAttendance(studentId, studentName) {
            isProcessing = true;
            detectionStatus.className = "badge bg-warning text-dark status-badge";
            detectionStatus.innerText = "Memproses: " + studentName;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            if (currentFacingMode === "user") {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0);

            $.ajax({
                url: "{{ route('izin.scan') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    student_id: studentId,
                    image: canvas.toDataURL('image/jpeg', 0.7) // Kompresi image 70% agar payload cepat dikirim
                },
                success: function(res) {
                    if (res.action === 'ask_permission') {
                        confirmPermission(res.data, canvas.toDataURL('image/jpeg', 0.7));
                    } else if (res.action === 'ask_return') {
                        confirmReturn(res.data, canvas.toDataURL('image/jpeg', 0.7));
                    } else {
                        Swal.fire({
                            title: 'Presensi Berhasil',
                            text: res.message || `Wajah ${studentName} terverifikasi!`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            isProcessing = false;
                            detectionStatus.className = "badge bg-success status-badge";
                            detectionStatus.innerText = "Kamera Aktif";
                        });
                    }
                },
                error: function(err) {
                    Swal.fire({
                        title: 'Gagal',
                        text: err.responseJSON?.message || 'Gagal memproses data.',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        isProcessing = false;
                        detectionStatus.className = "badge bg-success status-badge";
                        detectionStatus.innerText = "Kamera Aktif";
                    });
                }
            });
        }

        function confirmPermission(data, image) {
            Swal.fire({
                title: 'Konfirmasi Izin Keluar',
                text: `Siswa ${data.student.name} mengajukan izin?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Izinkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.store') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            student_id: data.student.id,
                            reason: 'Izin Keluar Gerbang',
                            image: image
                        },
                        success: () => {
                            Swal.fire({ title: 'Berhasil', text: 'Izin dicatat', icon: 'success', timer: 2000, showConfirmButton: false })
                                .then(() => { isProcessing = false; });
                        },
                        error: () => {
                            Swal.fire({ title: 'Gagal', text: 'Gagal menyimpan izin', icon: 'error', timer: 2000, showConfirmButton: false })
                                .then(() => { isProcessing = false; });
                        }
                    });
                } else {
                    isProcessing = false;
                }
            });
        }

        function confirmReturn(data, image) {
            Swal.fire({
                title: 'Konfirmasi Kembali',
                text: `${data.student.name} masuk kembali ke sekolah?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Masuk',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.return') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: data.id,
                            image: image
                        },
                        success: () => {
                            Swal.fire({ title: 'Berhasil', text: 'Siswa berhasil kembali', icon: 'success', timer: 2000, showConfirmButton: false })
                                .then(() => { isProcessing = false; });
                        },
                        error: () => {
                            Swal.fire({ title: 'Gagal', text: 'Gagal memperbarui status', icon: 'error', timer: 2000, showConfirmButton: false })
                                .then(() => { isProcessing = false; });
                        }
                    });
                } else {
                    isProcessing = false;
                }
            });
        }
    </script>
</body>
</html>
