<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            text-align: center;
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 90%;
        }
        .lock-icon {
            font-size: 80px;
            color: #dc3545; /* Merah Bootstrap */
            margin-bottom: 20px;
            animation: shake 2s infinite; /* Animasi bergetar */
        }
        h1 {
            font-weight: 800;
            color: #333;
            font-size: 4rem;
            margin: 0;
            line-height: 1;
        }
        h4 { color: #555; margin-top: 10px; margin-bottom: 20px; }
        p { color: #777; margin-bottom: 30px; }
        
        /* Animasi Kunci Bergetar */
        @keyframes shake {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            50% { transform: rotate(0deg); }
            75% { transform: rotate(-15deg); }
            100% { transform: rotate(0deg); }
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="lock-icon">
            <i class="fas fa-user-lock"></i>
        </div>
        <h1>403</h1>
        <h4>Oops! Area Terlarang</h4>
        <p>
            Maaf, akun Anda tidak memiliki izin untuk masuk ke halaman ini.<br>
            Silakan kembali atau hubungi Administrator sekolah.
        </p>
        
        <div class="d-grid gap-2 col-8 mx-auto">
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                <i class="fas fa-home me-2"></i> Kembali ke Dashboard
            </a>
            
            <form method="POST" action="" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm mt-2 rounded-pill">
                    Ganti Akun / Logout
                </button>
            </form>
        </div>
    </div>

</body>
</html>