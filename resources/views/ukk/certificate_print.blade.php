<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat_UKK_{{ Str::slug($assessment->student->name, '_') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.2;
        }
        .border-container {
            border: 3px double #000;
            padding: 15px;
            height: 95%;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .title { font-size: 16pt; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { font-size: 12pt; margin-bottom: 15px; }
        .table-units {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-units th, .table-units td {
            border: 1px solid #000;
            padding: 5px 7px;
            font-size: 8.5pt;
        }
        .table-units th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
        }
        .footer-table td {
            text-align: center;
            vertical-align: bottom;
            height: 65px;
        }
    </style>
</head>
<body onload="window.print()">

<div class="border-container">
    <div class="text-center">
        <div class="title fw-bold">SERTIFIKAT UJI KOMPETENSI KEAHLEAN</div>
        <div class="subtitle">Nomor: {{ $assessment->id }}/UKK/{{ $assessment->student->classroom->name ?? 'TITL' }}/{{ date('Y') }}</div>
        
        <p style="margin: 3px 0;">Dengan ini menyatakan bahwa:</p>
        <h3 style="margin: 4px 0; text-transform: uppercase; text-decoration: underline;">{{ $assessment->student->name }}</h3>
        <p style="margin: 2px 0;">NIS / NISN: {{ $assessment->student->nis }} &nbsp;|&nbsp; Kelas: {{ $assessment->student->classroom->name ?? '-' }}</p>
        
        <p style="margin: 5px 0;">Dinyatakan <strong style="font-size: 11pt; text-transform: uppercase;"><u>{{ $assessment->status }}</u></strong> pada Pelaksanaan Uji Kompetensi Keahlian Paket:</p>
        <h4 style="margin: 2px 0; text-transform: uppercase;">{{ $assessment->package->package_name }}</h4>
    </div>

    <!-- TABEL KODE UNIT, JUDUL UNIT, DAN ELEMEN KOMPETENSI -->
    <table class="table-units">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="16%">Kode Unit</th>
                <th width="35%">Judul Unit Kompetensi</th>
                <th width="35%">Elemen Kompetensi</th>
                <th width="10%">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessment->package->units as $idx => $unit)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold text-center">{{ $unit->unit_code }}</td>
                    <td>{{ $unit->unit_title }}</td>
                    <td>{{ $unit->element_name }}</td>
                    <td class="text-center fw-bold">{{ $assessment->status === 'Kompeten' ? 'K' : 'BK' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-2">Unit kompetensi belum dikonfigurasi untuk paket ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN MULTI-ROLE -->
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
                NIP. {{ $settings['nip'] }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>