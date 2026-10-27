<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
            color: #333;
        }

        /* --- HALAMAN DEPAN & BELAKANG --- */
        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
            page-break-after: always;
            box-sizing: border-box;
        }

        /* --- BINGKAI (FIXED PER HALAMAN) --- */
        .border-pattern {
            position: absolute;
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
            position: relative;
            padding: 15mm 20mm;
            text-align: center;
            z-index: 1;
        }

        /* --- LOGO --- */
        .logo-left {
            position: absolute;
            top: 15mm;  
            left: 20mm;
            width: 25mm; 
            height: auto;
        }
        .logo-right {
            position: absolute;
            top: 15mm;  
            right: 20mm;
            width: 25mm; 
            height: auto;
        }

        /* --- HEADER --- */
        .header {
            margin-top: 5mm; 
            margin-bottom: 3mm;
            padding: 0 35mm;
        }
        .header h2 {
            font-size: 15pt;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .header h3 {
            font-size: 13pt;
            margin: 1mm 0;
            font-weight: normal;
        }

        /* --- JUDUL --- */
        .cert-title {
            font-size: 32pt;
            font-weight: bold;
            color: #1a4d2e;
            margin-top: 3mm;
            text-decoration: underline;
            text-decoration-color: #daa520;
        }

        .cert-subtitle {
            font-size: 13pt;
            margin-top: 1mm;
            margin-bottom: 4mm;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* --- KONTEN SISWA --- */
        .student-name {
            font-size: 22pt;
            font-weight: bold;
            margin: 2mm 0;
            text-transform: uppercase;
            color: #000;
            border-bottom: 1px solid #999;
            display: inline-block;
            min-width: 130mm;
            padding-bottom: 2px;
        }
        
        .student-meta { font-size: 11pt; margin-top: 1mm; }

        .desc {
            font-size: 12pt;
            line-height: 1.4;
            margin: 4mm 20mm;
        }

        .industry-name {
            font-weight: bold;
            font-size: 15pt;
        }

        /* --- NILAI HALAMAN DEPAN --- */
        .score-box {
            margin: 3mm auto;
            border: 2px solid #333;
            padding: 2mm 8mm;
            display: inline-block;
            background-color: #f9f9f9;
        }
        .score-val { font-size: 13pt; font-weight: bold; }

        /* --- FOOTER / TTD --- */
        .footer-table {
            width: 100%;
            margin-top: 5mm;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .footer-cell {
            width: 33%;
            vertical-align: top;
            text-align: center;
            padding: 0 3mm;
        }
        
        .sign-title { font-size: 10.5pt; margin-bottom: 20mm; }
        .sign-name { font-weight: bold; text-decoration: underline; font-size: 10.5pt; }
        .sign-nip { font-size: 9.5pt; margin-top: 2px; }

        /* --- TABEL TRANSKRIP (HALAMAN BELAKANG) --- */
        .transcript-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1a4d2e;
            margin-bottom: 4mm;
            text-transform: uppercase;
        }

        .table-transcript {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            font-size: 10pt;
        }

        .table-transcript th, .table-transcript td {
            border: 1px solid #333;
            padding: 2mm 3mm;
            text-align: left;
        }

        .table-transcript th {
            background-color: #1a4d2e;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
    </style>
</head>
<body>

    <!-- ================= HALAMAN 1: SERTIFIKAT UTAMA ================= -->
    <div class="page">
        <div class="border-pattern">
            <div class="inner-border"></div>
        </div>

        <div class="content-wrapper">
            @if(!empty($school['logo_left']))
                <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-left">
            @endif

            @if(!empty($school['logo_right']))
                <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-right">
            @endif
            
            <div class="header">
                <h2>PEMERINTAH PROVINSI SUMATERA BARAT</h2>
                <h3>DINAS PENDIDIKAN — {{ strtoupper($school['name'] ?? 'SMK NEGERI') }}</h3>
            </div>

            <div class="cert-title">SERTIFIKAT PKL</div>
            <div class="cert-subtitle">PRAKTIK KERJA LAPANGAN</div>

            <div style="font-size: 11pt;">Diberikan kepada:</div>

            <div class="student-name">{{ $internship->student->name }}</div>
            <div class="student-meta">
                NIS: {{ $internship->student->nis }} &nbsp; | &nbsp; 
                NISN: {{ $internship->student->nisn ?? '-' }} &nbsp; | &nbsp; 
                Kelas: {{ $internship->student->classroom->name ?? '-' }}
            </div>

            <div class="desc">
                Telah melaksanakan Praktik Kerja Lapangan (PKL) sesuai Kurikulum Merdeka selama 6 (enam) bulan,<br>
                mulai tanggal <strong>{{ \Carbon\Carbon::parse($internship->start_date)->isoFormat('D MMMM Y') }}</strong> sampai dengan <strong>{{ \Carbon\Carbon::parse($internship->end_date)->isoFormat('D MMMM Y') }}</strong><br>
                bertempat di:<br>
                <span class="industry-name">{{ strtoupper($internship->industry->name) }}</span>
            </div>

            <div class="score-box">
                Predikat: <span class="score-val">{{ strtoupper($predikat ?? 'SANGAT BAIK') }} ({{ $internship->grade->final_score ?? 0 }})</span>
            </div>

            <table class="footer-table">
                <tr>
                    <td class="footer-cell">
                        <div class="sign-title">Guru Pembimbing,</div>
                        <div class="sign-name">{{ $teacher->user->name ?? '-' }}</div>
                        <div class="sign-nip">NIP. {{ $teacher->nip ?? '-' }}</div>
                    </td>

                    <td class="footer-cell">
                        <div class="sign-title">
                            Pimpinan / Instruktur DU/DI,<br>
                            <strong>{{ $internship->industry->name }}</strong>
                        </div>
                        <div class="sign-name">{{ $internship->industry->instructor_name ?? '....................................' }}</div>
                        <div class="sign-nip">NIP/NIK. {{ $internship->industry->instructor_nip ?? '-' }}</div>
                    </td>

                    <td class="footer-cell">
                        <div class="sign-title">
                            {{ $school['sign_city'] ?? 'Padang' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                            Kepala Sekolah,
                        </div>
                        <div class="sign-name">{{ $school['headmaster_name'] }}</div>
                        <div class="sign-nip">NIP. {{ $school['headmaster_nip'] }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <!-- ================= HALAMAN 2: TRANSKRIP CAPAIAN KOMPETENSI (TP) ================= -->
    <div class="page" style="page-break-after: avoid;">
        <div class="border-pattern">
            <div class="inner-border"></div>
        </div>

        <div class="content-wrapper">
            <div class="transcript-title">TRANSKRIP CAPAIAN KOMPETENSI PKL</div>
            <div style="font-size: 11pt; margin-bottom: 4mm;">
                Nama: <strong>{{ $internship->student->name }}</strong> | NIS: <strong>{{ $internship->student->nis }}</strong> | Tempat PKL: <strong>{{ $internship->industry->name }}</strong>
            </div>

            <table class="table-transcript">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 45%;">Tujuan Pembelajaran / Elemen Kompetensi</th>
                        <th style="width: 12%;">Skor (0-100)</th>
                        <th style="width: 38%;">Deskripsi Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>Internalisasi dan Penerapan Soft Skills dalam Dunia Kerja</td>
                        <td class="text-center">{{ $grades['soft_skills'] ?? 88 }}</td>
                        <td>Sangat baik dalam berinteraksi, berkomunikasi, serta menunjukkan kedisiplinan dan etika kerja yang tinggi di tempat PKL.</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Penerapan Norma, POS, dan K3LH di Tempat Kerja</td>
                        <td class="text-center">{{ $grades['k3lh'] ?? 90 }}</td>
                        <td>Memahami dan menerapkan aturan keselamatan kerja (K3LH) serta Prosedur Operasional Standar (POS) dengan konsisten.</td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>Penerapan Kompetensi Teknis (Hard Skills) sesuai Bidang Keahlian</td>
                        <td class="text-center">{{ $grades['hard_skills'] ?? 92 }}</td>
                        <td>Mampu menguasai dan menyelesaikan pekerjaan teknis operasional industri secara mandiri dan presisi.</td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>Pemahaman Alur Bisnis dan Tata Kelola Dunia Kerja</td>
                        <td class="text-center">{{ $grades['business_process'] ?? 85 }}</td>
                        <td>Memahami alur kerja, struktur organisasi, serta proses operasional bisnis pada tempat pelaksanaan PKL.</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table" style="margin-top: 10mm;">
                <tr>
                    <td class="footer-cell">
                        <div class="sign-title">
                            Mengetahui,<br>
                            Guru Pembimbing PKL,
                        </div>
                        <div class="sign-name">{{ $teacher->user->name ?? '-' }}</div>
                        <div class="sign-nip">NIP. {{ $teacher->nip ?? '-' }}</div>
                    </td>

                    <td class="footer-cell"></td>

                    <td class="footer-cell">
                        <div class="sign-title">
                            Instruktur / Penilai Industri,<br>
                            <strong>{{ $internship->industry->name }}</strong>
                        </div>
                        <div class="sign-name">{{ $internship->industry->instructor_name ?? '....................................' }}</div>
                        <div class="sign-nip">NIP/NIK. {{ $internship->industry->instructor_nip ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>