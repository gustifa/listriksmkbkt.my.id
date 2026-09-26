<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ID Card - {{ $student->name }}</title>
    <style>
        @page {
            size: 9cm 4cm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            background-color: #f0f0f0; /* Background saat preview browser */
        }

        .id-card-container {
            width: 9cm;
            height: 4cm;
            background: white;
            border: 1px solid #ddd;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            background: linear-gradient(135deg, #ffffff 60%, #e3f2fd 60%);
        }

        /* Bagian Kiri (QR Code) */
        .left-section {
            width: 3.5cm;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 2px dashed #ccc;
        }

        .qr-code {
            width: 2.8cm;
            height: 2.8cm;
        }

        /* Bagian Kanan (Identitas) */
        .right-section {
            flex: 1;
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .header {
            font-size: 8pt;
            font-weight: bold;
            color: #1a73e8; /* Warna Biru Header */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .student-name {
            font-size: 11pt;
            font-weight: 800;
            color: #333;
            line-height: 1.1;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .student-nis {
            font-size: 14pt; /* NIS dibuat besar agar mudah dibaca */
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            color: #555;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .student-class {
            font-size: 9pt;
            background: #333;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 4px;
            right: 8px;
            font-size: 6pt;
            color: #999;
        }

        @media print {
            body { background: none; }
            .id-card-container { border: none; } /* Hilangkan border saat print asli */

            /* Sembunyikan tombol saat print */
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi (Tidak ikut ter-print) -->
    <div class="no-print" style="position: fixed; top: 10px; left: 10px;">
        <button onclick="window.print()" style="padding: 5px 10px; cursor: pointer; font-weight:bold;">Cetak Kartu</button>
        <button onclick="window.close()" style="padding: 5px 10px; cursor: pointer;">Tutup</button>
    </div>

    <!-- KARTU ID -->
    <div class="id-card-container">
        <div class="left-section">
            <!-- Generate QR Code NISN/NIS -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $student->nis }}" alt="QR" class="qr-code">
        </div>

        <div class="right-section">
            <div class="header">KARTU PELAJAR</div>

            <div class="student-name">
                {{ Str::limit($student->name, 18) }}
            </div>

            <div class="student-nis">
                {{ $student->nis }}
            </div>

            <div>
                <span class="student-class">{{ $student->classroom->name ?? '-' }}</span>
            </div>
        </div>

        <div class="footer">SMK NEGERI TEKNOLOGI</div>
    </div>

    <script>
        // Otomatis trigger print saat halaman dibuka
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
