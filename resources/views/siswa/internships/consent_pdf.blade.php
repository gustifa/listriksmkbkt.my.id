<!DOCTYPE html>
<html>
<head>
    <title>Surat Izin Orang Tua - {{ $student->name }}</title>
    <style>
        @page {
            margin-top: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
            margin-left: 2.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif; /* Menggunakan font standar surat resmi */
            font-size: 12pt;
            line-height: 1.5;
        }

        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .logo-img { width: 90px; height: auto; }
        .school-info { text-align: center; }
        .school-info h2 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 10pt; }

        /* JUDUL SURAT */
        .title-surat { text-align: center; margin-bottom: 25px; }
        .title-surat h3 { margin: 0; text-decoration: underline; font-size: 14pt; text-transform: uppercase; font-weight: bold; }
        .title-surat p { margin: 0; font-size: 12pt; }

        /* ISI SURAT */
        .content { text-align: justify; margin-bottom: 15px; }

        .data-table { width: 100%; margin-left: 20px; margin-bottom: 15px; }
        .data-table td { vertical-align: top; padding: 2px 0; }
        .col-label { width: 160px; }
        .col-sep { width: 10px; }

        /* KOTAK DUDI */
        .dudi-box {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
            background-color: #f9f9f9;
        }

        /* TANDA TANGAN */
        .footer-section { margin-top: 40px; width: 100%; page-break-inside: avoid; }
        .ttd-box { width: 45%; text-align: center; float: left; }
        .ttd-box-right { width: 45%; text-align: center; float: right; }

        .clear { clear: both; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center; vertical-align: middle;">
                @if(!empty($school['logo_left']))
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                <h2>PEMERINTAH {{ strtoupper($school['provinsi_name'] ?? 'PROVINSI SUMATERA BARAT') }}</h2>
                <h2>DINAS PENDIDIKAN</h2>
                <h1>{{ $school['name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</h1>
                <p>{{ $school['address'] ?? 'Alamat Sekolah' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
            </td>
            <td width="15%" style="text-align: center; vertical-align: middle;">
                @if(!empty($school['logo_right']))
                    <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL -->
    <div class="title-surat">
        <h3>SURAT PERNYATAAN IZIN ORANG TUA</h3>
        <p>Nomor: _____/PKL/SMKN1-BKT/{{ date('Y') }}</p>
    </div>

    <!-- ISI -->
    <div class="content">
        <p>Saya yang bertanda tangan di bawah ini:</p>

        <table class="data-table">
            <tr>
                <td class="col-label">Nama Orang Tua / Wali</td>
                <td class="col-sep">:</td>
                <td>..........................................................................</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>..........................................................................</td>
            </tr>
            <tr>
                <td>No. HP / WA</td>
                <td>:</td>
                <td>..........................................................................</td>
            </tr>
            <tr><td colspan="3" style="height: 10px;"></td></tr>
            <tr>
                <td colspan="3">Orang tua / Wali dari siswa:</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Nama Siswa</td>
                <td>:</td>
                <td><strong>{{ strtoupper($student->name) }}</strong></td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">NIS / NISN</td>
                <td>:</td>
                <td>{{ $student->nis }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Kelas</td>
                <td>:</td>
                <td>{{ $student->classroom->name ?? '-' }}</td>
            </tr>
        </table>

        <p>
            Dengan ini menyatakan <strong>menyetujui / mengizinkan</strong> anak saya tersebut di atas untuk mengikuti dan melaksanakan Praktik Kerja Lapangan (PKL) selama 6 (enam) bulan, di:
        </p>

        <div class="dudi-box">
            {{ $internship->industry->name ?? '(Nama Industri / Perusahaan)' }}
        </div>

        <p>
            Saya bersedia mematuhi segala peraturan dan tata tertib yang berlaku di Sekolah maupun di tempat PKL (Dunia Kerja), serta mendukung sepenuhnya kegiatan tersebut demi kelancaran pendidikan anak saya.
        </p>

        <p>
            Demikian surat pernyataan ini saya buat dengan sesungguhnya tanpa ada paksaan dari pihak manapun untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- TANDA TANGAN -->
    <div class="footer-section">
        <div class="ttd-box">
            <p>Mengetahui,</p>
            <p>Kepala Bengkel / Ka. Prog</p>
            <br><br><br><br>
            <p style="text-decoration: underline; font-weight: bold;">{{ $school['kabeng_name'] ?? '.........................' }}</p>
            <p>NIP. {{ $school['kabeng_nip'] ?? '.........................' }}</p>
        </div>

        <div class="ttd-box-right">
            <p>Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Orang Tua / Wali Murid</p>
            <br>
            <p style="font-size: 10px; font-style: italic;">(Materai 10.000)</p>
            <br><br>
            <p style="font-weight: bold;">(.......................................)</p>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
