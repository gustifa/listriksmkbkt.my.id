<!DOCTYPE html>
<html>
<head>
    <title>Cetak Kartu</title>
    <style>
        /* Margin halaman kecil agar muat maksimal */
        @page { margin: 1cm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        /* Container Halaman */
        .page {
            width: 100%;
            overflow: hidden;
        }

        /* Desain Kartu */
        .card {
            width: 48%; /* Lebar 48% */
            height: 5.4cm; /* Tinggi standar ID Card */
            border: 1px solid #000;
            float: left;
            margin-right: 2%;
            margin-bottom: 0.5cm;
            padding: 5px;
            box-sizing: border-box;
            background: #fff;
            position: relative;
            page-break-inside: avoid;
        }

        /* Kartu di kolom kanan (Genap) */
        .card:nth-child(even) {
            margin-right: 0;
            float: right;
        }

        /* Reset float */
        .card:nth-child(odd) {
            clear: left;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
            clear: both;
            display: block;
            height: 0;
        }

        /* --- HEADER KARTU --- */
        .card-header {
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
            display: table;
            width: 100%;
        }
        .header-logo { display: table-cell; width: 35px; vertical-align: middle; }
        .header-logo img { width: 30px; height: auto; }

        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            line-height: 1.1;
        }
        /* Hapus limit karakter, biarkan font mengecil atau text wrap */
        .header-text h3 {
            margin: 0;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h4 {
            margin: 0;
            font-size: 6pt;
            font-weight: normal;
        }

        /* JUDUL KEGIATAN */
        .event-title {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            background-color: #eee;
            border: 1px solid #ccc;
            margin-bottom: 4px;
            padding: 2px 0;
            text-transform: uppercase;
        }

        /* BODY (FOTO & BIODATA) */
        .card-body { display: table; width: 100%; font-size: 8pt; }
        .photo-box {
            display: table-cell;
            width: 2cm;
            height: 2.5cm;
            border: 1px solid #999;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
            color: #999;
        }
        .bio-box {
            display: table-cell;
            vertical-align: top;
            padding-left: 6px;
        }

        /* Tabel Biodata (Agar titik dua sejajar & teks panjang rapi) */
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .bio-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        .bio-label {
            width: 40px; /* Lebar kolom label */
            font-weight: bold;
        }
        .bio-sep {
            width: 8px;
            text-align: center;
        }

        /* FOOTER (TANDA TANGAN) */
        .card-footer {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 160px; /* Diperlebar agar nama/kota panjang muat */
            text-align: center;
            font-size: 7pt;
            line-height: 1.2;
            z-index: 10;
        }
        .ttd-img {
            height: 25px;
            width: auto;
            display: block;
            margin: 1px auto;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 7pt;
        }
    </style>
</head>
<body>

    @foreach($chunks as $pageUsers)
        <div class="page">
            @foreach($pageUsers as $u)
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="header-logo">
                            @if(!empty($school['logo_left']))
                                <img src="{{ $school['logo_left'] }}">
                            @endif
                        </div>
                        <div class="header-text">
                            <!-- HAPUS Str::limit agar teks tampil penuh -->
                            <h3>{{ $school['school_name'] }}</h3>
                            <h4>{{ $school['school_address'] }}</h4>
                        </div>
                        <div class="header-logo" style="text-align: right;">
                            @if(!empty($school['logo_right']))
                                <img src="{{ $school['logo_right'] }}">
                            @endif
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="event-title">
                        {{ $cardInfo['title_1'] }}<br>
                        <span style="font-size: 6pt; font-weight: normal;">{{ $cardInfo['title_2'] }}</span>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="photo-box">FOTO<br>2x3</div>
                        <div class="bio-box">
                            <!-- Menggunakan Tabel untuk Biodata -->
                            <table class="bio-table">
                                @if($cardInfo['type'] == 'student')
                                    <tr>
                                        <td class="bio-label">Nama</td>
                                        <td class="bio-sep">:</td>
                                        <td><strong>{{ $u->name }}</strong></td> <!-- Full Name -->
                                    </tr>
                                    <tr>
                                        <td class="bio-label">NIS</td>
                                        <td class="bio-sep">:</td>
                                        <td>{{ $u->nis }}</td>
                                    </tr>
                                    <tr>
                                        <td class="bio-label">Kelas</td>
                                        <td class="bio-sep">:</td>
                                        <td>{{ $u->classroom->name ?? '-' }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="bio-label">Nama</td>
                                        <td class="bio-sep">:</td>
                                        <td><strong>{{ $u->user->name }}</strong></td> <!-- Full Name -->
                                    </tr>
                                    <tr>
                                        <td class="bio-label">NIP</td>
                                        <td class="bio-sep">:</td>
                                        <td>{{ $u->nip ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="bio-label">Status</td>
                                        <td class="bio-sep">:</td>
                                        <td>PENGAWAS</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Footer Tanda Tangan -->
                    <div class="card-footer">
                        <div>{{ $school['sign_city'] ?? 'Kota' }}, {{ $cardInfo['date'] }}</div>
                        <div style="margin-bottom: 2px;">Kepala Sekolah,</div>

                        @if(!empty($school['sign_image']))
                            <img src="{{ $school['sign_image'] }}" class="ttd-img">
                        @else
                            <br><br>
                        @endif

                        <div class="sign-name">
                            {{ $school['sign_name'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Page Break antar halaman -->
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
