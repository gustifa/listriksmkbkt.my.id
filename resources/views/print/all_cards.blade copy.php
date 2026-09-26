<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Siswa | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e0e0e0;
            margin: 0;
            padding: 20px;
        }

        /* --- Tampilan Layar --- */
        .screen-header {
            text-align: center;
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #0d47a1;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
        }

        /* --- Kertas A4 --- */
        .page-a4 {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 10px 15px;
            align-content: start;
            margin-bottom: 20px;
        }

        /* --- Desain Kartu --- */
        .id-card-wrapper {
            border: 1px dashed #ccc;
            padding: 2px;
            page-break-inside: avoid;
        }

        .id-card {
            width: 100%;
            height: 54mm; /* Standar ID Card */
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            display: flex;
            border: 1px solid #eee;
        }

        /* Bar Warna Kiri */
        .accent-bar {
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, #005bea, #00c6fb);
            flex-shrink: 0;
        }

        /* Container Utama di sebelah Accent Bar */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 5px 10px;
            position: relative;
        }

        /* --- HEADER (LOGO KIRI KANAN) --- */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #005bea;
            padding-bottom: 4px;
            margin-bottom: 4px;
            height: 35px; /* Tinggi area kop */
        }

        .header-logo {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .school-header-text {
            flex: 1;
            text-align: center;
            font-size: 10px; /* Ukuran font nama sekolah diperbesar sedikit */
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
            line-height: 1.1;
            padding: 0 5px;
        }

        .school-address {
            font-size: 5px;
            font-weight: 400;
            color: #666;
            text-transform: none;
        }

        /* --- BODY KARTU --- */
        .card-body {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .qr-area {
            flex: 0 0 70px; /* Lebar area QR */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 8px;
        }

        .student-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .label-small { font-size: 7px; color: #888; margin-bottom: 1px; }
        .student-name { font-size: 11px; font-weight: 700; color: #000; margin: 0 0 2px 0; line-height: 1.1; }
        .student-class {
            font-size: 9px; font-weight: 600; color: #005bea;
            background: #eef4ff; display: inline-block;
            padding: 1px 5px; border-radius: 3px; margin-bottom: 2px;
        }
        .student-nis { font-size: 9px; color: #444; }

        /* --- FOOTER (TANDA TANGAN) --- */
        .signature-area {
            position: absolute;
            bottom: 5px;
            right: 10px;
            text-align: center;
            width: 100px;
        }

        .sig-date { font-size: 6px; color: #333; margin-bottom: 1px; }
        .sig-title { font-size: 6px; font-weight: bold; margin-bottom: 0px; }
        .sig-img {
            height: 25px; /* Tinggi tanda tangan */
            width: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .sig-name { font-size: 7px; font-weight: bold; text-decoration: underline; margin-top: -2px; }
        .sig-nip { font-size: 6px; color: #555; }

        /* --- PRINT SETTINGS --- */
        @media print {
            body { margin: 0; padding: 0; background: none; }
            .no-print { display: none !important; }
            .screen-header {
                box-shadow: none; margin: 0; padding: 10px 0;
                text-align: left; border-bottom: 1px solid #000; width: 100%;
            }
            .page-a4 { width: 100%; margin: 0; padding: 0; border: none; box-shadow: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="screen-header">
        <h2 style="margin: 0;">Kartu Identitas Kelas: {{ $classroom->name ?? 'Semua Siswa' }}</h2>
        <div class="no-print">
            <div class="alert-info">
                <strong>Tips:</strong> Gunakan margin 'Minimum' saat print. Ganti path gambar logo di kode sebelum produksi.
            </div>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background: #005bea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                🖨️ Cetak Sekarang
            </button>
        </div>
    </div>

    <div class="page-a4">
        @foreach($students as $student)
            <div class="id-card-wrapper">
                <div class="id-card">
                    <div class="accent-bar"></div>

                    <div class="main-content">

                       @include('print.card_header')

                        <div class="card-body">
                            <div class="qr-area">
                                {!! QrCode::size(130)->generate($student->nis) !!}
                            </div>
                            <div class="student-info">
                                <div class="label-small">Nama Siswa:</div>
                                <h1 class="student-name">{{ Str::limit($student->name, 20) }}</h1>

                                <div class="label-small" style="margin-top:2px;">Kelas: {{ $student->classroom->name ?? '-' }}</div>
                                {{-- <div class="student-class">{{ $student->classroom->name ?? '-' }}</div> --}}

                                <div class="student-nis">NIS: {{ $student->nis }}</div>
                            </div>
                        </div>

                        @include('print.card_signature')


                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
