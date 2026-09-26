<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Semua Kartu Siswa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e0e0e0; /* Abu-abu di layar agar kertas terlihat */
            margin: 0;
            padding: 20px;
        }

        /* Simulasi Kertas A4 di Layar */
        .page-a4 {
            background: white;
            width: 210mm; /* Lebar A4 */
            min-height: 297mm; /* Tinggi A4 */
            margin: 0 auto;
            padding: 10mm; /* Margin aman printer */
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 Kolom */
            grid-gap: 10px 15px; /* Jarak antar kartu (AtasBawah KiriKanan) */
            align-content: start; /* Mulai dari atas */
            margin-bottom: 20px;
        }

        /* --- DESAIN KARTU (Sama dengan Single, tapi disesuaikan ukurannya) --- */
        .id-card-wrapper {
            /* Wrapper untuk border putus-putus (Garis Potong) */
            border: 1px dashed #ccc;
            padding: 2px;
            page-break-inside: avoid; /* PENTING: Jangan potong elemen ini ke halaman baru */
        }

        .id-card {
            width: 100%; /* Mengikuti lebar kolom grid */
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

        /* Tombol Print Floating */
        .fab-print {
            position: fixed; bottom: 30px; right: 30px;
            background: #005bea; color: white;
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,91,234,0.4);
            border: none;
            z-index: 100;
        }

        /* --- PENGATURAN PRINT --- */
        @media print {
            body { margin: 0; padding: 0; background: none; }
            .page-a4 {
                width: 100%; margin: 0; padding: 5mm; /* Sesuaikan margin printer fisik */
                border: none; box-shadow: none;
            }
            .fab-print { display: none; } /* Sembunyikan tombol saat print */

            /* Paksa Background Color tercetak */
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <!-- Header Judul (Hanya tampil jika variabel $classroom dikirim) -->
@if(isset($classroom))
    <div class="no-print" style="text-align: center; padding: 20px; background: #fff; margin-bottom: 20px; border-bottom: 1px solid #ccc;">
        <h2>Preview Kartu Kelas: {{ $classroom->name }}</h2>
        <p>Pastikan setingan printer: A4, Margins Minimum/None, Scale 100%.</p>
        <button onclick="window.print()" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">🖨️ Cetak Sekarang</button>
    </div>
@endif

    <button class="fab-print" onclick="window.print()">🖨️</button>

    <div class="page-a4">
        @foreach($students as $student)
            <div class="id-card-wrapper">
                <div class="id-card">
                    <div class="accent-bar"></div>
                    <div class="card-content">
                        <div class="qr-area">
                            {!! QrCode::size(150)->generate($student->nis) !!}
                        </div>
                        <div class="text-area">
                            <div class="school-name">SMK Negeri 1 Bukittinggi</div>
                            <h1 class="student-name">{{ Str::limit($student->name, 20) }}</h1> <div class="student-class">{{ $student->class_name }}</div>
                            <div class="nis-label">NIS: {{ $student->nis }}</div>
                            <div class="nis-label">Kelas: {{ $student->classroom->name  ?? "-"}}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
