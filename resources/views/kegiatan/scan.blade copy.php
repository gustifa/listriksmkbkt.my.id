@extends('layouts.app') <!-- Sesuaikan dengan layout Anda -->

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h2 class="mb-4">Scan QR Code Absensi</h2>
            
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Area Kamera akan muncul di div ini -->
                    <div id="reader" style="width: 100%; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden;"></div>
                    
                    <!-- Area Pesan/Notifikasi (Sukses/Gagal) -->
                    <div id="result" class="mt-4" style="display: none;">
                        <div class="alert" id="result-alert" role="alert">
                            <h4 class="alert-heading" id="result-title"></h4>
                            <p id="result-message" class="mb-0"></p>
                        </div>
                        <!-- Tombol untuk scan ulang (muncul setelah berhasil/gagal) -->
                        <button class="btn btn-primary mt-2" id="btn-scan-ulang" onclick="resetScanner()" style="display: none;">Scan Ulang</button>
                    </div>

                    <p class="text-muted mt-3 small">Arahkan kamera ke QR Code kegiatan. Pastikan pencahayaan cukup.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load library HTML5-QRCode via CDN -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<!-- Tambahkan tag meta CSRF Token di head layout.app Anda jika belum ada -->
<!-- <meta name="csrf-token" content="{{ csrf_token() }}"> -->

<script>
    // Inisialisasi variabel scanner
    let html5QrcodeScanner;
    let isProcessing = false; // Mencegah scan berulang saat sedang memproses data

    // Fungsi yang dipanggil ketika QR Code berhasil terbaca
    function onScanSuccess(decodedText, decodedResult) {
        // Jika sedang memproses data sebelumnya, abaikan scan ini
        if (isProcessing) return;
        isProcessing = true;

        // 1. Hentikan (pause) scanner sementara agar tidak scan berulang kali
        html5QrcodeScanner.pause(true);

        // 2. Kirim data (decodedText / kode unik) ke server via AJAX Fetch
        // Pastikan Anda memiliki meta tag csrf-token di layout utama Anda
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('scan.proses') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ kode_unik: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            // Tampilkan area hasil
            const resultDiv = document.getElementById('result');
            const resultAlert = document.getElementById('result-alert');
            const resultTitle = document.getElementById('result-title');
            const resultMessage = document.getElementById('result-message');
            const btnUlang = document.getElementById('btn-scan-ulang');

            resultDiv.style.display = 'block';
            btnUlang.style.display = 'inline-block';

            // Bersihkan class alert sebelumnya
            resultAlert.className = 'alert';

            // Tangani response dari server
            if (data.status === 'success') {
                resultAlert.classList.add('alert-success');
                resultTitle.innerHTML = '<i class="bi bi-check-circle-fill"></i> Berhasil!';
                resultMessage.innerText = data.message;
                
                // Opsional: Mainkan suara 'beep' sukses di sini
                
            } else if (data.status === 'warning') {
                resultAlert.classList.add('alert-warning');
                resultTitle.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Perhatian!';
                resultMessage.innerText = data.message;
            } else {
                resultAlert.classList.add('alert-danger');
                resultTitle.innerHTML = '<i class="bi bi-x-circle-fill"></i> Gagal!';
                resultMessage.innerText = data.message || 'Terjadi kesalahan tidak dikenal.';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Tangani error jaringan / server
            const resultDiv = document.getElementById('result');
            const resultAlert = document.getElementById('result-alert');
            
            resultDiv.style.display = 'block';
            resultAlert.className = 'alert alert-danger';
            document.getElementById('result-title').innerHTML = 'Error Server!';
            document.getElementById('result-message').innerText = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
            document.getElementById('btn-scan-ulang').style.display = 'inline-block';
        });
    }

    // Fungsi yang dipanggil jika gagal membaca (biasanya diabaikan saja)
    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // console.warn(`Code scan error = ${error}`);
    }

    // Fungsi untuk memulai ulang scanner setelah sukses/gagal
    function resetScanner() {
        document.getElementById('result').style.display = 'none';
        document.getElementById('btn-scan-ulang').style.display = 'none';
        isProcessing = false;
        
        // Lanjutkan scanner (resume)
        html5QrcodeScanner.resume();
    }

    // Konfigurasi dan mulai HTML5 QRCode Scanner saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 10,             // Frame per second (kecepatan scan)
                qrbox: {width: 250, height: 250}, // Ukuran kotak fokus scanner
                aspectRatio: 1.0,    // Rasio aspek kamera
                showTorchButtonIfSupported: true // Tampilkan tombol flash jika didukung HP
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    });
</script>
@endsection