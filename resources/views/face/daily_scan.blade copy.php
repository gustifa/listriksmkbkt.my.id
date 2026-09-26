<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png')}}" type="image/png"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet"/>
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet"/>
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css')}}" rel="stylesheet"/>
	<script src="{{ asset('backend/assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css')}}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css')}}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ asset('backend/assets/css/dark-theme.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/semi-dark.css')}}"/>
	<link rel="stylesheet" href="{{ asset('backend/assets/css/header-colors.css')}}"/>

	<link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >

	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css" rel="stylesheet">
    <title>SISFO SMK | @yield('title') </title>
</head>

<body>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="shadow card">

                    <div class="text-white card-header bg-success d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-smile-beam me-2"></i> Absensi Wajah (Datang & Pulang)</span>
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-success fw-bold">Dashboard</a>
                    </div>
                    <div class="text-center card-body bg-light">

                        <div id="status-loading" class="alert alert-warning">
                            <span class="spinner-border spinner-border-sm me-2"></span> Memuat Data Wajah Seluruh Siswa...
                        </div>

                        <div class="rounded shadow-lg position-relative d-inline-block">
                            <!-- Video Webcam -->
                            <video id="video" width="640" height="480" autoplay muted style="border-radius: 10px; background: #000; object-fit: cover;"></video>
                            <canvas id="overlay" class="top-0 position-absolute start-0"></canvas>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted small">Pastikan pencahayaan cukup terang. Lepas masker/kacamata hitam.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const video = document.getElementById('video');
    const statusMsg = document.getElementById('status-loading');

    let labeledFaceDescriptors = [];
    let faceMatcher = null;
    let isProcessing = false; // Cegah spam request

     // 1. Load Models & Data Siswa
    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
    ]).then(loadStudentData);

    async function loadStudentData() {
       // Panggil API baru get-all-descriptors
        // Ambil data wajah satu kelas dari Server
        // const response = await fetch(`/face/all-descriptors`);
        const response = await fetch("{{ route('face.descriptors.all') }}");
        const data = await response.json();

        if(data.length === 0) {
                statusMsg.className = 'alert alert-danger';
                statusMsg.innerText = "Belum ada siswa yang mendaftarkan wajah di sistem!";
                return;
            }

        // Format ulang data untuk Face API
        labeledFaceDescriptors = data.map(d => {
            // Convert array biasa kembali ke Float32Array
            return new faceapi.LabeledFaceDescriptors(d.label, [new Float32Array(d.descriptor)]);
        });

        faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.5); // 0.5 Threshold (Semakin kecil semakin ketat)

        document.getElementById('status-loading').classList.remove('alert-warning');
        document.getElementById('status-loading').classList.add('alert-success');
        document.getElementById('status-loading').innerText = "Sistem Siap! Silakan menghadap kamera.";

        startVideo();
    }

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: {} })
            .then(stream => video.srcObject = stream)
            .catch(err => {
                statusMsg.className = 'alert alert-danger';
                statusMsg.innerText = "Kamera tidak terdeteksi / Izin ditolak.";
            });
    }

    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            // Jika sedang memproses data absensi, pause deteksi visual
            if(isProcessing || !faceMatcher) return;

            const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box;
                // Gambar label nama di layar
                const drawBox = new faceapi.draw.DrawBox(box, { label: result.toString() });
                drawBox.draw(canvas);

                // --- LOGIKA ABSENSI ---
                if (result.label !== 'unknown') {
                    // Format Label: "12345 - Ahmad"
                    // Ambil NIS (12345)
                    const nis = result.label.split(' - ')[0];
                    const nama = result.label.split(' - ')[1];

                    performDailyAttendance(nis, nama);
                }
            });
        }, 1000); // 1000 = Scan tiap 1 detik
    });

    // Kirim Data ke DailyAttendanceController (Sama seperti QR Code)
    function performDailyAttendance(nis, fullName) {
        isProcessing = true; // Kunci proses agar tidak double scan

        Swal.fire({
            title: 'Wajah Terdeteksi!',
            text: 'Memproses kehadiran ' + fullName + '...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('daily.store') }}", // Reuse controller QR Code
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                nis: nis
            },
            success: function(response) {
                if(response.status == 'success') {
                    // Warna Alert: Hijau (Masuk), Biru (Pulang)
                    let color = response.type == 'in' ? '#28a745' : '#17a2b8';

                    Swal.fire({
                        title: response.message,
                        text: response.student,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        iconColor: color
                    }).then(() => {
                        // Jeda sebentar agar orang tersebut pergi dari kamera
                        setTimeout(() => { isProcessing = false; }, 2000);
                    });
                } else {
                    // Error (Misal: Sudah absen pulang)
                    Swal.fire({
                        title: 'Info',
                        text: response.message,
                        icon: 'info',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        setTimeout(() => { isProcessing = false; }, 2000);
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                console.log("Error API");
                // Reset processing agar bisa scan ulang jika error koneksi
                setTimeout(() => { isProcessing = false; }, 3000);
            }
        });
    }
</script>
</body>

</html>

