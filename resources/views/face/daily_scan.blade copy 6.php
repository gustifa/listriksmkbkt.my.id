<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png')}}" type="image/png"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">

    <title>SISFO SMK | Monitor Gerbang</title>
    
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); }
        /* Canvas screenshot disembunyikan */
        #capture-canvas { display: none; }
    </style>
</head>

<body class="bg-light">
    <div class="page-content">
        <div class="row justify-content-center pt-4">
            <div class="col-12 col-md-10 col-lg-8 text-center">
                <div class="shadow card border-0">
                    <div class="text-white card-header bg-success d-flex justify-content-between align-items-center py-3">
                        <span class="fw-bold"><i class="fas fa-camera me-2"></i> MONITOR GERBANG</span>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold">DASHBOARD</a>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="btn-group w-75" role="group">
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_harian" value="harian" checked>
                                <label class="btn btn-outline-primary py-2 fw-bold" for="mode_harian">ABSENSI HARIAN</label>
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_izin" value="izin_keluar">
                                <label class="btn btn-outline-warning py-2 fw-bold text-dark" for="mode_izin">IZIN KELUAR</label>
                            </div>
                        </div>

                        <div id="status-loading" class="alert alert-warning py-2 mb-3">
                            <span class="spinner-border spinner-border-sm me-2"></span> Menginisialisasi Kamera...
                        </div>

                        <div class="video-container">
                            <video id="video" autoplay muted playsinline></video>
                            <canvas id="overlay"></canvas>
                        </div>

                        <canvas id="capture-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        const video = document.getElementById('video');
        const statusMsg = document.getElementById('status-loading');
        const captureCanvas = document.getElementById('capture-canvas');
        let faceMatcher = null;
        let isProcessing = false;

        // 1. Load Face Models
        Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(loadDescriptors);

        async function loadDescriptors() {
            try {
                const response = await fetch("{{ route('face.descriptors.all') }}");
                const data = await response.json();
                if(data.length === 0) {
                    statusMsg.className = 'alert alert-danger';
                    statusMsg.innerText = "Data wajah belum terdaftar!";
                    return;
                }
                const labeledDescriptors = data.map(d => new faceapi.LabeledFaceDescriptors(d.label, [new Float32Array(d.descriptor)]));
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
                statusMsg.className = 'alert alert-success';
                statusMsg.innerText = "Sistem Aktif! Menunggu Wajah...";
                startCamera();
            } catch (err) {
                statusMsg.className = 'alert alert-danger';
                statusMsg.innerText = "Gagal memuat data wajah.";
            }
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: {} })
                .then(stream => { video.srcObject = stream; })
                .catch(() => { statusMsg.innerText = "Kamera tidak aktif."; });
        }

        // FUNGSI SCREENSHOT
        function takeScreenshot() {
            captureCanvas.width = video.videoWidth;
            captureCanvas.height = video.videoHeight;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
            return captureCanvas.toDataURL('image/jpeg', 0.8);
        }

        video.addEventListener('play', () => {
            const overlay = document.getElementById('overlay');
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(overlay, displaySize);

            setInterval(async () => {
                if(isProcessing || !faceMatcher) return;
                const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options()).withFaceLandmarks().withFaceDescriptors();
                const resized = faceapi.resizeResults(detections, displaySize);
                overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);

                resized.forEach(det => {
                    const match = faceMatcher.findBestMatch(det.descriptor);
                    new faceapi.draw.DrawBox(det.detection.box, { label: match.toString() }).draw(overlay);

                    if (match.label !== 'unknown' && match.distance < 0.45) {
                        isProcessing = true; 
                        const screenshot = takeScreenshot(); // AMBIL SCREENSHOT
                        const [nis, name] = match.label.split(' - ');
                        handleAction(nis, name, screenshot);
                    }
                });
            }, 1000);
        });

        function handleAction(nis, name, image) {
            let mode = $('input[name="mode_absen"]:checked').val();
            if (mode === 'izin_keluar') {
                checkPermission(nis, name, image);
            } else {
                submitAttendance(nis, name, image);
            }
        }

        // --- 1. AJAX: ABSENSI HARIAN ---
        function submitAttendance(nis, name, image) {
            Swal.fire({ title: 'Memproses...', text: name, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ route('daily.store') }}",
                type: "POST",
                data: { nis: nis, mode: 'harian', image: image }, // KIRIM IMAGE
                success: function(res) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: res.message + " (" + name + ")",
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { isProcessing = false; });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || "Gagal Absen";
                    Swal.fire({
                        title: 'Gagal',
                        text: msg,
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false 
                    }).then(() => { isProcessing = false; });
                }
            });
        }

        // --- 2. AJAX: CEK IZIN ---
        function checkPermission(nis, name, image) {
            $.ajax({
                url: "{{ route('izin.check') }}",
                type: "POST",
                data: { nis: nis },
                success: function(res) {
                    if (res.status === 'active_permission') {
                        confirmReturn(res.data, image);
                    } else if (res.status === 'can_leave') {
                        inputReason(nis, name, image);
                    } else {
                        Swal.fire({ title: 'Info', text: res.message, icon: 'info', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                    }
                },
                error: function() {
                    Swal.fire({ title: 'Error', text: 'Sistem Error', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                }
            });
        }

        function inputReason(nis, name, image) {
            Swal.fire({
                title: 'Alasan Keluar',
                text: name,
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    savePermission(nis, result.value, image);
                } else {
                    isProcessing = false;
                }
            });
        }

        function savePermission(nis, reason, image) {
            $.ajax({
                url: "{{ route('izin.store') }}",
                type: "POST",
                data: { nis: nis, reason: reason, image: image }, // KIRIM IMAGE
                success: (res) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Izin Disimpan',
                        html: `<a href="{{ url('izin/print') }}/${res.id}" target="_blank" class="btn btn-primary mt-3">CETAK SURAT IZIN</a>`,
                        showConfirmButton: true,
                        confirmButtonText: 'Selesai'
                    }).then(() => isProcessing = false);
                },
                error: function() {
                    Swal.fire({ title: 'Gagal', text: 'Gagal simpan izin', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
                }
            });
        }

        function confirmReturn(data, image) {
            Swal.fire({
                title: 'Siswa Kembali?',
                text: `${data.student.name} ingin masuk?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Masuk',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.return') }}",
                        type: "POST",
                        data: { id: data.id, image: image }, // KIRIM IMAGE
                        success: () => {
                            Swal.fire({ title: 'Berhasil', text: 'Siswa masuk kembali', icon: 'success', timer: 2000, showConfirmButton: false }).then(() => isProcessing = false);
                        },
                        error: function() {
                            Swal.fire({ title: 'Gagal', text: 'Gagal update status', icon: 'error', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false);
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