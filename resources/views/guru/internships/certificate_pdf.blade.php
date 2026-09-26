<!DOCTYPE html>
<html>
<head>
    <title>Sertifikat PKL - {{ $internship->student->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
        }

        /* --- BINGKAI (FIXED) --- */
        .border-pattern {
            position: fixed;
            top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
            border: 2mm double #1a4d2e; /* Hijau Tua */
            z-index: -10;
        }

        .inner-border {
            position: absolute;
            top: 2mm; left: 2mm; right: 2mm; bottom: 2mm;
            border: 1mm solid #daa520; /* Emas */
            z-index: -9;
        }

        /* --- CONTAINER UTAMA --- */
        .content-wrapper {
            position: absolute;
            top: 0; left: 0; 
            width: 100%; height: 100%;
            text-align: center;
            z-index: 1;
        }

        /* --- PENGATURAN LOGO --- */
        .logo {
            position: absolute;
            /* Koordinat ini dihitung agar pas DI DALAM bingkai */
            top: 22mm;  
            left: 25mm;
            
            /* Ukuran Logo */
            width: 25mm; 
            height: auto;
            object-fit: contain; /* Agar logo tidak gepeng */
        }

        /* Jika nanti butuh logo kanan */
        .logo-right {
            left: auto;
            right: 25mm;
        }

        /* --- HEADER --- */
        .header {
            /* Margin top disesuaikan agar sejajar visual dengan Logo */
            margin-top: 22mm; 
            margin-bottom: 5mm;
            padding: 0 40mm; /* Padding agar teks tidak terlalu lebar jika panjang */
        }
        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
            text-transform: uppercase;
            color: #333;
        }
        .header h3 {
            font-size: 14pt;
            margin: 2mm 0;
            font-weight: normal;
        }

        /* --- JUDUL --- */
        .cert-title {
            font-family: 'Times New Roman', serif;
            font-size: 34pt;
            font-weight: bold;
            color: #1a4d2e;
            margin-top: 5mm;
            text-decoration: underline;
            text-decoration-color: #daa520;
        }

        .cert-subtitle {
            font-size: 14pt;
            margin-top: 2mm;
            margin-bottom: 8mm;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* --- KONTEN SISWA --- */
        .student-name {
            font-size: 24pt;
            font-weight: bold;
            margin: 2mm 0;
            text-transform: uppercase;
            color: #000;
            border-bottom: 1px solid #999;
            display: inline-block;
            min-width: 140mm;
            padding-bottom: 2px;
        }
        
        .student-meta { font-size: 12pt; margin-top: 2mm; }

        .desc {
            font-size: 13pt;
            line-height: 1.4;
            margin: 5mm 25mm;
        }

        .industry-name {
            font-weight: bold;
            font-size: 16pt;
        }

        /* --- NILAI --- */
        .score-box {
            margin: 5mm auto;
            border: 2px solid #333;
            padding: 2mm 10mm;
            display: inline-block;
            background-color: #f9f9f9;
        }
        .score-val { font-size: 14pt; font-weight: bold; }

        /* --- FOOTER --- */
        .footer-table {
            width: 100%;
            margin-top: 8mm; /* Jarak dari nilai ke TTD */
            border-collapse: collapse;
        }
        .footer-cell {
            width: 33%;
            vertical-align: top;
            text-align: center;
            padding: 0 5mm;
        }
        
        .sign-title { font-size: 11pt; margin-bottom: 25mm; /* Ruang untuk TTD basah */ }
        .sign-name { font-weight: bold; text-decoration: underline; font-size: 11pt; }
        .sign-nip { font-size: 10pt; margin-top: 2px; }

    </style>
</head>
<body>

    <div class="border-pattern">
        <div class="inner-border"></div>
    </div>

    <div class="content-wrapper">

        @if(!empty($school['logo_left']))
            <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo">
        @endif
        
        <div class="header">
            <h2>PEMERINTAH PROVINSI SUMATERA BARAT</h2>
            <h3>DINAS PENDIDIKAN</h3>
        </div>

        <div class="cert-title">SERTIFIKAT PKL</div>
        <div class="cert-subtitle">PRAKTIK KERJA LAPANGAN</div>

        <div style="font-size: 12pt;">Diberikan kepada:</div>

        <div class="student-name">{{ $internship->student->name }}</div>
        <div class="student-meta">NIS: {{ $internship->student->nis }} &nbsp; | &nbsp; Kelas: {{ $internship->student->classroom->name ?? '-' }}</div>

        <div class="desc">
            Telah melaksanakan Praktik Kerja Lapangan (PKL) selama 6 (enam) bulan,<br>
            mulai tanggal <strong>{{ $internship->start_date->isoFormat('D MMMM Y') }}</strong> sampai dengan <strong>{{ $internship->end_date->isoFormat('D MMMM Y') }}</strong><br>
            bertempat di:<br>
            <span class="industry-name">{{ strtoupper($internship->industry->name) }}</span>
        </div>

        <div class="score-box">
            Predikat: <span class="score-val">{{ strtoupper($predikat) }} ({{ $internship->grade->final_score }})</span>
        </div>

        <table class="footer-table">
            <tr>
                <td class="footer-cell">
                    <div class="sign-title">Guru Pembimbing,</div>
                    <div class="sign-name">{{ $teacher->user->name }}</div>
                    <div class="sign-nip">NIP. {{ $teacher->nip ?? '-' }}</div>
                </td>

                <td class="footer-cell">
                    <div class="sign-title">Pimpinan DU/DI,</div>
                    <div class="sign-name">....................................</div>
                    <div class="sign-nip">{{ $internship->industry->name }}</div>
                </td>

                <td class="footer-cell">
                    <div class="sign-title">
                        {{ $school['sign_city'] }}, {{ date('d F Y') }}<br>
                        Kepala Sekolah,
                    </div>
                    <div class="sign-name">{{ $school['headmaster_name'] }}</div>
                    <div class="sign-nip">NIP. {{ $school['headmaster_nip'] }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>