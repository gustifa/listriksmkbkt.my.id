@section('title', 'Scan QR Kelas - ' . $schedule->classroom->name)

<x-app-layout>
    <!-- Meta CSRF Token untuk AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="page-content">
        
        <!-- Info Jadwal yang Sedang Diabsen -->
        <div class="text-center alert alert-info shadow-sm mb-4">
            <h5 class="mb-0 fw-bold">{{ $schedule->classroom->name }}</h5>
            <small class="d-block fw-bold text-dark">{{ $schedule->subject->name }}</small>
            <span class="badge bg-primary mt-1">
                <i class="fas fa-clock me-1"></i>
                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </span>
        </div>
        
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow border-0">
                        <div class="card-header bg-dark text-white text-center d-flex justify-content-between align-items-center">
                            <span class="fw-bold"><i class="fas fa-qrcode me-2"></i> SCANNER KEHADIRAN PEMBELAJARAN</span>
                            <a href="{{ route('schedule.index') }}" class="btn btn-sm btn-secondary rounded-circle" title="Tutup">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <div class="card-body bg-light">
                            
                            <!-- Peringatan Audio Browser -->
                            <div id="audio-alert" class="alert alert-warning small mb-2" style="display:none;">
                                <i class="fas fa-volume-mute"></i> Klik di mana saja pada layar agar suara notifikasi aktif.
                            </div>

                            <!-- Area Kamera Scanner -->
                            <!-- Library html5-qrcode akan merender video di sini -->
                            <div id="reader" style="width: 100%; border-radius: 8px; overflow:hidden; border: 2px solid #333; background: #000;"></div>
                            
                            <div class="mt-3 text-center text-muted small">
                                <i class="fas fa-info-circle me-1"></i> Arahkan QR Code Kartu Pelajar ke kamera.
                            </div>

                            <div class="mt-4 text-center">
                                <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Jadwal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load Scripts -->
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready(function() {
                
                // --- 1. SETUP AUDIO (BEEP) ---
                // Pastikan file audio ada di public/audio/
                const audioSuccess = new Audio("{{ asset('audio/success.mp3') }}");
                const audioError = new Audio("{{ asset('audio/error.mp3') }}");

                function playSound(type) {
                    let sound = (type === 'success') ? audioSuccess : audioError;
                    sound.currentTime = 0;
                    sound.play().catch(e => {
                        console.warn('Audio autoplay blocked by browser');
                        $('#audio-alert').show(); // Tampilkan instruksi klik jika di-block
                    });
                }

                // --- 2. LOGIKA SAAT SCAN SUKSES ---
                function onScanSuccess(decodedText, decodedResult) {
                    // Pause scanner agar tidak scan berulang kali dalam hitungan milidetik
                    try { html5QrcodeScanner.pause(); } catch(e) {}

                    // Tampilkan Loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mengecek data siswa...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Kirim ke Backend via AJAX
                    $.ajax({
                        url: "{{ route('attendance.store_scan') }}", // Route POST ke AttendanceController@store
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            nis: decodedText,
                            schedule_id: "{{ $schedule->id }}"
                        },
                        success: function(res) {
                            if(res.status == 'success') {
                                // --- SUKSES (Hadir) ---
                                playSound('success');
                                Swal.fire({
                                    title: 'BERHASIL!',
                                    html: `<h4 class="text-success fw-bold">${res.student}</h4><p class="text-muted small">${res.message}</p>`,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    try { html5QrcodeScanner.resume(); } catch(e){}
                                });

                            } else if (res.status == 'warning') {
                                // --- WARNING (Sudah Absen) ---
                                playSound('error');
                                Swal.fire({
                                    title: 'PERHATIAN',
                                    text: res.message,
                                    icon: 'warning',
                                    timer: 2500,
                                    showConfirmButton: false
                                }).then(() => {
                                    try { html5QrcodeScanner.resume(); } catch(e){}
                                });

                            } else {
                                // --- GAGAL (Salah Kelas / Security Alert) ---
                                playSound('error');
                                Swal.fire({
                                    title: 'GAGAL!',
                                    text: res.message,
                                    icon: 'error',
                                    timer: 2500,
                                    showConfirmButton: false
                                    // confirmButtonText: 'OK, Lanjut',
                                    // confirmButtonColor: '#d33'
                                }).then(() => {
                                    try { html5QrcodeScanner.resume(); } catch(e){}
                                });
                            }
                        },
                        error: function(xhr) {
                            // --- ERROR SERVER (500, 404, dll) ---
                            playSound('error');
                            let msg = 'Terjadi kesalahan sistem.';
                            
                            // Ambil pesan error spesifik dari Laravel jika ada
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                title: 'Error Server',
                                text: msg,
                                icon: 'error',
                                confirmButtonText: 'Coba Lagi'
                            }).then(() => {
                                try { html5QrcodeScanner.resume(); } catch(e){}
                            });
                        }
                    });
                }

                function onScanFailure(error) {
                    // Biarkan kosong untuk menghindari spam log di console browser
                }

                // --- 3. INISIALISASI SCANNER ---
                let html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: 250,
                        aspectRatio: 1.0 
                    },
                    /* verbose= */ false
                );
                
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            });
        </script>
    </div>
</x-app-layout>