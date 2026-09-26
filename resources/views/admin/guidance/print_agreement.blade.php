<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perjanjian Siswa - {{ $student->name }}</title>
    
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background-color: #fff;
        }
        
        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 4px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .logo-img { width: 85px; height: auto; }
        .school-info { text-align: center; }
        .school-info h3 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h2 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .school-info p { margin: 0; font-size: 10pt; }

        /* JUDUL SURAT */
        .title-surat { text-align: center; margin-bottom: 30px; }
        .title-surat h1 { margin: 0; text-decoration: underline; font-size: 14pt; text-transform: uppercase; font-weight: bold; }

        /* ISI SURAT */
        .content { text-align: justify; margin-bottom: 20px; }
        
        .data-table { width: 100%; margin-left: 20px; margin-bottom: 20px; border-collapse: collapse; }
        .data-table td { vertical-align: top; padding: 4px 0; }
        .col-label { width: 150px; }
        .col-sep { width: 15px; text-align: center; }

        .bold { font-weight: bold; }
        .indent { text-indent: 40px; }

        /* KOTAK JANJI */
        .commitment-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 15px 0;
            background-color: #fafafa;
            font-style: italic;
        }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
            padding-bottom: 15px;
        }

        .materai-box {
            width: 80px;
            height: 50px;
            border: 1px dashed #999;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #666;
            font-style: italic;
        }

        /* HIDE BUTTONS ON PRINT */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak (Hanya tampil di layar) -->
    <div class="no-print" style="text-align: right; margin-bottom: 20px; padding: 10px; background: #f0f0f0;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak Surat
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: left;">
                @if(!empty($school['logo_left']))
                    <img src="{{ asset('storage/'.$school['logo_left']) }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                <h3>PEMERINTAH PROVINSI {{ strtoupper($school['provinsi_name'] ?? 'SUMATERA BARAT') }}</h3>
                <h3>DINAS PENDIDIKAN</h3>
                <h2>{{ $school['name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</h2>
                <p>{{ $school['address'] ?? 'Jalan Iskandar Teja Sukmana' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
            </td>
            <td width="15%" style="text-align: right;">
                @if(!empty($school['logo_right']))
                    <img src="{{ asset('storage/'.$school['logo_right']) }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL -->
    <div class="title-surat">
        <h1>SURAT PERNYATAAN DAN PERJANJIAN SISWA</h1>
    </div>

    <!-- ISI SURAT -->
    <div class="content">
        <p>Saya yang bertanda tangan di bawah ini:</p>

        <table class="data-table">
            <tr>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-sep">:</td>
                <td class="bold uppercase">{{ $student->name }}</td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td>{{ $student->nis }}</td>
            </tr>
            <tr>
                <td>Kelas / Kompetensi</td>
                <td>:</td>
                <td>{{ $student->classroom->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $student->address ?? '..................................................................' }}</td>
            </tr>
            <tr>
                <td>Nama Orang Tua/Wali</td>
                <td>:</td>
                <td>..................................................................</td>
            </tr>
        </table>

        <p class="indent">
            Dengan ini menyatakan dengan sesungguhnya dan penuh kesadaran bahwa saya telah melakukan pelanggaran tata tertib sekolah berupa:
        </p>

        <!-- KOTAK MASALAH -->
        <div style="margin: 10px 40px; padding: 10px; border-left: 3px solid #000; background: #f9f9f9;">
            <strong>"{{ $guidance->problem_summary }}"</strong>
        </div>

        <p class="indent">
            Sehubungan dengan hal tersebut, saya menyadari kesalahan yang saya lakukan dan memohon maaf kepada pihak sekolah. Sebagai bentuk komitmen perbaikan diri, saya berjanji:
        </p>

        <!-- KOTAK JANJI SISWA (Dari form input pembinaan) -->
        <div class="commitment-box">
            "{{ $guidance->student_commitment }}"
        </div>

        <p class="indent">
            Apabila di kemudian hari saya mengulangi pelanggaran yang sama atau melakukan pelanggaran tata tertib sekolah lainnya, saya bersedia menerima sanksi yang lebih berat dari pihak sekolah, sesuai dengan ketentuan dan peraturan yang berlaku di {{ $school['name'] ?? 'SMK Negeri 1 Bukittinggi' }}.
        </p>

        <p class="indent">
            Demikian surat pernyataan dan perjanjian ini saya buat dengan sebenarnya, tanpa ada paksaan dari pihak manapun, agar dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui/Menyetujui,<br>Orang Tua / Wali Murid</p>
                <br><br><br><br>
                <p class="bold">(.......................................)</p>
            </td>
            <td>
                <p>{{ $school['sign_city'] ?? 'Bukittinggi' }}, {{ \Carbon\Carbon::parse($guidance->date)->translatedFormat('d F Y') }}<br>Yang Membuat Pernyataan,</p>
                <div class="materai-box">Materai<br>10.000</div>
                <p class="bold" style="text-decoration: underline;">{{ strtoupper($student->name) }}</p>
                <p>NIS. {{ $student->nis }}</p>
            </td>
        </tr>
    </table>

    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td>
                <p>Guru Pembimbing / BK</p>
                <br><br><br><br>
                <p class="bold" style="text-decoration: underline;">{{ strtoupper($guidance->teacher->user->name ?? $guidance->teacher->name) }}</p>
                <p>NIP. {{ $guidance->teacher->nip ?? '-' }}</p>
            </td>
            <td>
                <p>Mengetahui,<br>Kepala Sekolah</p>
                <br><br><br><br>
                <p class="bold" style="text-decoration: underline;">{{ $school['headmaster_name'] ?? '.........................' }}</p>
                <p>NIP. {{ $school['headmaster_nip'] ?? '.........................' }}</p>
            </td>
        </tr>
    </table>

</body>
</html>