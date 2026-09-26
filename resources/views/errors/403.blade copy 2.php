<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ACCESS DENIED</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0d0d0d;
            color: #0f0; /* Hijau Hacker */
            font-family: 'Share Tech Mono', monospace;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        h1 {
            font-size: 80px;
            margin: 0;
            text-shadow: 0 0 20px rgba(0, 255, 0, 0.6);
        }
        .warning-box {
            border: 2px solid #0f0;
            padding: 40px;
            text-align: center;
            background: rgba(0, 20, 0, 0.8);
            box-shadow: 0 0 50px rgba(0, 255, 0, 0.1);
            position: relative;
        }
        p { font-size: 18px; letter-spacing: 1px; margin: 20px 0; }
        
        .btn {
            background: transparent;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px 30px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 20px #0f0;
        }

        /* Efek Garis Scan di Layar */
        .scanline {
            width: 100%;
            height: 100px;
            z-index: 10;
            background: linear-gradient(0deg, rgba(0,0,0,0) 0%, rgba(0, 255, 0, 0.2) 50%, rgba(0,0,0,0) 100%);
            opacity: 0.1;
            position: absolute;
            bottom: 100%;
            animation: scanline 10s linear infinite;
            pointer-events: none;
        }
        @keyframes scanline {
            0% { bottom: 100%; }
            100% { bottom: -100%; }
        }
    </style>
</head>
<body>

    <div class="scanline"></div>

    <div class="warning-box">
        <h1>403</h1>
        <div style="border-bottom: 1px solid #0f0; width: 50%; margin: 10px auto;"></div>
        <h2>ACCESS DENIED</h2>
        <p>SYSTEM: Permission level insufficient.</p>
        <p>USER: {{ Auth::user()->name ?? 'Unknown' }}</p>
        <p>ROLE: {{ Auth::user()->getRoleNames()->first() ?? 'Guest' }}</p>
        
        <a href="{{ route('dashboard') }}" class="btn">
            [ RETURN TO DASHBOARD ]
        </a>
    </div>

</body>
</html>