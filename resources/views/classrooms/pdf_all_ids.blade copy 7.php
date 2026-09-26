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
            font-size: 10pt;
        }

        .wrapper {
            width: 100%;
            text-align: left;
        }

        .card-container {
            width: 9cm;
            height: 4cm;
            display: inline-block;
            vertical-align: top;
            border: 1px dashed #aaa;
            margin-right: 0.2cm;
            margin-bottom: 0.5cm;
            position: relative;
            background-color: #fff;
            page-break-inside: avoid;
        }

        .card-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Gradient sedikit diubah agar area kanan lebih bersih untuk teks */
            background: linear-gradient(135deg, #ffffff 70%, #e3f2fd 70%);
            z-index: -1;
        }

        .card-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        /* BAGIAN KIRI: Barcode/QR */
        .left-col {
            width: 30%; /* Sedikit diperkecil agar kanan lebih luas */
            text-align: center;
            vertical-align: middle;
            border-right: 2px solid #1a73e8;
            padding: 5px;
        }

        /* BAGIAN KANAN: Nama & NIS */
        .right-col {
            width: 70%;
            text-align: center; /* KUNCI: Membuat teks rata tengah secara horizontal */
            vertical-align: middle; /* KUNCI: Membuat teks rata tengah secara vertikal */
            padding-left: 5px;
            padding-right: 5px;
        }

        .header {
            font-size: 7pt;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px; /* Jarak ke nama */
        }

        .name {
            font-size: 12pt; /* Diperbesar agar jelas */
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .nis {
            font-size: 11pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #444;
            margin-bottom: 8px;
        }

        .class-badge {
            display: inline-block;
            background-color: #333;
            color: #fff;
            font-size: 7pt;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 4px;
            right: 8px;
            font-size: 5pt;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

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
                            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($student->nis)) }}" class="qr-img">
                        </td>

                        <td class="right-col">
                            <div class="header">KARTU PELAJAR</div>

                            <div class="name">{{ \Illuminate\Support\Str::limit($student->name, 20) }}</div>
                            <div class="nis">{{ $student->nis }}</div>

                            <div class="class-badge">{{ $classroom->name }}</div>
                        </td>
                    </tr>
                </table>

                <div class="footer">SMK NEGERI TEKNOLOGI</div>
            </div>
        @endforeach
    </div>

</body>
</html>
