<!DOCTYPE html>
<html>
<head>
    <title>Sertifikat PKL - {{ $internship->student->name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }
        .container {
            width: 100%;
            height: 100vh;
            padding: 40px;
            box-sizing: border-box;
            background-color: #fff;
            position: relative;
        }
        .border-pattern {
            position: absolute;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            border: 5px double #1a4d2e; /* Hijau Tua */
            padding: 5px;
        }
        .inner-border {
            border: 2px solid #daa520; /* Emas */
            height: 100%;
            padding: 40px;
            text-align: center;
            box-sizing: border-box;
        }

        /* HEADER */
        .header h2 {
            font-size: 20pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #333;
        }
        .header h3 {
            font-size: 16pt;
            margin: 5px 0 20px 0;
            font-weight: normal;
        }

        .logo {
            position: absolute;
            top: 60px;
            width: 80px;
            height: auto;
        }
        .logo-left { left: 80px; }
        .logo-right { right: 80px; }

        /* JUDUL SERTIFIKAT */
        .cert-title {
            font-family: 'Times New Roman', serif; /* Atau font script jika ada */
            font-size: 42pt;
            font-weight: bold;
            color: #1a4d2e; /* Hijau */
            margin: 10px 0;
            text-decoration: underline;
            text-decoration-color: #daa520;
        }

        .cert-subtitle {
            font-size: 14pt;
            margin-bottom: 5px;
        }

        /* KONTEN UTAMA */
        .student-name {
            font-size: 28pt;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            color: #000;
            border-bottom: 2px solid #ccc;
            display: inline-block;
            padding-bottom: 5px;
            min-width: 400px;
        }

        .desc {
            font-size: 14pt;
            line-height: 1.5;
            margin: 20px 80px;
        }

        .industry-name {
            font-weight: bold;
            font-size: 16pt;
        }

        /* NILAI */
        .score-box {
            margin: 20px auto;
            border: 2px solid #333;
            padding: 10px 30px;
            display: inline-block;
            background-color: #f9f9f9;
        }
        .score-val { font-size: 18pt; font-weight: bold; }
        .score-label { font-size: 12pt; text-transform: uppercase; }

        /* TANDA TANGAN */
        .footer {
            margin-top: 50px;
            width: 100%;
            display: table;
        }
        .sign-col {
            display: table-cell;
            width: 33%;
            vertical-align: top;
            text-align: center;
        }
        .sign-title { font-size: 12pt; margin-bottom: 70px; }
        .sign-name { font-weight: bold; text-decoration: underline; font-size: 12pt; }
        .sign-nip { font-size: 11pt; }

    </style>
</head>
<body>
    <div class="container">
        <div class="border-pattern">
            <div class="inner-border">

                <!-- LOGO -->
                @if(!empty($school['logo_left']))
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo logo-left">
                @endif

                <!-- HEADER -->
                <div class="header">
                    <h2>PEMERINTAH PROVINSI SUMATERA BARAT</h2>
                    <h3>DINAS PENDIDIKAN</h3>
                </div>

                <!-- JUDUL -->
                <div class="cert-title">SERTIFIKAT PKL</div>
                <div class="cert-subtitle">PRAKTIK KERJA LAPANGAN</div>
                <div class="cert-subtitle">Diberikan kepada:</div>

                <!-- NAMA SISWA -->
                <div class="student-name">{{ $internship->student->name }}</div>
                <div style="font-size: 12pt;">NIS: {{ $internship->student->nis }} &nbsp; | &nbsp; Kelas: {{ $internship->student->classroom->name ?? '-' }}</div>

                <!-- ISI -->
                <div class="desc">
                    Telah melaksanakan Praktik Kerja Lapangan (PKL) selama 6 (enam) bulan,<br>
                    mulai tanggal <strong>{{ $internship->start_date->isoFormat('D MMMM Y') }}</strong> sampai dengan <strong>{{ $internship->end_date->isoFormat('D MMMM Y') }}</strong><br>
                    bertempat di:
                    <br>
                    <div class="industry-name">{{ strtoupper($internship->industry->name) }}</div>
                </div>

                <!-- NILAI -->
                <div class="score-box">
                    <span class="score-label">Predikat:</span><br>
                    <span class="score-val">{{ strtoupper($predikat) }} ({{ $internship->grade->final_score }})</span>
                </div>

                <!-- TANDA TANGAN -->
                <div class="footer">
                    <!-- Guru Pembimbing -->
                    <div class="sign-col">
                        <div class="sign-title">Guru Pembimbing,</div>
                        <div class="sign-name">{{ $teacher->user->name }}</div>
                        <div class="sign-nip">NIP. {{ $teacher->nip ?? '-' }}</div>
                    </div>

                    <!-- Pimpinan Industri (Opsional/Kosongkan untuk TTD Manual) -->
                    <div class="sign-col">
                        <div class="sign-title">Pimpinan DU/DI,</div>
                        <br>
                        <div class="sign-name">....................................</div>
                        <div class="sign-nip">{{ $internship->industry->name }}</div>
                    </div>

                    <!-- Kepala Sekolah -->
                    <div class="sign-col">
                        <div class="sign-title">
                            {{ $school['sign_city'] }}, {{ date('d F Y') }}<br>
                            Kepala Sekolah,
                        </div>
                        <div class="sign-name">{{ $school['headmaster_name'] }}</div>
                        <div class="sign-nip">NIP. {{ $school['headmaster_nip'] }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
