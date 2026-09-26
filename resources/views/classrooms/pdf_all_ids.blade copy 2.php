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
            background-color: #fff;
        }

        /* Container Utama untuk menengahkan kartu dalam halaman */
        .wrapper {
            width: 100%;
            text-align: center; /* Menengahkan kartu secara horizontal */
        }

        /* Kotak Kartu */
        .card-container {
            width: 8.6cm;  /* Sedikit dikurangi untuk memberi ruang border/margin */
            height: 5.4cm; /* Ukuran standar ID Card (ID-1) */

            /* Gunakan inline-block agar page-break berfungsi normal di PDF */
            display: inline-block;
            vertical-align: top;

            /* Garis Pinggir Tegas */
            border: 2px solid #333;
            border-radius: 10px; /* Sudut melengkung */

            margin: 0.2cm;     /* Jarak antar kartu */
            margin-bottom: 0.5cm;

            position: relative;
            background-color: #fff;
            overflow: hidden; /* Agar background tidak keluar dari border radius */

            /* Mencegah kartu terbelah di akhir halaman */
            page-break-inside: avoid;
        }

        /* Latar Belakang Menarik */
        .card-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Gradient background halus */
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            z-index: -1;
        }

        /* Hiasan Dekoratif (Shape) */
        .card-bg::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60%;
            height: 100%;
            background: linear-gradient(to bottom left, rgba(26, 115, 232, 0.1), rgba(26, 115, 232, 0.05));
            clip-path: polygon(30% 0, 100% 0, 100% 100%, 0% 100%);
        }

        .card-bg::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: #1a73e8; /* Garis bawah biru */
        }

        /* Layout Isi (Tabel untuk kestabilan PDF) */
        .card-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .left-col {
            width: 32%;
            text-align: center;
            vertical-align: middle;
            padding: 5px;
            border-right: 1px dashed #ccc; /* Pemisah QR dan Teks */
        }

        .right-col {
            width: 68%;
            vertical-align: middle;
            padding-left: 12px;
            padding-right: 5px;
            text-align: left;
        }

        /* Tipografi */
        .school-name {
            font-size: 7pt;
            font-weight: bold;
            color: #666;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .card-title {
            font-size: 12pt;
            font-weight: 900;
            color: #1a73e8;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #1a73e8;
            display: inline-block;
            padding-bottom: 2px;
        }

        .name {
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
            line-height: 1.2;
            margin-bottom: 4px;
            /* Mencegah nama panjang merusak layout */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nis {
            font-size: 10pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #444;
            margin-bottom: 8px;
        }

        .class-badge {
            display: inline-block;
            background-color: #1a73e8;
            color: #fff;
            font-size: 8pt;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
        }

        /* Gambar QR */
        .qr-img {
            width: 2.4cm;
            height: 2.4cm;
            padding: 2px;
            border: 1px solid #eee;
            background: #fff;
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
                            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($student->nis)) }}" class="qr-img">
                        </td>
                        <td class="right-col">
                            <div class="school-name">SMK Negeri Teknologi</div>
                            <div class="card-title">Kartu Pelajar</div>

                            <div class="name">{{ \Illuminate\Support\Str::limit($student->name, 22) }}</div>
                            <div class="nis">NIS: {{ $student->nis }}</div>
                            <div class="class-badge">{{ $classroom->name }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

</body>
</html>
