<!DOCTYPE html>
<html>
<head>
    <title>Cetak Kartu</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        /* Container Grid */
        .page-container {
            width: 100%;
            /* Trik agar float left bekerja seperti grid */
            overflow: hidden;
        }

        /* Desain Kartu (Ukuran ID Card Standar ~9cm x 5.5cm) */
        .card {
            width: 8.8cm;
            height: 5.4cm;
            border: 1px solid #000;
            float: left;
            margin-right: 0.5cm;
            margin-bottom: 0.5cm;
            position: relative;
            padding: 5px;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
        }

        /* Agar setiap baris ke-2 (genap) margin kanannya hilang */
        .card:nth-child(even) {
            margin-right: 0;
        }

        /* Kop Kartu */
        .card-header {
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 40px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 35px;
            height: auto;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            line-height: 1;
        }
        .header-text h3 { margin: 0; font-size: 9pt; font-weight: bold; }
        .header-text h4 { margin: 0; font-size: 7pt; }

        /* Judul Kegiatan */
        .event-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            background-color: #eee;
            border: 1px solid #ccc;
            margin-bottom: 5px;
            padding: 2px;
            text-transform: uppercase;
        }

        /* Konten Biodata */
        .card-body {
            display: table;
            width: 100%;
            font-size: 9pt;
        }
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
            padding-left: 5px;
        }
        .bio-row { margin-bottom: 2px; }
        .label { display: inline-block; width: 50px; font-weight: bold; }

        /* Tanda Tangan */
        .card-footer {
            margin-top: 2px;
            text-align: right;
            font-size: 7pt;
        }
        .ttd-img {
            height: 30px;
            display: block;
            margin-left: auto;
            margin-right: 5px;
        }

        /* Page Break tiap 10 kartu (5 baris) agar rapi */
        .page-break {
            clear: both;
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="page-container">
        @foreach($users as $index => $u)
            <div class="card">
                <!-- Header -->
                <div class="card-header">
                    <div class="header-logo">
                        @if(!empty($school['logo_left']))
                            <img src="{{ $school['logo_left'] }}">
                        @endif
                    </div>
                    <div class="header-text">
                        <h3>{{ $school['school_name'] }}</h3>
                        <h4>{{ \Illuminate\Support\Str::limit($school['school_address'], 60) }}</h4>
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
                    <span style="font-size: 8pt; font-weight: normal;">{{ $cardInfo['title_2'] }}</span>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- Foto -->
                    <div class="photo-box">
                        @php
                            // Cek jika ada foto user (asumsi field 'photo' ada di tabel)
                            // Jika tidak, tampilkan placeholder
                            $photoPath = null;
                            // if($cardInfo['type'] == 'student' && $u->photo) $photoPath = public_path('storage/'.$u->photo);
                        @endphp

                        @if($photoPath && file_exists($photoPath))
                            <img src="{{ $photoPath }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            FOTO<br>2x3
                        @endif
                    </div>

                    <!-- Biodata -->
                    <div class="bio-box">
                        @if($cardInfo['type'] == 'student')
                            <div class="bio-row"><span class="label">Nama</span>: {{ \Illuminate\Support\Str::limit($u->name, 18) }}</div>
                            <div class="bio-row"><span class="label">NIS</span>: {{ $u->nis }}</div>
                            <div class="bio-row"><span class="label">Kelas</span>: {{ $u->classroom->name ?? '-' }}</div>
                            <div class="bio-row"><span class="label">Ruang</span>: ______</div>
                        @else
                            <div class="bio-row"><span class="label">Nama</span>: {{ \Illuminate\Support\Str::limit($u->user->name, 18) }}</div>
                            <div class="bio-row"><span class="label">NIP</span>: {{ $u->nip ?? '-' }}</div>
                            <div class="bio-row"><span class="label">Status</span>: PENGAWAS</div>
                        @endif
                    </div>
                </div>

                <!-- Footer TTD -->
                <div class="card-footer">
                    <div>Jakarta, {{ $cardInfo['date'] }}</div>
                    <div>Kepala Sekolah,</div>

                    @if(!empty($school['sign_image']))
                        <img src="{{ $school['sign_image'] }}" class="ttd-img">
                    @else
                        <br><br>
                    @endif

                    <div style="font-weight: bold; text-decoration: underline;">{{ $school['sign_name'] }}</div>
                </div>
            </div>

            {{-- Logic Page Break setiap 8 kartu (atau 10 tergantung setting margin) --}}
            @if (($index + 1) % 8 == 0)
                <div class="page-break"></div>
            @endif

        @endforeach
    </div>

</body>
</html>
