<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat_UKK_{{ Str::slug($assessment->student->name, '_') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Montserrat:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * { 
            box-sizing: border-box; 
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.4;
            background-color: #fff;
        }
        .page {
            width: 210mm;
            height: 297mm;
            padding: 12mm 15mm;
            box-sizing: border-box;
            page-break-after: always;
            page-break-inside: avoid;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Menyebarkan elemen secara proporsional dari atas ke bawah */
        }
        .page:last-child { 
            page-break-after: avoid; 
        }

        /* Bingkai Tipis Resmi Pedoman UKK */
        .cert-border {
            border: 2px solid #1e293b;
            padding: 10mm 12mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .text-italic { font-style: italic; color: #4b5563; }

        /* Logo Tut Wuri Handayani */
        .tutwuri-logo {
            height: 85px;
            width: auto;
            object-fit: contain;
            margin-bottom: 12px;
        }

        /* Judul Sertifikat */
        .cert-title-id {
            font-family: 'Cinzel', serif;
            font-size: 18pt;
            font-weight: 800;
            color: #111827;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cert-title-en {
            font-family: 'Montserrat', sans-serif;
            font-size: 13pt;
            font-weight: 700;
            color: #2563eb;
            font-style: italic;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .cert-number {
            font-size: 9.5pt;
            font-weight: 600;
            color: #374151;
            margin-bottom: 25px;
        }

        /* Seksi Teks Bilingual (Ditingkatkan Jaraknya) */
        .bilingual-block {
            margin-bottom: 20px;
        }
        .label-id {
            font-size: 10pt;
            color: #1f2937;
        }
        .label-en {
            font-size: 9pt;
            font-style: italic;
            color: #6b7280;
        }

        /* Nama Siswa (Diperbesar) */
        .student-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 18pt;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .student-nisn {
            font-size: 10pt;
            font-weight: 600;
            color: #374151;
        }

        /* Konsentrasi & Judul Penugasan (Diperbesar) */
        .major-title {
            font-size: 13pt;
            font-weight: 700;
            color: #111827;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .assignment-title {
            font-size: 11pt;
            font-weight: 700;
            color: #111827;
            margin-top: 4px;
            line-height: 1.4;
            padding: 0 10px;
        }
        .predicate-title {
            font-size: 13pt;
            font-weight: 700;
            color: #111827;
            margin-top: 4px;
        }

        /* Masa Berlaku */
        .validity-block {
            margin-top: 15px;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        /* Tabel Tanda Tangan */
        .footer-signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .footer-signatures td {
            vertical-align: top;
            font-size: 9.5pt;
        }

        /* Tabel Unit Kompetensi (Halaman 2) */
        .table-units {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .table-units th, .table-units td {
            border: 1px solid #374151;
            padding: 7px 10px;
            font-size: 9pt;
        }
        .table-units th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: 700;
            text-align: center;
        }

        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
            .page { 
                width: 210mm; 
                height: 297mm; 
                padding: 10mm; 
                page-break-after: always;
                overflow: hidden;
            }
        }
    </style>
</head>
<body onload="window.print()">

<!-- ================= HALAMAN 1: SERTIFIKAT UTAMA ================= -->
<div class="page">
    <div class="cert-border">
        
        <div class="text-center">
            <!-- Logo Tut Wuri Handayani -->
            <img src="{{ asset('upload/logo_tutwuri.png') }}" 
                 class="tutwuri-logo" 
                 alt="Tut Wuri Handayani" 
                 onerror="this.src='{{ asset('images/logo_tutwuri.png') }}'; this.onerror=null;">
            
            <div class="cert-title-id">SERTIFIKAT KOMPETENSI</div>
            <div class="cert-title-en">COMPETENCY CERTIFICATE</div>
            <div class="cert-number">
                Nomor : {{ $settings['school_code'] ?? 'SMKN1BKT' }}-{{ $assessment->package->major_code ?? 'TITL' }}/UKK-{{ date('Y') }}/{{ $certNumber }}
            </div>

            <div class="bilingual-block">
                <div class="label-id">Dengan ini menyatakan bahwa,</div>
                <div class="label-en">Hereby declare that</div>
                <div class="student-name">{{ $assessment->student->name }}</div>
                <div class="student-nisn">NISN/NIS: {{ $assessment->student->nisn ?? $assessment->student->nis }}</div>
            </div>

            <div class="bilingual-block">
                <div class="label-id">Telah mengikuti Uji Kompetensi Keahlian</div>
                <div class="label-en">Has taken the skills competency test</div>
            </div>

            <div class="bilingual-block">
                <div class="label-id">pada Kompetensi / Konsentrasi Keahlian</div>
                <div class="label-en">on Competency of</div>
                <div class="major-title">
                    {{ $assessment->package->major_name ?? $assessment->student->classroom->department ?? 'TEKNIK INSTALASI TENAGA LISTRIK' }}
                </div>
            </div>

            <div class="bilingual-block">
                <div class="label-id">pada Judul Penugasan</div>
                <div class="label-en">on Assignment</div>
                <div class="assignment-title">
                    {{ $assessment->package->package_name }}
                </div>
            </div>

            <div class="bilingual-block">
                <div class="label-id">dengan predikat</div>
                <div class="label-en">with achievement level</div>
                <div class="predicate-title">
                    @if(($assessment->final_score ?? 0) >= 90 || $assessment->status === 'Sangat Kompeten')
                        Sangat Kompeten<br><span class="label-en" style="font-weight: 600; color: #111827;">Very Competent</span>
                    @elseif(($assessment->final_score ?? 0) >= 75 || $assessment->status === 'Kompeten')
                        Kompeten<br><span class="label-en" style="font-weight: 600; color: #111827;">Competent</span>
                    @else
                        Belum Kompeten<br><span class="label-en" style="font-weight: 600; color: #111827;">Not Yet Competent</span>
                    @endif
                </div>
            </div>

            <div class="validity-block">
                <div class="label-id">Sertifikat ini berlaku untuk : 3 (tiga) Tahun</div>
                <div class="label-en">This certificate is valid for : 3 (three) Years</div>
            </div>

            <div style="font-size: 9.5pt; margin-top: 10px;">
                Bukittinggi, {{ \Carbon\Carbon::parse($assessment->exam_date)->translatedFormat('d F Y') }}
            </div>
        </div>

        <!-- Tanda Tangan Sesuai Pedoman Lampiran 1 -->
        <table class="footer-signatures">
            <tr>
                <td width="50%" class="text-center">
                    Atas nama {{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}<br>
                    <span class="label-en">On behalf of {{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }}</span>
                    <br><br><br><br><br>
                    <strong><u>{{ $settings['headmaster'] ?? 'Gustian Budiarto, M.Pd' }}</u></strong><br>
                    Kepala Sekolah<br>
                    <span class="label-en">School Principal</span>
                </td>
                <td width="50%" class="text-center">
                    @if(!empty($assessment->externalExaminer->company_name))
                        <strong>{{ $assessment->externalExaminer->company_name }}</strong>
                    @else
                        DUNIA KERJA / ASSOC. PROFESI
                    @endif
                    <br><br><br><br><br><br>
                    <strong><u>{{ $assessment->externalExaminer->name ?? '.........................' }}</u></strong><br>
                    Penguji Eksternal<br>
                    <span class="label-en">External Assessor</span>
                </td>
            </tr>
        </table>

    </div>
</div>

<!-- ================= HALAMAN 2: DAFTAR UNIT KOMPETENSI ================= -->
<div class="page">
    <div class="cert-border">
        
        <div>
            <div class="text-center mb-3">
                <h3 style="font-family: 'Cinzel', serif; margin: 0; color: #111827; font-size: 14pt;">DAFTAR UNIT KOMPETENSI</h3>
                <div class="label-en" style="font-size: 10pt; font-weight: 600; margin-bottom: 15px;">List of Competency Unit</div>
            </div>

            <table class="table-units">
                <thead>
                    <tr>
                        <th width="30%">Kode Unit<br><span style="font-weight: 400; font-style: italic;">Unit Code</span></th>
                        <th width="70%">Judul Unit<br><span style="font-weight: 400; font-style: italic;">Unit Title</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessment->package->units as $unit)
                        <tr>
                            <td class="text-center fw-bold" style="color: #111827;">{{ $unit->unit_code }}</td>
                            <td>{{ $unit->unit_title }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-3 text-muted">Belum ada unit kompetensi yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan Halaman 2 -->
        <table class="footer-signatures">
            <tr>
                <td width="50%">
                    Penguji Internal<br>
                    <span class="label-en">Internal Assessor</span>
                    <br><br><br><br>
                    <strong><u>{{ $assessment->internalExaminer->name ?? '.........................' }}</u></strong><br>
                    <small>({{ $settings['school_name'] ?? 'SMK NEGERI 1 BUKITTINGGI' }})</small>
                </td>
                <td width="50%">
                    Penguji Eksternal<br>
                    <span class="label-en">External Assessor</span>
                    <br><br><br><br>
                    <strong><u>{{ $assessment->externalExaminer->name ?? '.........................' }}</u></strong><br>
                    @if(!empty($assessment->externalExaminer->company_name))
                        <small>({{ $assessment->externalExaminer->company_name }})</small>
                    @endif
                </td>
            </tr>
        </table>

    </div>
</div>

</body>
</html>