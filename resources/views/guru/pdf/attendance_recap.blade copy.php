<!DOCTYPE html>
<html>
<head>
    <title>Daftar Hadir - {{ $schedule->classroom->name }} - {{ $schedule->subject->name }}</title>
    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2.5cm' }};
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.15;
        }

        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 5px; }
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h2 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 10pt; }

        /* JUDUL */
        .title-doc { text-align: center; margin-bottom: 15px; }
        .title-doc h3 { margin: 0; text-decoration: underline; font-size: 14pt; text-transform: uppercase; }
        .title-doc h4 { margin: 0; font-size: 12pt; text-transform: uppercase; }

        /* INFO KELAS */
        .info-table { width: 100%; margin-bottom: 15px; font-weight: bold; font-size: 11pt; }
        .info-table td { padding: 2px 0; }

        /* TABEL ABSENSI (GRID) */
        .grid-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 15px; }
        .grid-table th, .grid-table td { border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; }

        /* Rotasi Header Tanggal agar muat dan Jelas */
        .th-date {
            height: 100px; /* Tinggi header agar teks vertikal muat */
            vertical-align: bottom;
            padding-bottom: 5px;
            width: 25px; /* Lebar kolom tanggal dipersempit */
        }

        .date-text {
            /* Trik CSS untuk DomPDF agar teks vertikal 90 derajat */
            display: block;
            transform: rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
            width: 20px;
            margin: 0 auto;
            font-size: 9pt; /* Ukuran font tanggal diperjelas */
            font-weight: bold;
        }

        .col-nama { text-align: left !important; padding-left: 5px; text-transform: uppercase; }
        .col-no { width: 30px; }
        .col-nis { width: 60px; }

        /* CELL STATUS */
        .status-cell { font-weight: bold; font-size: 9pt; }

        /* TANDA TANGAN */
        .footer-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { width: 300px; text-align: center; float: left; }
        .ttd-box-right { width: 300px; text-align: center; float: right; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center; vertical-align: middle;">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                @if(isset($school['provinsi_name']))
                    <h2>PEMERINTAH {{ strtoupper($school['provinsi_name']) }}</h2>
                @endif
                <h2>DINAS PENDIDIKAN</h2>
                <h1>{{ $school['school_name'] ?? 'SEKOLAH' }}</h1>
                <p>{{ $school['school_address'] ?? '-' }}</p>
                <p>Email: {{ $school['school_email'] ?? '-' }} | Website: {{ $school['school_web'] ?? '-' }}</p>
            </td>
            <td width="15%" style="text-align: center; vertical-align: middle;">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL -->
    <div class="title-doc">
        <h3>DAFTAR KEHADIRAN SISWA</h3>
        <h4>TAHUN PELAJARAN {{ $tahunAjaran }}</h4>
    </div>

    <!-- INFO KELAS -->
    <table class="info-table">
        <tr>
            <td width="15%">MATA PELAJARAN</td><td width="35%">: {{ strtoupper($schedule->subject->name) }}</td>
            <td width="15%">KELAS</td><td width="35%">: {{ strtoupper($schedule->classroom->name) }}</td>
        </tr>
        <tr>
            <td>SEMESTER</td><td>: {{ strtoupper($semester) }}</td>
            <td>GURU PENGAMPU</td><td>: {{ strtoupper($schedule->teacher->user->name ?? '-') }}</td>
        </tr>
    </table>

    <!-- TABEL GRID ABSENSI -->
    <table class="grid-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2">NAMA PESERTA DIDIK</th>
                <th rowspan="2" class="col-nis">NIS</th>

                <!-- HEADER TANGGAL (BULAN/TGL) -->
                @if(count($dates) > 0)
                    <th colspan="{{ count($dates) }}">TANGGAL PERTEMUAN</th>
                @else
                    <th rowspan="2">TANGGAL</th>
                @endif

                <!-- HEADER REKAP -->
                <th colspan="5">JUMLAH</th>
            </tr>
            <tr>
                <!-- LOOP TANGGAL -->
                @foreach($dates as $date)
                    <th class="th-date">
                        <div class="date-text">
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d M') }}
                        </div>
                    </th>
                @endforeach

                @if(count($dates) == 0) <th>-</th> @endif

                <th width="20">H</th>
                <th width="20">S</th>
                <th width="20">I</th>
                <th width="20">A</th>
                <th width="20">T</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="col-nama">{{ $student->name }}</td>
                <td>{{ $student->nis }}</td>

                <!-- LOOP STATUS PER TANGGAL -->
                @foreach($dates as $date)
                    @php
                        $status = $attendanceMap[$student->id][$date] ?? '-';
                        $color = '';
                        if($status == 'S') $color = 'color:blue;';
                        if($status == 'I') $color = 'color:orange;';
                        if($status == 'A') $color = 'color:red;';
                        if($status == 'T') $color = 'color:brown;';
                    @endphp
                    <td class="status-cell" style="{{ $color }}">{{ $status }}</td>
                @endforeach

                @if(count($dates) == 0) <td>-</td> @endif

                <!-- REKAP PER SISWA -->
                <td>{{ $recapMap[$student->id]['H'] }}</td>
                <td>{{ $recapMap[$student->id]['S'] }}</td>
                <td>{{ $recapMap[$student->id]['I'] }}</td>
                <td>{{ $recapMap[$student->id]['A'] }}</td>
                <td>{{ $recapMap[$student->id]['T'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9pt; margin-bottom: 20px;">
        <strong>Keterangan:</strong> (H) Hadir, (S) Sakit, (I) Izin, (A) Alpa, (T) Terlambat
    </div>

    <!-- TANDA TANGAN -->
    <div class="footer-section">
        <div class="ttd-box">
            <p>Mengetahui,</p>
            <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }}</p>
            <br><br><br><br>
            <p style="text-decoration: underline; font-weight: bold;">{{ $school['sign_name'] }}</p>
            <p>NIP. {{ $school['sign_nip'] }}</p>
        </div>

        <div class="ttd-box-right">
            <p>{{ $school['sign_city'] ?? 'Bukittinggi' }}, {{ date('d F Y') }}</p>
            <p>Guru Mata Pelajaran</p>
            <br><br><br><br>
            <p style="text-decoration: underline; font-weight: bold;">{{ strtoupper($schedule->teacher->user->name ?? '-') }}</p>
            <p>NIP. {{ $schedule->teacher->nip ?? '-' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
