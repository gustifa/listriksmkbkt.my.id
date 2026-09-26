@section('title')
   Mengabsen Kelas: {{ $schedule->classroom->name }}
@endsection

<x-app-layout>
    <div class="page-content">
        
        <div class="text-center alert alert-info">
            Mengabsen Kelas: <strong>{{ $schedule->classroom->name }}</strong> <br>
            Mapel: {{ $schedule->subject->name }}
        </div>
        
        <div class="container mt-5">
            <h2 class="text-center">Scanner Absensi Kelas</h2>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div id="reader" width="600px"></div>
                    <div id="result" class="mt-3 alert d-none"></div>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            function onScanSuccess(decodedText, decodedResult) {
                // 1. Pause scanner dulu agar tidak scan berulang-ulang secepat kilat
            try {
                html5QrcodeScanner.pause();
            } catch(e) {
                // Abaikan error jika scanner sedang tidak aktif (misal upload file)
            }

            $.ajax({
                url: "{{ route('scan.store') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    nis: decodedText,
                    schedule_id: "{{ $schedule->id }}"
                },
                success: function(response) {
                    if(response.status == 'success') {
                        // --- A. SCAN BERHASIL (Hadir) ---
                        Swal.fire({
                            title: 'BERHASIL!',
                            text: response.message + ' : ' + response.student,
                            icon: 'success',
                            timer: 2000, // Otomatis tutup setelah 2 detik
                            showConfirmButton: false
                        }).then(() => {
                            // Resume scanner setelah alert tutup
                            try { html5QrcodeScanner.resume(); } catch(e){}
                        });

                        // Optional: Mainkan suara beep
                        // var audio = new Audio('path/to/beep.mp3'); audio.play();

                    } else {
                        // --- B. SCAN GAGAL (Sudah Absen / Siswa Tidak Dikenal) ---
                        Swal.fire({
                            title: 'GAGAL!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK, Scan Lanjut',
                            // timer: 2000, // Otomatis tutup setelah 2 detik
                            // showConfirmButton: false
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            // Resume scanner setelah tombol OK ditekan
                            try { html5QrcodeScanner.resume(); } catch(e){}
                        });
                    }
                },
                error: function(xhr) {
                    // --- C. ERROR SERVER (500/404) ---
                    Swal.fire({
                        title: 'Error Sistem',
                        text: 'Terjadi kesalahan koneksi atau server.',
                        icon: 'warning',
                        confirmButtonText: 'Coba Lagi'
                    }).then(() => {
                        try { html5QrcodeScanner.resume(); } catch(e){}
                    });
                }
            });
            }

            function onScanFailure(error) {
                // Biarkan kosong agar tidak spam console log
            }

            let html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { fps: 10, qrbox: 250 });
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </div>
</x-app-layout>
