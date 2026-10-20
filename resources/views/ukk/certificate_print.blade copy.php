<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat_UKK_{{ Str::slug($assessment->student->name, '_') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.2;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 287mm;
            height: 198mm;
            background: #fff;
            padding: 8px;
            box-sizing: border-box;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        /* Bingkai */
        .border-outer {
            border: 3px solid #1e3a8a;
            padding: 3px;
            height: 100%;
        }
        .border-inner {
            border: 2px solid #d97706;
            padding: 15px 25px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
        }

        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }

        /* Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
        }
        .school-title {
            font-family: 'Cinzel', serif;
            font-size: 13pt;
            font-weight: 700;
            color: #1e3a8a;
            letter-spacing: 1px;
        }

        /* Judul Sertifikat */
        .cert-title { 
            font-family: 'Cinzel', serif;
            font-size: 20pt; 
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase; 
            letter-spacing: 2px;
            margin-top: 5px;
        }
        .cert-subtitle { 
            font-size: 9.5pt; 
            color: #d97706;
            font-weight: 600;
            margin-bottom: 10px; 
            letter-spacing: 1px;
        }

        .student-name {
            font-family: 'Cinzel', serif;
            font-size: 18pt;
            color: #000;
            margin: 4px 0;
            text-transform: uppercase;
            border-bottom: 2px solid #d97706;
            display: inline-block;
            padding: 0 15px 2px 15px;
        }

        .status-badge {
            display: inline-block;
            font-size: 14pt;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 2px 16px;
            border: 2px solid #15803d;
            border-radius: 4px;
            margin: 4px 0;
        }

        .package-title {
            font-size: 10.5pt;
            font-weight: 700;
            color: #1e3a8a;
            margin: 4px auto;
            max-width: 90%;
            text-transform: uppercase;
            line-height: 1.3;
        }

        /* Halaman 2 Table */
        .table-units {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-units th, .table-units td {
            border: 1px solid #94a3b8;
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .table-units th {
            background-color: #1e3a8a;
            color: #ffffff;
            text-align: center;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Layout Footer Tanda Tangan Normal Flow */
        .footer-table {
            width: 100%;
            margin-top: 15px;
        }
        .footer-table td {
            text-align: center;
            vertical-align: bottom;
            height: 55px;
            font-size: 9pt;
        }

        @media print {
            body { background: none; }
            .page { width: 100%; height: 190mm; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<!-- ================= HALAMAN 1: UTAMA ================= -->
<div class="page">
    <div class="border-outer">
        <div class="border-inner">
            
            <!-- KOP HEADER -->
            <table class="header-table">
                <tr>
                    <td width="15%" class="text-center">
                        <img src="{{ asset('images/logo.png') }}" style="height: 45px;" alt="Logo" onerror="this.style.display='none'">
                    </td>
                    <td width="70%" class="text-center">
                        <div class="school-title">{{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</div>
                        <div style="font-size: 8pt; color: #475569;">Sertifikasi Uji Kompetensi Keahlian SMK Tahun {{ date('Y') }}</div>
                    </td>
                    <td width="15%" class="text-center">
                        <img src="{{ asset('images/tutwuri.png') }}" style="height: 45px;" alt="Logo" onerror="this.style.display='none'">
                    </td>
                </tr>
            </table>

            <!-- ISI SERTIFIKAT -->
            <div class="text-center">
                <div class="cert-title">SERTIFIKAT KOMPETENSI</div>
                <!-- Format Nomor Sertifikat Rapi -->
                <div class="cert-subtitle">NOMOR: {{ $certNumber ?? '001' }}/UKK/{{ $assessment->student->classroom->name ?? 'TITL' }}/{{ date('Y') }}</div>
                
                <p style="margin: 4px 0 2px 0; font-size: 10pt; color: #334155;">Dengan ini menyatakan bahwa:</p>
                <div><span class="student-name">{{ $assessment->student->name }}</span></div>
                <p style="margin: 4px 0 8px 0; font-size: 9.5pt; color: #475569;">
                    NIS / NISN: <strong>{{ $assessment->student->nis }}</strong> &nbsp;|&nbsp; Kelas: <strong>{{ $assessment->student->classroom->name ?? '-' }}</strong>
                </p>
                
                <p style="margin: 2px 0; font-size: 10pt; color: #334155;">Dinyatakan:</p>
                <div>
                    <span class="status-badge">{{ $assessment->status }}</span>
                </div>

                <p style="margin: 6px 0 2px 0; font-size: 10pt; color: #334155;">Pada Pelaksanaan Uji Kompetensi Keahlian Paket:</p>
                <div class="package-title">[{{ $assessment->package->major_code }}] {{ $assessment->package->package_name }}</div>
            </div>

            <!-- TANDA TANGAN (TIDAK TABRAKAN LAGI) -->
            <table class="footer-table">
                <tr>
                    <td width="33%">
                        Penguji Eksternal (DUDI)<br><br><br><br>
                        <strong><u>{{ $assessment->externalExaminer->name ?? '.........................' }}</u></strong>
                    </td>
                    <td width="33%">
                        Penguji Internal<br><br><br><br>
                        <strong><u>{{ $assessment->internalExaminer->name ?? '.........................' }}</u></strong>
                    </td>
                    <td width="34%">
                        Bukittinggi, {{ \Carbon\Carbon::parse($assessment->exam_date)->translatedFormat('d F Y') }}<br>
                        Kepala Sekolah<br><br><br><br>
                        <strong><u>{{ $settings['headmaster'] }}</u></strong><br>
                        <small>NIP. {{ $settings['nip'] }}</small>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>

<!-- ================= HALAMAN 2: DAFTAR UNIT ================= -->
<div class="page">
    <div class="border-outer">
        <div class="border-inner">
            
            <div>
                <div class="text-center mb-2" style="border-bottom: 2px solid #1e3a8a; padding-bottom: 6px;">
                    <h3 style="font-family: 'Cinzel', serif; margin: 0; color: #1e3a8a; font-size: 13pt;">DAFTAR UNIT KOMPETENSI HASIL UJIAN</h3>
                    <div style="font-size: 9pt; color: #475569; margin-top: 2px;">
                        Siswa: <strong>{{ $assessment->student->name }}</strong> (NIS: {{ $assessment->student->nis }})
                    </div>
                </div>

                <table class="table-units">
                    <thead>
                        <tr>
                            <th width="5%">NO</th>
                            <th width="18%">KODE UNIT</th>
                            <th width="37%">JUDUL UNIT KOMPETENSI</th>
                            <th width="30%">ELEMEN KOMPETENSI</th>
                            <th width="10%">HASIL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessment->package->units as $idx => $unit)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-center" style="color: #1e3a8a;">{{ $unit->unit_code }}</td>
                                <td>{{ $unit->unit_title }}</td>
                                <td>{{ $unit->element_name }}</td>
                                <td class="text-center fw-bold" style="color: {{ $assessment->status === 'Kompeten' ? '#15803d' : '#b91c1c' }};">
                                    {{ $assessment->status === 'Kompeten' ? 'Kompeten' : 'Belum' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Unit kompetensi belum dikonfigurasi untuk paket ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 10px; font-size: 8.5pt; background: #f8fafc; padding: 6px 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                    <strong>Ringkasan Perolehan Nilai:</strong>
                    <span class="ms-3 me-3">Nilai Internal: <strong>{{ $assessment->score_internal ?? '-' }}</strong></span> |
                    <span class="ms-3 me-3">Nilai Eksternal: <strong>{{ $assessment->score_external ?? '-' }}</strong></span> |
                    <span class="ms-3">Nilai Akhir: <strong style="color: #1e3a8a;">{{ $assessment->final_score ?? '-' }}</strong></span>
                </div>
            </div>

            <!-- TANDA TANGAN HALAMAN 2 -->
            <table class="footer-table">
                <tr>
                    <td width="50%" class="text-center">
                        Tanda Tangan Pemilik / Siswa<br><br><br><br>
                        <strong><u>{{ $assessment->student->name }}</u></strong>
                    </td>
                    <td width="50%" class="text-center">
                        Bukittinggi, {{ \Carbon\Carbon::parse($assessment->exam_date)->translatedFormat('d F Y') }}<br>
                        Ketua Tim Penguji<br><br><br><br>
                        <strong><u>{{ $assessment->internalExaminer->name ?? '.........................' }}</u></strong>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>

</body>
</html>