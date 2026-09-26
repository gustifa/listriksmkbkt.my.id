<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Diperlukan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .error-card {
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .icon-box {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card mx-auto">
            <div class="icon-box">
                ⚠️
            </div>
            <h2 class="fw-bold text-dark">Aplikasi Kedaluwarsa</h2>
            <p class="text-muted mt-3">
                Mohon maaf, aplikasi tidak dapat digunakan karena terdapat versi pembaruan terbaru di GitHub yang belum diterapkan pada server ini.
            </p>
            <hr>
            <div class="alert alert-warning text-start small">
                <strong>Instruksi untuk Admin:</strong><br>
                Silakan masuk ke server/terminal dan jalankan perintah:<br>
                <code>git pull origin main</code>
            </div>
            
            <a href="/" class="btn btn-primary mt-3">Cek Pembaruan & Muat Ulang</a>
        </div>
    </div>
</body>
</html>