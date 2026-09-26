@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h2 class="mb-4 fw-bold text-primary"><i class="fas fa-qrcode me-2"></i>Scanner Absensi Bengkel</h2>
            
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <div id="gps-status" class="alert alert-info border-0 mb-3 shadow-sm">
                        <i class="fas fa-spinner fa-spin me-2"></i> Sedang mengunci sinyal GPS...
                    </div>

                    <div id="reader" class="rounded-3 overflow-hidden shadow-sm" style="width: 100%; max-width: 500px; margin: 0 auto; border: 2px solid #dee2e6;"></div>
                    
                    <div id="result" class="mt-4" style="display: none;">
                        <div class="alert shadow-sm" id="result-alert" role="alert">
                            <h4 class="alert-heading fw-bold" id="result-title"></h4>
                            <p id="result-message" class="mb-0 font-monospace"></p>
                        </div>
                        <button class="btn btn-primary btn-lg rounded-pill px-5 mt-2 shadow" id="btn-scan-ulang" onclick="resetScanner()">
                            <i class="fas fa-sync-alt me-2"></i> Scan Ulang
                        </button>
                    </div>

                    <p class="text-muted mt-3 small italic">
                        <i class="fas fa-info-circle me-1"></i> Pastikan Anda berada di area kegiatan dan GPS HP aktif.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>



<script>
    let html5QrcodeScanner;
    let isProcessing = false;
    let userLat = null;
    let userLng = null;
    let isLocationReady = false;

    // 1. FUNGSI UNTUK MENGUNCI LOKASI (WAJIB TERBACA DULU)
    function getLocation() {
        const gpsStatus = document.getElementById('gps-status');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;
                    isLocationReady = true;

                    gpsStatus.className = "alert alert-success border-0 mb-3 shadow-sm";
                    gpsStatus.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i> GPS Terkunci! Silakan lakukan scan.';
                    console.log("Location Ready:", userLat, userLng);
                },
                (error) => {
                    isLocationReady = false;
                    gpsStatus.className = "alert alert-danger border-0 mb-3 shadow-sm";
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            gpsStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Izin GPS Ditolak! Mohon izinkan lokasi di browser.';
                            break;
                        default:
                            gpsStatus.innerHTML = '<i class="fas fa-satellite-dish me-2"></i> Sinyal GPS Lemah. Mohon cari tempat terbuka.';
                            break;
                    }
                },
                { 
                    enableHighAccuracy: true, // Akurasi Tinggi untuk Area Bengkel
                    timeout: 10000, 
                    maximumAge: 0 
                }
            );
        } else {
            gpsStatus.innerHTML = "Browser Anda tidak mendukung GPS.";
        }
    }

    // 2. FUNGSI SAAT SCAN BERHASIL
    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;

        // VALIDASI: LOKASI HARUS TERBACA DULU
        if (!isLocationReady || userLat === null) {
            alert("⚠️ LOKASI BELUM TERKUNCI!\nMohon tunggu hingga status GPS berwarna hijau sebelum melakukan scan.");
            getLocation(); // Coba ambil ulang lokasi
            return;
        }

        isProcessing = true;
        html5QrcodeScanner.pause(true);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // KIRIM DATA KE CONTROLLER
        fetch("{{ route('scan.proses') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                kode_unik: decodedText,
                latitude: userLat,
                longitude: userLng
            })
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('result');
            const resultAlert = document.getElementById('result-alert');
            const resultTitle = document.getElementById('result-title');
            const resultMessage = document.getElementById('result-message');
            const btnUlang = document.getElementById('btn-scan-ulang');

            resultDiv.style.display = 'block';
            btnUlang.style.display = 'inline-block';
            resultAlert.className = 'alert shadow-sm';

            if (data.status === 'success') {
                resultAlert.classList.add('alert-success');
                resultTitle.innerHTML = '<i class="fas fa-check-circle me-2"></i> ABSEN BERHASIL!';
                resultMessage.innerText = data.message;
                // Getar HP jika sukses (hanya Android)
                if (navigator.vibrate) navigator.vibrate(200);
            } else if (data.status === 'warning') {
                resultAlert.classList.add('alert-warning');
                resultTitle.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> SUDAH ABSEN';
                resultMessage.innerText = data.message;
            } else {
                resultAlert.classList.add('alert-danger');
                resultTitle.innerHTML = '<i class="fas fa-times-circle me-2"></i> ABSEN GAGAL!';
                resultMessage.innerText = data.message;
            }
        })
        .catch(error => {
            isProcessing = false;
            console.error('Error:', error);
            alert("Terjadi kesalahan server. Mohon periksa koneksi internet.");
        });
    }

    function onScanFailure(error) {
        // Abaikan error pembacaan kecil
    }

    // 3. FUNGSI RESET SCANNER
    function resetScanner() {
        document.getElementById('result').style.display = 'none';
        document.getElementById('btn-scan-ulang').style.display = 'none';
        isProcessing = false;
        
        // Refresh lokasi tiap kali scan ulang
        getLocation(); 
        html5QrcodeScanner.resume();
    }

    // 4. JALANKAN SAAT HALAMAN DIBUKA
    document.addEventListener("DOMContentLoaded", function() {
        getLocation();

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 15, 
                qrbox: {width: 250, height: 250}, 
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true 
            },
            false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    });
</script>
@endsection