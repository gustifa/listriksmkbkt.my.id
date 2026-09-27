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

    <title>SISFO SMK | Monitor Gerbang (Mobile Optimized)</title>
    
    <style>
        .video-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1); }
        #capture-canvas { display: none; }
        .btn-group .btn { font-size: 0.85rem; padding: 10px 5px; }
    </style>
</head>

<body class="bg-light">
    <div class="page-content">
        <div class="row justify-content-center pt-2">
            <div class="col-12 col-md-8 text-center">
                <div class="shadow card border-0">
                    <div class="text-white card-header bg-success d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold small"><i class="fas fa-bolt me-1"></i> GERBANG SMK</span>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold py-0">DASHBOARD</a>
                    </div>
                    <div class="card-body px-2">
                        
                        <div class="mb-2 d-flex justify-content-center">
                            <div class="input-group input-group-sm w-100">
                                <span class="input-group-text bg-white"><i class="fas fa-video"></i></span>
                                <select id="camera-select" class="form-select border-start-0 shadow-none">
                                    <option value="">Mencari Kamera...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_harian" value="harian" checked>
                                <label class="btn btn-outline-primary fw-bold" for="mode_harian">HARIAN</label>
                                <input type="radio" class="btn-check" name="mode_absen" id="mode_izin" value="izin_keluar">
                                <label class="btn btn-outline-warning fw-bold text-dark" for="mode_izin">IZIN</label>
                            </div>
                        </div>

                        <div id="status-loading" class="alert alert-warning py-1 mb-2 small">
                            <span class="spinner-border spinner-border-sm me-1"></span> Loading AI Models...
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
        const cameraSelect = document.getElementById('camera-select');
        const statusMsg = document.getElementById('status-loading');
        const captureCanvas = document.getElementById('capture-canvas');
        
        let faceMatcher = null;
        let isProcessing = false;
        let currentStream = null;

        // 1. LOAD MODEL VERSI TINY (DENGAN PENANGANAN ERROR LENGKAP)
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceLandmark68Net.loadFromUri("{{ asset('models') }}"),
            faceapi.nets.faceRecognitionNet.loadFromUri("{{ asset('models') }}")
        ]).then(initSystem).catch(err => {
            console.error("Gagal memuat berkas model AI:", err);
            statusMsg.className = 'alert alert-danger py-1 small';
            statusMsg.innerText = "Error: Berkas model AI tidak dapat diakses di folder public/models.";
        });

        async function initSystem() {
            try {
                // Pemicu awal izin kamera agar label nama kamera terbaca oleh browser
                try {
                    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    tempStream.getTracks().forEach(track => track.stop());
                } catch (camErr) {
                    console.warn("Akses kamera belum disetujui atau diblokir:", camErr);
                }

                // Ambil daftar perangkat kamera
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                
                cameraSelect.innerHTML = '';
                if (videoDevices.length === 0) {
                    cameraSelect.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                } else {
                    videoDevices.forEach((d, i) => {
                        const opt = document.createElement('option');
                        opt.value = d.deviceId;
                        opt.text = d.label || `Kamera ${i + 1}`;
                        cameraSelect.appendChild(opt);
                    });
                }

                // Ambil data deskriptor wajah dari backend Laravel
                const response = await fetch("{{ route('face.descriptors.all') }}");
                if (!response.ok) {
                    throw new Error(`HTTP Error Status: ${response.status} saat mengambil deskriptor.`);
                }
                const data = await response.json();
                
                if (!Array.isArray(data) || data.length === 0) {
                    console.warn("Data deskriptor dari server kosong.");
                }

                // Parsing deskriptor (aman untuk format String JSON maupun Array)
                const labeledDescriptors = data.map(d => {
                    let rawDescriptor = d.descriptor;
                    if (typeof rawDescriptor === 'string') {
                        rawDescriptor = JSON.parse(rawDescriptor);
                    }
                    return new faceapi.LabeledFaceDescriptors(
                        d.label, 
                        [new Float32Array(rawDescriptor)]
                    );
                });

                if (labeledDescriptors.length > 0) {
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
                } else {
                    console.warn("Belum ada data wajah terdaftar yang valid di database.");
                }
                
                statusMsg.className = 'alert alert-success py-1 small';
                statusMsg.innerText = "Sistem Siap!";
                
                if (cameraSelect.value) {
                    startCamera(cameraSelect.value);
                }
            } catch (err) {
                // Cetak detail error ke Console DevTools
                console.error("Detail Error Init System:", err);
                statusMsg.className = 'alert alert-danger py-1 small';
                statusMsg.innerText = `Error: ${err.message || "Gagal muat AI."}`;
            }
        }

        cameraSelect.addEventListener('change', () => startCamera(cameraSelect.value));

        function startCamera(deviceId) {
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());
            const constraints = { video: { deviceId: deviceId ? { exact: deviceId } : undefined } };
            navigator.mediaDevices.getUserMedia(constraints).then(s => {
                currentStream = s;
                video.srcObject = s;
            }).catch(err => {
                console.error("Gagal membuka stream kamera:", err);
            });
        }

        // FUNGSI SCREENSHOT OPTIMASI HP (KOMPRESI TINGGI)
        function takeScreenshot() {
            captureCanvas.width = 320; 
            captureCanvas.height = 240;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, 320, 240);
            return captureCanvas.toDataURL('image/jpeg', 0.4);
        }

        video.addEventListener('play', () => {
            const overlay = document.getElementById('overlay');
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(overlay, displaySize);

            setInterval(async () => {
                if(isProcessing || !faceMatcher) return;

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 }))
                    .withFaceLandmarks().withFaceDescriptors();

                const resized = faceapi.resizeResults(detections, displaySize);
                overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);

                resized.forEach(det => {
                    const match = faceMatcher.findBestMatch(det.descriptor);
                    if (match.label !== 'unknown' && match.distance < 0.45) {
                        isProcessing = true; 
                        const screenshot = takeScreenshot();
                        const [nis, name] = match.label.split(' - ');
                        handleAction(nis, name, screenshot);
                    }
                });
            }, 1500);
        });

        function handleAction(nis, name, image) {
            let mode = $('input[name="mode_absen"]:checked').val();
            if (mode === 'izin_keluar') {
                checkPermission(nis, name, image);
            } else {
                submitAttendance(nis, name, image);
            }
        }

        function submitAttendance(nis, name, image) {
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: "{{ route('daily.store') }}",
                type: "POST",
                data: { nis: nis, mode: 'harian', image: image },
                success: function(res) {
                    Swal.fire({ title: 'Berhasil', text: name, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => { isProcessing = false; });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || "Gagal Absen";
                    Swal.fire({ title: 'Gagal', text: msg, icon: 'error', timer: 3000, showConfirmButton: false }).then(() => { isProcessing = false; });
                }
            });
        }

        function checkPermission(nis, name, image) {
            $.ajax({
                url: "{{ route('izin.check') }}", type: "POST", data: { nis: nis },
                success: function(res) {
                    if (res.status === 'active_permission') { confirmReturn(res.data, image); } 
                    else if (res.status === 'can_leave') { inputReason(nis, name, image); } 
                    else { Swal.fire({ title: 'Info', text: res.message, icon: 'info', timer: 3000, showConfirmButton: false }).then(() => isProcessing = false); }
                },
                error: () => { isProcessing = false; }
            });
        }

        function inputReason(nis, name, image) {
            Swal.fire({
                title: 'Alasan Keluar', text: name, input: 'text',
                showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.store') }}", type: "POST",
                        data: { nis: nis, reason: result.value, image: image },
                        success: (res) => {
                            Swal.fire({ icon: 'success', title: 'Berhasil', html: `<a href="{{ url('izin/print') }}/${res.id}" target="_blank" class="btn btn-primary mt-2">CETAK IZIN</a>`, showConfirmButton: true, confirmButtonText: 'Selesai' }).then(() => isProcessing = false);
                        },
                        error: () => { isProcessing = false; }
                    });
                } else { isProcessing = false; }
            });
        }

        function confirmReturn(data, image) {
            Swal.fire({ title: 'Siswa Kembali?', text: `${data.student.name}`, icon: 'question', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('izin.return') }}", type: "POST", data: { id: data.id, image: image },
                        success: () => { Swal.fire({ title: 'Selesai', icon: 'success', timer: 2000, showConfirmButton: false }).then(() => isProcessing = false); },
                        error: () => { isProcessing = false; }
                    });
                } else { isProcessing = false; }
            });
        }
    </script>
</body>
</html>