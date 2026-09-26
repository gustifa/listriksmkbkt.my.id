<!DOCTYPE html>
<html>
<head>
    <title>ID Card Kelas {{ $classroom->name }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact; /* Agar warna background tercetak */
        }

        /* Container Utama */
        .wrapper {
            width: 100%;
            font-size: 0; /* Menghilangkan spasi antar inline-block */
        }

        /* Kotak Kartu */
        .card-container {
            width: 9cm;
            height: 5.5cm; /* Sedikit dipertinggi agar lebih proporsional */
            display: inline-block;
            vertical-align: top;
            border: 1px solid #ddd;
            margin-right: 10px;
            margin-bottom: 15px;
            position: relative;
            background-color: #fff;
            box-sizing: border-box;
            page-break-inside: avoid;
            border-radius: 8px; /* Sudut melengkung sedikit */
            overflow: hidden; /* Agar background tidak keluar border */
        }

        /* Latar Belakang Dekoratif */
        .card-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Gradient halus */
            background: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
            z-index: -1;
        }

        /* Tabel Layout */
        .card-table {
            width: 100%;
            height: 4.5cm; /* Tinggi area konten (dikurangi tinggi footer) */
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Kolom Kiri (Barcode/QR) */
        .left-col {
            width: 30%;
            text-align: center;
            vertical-align: middle;
            background-color: rgba(26, 115, 232, 0.05); /* Sedikit warna biru transparan */
            border-right: 2px dashed #ccc;
        }

        /* Kolom Kanan (Identitas) */
        .right-col {
            width: 70%;
            text-align: center; /* Rata Tengah Horizontal */
            vertical-align: middle; /* Rata Tengah Vertikal */
            padding: 10px;
        }

        /* Elemen Teks */
        .header-title {
            font-size: 8pt;
            color: #1a73e8;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 1px solid #1a73e8;
            display: inline-block;
            padding-bottom: 2px;
        }

        .student-name {
            font-size: 14pt; /* Diperbesar */
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .student-nis {
            font-size: 11pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace; /* Font monospaced untuk angka */
            color: #555;
            background-color: #eee;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .class-badge {
            font-size: 9pt;
            color: #1a73e8;
            font-weight: bold;
        }

        /* Footer Bar (Pengisi Bagian Bawah) */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1cm;
            background-color: #1a73e8; /* Warna Utama */
            color: #fff;
            display: flex; /* Untuk HTML biasa */
        }

        /* Tabel khusus footer untuk kompatibilitas PDF/DomPDF */
        .footer-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .footer-cell {
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .qr-img {
            width: 2.2cm;
            height: 2.2cm;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        @foreach($classroom->students as $student)
            <div class="card-container">
                <div class="card-bg"></div>

                <table class="card-table">
                    <tr>
                        <td class="left-col">
                            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->margin(1)->generate($student->nis)) }}" class="qr-img">
                        </td>

                        <td class="right-col">
                            <div class="header-title">Kartu Pelajar</div>

                            <div class="student-name">
                                {{ \Illuminate\Support\Str::limit($student->name, 18) }}
                            </div>

                            <div class="student-nis">
                                {{ $student->nis }}
                            </div>

                            <div class="class-badge">
                                KELAS {{ $classroom->name }}
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="bottom-bar">
                    <table class="footer-table">
                        <tr>
                            <td class="footer-cell">
                                SMK Negeri Teknologi • Tahun Ajaran 2024/2025
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
