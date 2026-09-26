<!DOCTYPE html>
<html>
<head>
    <title>Surat Panggilan Orang Tua - {{ $student->name }}</title>
    <style>
        @page { margin: 2cm 2.5cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 25px; padding-bottom: 10px; }
        .logo-img { width: 85px; height: auto; }
        .school-info { text-align: center; }
        .school-info h3 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h2 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 10pt; }
        .content { text-align: justify; margin-bottom: 20px; }
        .data-table { width: 100%; margin-left: 20px; margin-bottom: 20px; border-collapse: collapse; }
        .data-table td { vertical-align: top; padding: 4px 0; }
        .bold { font-weight: bold; }
        .signature-table { width: 100%; margin-top: 50px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; text-align: center; width: 50%; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: left;">
                @if(!empty($school['logo_left'])) <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img"> @endif
            </td>
            <td width="70%" class="school-info">
                <h3>PEMERINTAH PROVINSI {{ strtoupper($school['provinsi_name'] ?? 'SUMATERA BARAT') }}</h3>
                <h3>DINAS PENDIDIKAN</h3>
                <h2>{{ $school['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</h2>
                <p>{{ $school['school_address'] ?? '-' }}</p>
                <p>Telp: {{ $school['school_phone'] ?? '-' }} | Email: {{ $school['school_email'] ?? '-' }}</p>
            </td>
            <td width="15%" style="text-align: right;">
                @if(!empty($school['logo_right'])) <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-img"> @endif
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td width="15%">Nomor</td><td width="2%">:</td><td width="50%">_____/BK/SMKN1-BKT/{{ date('Y') }}</td>
            <td width="33%" style="text-align: right;">Bukittinggi, {{ date('d F Y') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td><td>:</td><td>-</td><td></td>
        </tr>
        <tr>
            <td>Perihal</td><td>:</td><td class="bold underline" style="text-decoration: underline;">Panggilan Orang Tua / Wali Murid</td><td></td>
        </tr>
    </table>

    <div class="content">
        <p>Yth. Bapak/Ibu Orang Tua / Wali dari siswa:</p>
        <table class="data-table">
            <tr><td width="25%">Nama</td><td width="2%">:</td><td class="bold uppercase">{{ $student->name }}</td></tr>
            <tr><td>NIS</td><td>:</td><td>{{ $student->nis }}</td></tr>
            <tr><td>Kelas</td><td>:</td><td>{{ $student->classroom->name ?? '-' }}</td></tr>
        </table>

        <p>Dengan hormat,</p>
        <p>
            Sehubungan dengan adanya hal-hal penting terkait perkembangan akademik dan kedisiplinan putra/putri Bapak/Ibu di sekolah, maka kami mengharap kehadiran Bapak/Ibu pada:
        </p>

        <table class="data-table" style="margin-left: 40px;">
            <tr><td width="25%">Hari, Tanggal</td><td width="2%">:</td><td class="bold">{{ \Carbon\Carbon::parse($request->summon_date)->translatedFormat('l, d F Y') }}</td></tr>
            <tr><td>Pukul</td><td>:</td><td class="bold">{{ $request->summon_time }} WIB s.d Selesai</td></tr>
            <tr><td>Tempat</td><td>:</td><td class="bold">Ruang Bimbingan Konseling (BK) SMKN 1 Bukittinggi</td></tr>
            <tr><td>Menemui</td><td>:</td><td>Wali Kelas / Guru BK</td></tr>
        </table>

        <p>
            Mengingat pentingnya pembinaan dan evaluasi bagi peserta didik, kami sangat mengharapkan kehadiran Bapak/Ibu tepat pada waktunya.
        </p>
        <p>Demikian surat panggilan ini kami sampaikan, atas perhatian dan kerjasama Bapak/Ibu kami ucapkan terima kasih.</p>
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui,<br>Kepala Sekolah</p>
                <br><br><br><br>
                <p class="bold" style="text-decoration: underline;">{{ $school['sign_name'] ?? '.........................' }}</p>
                <p>NIP. {{ $school['sign_nip'] ?? '-' }}</p>
            </td>
            <td>
                <p><br>Guru Pembimbing / BK</p>
                <br><br><br><br>
                <p class="bold" style="text-decoration: underline;">{{ strtoupper($guidance->teacher->user->name ?? $guidance->teacher->name) }}</p>
                <p>NIP. {{ $guidance->teacher->nip ?? '-' }}</p>
            </td>
        </tr>
    </table>

</body>
</html>