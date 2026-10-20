<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat_UKK_{{ Str::slug($assessment->student->name, '_') }}</title>
    <!-- Font Google untuk Nuansa Resmi Sertifikat Pedoman UKK 2026 -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Montserrat:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <style>
    @page {
        size: A4 portrait;
        margin: 0; /* Menghilangkan margin bawaan printer */
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
        font-size: 9pt;
        color: #1f2937;
        line-height: 1.25;
        background-color: #fff;
    }
    .page {
        width: 210mm;
        height: 296mm; /* Disesuaikan agar presisi dengan tinggi A4 */
        padding: 10mm 15mm;
        box-sizing: border-box;
        page-break-after: always;
        page-break-inside: avoid;
        position: relative;
        overflow: hidden; /* Mencegah elemen meluber ke halaman baru */
    }
    .page:last-child { 
        page-break-after: avoid; 
    }

    .text-center { text-align: center; }
    .fw-bold { font-weight: 700; }
    .text-italic { font-style: italic; color: #4b5563; }

    /* Logo Tut Wuri Handayani */
    .tutwuri-logo {
        height: 65px;
        width: auto;
        object-fit: contain;
        margin-bottom: 8px;
    }

    /* Judul Sertifikat */
    .cert-title-id {
        font-family: 'Cinzel', serif;
        font-size: 15pt;
        font-weight: 800;
        color: #111827;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 1px;
    }
    .cert-title-en {
        font-family: 'Montserrat', sans-serif;
        font-size: 12pt;
        font-weight: 700;
        color: #2563eb;
        font-style: italic;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }
    .cert-number {
        font-size: 8.5pt;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
    }

    /* Seksi Teks Bilingual */
    .bilingual-block {
        margin-bottom: 10px;
    }
    .label-id {
        font-size: 8.5pt;
        color: #1f2937;
    }
    .label-en {
        font-size: 8pt;
        font-style: italic;
        color: #6b7280;
    }

    /* Nama Siswa */
    .student-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 15pt;
        font-weight: 700;
        color: #111827;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
        margin-bottom: 2px;
    }
    .student-nisn {
        font-size: 9pt;
        font-weight: 600;
        color: #374151;
    }

    /* Konsentrasi & Judul Penugasan */
    .major-title {
        font-size: 11pt;
        font-weight: 700;
        color: #111827;
        margin-top: 2px;
    }
    .assignment-title {
        font-size: 10.5pt;
        font-weight: 700;
        color: #111827;
        margin-top: 2px;
    }
    .predicate-title {
        font-size: 11pt;
        font-weight: 700;
        color: #111827;
        margin-top: 2px;
    }

    /* Masa Berlaku */
    .validity-block {
        margin-top: 12px;
        margin-bottom: 12px;
        font-size: 8pt;
    }

    /* Tabel Tanda Tangan */
    .footer-signatures {
        width: 100%;
        margin-top: 15px;
        border-collapse: collapse;
    }
    .footer-signatures td {
        vertical-align: top;
        font-size: 8.5pt;
    }

    /* Tabel Unit Kompetensi (Halaman 2) */
    .table-units {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 15px;
    }
    .table-units th, .table-units td {
        border: 1px solid #374151;
        padding: 5px 7px;
        font-size: 8pt;
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
            height: 296mm; 
            padding: 10mm 15mm; 
            page-break-after: always;
            overflow: hidden;
        }
    }
</style>
</head>
<body onload="window.print()">

<!-- ================= HALAMAN 1: SERTIFIKAT UTAMA ================= -->
<div class="page">
    <div class="text-center">
        <!-- Logo Tut Wuri Handayani sesuai Pedoman Resmi -->
        <!-- Logo Tut Wuri Handayani dari folder public/upload -->
        <img src="{{ asset('upload/logo_tutwuri.png') }}" 
            class="tutwuri-logo" 
            alt="Tut Wuri Handayani" 
            onerror="this.src='{{ asset('images/logo_tutwuri.png') }}'; this.onerror=null;">
        
        <div class="cert-title-id">SERTIFIKAT KOMPETENSI</div>
        <div class="cert-title-en">COMPETENCY CERTIFICATE</div>
        <div class="cert-number">Nomor : {{ $settings['school_code'] ?? 'SMKN1BKT' }}-{{ $assessment->package->major_code ?? 'TITL' }}/UKK-{{ date('Y') }}/{{ str_pad($assessment->id, 3, '0', STR_PAD_LEFT) }}</div>

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
            <div class="label-id">pada Kompetensi /Konsentrasi Keahlian</div>
            <div class="label-en">on Competency of</div>
            <div class="major-title">{{ $assessment->package->major_name }}</div>
        </div>

        <div class="bilingual-block">
            <div class="label-id">pada Judul Penugasan</div>
            <div class="label-en">on Assignment</div>
            <div class="assignment-title">{{ $assessment->package->assignment_title ?? $assessment->package->package_name }}</div>
            <div class="label-id" style="margin-top: 4px;">dengan predikat</div>
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

        <div style="margin-top: 10px; font-size: 9pt;">
            Bukittinggi, {{ \Carbon\Carbon::parse($assessment->exam_date)->translatedFormat('d F Y') }}
        </div>
    </div>

    <!-- Tanda Tangan Sesuai Pedoman Lampiran 1 -->
    <table class="footer-signatures">
        <tr>
            <td width="50%" class="text-center">
                Atas nama {{ $settings['school_name'] ?? 'SMKN 1 BUKITTINGGI' }}<br>
                <span class="label-en">On behalf of {{ $settings['school_name'] ?? 'SMKN 1 BUKITTINGGI' }}</span>
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

<!-- ================= HALAMAN 2: DAFTAR UNIT KOMPETENSI ================= -->
<div class="page">
    <div class="text-center mb-3">
        <h3 style="font-family: 'Cinzel', serif; margin: 0; color: #111827; font-size: 13pt;">DAFTAR UNIT KOMPETENSI</h3>
        <div class="label-en" style="font-size: 10pt; font-weight: 600; margin-bottom: 10px;">List of Competency Unit</div>
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

    <!-- Tanda Tangan Sesuai Pedoman Lampiran 1 Halaman Belakang -->
    <table class="footer-signatures" style="margin-top: 40px;">
        <tr>
            <td width="50%">
                Penguji Internal<br>
                <span class="label-en">Internal Assessor</span>
                <br><br><br><br>
                <strong><u>{{ $assessment->internalExaminer->name ?? '.........................' }}</u></strong> 
                <small>({{ $settings['school_name'] ?? 'SMKN 1 BUKITTINGGI' }})</small>
            </td>
            <td width="50%">
                Penguji Eksternal<br>
                <span class="label-en">External Assessor</span>
                <br><br><br><br>
                <strong><u>{{ $assessment->externalExaminer->name ?? '.........................' }}</u></strong> 
                @if(!empty($assessment->externalExaminer->company_name))
                    <small>({{ $assessment->externalExaminer->company_name }})</small>
                @endif
            </td>
        </tr>
    </table>
</div>

</body>
</html>