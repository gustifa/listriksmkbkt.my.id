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
                    <div class="text-white card-header bg-primary d-flex justify-content-between">
                        <span><i class="fas fa-user-check"></i> Absensi Wajah: {{ $schedule->classroom->name }}</span>
                        <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-light text-primary">Selesai</a>
                    </div>
                    <div class="text-center card-body">

                        <div id="status-loading" class="alert alert-warning">
                            <span class="spinner-border spinner-border-sm"></span> Memuat Data Wajah Siswa Kelas Ini...
                        </div>

                        <div class="position-relative d-inline-block">
                            <video id="video" width="640" height="480" autoplay muted style="border-radius: 10px; background: #000;"></video>
                            <canvas id="overlay" class="top-0 position-absolute start-0"></canvas>
                        </div>

                        <div id="last-scanned" class="mt-3 alert alert-success d-none">
                            Berhasil Absen: <strong id="student-name"></strong>
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
    const scheduleId = "{{ $schedule->id }}";
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
        // Ambil data wajah satu kelas dari Server
        const response = await fetch(`/face/descriptors/${scheduleId}`);
        const data = await response.json();

        if(data.length === 0) {
            alert("Belum ada siswa di kelas ini yang mendaftarkan wajah!");
            return;
        }

        // Format ulang data untuk Face API
        labeledFaceDescriptors = data.map(d => {
            // Convert array biasa kembali ke Float32Array
            return new faceapi.LabeledFaceDescriptors(d.label, [new Float32Array(d.descriptor)]);
        });

        faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6); // 0.6 = Threshold kemiripan

        document.getElementById('status-loading').classList.remove('alert-warning');
        document.getElementById('status-loading').classList.add('alert-success');
        document.getElementById('status-loading').innerText = "Sistem Siap! Silakan menghadap kamera.";

        startVideo();
    }

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: {} })
            .then(stream => video.srcObject = stream)
            .catch(err => console.error(err));
    }

    video.addEventListener('play', () => {
        const canvas = document.getElementById('overlay');
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if(isProcessing || !faceMatcher) return;

            const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, { label: result.toString() });
                drawBox.draw(canvas);

                // LOGIKA ABSENSI
                if (result.label !== 'unknown') {
                    // Label format: "12345 - Ahmad"
                    // Kita butuh NIS nya saja -> "12345"
                    const nis = result.label.split(' - ')[0];

                    performAttendance(nis, result.label);
                }
            });
        }, 1000); // Scan setiap 1 detik
    });

    function performAttendance(nis, fullName) {
        isProcessing = true; // Pause deteksi

        $.ajax({
            url: "{{ route('scan.store') }}", // Pakai route store yang SAMA dengan QR Code
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                nis: nis,
                schedule_id: scheduleId
            },
            success: function(response) {
                // Tampilkan notifikasi sukses
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message + ' : ' + fullName,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    isProcessing = false; // Resume deteksi
                });
            },
            error: function(xhr) {
                // Error (misal sudah absen)
                
                // Kita abaikan alert error agar tidak mengganggu flow, cukup console log
                // atau gunakan Toast kecil
                console.log(xhr.responseJSON.message);
                isProcessing = false; // Resume deteksi
            }
        });
    }
</script>
</body>

</html>

