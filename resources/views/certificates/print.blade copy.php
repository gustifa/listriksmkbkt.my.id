<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Sertifikat Reward</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400&display=swap');

        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: #eee;
            font-family: 'Playfair Display', serif;
            -webkit-print-color-adjust: exact;
        }

        .page-break {
            page-break-after: always;
        }

        .certificate-container {
            width: 297mm;
            height: 210mm;
            background: #fff;
            position: relative;
            margin: 0 auto;
            box-sizing: border-box;
            padding: 20px;
            overflow: hidden;
            border: 10px solid #ddd; /* Fallback border */
        }

        /* Desain Border Emas */
        .border-pattern {
            position: absolute;
            top: 15px; left: 15px; right: 15px; bottom: 15px;
            border: 2px solid #C5A059;
            padding: 5px;
        }
        .border-pattern:before {
            content: '';
            position: absolute;
            top: 5px; left: 5px; right: 5px; bottom: 5px;
            border: 5px double #C5A059;
        }

        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            padding-top: 50px;
            color: #333;
        }

        .logo {
            font-family: 'Roboto', sans-serif;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 14pt;
            margin-bottom: 20px;
        }

        .title {
            font-family: 'Great Vibes', cursive;
            font-size: 68pt;
            color: #C5A059;
            margin: 10px 0;
            line-height: 1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .subtitle {
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            border-bottom: 1px solid #C5A059;
            display: inline-block;
            padding-bottom: 5px;
        }

        .presented-to {
            font-size: 12pt;
            font-family: 'Roboto', sans-serif;
            margin-bottom: 10px;
            color: #555;
        }

        .student-name {
            font-size: 36pt;
            font-weight: bold;
            margin: 10px 0;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .description {
            font-family: 'Roboto', sans-serif;
            font-size: 14pt;
            line-height: 1.5;
            margin: 20px auto;
            width: 70%;
            color: #555;
        }

        .rank-badge {
            display: inline-block;
            background: #C5A059;
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-family: 'Roboto', sans-serif;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
            font-family: 'Roboto', sans-serif;
        }

        .signature {
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
            height: 50px; /* Space for sign */
        }

        @media print {
            body { background: none; }
            .certificate-container { border: none; margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body onload="window.print()">

    @foreach($bestStudents as $index => $data)
    <div class="certificate-container">
        <div class="border-pattern">
            <div class="content">
                <div class="logo">SMK NEGERI TEKNOLOGI</div>
                
                <!-- Judul dari Input -->
                <div class="title">Certificate of Achievement</div>
                
                <div class="subtitle">{{ strtoupper($request->title) }}</div>

                <div class="presented-to">Diberikan Kepada:</div>
                
                <div class="student-name">{{ $data->student->name }}</div>
                
                <div class="rank-badge">
                    RANKING #{{ $index + 1 }} • Kehadiran Tepat Waktu: {{ $data->total_present }} Hari
                </div>

                <div class="description">
                    Atas kedisiplinan dan dedikasi luar biasa dalam kehadiran tepat waktu di sekolah selama periode <b>{{ $period }}</b>. Semoga prestasi ini menjadi inspirasi bagi siswa lainnya.
                </div>

                <div class="footer">
                    <div class="signature">
                        <div class="signature-line">
                            <!-- Tempat Tanda Tangan -->
                            <br><br>
                        </div>
                        <strong>{{ date('d F Y') }}</strong><br>
                        <small>Tanggal</small>
                    </div>
                    
                    <div class="signature">
                         <!-- Stempel Gambar (Opsional) -->
                         <!-- <img src="/path/to/stamp.png" style="position:absolute; width:100px; margin-top:-40px; opacity:0.8;"> -->
                        <div class="signature-line">
                            <br><br>
                        </div>
                        <strong>KEPALA SEKOLAH</strong><br>
                        <small>Tanda Tangan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Break untuk halaman berikutnya -->
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

    @endforeach

</body>
</html>