<!DOCTYPE html>
<html>
<head>
    <title>ID Card Kelas {{ $classroom->name }}</title>
    <style>
        @page {
            margin: 1cm; /* Margin kertas A4 */
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
        }

        /* Container Utama */
        .wrapper {
            width: 100%;
            text-align: left;
        }

        .card-container {
    width: 8.5cm;
    height: 3.4cm;

    /* Layout */
    display: inline-block;
    vertical-align: top;
    position: relative;

    /* --- GAYA GARIS MENARIK --- */
    /* Garis pinggir tipis abu-abu */
    border: 1px solid #e0e0e0;

    /* Garis Atas Tebal (Warna Utama/Biru) */
    border-top: 6px solid #1a73e8;

    /* Garis Bawah Sedang (Hitam/Gelap) */
    border-bottom: 3px solid #333;

    /* Sudut sedikit melengkung */
    border-radius: 8px;

    /* Agar garis tebal tidak mengubah ukuran total 9cm */
    box-sizing: border-box;

    /* Spasi */
    margin-right: 0.2cm;
    margin-bottom: 0.5cm;
    background-color: #fff;
    page-break-inside: avoid;
    overflow: hidden; /* Agar isi tidak keluar dari sudut lengkung */
}

        /* Latar Belakang Kartu */
        .card-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #ffffff 65%, #e3f2fd 65%);
            z-index: -1;
        }

        /* Layout Isi (Tabel untuk kestabilan PDF) */
        .card-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            padding-top: 30px;
        }

        .left-col {
            width: 35%;
            text-align: center;
            vertical-align: middle;
            border-right: 2px solid #1a73e8; /* Aksen garis pemisah */
        }

        .right-col {
            width: 65%;
            vertical-align: middle;
            padding-left: 10px;
            padding-right: 5px;

        }

        /* Tipografi */
        .header {
            font-size: 8pt;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .name {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
            line-height: 1.1;
            margin-bottom: 2px;
            /* Mencegah nama panjang merusak layout */
            white-space: nowrap;
            overflow: hidden;
        }

        .nis {
            font-size: 12pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #555;
            margin-bottom: 5px;
        }

        .class-badge {
            display: inline-block;
            background-color: #333;
            color: #fff;
            font-size: 7pt;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 4px;
            right: 8px;
            font-size: 6pt;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Gambar QR */
        .qr-img {
            width: 2.2cm;
            height: 2.2cm;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        @foreach($classroom->students as $index => $student)
            <div class="card-container">
                <div class="card-bg"></div>

                <table class="card-table">
                    <tr>
                        <td class="left-col">
                            <!-- Generate QR Code Lokal (PNG) -->
                            <!-- Pastikan extension=imagick aktif di php.ini untuk hasil terbaik -->
                            <!-- Atau gunakan format('svg') jika imagick tidak tersedia -->
                            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($student->nis)) }}" class="qr-img">
                        </td>
                        <td class="right-col">
                            <div class="header">Program Keahlian TKL</div>
                            <div class="name">{{ \Illuminate\Support\Str::limit($student->name, 20) }}</div>
                            <div class="nis">{{ $student->nis }}</div>
                            {{-- <div class="class-badge">{{ $classroom->name }}</div> --}}
                        </td>
                    </tr>
                </table>

                <div class="footer">SMK NEGERI 1 BUKITTINGGI</div>
            </div>
        @endforeach
    </div>

</body>
</html>
