@section('title', 'Scan Wajah - ' . $schedule->classroom->name)

<x-app-layout>
    <!-- Tambahkan CSRF Token untuk AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shadow card">
                    <div class="text-white card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i> Absensi Wajah</h5>
                        <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="text-center card-body">

                        <!-- Status Loading -->
                        <div id="status-loading" class="alert alert-info d-flex align-items-center justify-content-center">
                            <span class="spinner-border spinner-border-sm me-2"></span> 
                            <span>Sedang memuat model AI & Data Wajah...</span>
                        </div>

                        <!-- Area Kamera -->
                        <div class="position-relative d-inline-block rounded overflow-hidden shadow-sm" style="max-width: 100%;">
                            <video id="video" width="640" height="480" autoplay muted playsinline style="background: #000; transform: scaleX(-1);"></video>
                            <canvas id="overlay" class="position-absolute top-0 start-0" style="transform: scaleX(-1);"></canvas>
                        </div>

                        <!-- Info Kelas -->
                        <div class="mt-3">
                            <h5>Kelas: <strong>{{ $schedule->classroom->name }}</strong></h5>
                            <p class="text-muted">Mapel: {{ $schedule->subject->name }}</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Load Library -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const video = document.getElementById('video');
        const scheduleId = "{{ $schedule->id }}";
        let labeledFaceDescriptors = [];
        let faceMatcher = null;
        let isProcessing = false; // Flag untuk mencegah double scan
        let detectionInterval;

        // 1. Inisialisasi Model
        Promise.all([
            // Pastikan folder /models ada di folder public/ dan berisi file model face-api
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models')
        ]).then(loadStudentData).catch(err => {
            console.error(err);
            showAlert('error', 'Gagal memuat model AI. Pastikan folder /models tersedia.');
        });

        // 2. Ambil Data Wajah Siswa dari Server
        async function loadStudentData() {
            try {
                // Endpoint ini harus mengembalikan JSON berisi label (nama/nis) dan descriptor
                const response = await fetch(`/face/descriptors/${scheduleId}`);
                const data = await response.json();

                if(data.length === 0) {
                    showAlert('warning', "Belum ada data wajah siswa di kelas ini.");
                    return;
                }

                // Convert data dari DB ke format Face API
                labeledFaceDescriptors = data.map(d => {
                    return new faceapi.LabeledFaceDescriptors(d.label, [new Float32Array(d.descriptor)]);
                });

                faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.5); // 0.5 = Toleransi kemiripan

                // Update UI
                const statusEl = document.getElementById('status-loading');
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-success');
                statusEl.innerHTML = '<i class="fas fa-camera me-2"></i> Kamera Siap. Silakan menghadap kamera.';
                
                startVideo();

            } catch (error) {
                console.error("Error loading student data:", error);
                showAlert('error', 'Gagal mengambil data wajah siswa.');
            }
        }

        // 3. Nyalakan Kamera
        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: {} })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.error(err);
                    showAlert('error', 'Gagal mengakses kamera. Izinkan akses kamera di browser.');
                });
        }

        // 4. Proses Deteksi
        video.addEventListener('play', () => {
            const canvas = document.getElementById('overlay');
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            detectionInterval = setInterval(async () => {
                // Jika sedang memproses data ke server, jangan deteksi dulu
                if(isProcessing || !faceMatcher) return;

                const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                
                // Bersihkan canvas
                const context = canvas.getContext('2d');
                context.clearRect(0, 0, canvas.width, canvas.height);

                const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

                results.forEach((result, i) => {
                    const box = resizedDetections[i].detection.box;
                    
                    // -- VISUALISASI KOTAK --
                    // Hijau jika dikenal, Merah jika unknown
                    const color = result.label === 'unknown' ? '#ef4444' : '#10b981'; 
                    const drawBox = new faceapi.draw.DrawBox(box, { 
                        label: result.label === 'unknown' ? 'Tidak Dikenal' : result.label,
                        boxColor: color,
                        drawLabelOptions: { backgroundColor: color }
                    });
                    drawBox.draw(canvas);

                    // -- LOGIKA ABSENSI --
                    if (result.label !== 'unknown') {
                        // Label format di DB biasanya: "NIS - NAMA"
                        // Kita ambil NIS nya saja
                        const labelParts = result.label.split(' - ');
                        const nis = labelParts[0];
                        const name = labelParts.length > 1 ? labelParts[1] : nis;

                        performAttendance(nis, name);
                    }
                });
            }, 1000); // Deteksi setiap 1 detik
        });

        // 5. Kirim ke Server
        function performAttendance(nis, fullName) {
            isProcessing = true; // Kunci proses agar tidak spam request

            // Tampilkan Loading Swal
            Swal.fire({
                title: 'Mencatat Kehadiran...',
                text: fullName,
                timer: 1000,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('attendance.store_scan') }}", // Menggunakan route yang sama dengan QR
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    nis: nis,
                    schedule_id: scheduleId,
                    method: 'face' // Penanda bahwa ini absen wajah
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        isProcessing = false; // Buka kunci proses
                    });
                },
                error: function(xhr) {
                    // Jika error karena sudah absen, beri notif info saja
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    let icon = 'error';
                    
                    if (xhr.status === 200 || (xhr.responseJSON && xhr.responseJSON.status === 'warning')) {
                        icon = 'warning';
                    }

                    Swal.fire({
                        title: 'Info',
                        text: msg,
                        icon: icon,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        isProcessing = false; // Buka kunci proses
                    });
                }
            });
        }

        function showAlert(icon, message) {
            Swal.fire({
                icon: icon,
                title: 'Info Sistem',
                text: message
            });
        }
        
        // Cleanup saat keluar halaman (SPA handling jika ada)
        window.addEventListener('beforeunload', () => {
            if(detectionInterval) clearInterval(detectionInterval);
        });
    </script>
    @endpush
</x-app-layout>