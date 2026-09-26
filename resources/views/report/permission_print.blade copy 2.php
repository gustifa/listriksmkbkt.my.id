<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin - {{ $permit->student->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        /* --- HEADER (KOP SURAT) --- */
        header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
            position: relative;
        }
        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: auto;
        }
        header h1 {
            margin: 0;
            font-size: 18pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        header p {
            margin: 2px 0;
            font-size: 11pt;
        }

        /* --- CONTENT --- */
        .title {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 30px;
            font-size: 14pt;
        }
        .content {
            margin-left: 20px;
            margin-right: 20px;
            line-height: 1.6;
        }
        table.data {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        table.data td {
            vertical-align: top;
            padding: 5px 0;
        }
        table.data td:first-child {
            width: 150px;
            font-weight: bold;
        }
        table.data td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        /* --- FOOTER (TANDA TANGAN) --- */
        .footer {
            margin-top: 50px;
            width: 100%;
            display: table; /* Gunakan display table untuk layout PDF yang stabil */
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-name {
            margin-top: 70px;
            font-weight: bold;
            text-decoration: underline;
        }
        .nip {
            font-size: 10pt;
        }

        /* --- UTILS --- */
        .timestamp {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 8pt;
            font-style: italic;
            color: #666;
        }
        @media print {
            @page { margin: 2cm; size: A4; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    <div class="container">
        <!-- HEADER / KOP SURAT -->
        <header>
            <!-- Ganti src dengan path logo sekolah Anda -->
            <img src="https://via.placeholder.com/80?text=LOGO" alt="Logo Sekolah" class="logo">

            <h1>{{ $school['name'] }}</h1>
            <p>{{ $school['address'] }}</p>
            <p>Telp: {{ $school['phone'] }} | Email: {{ $school['email'] }}</p>
        </header>

        <!-- ISI SURAT -->
        <div class="title">SURAT IZIN MENINGGALKAN SEKOLAH</div>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini, Petugas Piket / Satpam menerangkan bahwa siswa:</p>

            <table class="data">
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td>{{ $permit->student->name }}</td>
                </tr>
                <tr>
                    <td>NIS / NISN</td>
                    <td>:</td>
                    <td>{{ $permit->student->nis }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>:</td>
                    <td>{{ $permit->student->classroom->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Hari / Tanggal</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($permit->date)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td>Jam Keluar</td>
                    <td>:</td>
                    <td>{{ $permit->time_out }} WIB</td>
                </tr>
                <tr>
                    <td>Keperluan</td>
                    <td>:</td>
                    <td>{{ $permit->reason }}</td>
                </tr>
            </table>

            <p>Telah diizinkan untuk meninggalkan lingkungan sekolah dengan alasan tersebut di atas.</p>
            <p>Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
        </div>

        <!-- FOOTER / TANDA TANGAN -->
        <div class="footer">
            <div class="signature-box">
                <p>Mengetahui,<br>Orang Tua / Wali Siswa</p>
                <div class="signature-name">( ................................... )</div>
            </div>

            <div class="signature-box">
                <p>
                    {{ isset($school['city']) ? $school['city'] : 'Kota Admin' }},
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Petugas Piket / Guru
                </p>
                <div class="signature-name">{{ auth()->user()->name ?? 'Petugas' }}</div>
                <div class="nip">NIP. .......................</div>
            </div>
        </div>
    </div>

    <!-- TIMESTAMP SYSTEM -->
    <div class="timestamp">
        Dicetak otomatis oleh Sistem Absensi Barcode pada {{ date('d/m/Y H:i:s') }}
        <br>ID Izin: #{{ $permit->id }}
    </div>

</body>
</html>
