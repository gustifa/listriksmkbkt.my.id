<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Akun Siswa - {{ $classroom->name ?? 'Seluruh Sekolah' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 15px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-print {
            padding: 10px 24px;
            background: #005bea;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Grid A4 Penuh Presisi (Memuat 15 Struk Akun per Lembar) */
        .page-a4 {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 15mm auto;
            padding: 8mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: 52mm;
            grid-gap: 5mm;
            page-break-after: always;
        }

        .account-card {
            border: 1px dashed #777;
            border-radius: 6px;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
        }

        .card-header-small {
            font-size: 8px;
            font-weight: 700;
            text-align: center;
            border-bottom: 1.5px solid #005bea;
            padding-bottom: 2px;
            text-transform: uppercase;
            color: #111;
        }

        .student-title {
            font-size: 9px;
            font-weight: 700;
            color: #000;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .account-box {
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 7.5px;
            margin: 2px 0;
        }

        .account-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5px;
        }

        .val-bold {
            font-weight: 700;
            color: #1a202c;
            font-family: monospace;
            font-size: 8.5px;
        }

        .portal-link {
            text-align: center;
            background: #eef4ff;
            color: #005bea;
            font-weight: 700;
            font-size: 7.5px;
            padding: 2px 0;
            border-radius: 3px;
            margin-bottom: 2px;
            border: 1px border-subtle #005bea;
        }

        /* Area Barcode / QR Code */
        .barcode-area {
            text-align: center;
            margin-top: 1px;
        }

        .barcode-area svg, .barcode-area img {
            max-width: 100%;
            height: 20px !important;
            display: block;
            margin: 0 auto;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none !important; }
            .page-a4 { box-shadow: none; margin: 0; width: 210mm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Seluruh Akun & Barcode</button>
    </div>

    @foreach($students->chunk(15) as $chunk)
        <div class="page-a4">
            @foreach($chunk as $student)
                @php
                    // 1. Ekstrak Email / Username Login
                    $loginEmail = $student->user->email
                                ?? $student->user->username
                                ?? $student->email
                                ?? $student->username
                                ?? $student->nis;

                    // 2. Ekstrak Password
                    $loginPassword = $student->plain_password
                                   ?? $student->user->plain_password
                                   ?? $student->password_default
                                   ?? $student->user->password_default
                                   ?? $student->nis;
                @endphp

                <div class="account-card">
                    <!-- Header Kop Sekolah -->
                    <div class="card-header-small">
                        {{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}
                    </div>

                    <!-- Nama Siswa & Kelas -->
                    <div class="student-title">
                        {{ $student->name }}
                        <div style="font-size: 7.5px; font-weight: normal; color: #555;">
                            Kelas: {{ $student->classroom->name ?? '-' }} | NIS: {{ $student->nis }}
                        </div>
                    </div>

                    <!-- Informasi URL Portal Ujian -->
                    <div class="portal-link">
                        🌐 Portal: https://listriksmkbkt.my.id/
                    </div>

                    <!-- Informasi Login Email & Password -->
                    <div class="account-box">
                        <div class="account-row">
                            <span class="text-muted">Email / Login:</span>
                            <span class="val-bold">{{ $loginEmail }}</span>
                        </div>
                        <div class="account-row">
                            <span class="text-muted">Password:</span>
                            <span class="val-bold">{{ $loginPassword }}</span>
                        </div>
                    </div>

                    <!-- Barcode / QR Code NIS -->
                    <div class="barcode-area">
                        {!! QrCode::size(130)->generate($student->nis) !!}
                        <div style="font-size: 6px; color: #666; margin-top: 1px;">* {{ $student->nis }} *</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</body>
</html>
