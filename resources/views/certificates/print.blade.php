<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
      <!-- DYNAMIC FAVICON -->
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    <title>Cetak Sertifikat Reward || {{$school['name'] ?? 'SMK GATECH'}}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap');

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
            padding-top: 40px; /* Reduced top padding to fit signatures */
            color: #333;
        }

        .logo {
            font-family: 'Roboto', sans-serif;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 14pt;
            margin-bottom: 10px;
        }

        .title {
            font-family: 'Great Vibes', cursive;
            font-size: 60pt;
            color: #C5A059;
            margin: 5px 0;
            line-height: 1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .subtitle {
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            border-bottom: 1px solid #C5A059;
            display: inline-block;
            padding-bottom: 5px;
        }

        .presented-to {
            font-size: 11pt;
            font-family: 'Roboto', sans-serif;
            margin-bottom: 5px;
            color: #555;
        }

        .student-name {
            font-size: 32pt;
            font-weight: bold;
            margin: 5px 0;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Styling untuk Kelas */
        .student-class {
            font-size: 14pt;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            margin-bottom: 15px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .description {
            font-family: 'Roboto', sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 10px auto 20px auto;
            width: 80%;
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
            margin-bottom: 15px;
        }

        /* Footer 3 Kolom Tanda Tangan */
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between; /* Menyebar ke seluruh lebar */
            padding: 0 50px;
            font-family: 'Roboto', sans-serif;
            font-size: 10pt; 
        }

        .signature {
            text-align: center;
            width: 30%; /* Bagi 3 kolom */
        }
        
        .signature strong {
            display: block;
            margin-bottom: 2px;
            font-size: 10pt;
            text-transform: uppercase;
        }

        .signature-line {
            width: 100%;
            border-bottom: 1px solid #333;
            margin: 0 auto 5px auto;
            height: 60px; /* Ruang untuk tanda tangan */
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
                <div class="logo">{{$school['name'] ?? 'SMK N 1 CONTOH'}}</div>
                
                <!-- Judul dari Input -->
                <div class="title">Certificate of Achievement</div>
                
                <div class="subtitle">{{ strtoupper($request->title) }}</div>

                <div class="presented-to">Diberikan Kepada:</div>
                
                <div class="student-name">{{ $data->student->name }}</div>
                
                <!-- Menampilkan Kelas -->
                <div class="student-class">
                    KELAS {{ $data->student->classroom->name ?? '-' }}
                </div>
                
                <div class="rank-badge">
                    RANKING #{{ $index + 1 }} • Kehadiran Tepat Waktu: {{ $data->total_present }} Hari
                </div>

                <div class="description">
                    Atas kedisiplinan dan dedikasi luar biasa dalam kehadiran tepat waktu di sekolah selama periode <b>{{ $period }}</b>. Semoga prestasi ini menjadi inspirasi bagi siswa lainnya.
                </div>

                <!-- Footer Tanda Tangan (3 Kolom) -->
                <div class="footer">
                    <!-- 1. Wali Kelas -->
                    <div class="signature">
                        <br>
                        <strong>Wali Kelas</strong>
                        <div class="signature-line"></div>
                        <br><br>
                        <span>( .......................... )</span>
                    </div>
                    
                    <!-- 2. Guru BK -->
                    <div class="signature">
                        <br>
                        <strong>Guru BK</strong>
                        <div class="signature-line"></div>
                        <br><br>
                        <span>( .......................... )</span>
                    </div>

                    <!-- 3. Ketua Program Keahlian -->
                    <div class="signature">
                        <span>{{ date('d F Y') }}</span><br>
                        <strong>Ketua Program Keahlian</strong>
                        <div class="signature-line"></div>
                        <br><br>
                        <span>( .......................... )</span>
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