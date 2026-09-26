<!DOCTYPE html>
<html>
<head>
    <title>Scanner Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<div class="alert alert-info text-center">
    Mengabsen Kelas: <strong>{{ $schedule->class_name }}</strong> <br>
    Mapel: {{ $schedule->subject_name }}
</div>
<body>
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
        // Mainkan suara beep (opsional)
        // var audio = new Audio('beep.mp3'); audio.play();

        // Kirim data ke Server
        $.ajax({
            url: "{{ route('scan.store') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                nis: decodedText, // Data hasil scan (NIS)
                schedule_id: "{{ $schedule->id }}"
            },
            success: function(response) {
                if(response.status == 'success') {
                    $('#result').removeClass('d-none alert-danger').addClass('alert-success').text(response.message + ' - ' + response.student);
                } else {
                    $('#result').removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }
                
                // Jeda 3 detik sebelum scan berikutnya agar tidak double input cepat
                html5QrcodeScanner.pause();
                setTimeout(function() { html5QrcodeScanner.resume(); }, 3000);
            },
            error: function(err) {
                console.log(err);
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
</body>
</html>