<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Siswa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e0e0e0;
            margin: 0;
            padding: 20px;
        }

        /* Tampilan di Layar (Preview Mode) */
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
            margin: 10px 0;
            display: inline-block;
        }

        /* Simulasi Kertas A4 */
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

        /* Desain Kartu */
        .id-card-wrapper {
            border: 1px dashed #ccc;
            padding: 2px;
            page-break-inside: avoid;
        }

        .id-card {
            width: 100%;
            height: 54mm;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            display: flex;
            border: 1px solid #eee;
        }

        .accent-bar {
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, #005bea, #00c6fb);
        }

        .card-content {
            flex: 1;
            display: flex;
            padding: 8px;
            align-items: center;
        }

        .qr-area {
            flex: 0 0 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-right: 2px dashed #eee;
            padding-right: 8px;
        }

        .text-area {
            flex: 1;
            padding-left: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .school-name { font-size: 7px; text-transform: uppercase; color: #666; letter-spacing: 1px; margin-bottom: 3px; }
        .student-name { font-size: 12px; font-weight: 700; color: #333; margin: 0; line-height: 1.2; }
        .student-class {
            font-size: 10px; font-weight: 600; color: #005bea;
            margin-top: 3px; background-color: #eef4ff;
            display: inline-block; padding: 2px 6px; border-radius: 4px;
        }
        .nis-label { font-size: 7px; color: #999; margin-top: auto; padding-top: 4px; }

        /* --- PENGATURAN CETAK (PRINT) --- */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: none;
            }

            /* Sembunyikan elemen layar (Background abu-abu, tombol, instruksi) */
            .no-print {
                display: none !important;
            }

            /* Header Judul (Agar ikut tercetak tapi rapi) */
            .screen-header {
                box-shadow: none;
                margin: 0;
                padding: 10px 0;
                text-align: left; /* Rata kiri saat diprint */
                border-bottom: 1px solid #000;
                margin-bottom: 20px;
                width: 100%;
                max-width: 100%;
            }

            .page-a4 {
                width: 100%;
                margin: 0;
                padding: 0; /* Margin dikontrol oleh setting printer */
                border: none;
                box-shadow: none;
            }

            /* Paksa background color (warna biru dll) tercetak */
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Header & Instruksi -->
    <div class="screen-header">
        <!-- Judul Ini AKAN TERCETAK -->
        <h2 style="margin: 0;">Kartu Identitas Kelas: {{ $classroom->name ?? 'Semua Siswa' }}</h2>

        <!-- Bagian ini HANYA TAMPIL DI LAYAR (Tidak ikut diprint) -->
        <div class="no-print">
            <div class="alert-info">
                <strong>Tips Printer:</strong> Gunakan kertas A4, Margin 'Minimum' atau 'None', dan Scale '100%'.
            </div>
            <br>
            <button onclick="window.print()" style="padding: 10px 20px; background: #005bea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                🖨️ Cetak Sekarang
            </button>
        </div>
    </div>

    <!-- Halaman Kertas -->
    <div class="page-a4">
        @foreach($students as $student)
            <div class="id-card-wrapper">
                <div class="id-card">
                    <div class="accent-bar"></div>
                    <div class="card-content">
                        <div class="qr-area">
                            <!-- QR Code -->
                            {!! QrCode::size(90)->generate($student->nis) !!}
                        </div>
                        <div class="text-area">
                            <div class="school-name">{{$settings['school_name']}}</div>
                            <h1 class="student-name">{{ Str::limit($student->name, 20) }}</h1>
                            <div class="student-class">{{ $student->classroom->name ?? '-' }}</div>
                            <div class="nis-label">NIS: {{ $student->nis }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
