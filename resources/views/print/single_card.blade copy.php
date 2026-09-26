<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Siswa - {{ $student->name }} | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
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

        /* Reset dasar untuk printing */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0; /* Warna background browser saja */
            font-family: 'Poppins', sans-serif;
        }

        /* Container utama untuk menengahkan kartu di layar */
        .print-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* --- DESAIN KARTU SISWA --- */
        .id-card {
            /* Ukuran Standar ID Card CR80 */
            width: 85.6mm;
            height: 54mm;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
            display: flex;
            border: 1px solid #ddd; /* Border tipis untuk panduan potong */
        }

        /* Aksen Warna di Samping (Opsional) */
        .accent-bar {
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, #005bea, #00c6fb); /* Ganti warna sekolah Anda */
        }

        .card-content {
            flex: 1;
            display: flex;
            padding: 10px;
            align-items: center;
        }

        /* Area QR Code (Kiri) */
        .qr-area {
            flex: 0 0 130px; /* Lebar tetap untuk area QR */
            display: flex;
            justify-content: center;
            align-items: center;
            border-right: 2px dashed #eee;
            padding-right: 10px;
        }

        /* Area Teks (Kanan) */
        .text-area {
            flex: 1;
            padding-left: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .school-name {
            font-size: 8px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .student-name {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin: 0;
            line-height: 1.2;
        }

        .student-class {
            font-size: 11px;
            font-weight: 600;
            color: #005bea; /* Warna aksen */
            margin-top: 4px;
            background-color: #eef4ff;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
        }
        
        .nis-label {
             font-size: 8px;
             color: #999;
             margin-top: auto;
             padding-top: 5px;
        }

        /* --- PENGATURAN KHUSUS CETAK (PRINT CSS) --- */
        @media print {
            body {
                background-color: #fff;
                -webkit-print-color-adjust: exact; /* Agar background color tercetak */
                print-color-adjust: exact;
            }
            .print-container {
                display: block;
                min-height: auto;
                padding: 0;
                margin: 20px; /* Margin saat dicetak di kertas A4 */
            }
            .id-card {
                box-shadow: none; /* Hilangkan bayangan saat cetak */
                border: 1px solid #ccc; /* Border tipis untuk panduan potong */
                page-break-inside: avoid; /* Jangan potong kartu di tengah halaman */
                margin-bottom: 10px; /* Jarak antar kartu jika cetak banyak */
            }
             /* Sembunyikan elemen browser yang tidak perlu */
            button.no-print { display: none; }
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-top: 20px;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #005bea; color: white; border: none; border-radius: 5px;">🖨️ Cetak Kartu</button>
    </div>

    <div class="print-container">
        <div class="id-card">
            <div class="accent-bar"></div>
            <div class="card-content">
                <div class="qr-area">
                    {!! $qrcode !!}
                </div>
                <div class="text-area">
                    <div class="school-name">SMK Negeri 1 Contoh</div>
                    <h1 class="student-name">{{ $student->name }}</h1>
                    <div class="student-class">{{ $student->class_name }}</div>
                    <div class="nis-label">NIS: {{ $student->nis }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>