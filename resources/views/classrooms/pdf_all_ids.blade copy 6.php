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

        /* Kotak Kartu */
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
            overflow: hidden; /* Mencegah isi keluar dari kotak */
        }

        .card-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Ubah gradient jadi vertikal atau radial biar bagus di tengah */
            background: linear-gradient(to bottom, #ffffff 50%, #e3f2fd 100%);
            z-index: -1;
        }

        /* Tabel Utama untuk Center Alignment yang Stabil di PDF */
        .card-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Penting agar text-overflow berfungsi */
        }

        .center-col {
            text-align: center;
            vertical-align: middle;
            padding: 5px;
        }

        /* Elemen - elemen */
        .header {
            font-size: 7pt;
            font-weight: bold;
            color: #1a73e8;
            letter-spacing: 1px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .qr-img {
            /* Ukuran disesuaikan agar muat vertikal */
            width: 1.8cm;
            height: 1.8cm;
            margin: 2px auto;
            display: block;
        }

        .name {
            font-size: 10pt; /* Sedikit dikecilkan agar aman */
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
            margin-top: 2px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nis {
            font-size: 10pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #555;
            margin-bottom: 2px;
        }

        .class-badge {
            display: inline-block;
            background-color: #1a73e8; /* Ubah jadi biru agar senada */
            color: #fff;
            font-size: 6pt;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 3px;
            width: 100%;
            text-align: center; /* Footer juga di tengah */
            font-size: 5pt;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
                        <td class="center-col">
                            <div class="header">KARTU PELAJAR</div>

                            <img src="data:image/svg+xml;base64, {{ base64_encode(QrCode::format('svg')->size(80)->margin(0)->generate($student->nis)) }}" class="qr-img">

                            <div class="name">{{ \Illuminate\Support\Str::limit($student->name, 25) }}</div>

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
