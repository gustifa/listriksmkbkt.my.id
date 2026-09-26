<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Siswa | Portrait</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e0e0e0;
            margin: 0;
            padding: 20px;
        }

        .screen-header {
            text-align: center; background: white; padding: 20px;
            margin-bottom: 20px; border-radius: 8px; max-width: 210mm;
            margin-left: auto; margin-right: auto;
        }

        /* --- Kertas A4 & Grid Aman --- */
        .page-a4 {
            background: white; width: 210mm; min-height: 297mm;
            margin: 0 auto; padding: 10mm; text-align: center;
        }

        .id-card-wrapper {
            display: inline-block; margin: 5px; vertical-align: top;
            page-break-inside: avoid;
        }

        /* --- Desain Kartu Portrait --- */
        .id-card {
            width: 54mm; height: 86mm; background-color: #fff;
            border-radius: 8px; border: 1px solid #ccc;
            position: relative; overflow: hidden; box-sizing: border-box;
            text-align: center; padding-top: 15px;
        }

        .accent-bar {
            height: 6px; width: 100%; background: #005bea;
            position: absolute; top: 0; left: 0;
        }

        /* --- HEADER --- */
        .header-table {
            width: 100%; border-collapse: collapse; margin-bottom: 2px;
            border-bottom: 1px solid #005bea; padding-bottom: 3px;
        }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-img { width: 25px; height: 25px; }
        .header-text-school {
            font-size: 8px; font-weight: 700; color: #333;
            text-transform: uppercase; line-height: 1; margin: 0;
        }
        .header-text-address { font-size: 5px; color: #666; margin: 0; }

        /* --- QR Area (DIPERBESAR) --- */
        .qr-area {
            margin: 5px auto;
            width: 38mm;  /* Diperbesar dari 30mm */
            height: 38mm; /* Diperbesar dari 30mm */
        }
        .qr-area img {
            width: 100%; height: 100%; object-fit: contain;
        }

        /* --- Teks Info Siswa (DIPERBESAR) --- */
        .student-name {
            font-size: 13px; /* Diperbesar dari 10px */
            font-weight: 700; color: #000;
            margin-bottom: 2px; line-height: 1.1; text-transform: uppercase;
        }
        .student-class {
            font-size: 9px; font-weight: 600; color: #fff;
            background: #005bea; display: inline-block;
            padding: 2px 6px; border-radius: 4px; margin-bottom: 4px;
        }
        .student-nis { 
            font-size: 10px; /* Diperbesar dari 8px */
            font-weight: 700; /* Ditebalkan */
            color: #333; 
        }

        /* --- Area Tanda Tangan (DIPERKECIL) --- */
        .signature-wrapper {
            position: absolute; bottom: 5px; left: 0;
            width: 100%; text-align: center;
            line-height: 1.1; /* Merapatkan jarak antar baris */
        }
        .sig-date { font-size: 5px; color: #333; }
        .sig-title { font-size: 5px; color: #333; margin-bottom: 12px; } /* Margin untuk spasi TTD manual */
        .sig-name { font-size: 6px; font-weight: bold; text-decoration: underline; color: #000; }
        .sig-nip { font-size: 5px; color: #555; }

        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none; }
            .page-a4 { padding: 5mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="screen-header no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #005bea; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Cetak Sekarang
        </button>
    </div>

    <div class="page-a4">
        @foreach($students as $student)
            <div class="id-card-wrapper">
                <div class="id-card">
                    <div class="accent-bar"></div>
                    
                    <table class="header-table">
                        <tr>
                            <td style="width: 25px; text-align: center;">
                                @if($favicon)
                                    <img src="{{ asset('storage/'.$favicon) }}" class="header-logo-img">
                                @endif
                            </td>
                            <td style="text-align: center; padding: 0 2px;">
                                <p class="header-text-school">{{ \App\Models\Setting::value('app_name', 'GATECH') }}</p>
                                <p class="header-text-address">{{ \App\Models\Setting::value('school_name', 'Sekolah') }}</p>
                            </td>
                            <td style="width: 25px;"></td>
                        </tr>
                    </table>

                    <div class="qr-area">
                        @php
                            $qrCodeSvg = QrCode::size(130)->generate($student->nis); 
                        @endphp
                        <img src="data:image/svg+xml;base64,{{ base64_encode($qrCodeSvg) }}" alt="QR Code">
                    </div>

                    <div class="student-name">{{ Str::limit($student->name, 20) }}</div>
                    <div class="student-class">{{ $student->classroom->name ?? '-' }}</div>
                    <div class="student-nis">NIS: {{ $student->nis }}</div>

                    <div class="signature-wrapper">
                        <div class="sig-date">Bukittinggi, {{ date('Y') }}</div>
                        <div class="sig-title">Kepala Sekolah</div>
                        
                        <div style="height: 12px;"></div> 

                        <div class="sig-name">GA TECH</div>
                        <div class="sig-nip">NIP. 19860802023211016</div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

</body>
</html>