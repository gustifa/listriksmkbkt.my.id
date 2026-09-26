<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin - {{ $permit->student->name }} | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    <style>
        
        /* Mengatur Margin Halaman secara Dinamis dari Database */
        @page {
            margin-top: {{ $school['margin_top'] ?? '2cm' }};
            margin-right: {{ $school['margin_right'] ?? '2cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2cm' }};
            margin-left: {{ $school['margin_left'] ?? '2cm' }};
        }

        /* Layout Kop Surat menggunakan Tabel agar rapi di PDF */
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }

        /* Logo harus menggunakan public_path agar terbaca oleh DOMPDF */
        .logo-img { width: 80px; height: auto; }

        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }

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
        <!-- <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button> -->
    </div>

    <div class="container">
        <!-- HEADER / KOP SURAT -->
        <!-- KOP SURAT DINAMIS -->
    <table class="header-table">
        <tr>
            <!-- LOGO KIRI -->
            <td width="15%" class="text-center">
                @if(isset($school['logo_left']) && $school['logo_left'])
                    <img src="{{ asset('storage/'.$school['logo_left']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>

            <!-- TEKS TENGAH (IDENTITAS SEKOLAH) -->
            <td width="70%" class="school-info">
                <h1>{{ $school['name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['address'] ?? 'Alamat sekolah belum diatur di menu pengaturan.' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
                <p>Website: {{ $school['web'] ?? '-' }}</p>
            </td>

            <!-- LOGO KANAN -->
            <td width="15%" class="text-center">
                @if(isset($school['logo_right']) && $school['logo_right'])
                    <img src="{{ asset('storage/'.$school['logo_right']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

        <!-- ISI SURAT -->
        <div class="title">SURAT IZIN MENINGGALKAN SEKOLAH</div>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini, 
                @php
                    $role = auth()->user()->jenis_user ?? 'Petugas';
                    if ($role == 'piket') echo 'Petugas Piket';
                    elseif ($role == 'guru') echo 'Guru Pengajar';
                    else echo 'Petugas Sekolah';
                @endphp
            menerangkan bahwa siswa:</p>

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
                    {{ $school['sign_city'] ?? 'Jakarta' }}, 
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    
                    <!-- Jabatan Dinamis -->
                    @if(Auth()->user()->jenis_user == 'piket')
                        Petugas Piket
                    @elseif(Auth()->user()->jenis_user == 'guru')
                        Guru Pengajar
                    @else
                        Petugas Sekolah
                    @endif
                </p>
                <div class="signature-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
                <!-- NIP dummy atau ambil dari relasi teacher jika ada -->
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