<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nilai Ujian Jurnal PKL - {{ $internship->student->name ?? '' }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm 1.5cm 2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .fw-bold {
            font-weight: bold;
        }
        .title-header {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .main-table th {
            font-weight: bold;
            text-transform: uppercase;
        }
        .sub-header {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>
<body>

    <!-- Judul Dokumen -->
    <div class="title-header">
        NILAI PRESENTASI LAPORAN ( UJIAN JURNAL ) PKL {{ $school['name'] ?? 'SMKN 1 BUKITTINGGI' }}<br>
        TAHUN PELAJARAN 2026/2027
    </div>

    <!-- Info Program & Penguji -->
    <table class="meta-table">
        <tr>
            <td width="22%">Program Keahlian</td>
            <td width="2%">:</td>
            <td>{{ $internship->student->classroom->major->name ?? '......................................................................' }}</td>
        </tr>
        <tr>
            <td>Kompetensi Keahlian</td>
            <td>:</td>
            <td>{{ $internship->student->classroom->name ?? '......................................................................' }}</td>
        </tr>
        <tr>
            <td>Nama Guru Penguji</td>
            <td>:</td>
            <td>{{ $teacher->name ?? '......................................................................' }}</td>
        </tr>
    </table>

    <!-- Tabel Nilai -->
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%" class="text-center" rowspan="2">NO.</th>
                <th class="text-center" rowspan="2">ASPEK PENILAIAN</th>
                <th width="12%" class="text-center" rowspan="2">SKOR MAKS</th>
                <th width="45%" class="text-center">SKOR PEROLEHAN</th>
            </tr>
            <tr>
                <th style="font-weight: normal; font-size: 10pt;">
                    <strong>NAMA SISWA :</strong> {{ $internship->student->name }}<br>
                    <strong>NIS :</strong> {{ $internship->student->nis }}
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- Section 1: Laporan -->
            <tr class="sub-header">
                <td class="text-center">1</td>
                <td>Laporan</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>1.1. Materi / Isi</td>
                <td class="text-center">35</td>
                <td class="text-center">{{ number_format($internship->exam->score_materi ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>1.2. Gambar</td>
                <td class="text-center">10</td>
                <td class="text-center">{{ number_format($internship->exam->score_gambar ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>1.3. Tata Bahasa / Sistematika Laporan</td>
                <td class="text-center">10</td>
                <td class="text-center">{{ number_format($internship->exam->score_sistematika ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>1.4. Tampilan Laporan</td>
                <td class="text-center">5</td>
                <td class="text-center">{{ number_format($internship->exam->score_tampilan ?? 0, 0) }}</td>
            </tr>
            <tr class="fw-bold">
                <td></td>
                <td class="text-right">Sub Total Skor</td>
                <td class="text-center">60</td>
                <td class="text-center">{{ number_format($internship->exam->subtotal_laporan ?? 0, 0) }}</td>
            </tr>

            <!-- Section 2: Presentasi / Wawancara Laporan -->
            <tr class="sub-header">
                <td class="text-center">2</td>
                <td>Presentasi / Wawancara laporan</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>2.1. Kerapian berpakaian</td>
                <td class="text-center">5</td>
                <td class="text-center">{{ number_format($internship->exam->score_pakaian ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>2.2. Bahasa Penyampaian</td>
                <td class="text-center">10</td>
                <td class="text-center">{{ number_format($internship->exam->score_bahasa ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>2.3. Penguasaan Materi</td>
                <td class="text-center">15</td>
                <td class="text-center">{{ number_format($internship->exam->score_penguasaan ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>2.4. Ketepatan Jawaban terhadap pertanyaan</td>
                <td class="text-center">10</td>
                <td class="text-center">{{ number_format($internship->exam->score_jawaban ?? 0, 0) }}</td>
            </tr>
            <tr class="fw-bold">
                <td></td>
                <td class="text-right">Sub Total Skor</td>
                <td class="text-center">40</td>
                <td class="text-center">{{ number_format($internship->exam->subtotal_presentasi ?? 0, 0) }}</td>
            </tr>

            <!-- Total Akhir -->
            <tr class="fw-bold" style="background-color: #f8f9fa;">
                <td></td>
                <td class="text-right">Jumlah Total</td>
                <td class="text-center">100</td>
                <td class="text-center" style="font-size: 12pt;">{{ number_format($internship->exam->total_score ?? 0, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td width="50%">
                Mengetahui :<br>
                Ketua PKL<br><br><br><br><br>
                <strong><u>{{ $school['headmaster_name'] ?? 'FITHRI NOVILIA, S.Pd' }}</u></strong><br>
                NIP. {{ $school['headmaster_nip'] ?? '19721102 199802 2 003' }}
            </td>
            <td width="50%" class="text-right">
                {{ $school['sign_city'] ?? 'Bukittinggi' }}, {{ $internship->exam->exam_date ? $internship->exam->exam_date->isoFormat('D MMMM Y') : date('d F Y') }}<br>
                Guru Penguji<br><br><br><br><br>
                <strong><u>{{ $teacher->name }}</u></strong><br>
                NIP. {{ $teacher->nip ?? '.........................................' }}
            </td>
        </tr>
    </table>

</body>
</html>