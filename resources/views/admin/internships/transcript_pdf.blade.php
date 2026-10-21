<!DOCTYPE html>
<html>
<head>
    <title>Transkrip Nilai PKL - {{ $student->name }}</title>
    <style>
        @page { margin: 1cm 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; }
        
        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h3 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h2 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 10pt; }

        .title { text-align: center; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; text-decoration: underline; font-size: 14pt; }

        /* TABEL BIODATA */
        .bio-table { width: 100%; margin-bottom: 20px; }
        .bio-table td { vertical-align: top; padding: 2px 0; }
        
        /* TABEL NILAI */
        .score-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .score-table th, .score-table td { border: 1px solid #000; padding: 6px; }
        .score-table th { background-color: #f0f0f0; text-align: center; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* TTD */
        .footer { width: 100%; margin-top: 40px; }
        .ttd-box { width: 33%; float: left; text-align: center; }
        .clear { clear: both; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center;">
                @if(!empty($school['logo_left']))
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                <h3>PEMERINTAH PROVINSI {{ strtoupper($school['provinsi_name']) }}</h3>
                <h3>DINAS PENDIDIKAN</h3>
                <h2>{{ $school['name'] }}</h2>
                <p>{{ $school['address'] }}</p>
                <p>Email: {{ $school['email'] }}</p>
            </td>
            <td width="15%" style="text-align: center;">
                @if(!empty($school['logo_right']))
                    <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <div class="title">TRANSKRIP NILAI PRAKTIK KERJA LAPANGAN (PKL)</div>

    <!-- BIODATA -->
    <table class="bio-table">
        <tr>
            <td width="25%">Nama Peserta Didik</td><td width="2%">:</td>
            <td width="73%" class="fw-bold uppercase">{{ strtoupper($student->name) }}</td>
        </tr>
        <tr>
            <td>NIS / NISN</td><td>:</td>
            <td>{{ $student->nis }}</td>
        </tr>
        <tr>
            <td>Kelas</td><td>:</td>
            <td>{{ $student->classroom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tempat PKL</td><td>:</td>
            <td class="fw-bold">{{ $internship->industry->name }}</td>
        </tr>
        <tr>
            <td>Alamat PKL</td><td>:</td>
            <td>{{ $internship->industry->address }}</td>
        </tr>
        <tr>
            <td>Waktu Pelaksanaan</td><td>:</td>
            <td>{{ $internship->start_date->translatedFormat('d F Y') }} s.d. {{ $internship->end_date->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <!-- TABEL NILAI -->
    <table class="score-table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th>KOMPONEN PENILAIAN</th>
                <th width="20%">NILAI (ANGKA)</th>
                <th width="25%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4" class="fw-bold" style="background-color:#fafafa;">A. ASPEK NON-TEKNIS (SOFT SKILLS)</td></tr>
            <tr>
                <td class="text-center">1</td><td>Disiplin</td>
                <td class="text-center">{{ $internship->grade->discipline }}</td><td class="text-center">{{ $internship->grade->discipline >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td><td>Kerjasama Tim</td>
                <td class="text-center">{{ $internship->grade->teamwork }}</td><td class="text-center">{{ $internship->grade->teamwork >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td><td>Inisiatif</td>
                <td class="text-center">{{ $internship->grade->initiative }}</td><td class="text-center">{{ $internship->grade->initiative >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td><td>Tanggung Jawab</td>
                <td class="text-center">{{ $internship->grade->responsibility }}</td><td class="text-center">{{ $internship->grade->responsibility >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>

            <tr><td colspan="4" class="fw-bold" style="background-color:#fafafa;">B. ASPEK TEKNIS (HARD SKILLS)</td></tr>
            <tr>
                <td class="text-center">5</td><td>Penguasaan Materi</td>
                <td class="text-center">{{ $internship->grade->technical_mastery }}</td><td class="text-center">{{ $internship->grade->technical_mastery >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td><td>Kualitas Hasil Kerja</td>
                <td class="text-center">{{ $internship->grade->work_quality }}</td><td class="text-center">{{ $internship->grade->work_quality >= 75 ? 'Kompeten' : 'Cukup' }}</td>
            </tr>

            <!-- TOTAL -->
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="2" class="text-center">NILAI AKHIR</td>
                <td class="text-center" style="font-size: 14pt;">{{ $internship->grade->final_score }}</td>
                <td class="text-center text-uppercase">{{ $predikat }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TTD -->
    <div class="footer">
        <div class="ttd-box">
            <p>Mengetahui,<br>Pembimbing DU/DI</p>
            <br><br><br>
            <p class="fw-bold"><u>{{ $internship->industry->contact_person ?? '.........................' }}</u></p>
            <p>Pimpinan/Pembimbing</p>
        </div>

        <div class="ttd-box">
            <p>Disetujui,<br>Kepala Bengkel/Kaprog</p>
            <br><br><br>
            <p class="fw-bold"><u>{{ $school['kabeng_name'] }}</u></p>
            <p>NIP. {{ $school['kabeng_nip'] }}</p>
        </div>

        <div class="ttd-box">
            <p>{{ $school['sign_city'] }}, {{ date('d F Y') }}<br>Guru Pembimbing</p>
            <br><br><br>
            <p class="fw-bold"><u>{{ $internship->advisor->name ?? '.........................' }}</u></p>
            <p>NIP. {{ $internship->advisor->nip ?? '-' }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <!-- PENGESAHAN KEPALA SEKOLAH (Opsional, di bawah tengah) -->
    <div style="text-align: center; margin-top: 30px;">
        <p>Mengesahkan,<br>Kepala Sekolah</p>
        <br><br><br>
        <p style="font-weight: bold; text-decoration: underline;">{{ $school['headmaster_name'] }}</p>
        <p>NIP. {{ $school['headmaster_nip'] }}</p>
    </div>

</body>
</html>