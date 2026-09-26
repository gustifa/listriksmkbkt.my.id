<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <!-- Menggunakan Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk Ikon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        .error-card {
            text-align: center;
            padding: 50px 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
            position: relative;
            overflow: hidden;
            border-top: 5px solid #ffc107; /* Kuning Peringatan */
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: #ffc107; /* Kuning Peringatan */
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 4px 4px 0px rgba(255, 193, 7, 0.1);
            position: relative;
            z-index: 2;
        }

        .error-icon {
            font-size: 3rem;
            color: #dc3545; /* Merah untuk ikon gembok */
            margin-bottom: 20px;
            animation: shake 3s infinite;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .error-desc {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-home {
            background-color: #0d6efd;
            border: none;
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-home:hover {
            background-color: #0b5ed7;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
            color: white;
        }

        @keyframes shake {
            0%, 100% {transform: rotate(0deg);}
            20% {transform: rotate(-10deg);}
            40% {transform: rotate(10deg);}
            60% {transform: rotate(-10deg);}
            80% {transform: rotate(10deg);}
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-user-lock"></i>
        </div>
        
        <div class="error-code">403</div>
        
        <h1 class="error-title">Akses Ditolak!</h1>
        
        <p class="error-desc">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Silakan hubungi administrator jika ini kesalahan.
        </p>

        <a href="{{ url('/dashboard') }}" class="btn-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

</body>
</html>