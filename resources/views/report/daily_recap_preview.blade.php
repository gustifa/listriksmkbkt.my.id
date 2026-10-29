<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Rekap Presensi Gerbang Harian/Mingguan</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: "Arial", sans-serif;
        }

        html, body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 4px;
            font-size: 8px;
        }

        .container-print {
            background: #fff;
            padding: 8px 12px;
            margin: 0 auto;
            max-width: 1050px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* 1. KOP SURAT */
        .kop-container {
            width: 100%;
            margin-bottom: 4px;
        }

        .header-kop {
            width: 100%;
            border-collapse: collapse;
        }

        .header-kop td {
            border: none !important;
            padding: 0;
            vertical-align: middle;
        }

        .kop-title {
            text-align: center;
            line-height: 1.1;
        }

        .kop-title .provinsi { font-size: 10px; font-weight: bold; margin: 0; }
        .kop-title .dinas { font-size: 9px; font-weight: bold; margin: 0; }
        .kop-title .sekolah { font-size: 11px; font-weight: bold; margin: 0; }
        .kop-title .alamat { font-size: 7.5px; font-style: italic; margin-top: 1px; }

        .kop-line {
            border-top: 2px solid #000;
            border-bottom: 0.5px solid #000;
            height: 2px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        /* 2. JUDUL DAFTAR HADIR & META INFO */
        .report-title {
            text-align: center;
            margin-bottom: 4px;
        }

        .report-title h4 {
            margin: 0;
            font-size: 10px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .report-title p {
            margin: 2px 0 0 0;
            font-size: 8px;
            font-weight: bold;
        }

        .meta-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .meta-info td {
            border: none !important;
            padding: 1px 0;
            vertical-align: top;
        }

        /* 3. TABEL REKAP UTAMA */
        .table-recap {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-recap th, 
        .table-recap td {
            border: 0.5px solid #000000;
            padding: 2px 0;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            line-height: 1;
        }

        .table-recap thead th {
            font-weight: bold;
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        .text-left {
            text-align: left !important;
            padding-left: 4px !important;
        }

        .col-no { width: 22px; }
        .col-nis { width: 45px; }
        .col-nama { width: 170px; }
        .col-lp { width: 20px; }
        .col-time { width: 35px; font-size: 7px; }
        .col-stat { width: 20px; font-size: 7.5px; }

        .txt-alpa { color: #d9534f; font-weight: bold; }
        .txt-late { color: #e67e22; font-weight: bold; }

        /* 4. AREA PENGESAHAN TANDA TANGAN */
        .signature-section {
            width: 100%;
            margin-top: 8px;
            border: none;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-section td {
            border: none !important;
            text-align: center;
            vertical-align: top;
            font-size: 8px;
            padding: 0;
            line-height: 1.1;
        }

        .signature-space {
            height: 25px;
        }

        /* OPSI CETAK NATIVE BROWSER */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .container-print { box-shadow: none; padding: 0; margin: 0; max-width: 100%; width: 100%; }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 4mm; }
            html, body { height: 100%; overflow: visible !important; }
            .table-recap th, .table-recap td { border: 0.5pt solid #000000; }
        }
    </style>
</head>
<body>

    <!-- HEADER KONTROL (TIDAK DICETAK) -->
    <div class="no-print" style="margin: 10px auto; max-width: 1050px; display: flex; justify-content: space-between; align-items: center; background: #e9ecef; padding: 8px 12px; border-radius: 6px;">
        <div>
            <h4 style="margin: 0; color: #0d6efd;">Pratinjau Presensi Gerbang - Kelas {{ $classroom->name ?? '' }}</h4>
            <small style="color: #6c757d;">Periode: {{ isset($startOfWeek) ? $startOfWeek->format('d/m/Y') : '-' }} s/d {{ isset($endOfWeek) ? $endOfWeek->format('d/m/Y') : '-' }}</small>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('daily.recap.index') }}" style="padding: 6px 14px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                ← Kembali Filter
            </a>
            <a href="{{ route('daily.recap.pdf', request()->all()) }}" style="padding: 6px 18px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                📥 Download PDF (DomPDF)
            </a>
            <button onclick="window.print()" style="padding: 6px 14px; background: #198754; color: #fff; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 10px;">
                🖨️ Cetak Native
            </button>
        </div>
    </div>

    <div class="container-print">

        <!-- 1. KOP SURAT RESMI -->
        <div class="kop-container">
            <table class="header-kop">
                <tr>
                    <td class="kop-title">
                        <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                        <p class="dinas">DINAS PENDIDIKAN</p>
                        <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                        <p class="alamat">{{ $settings['school_address'] }}</p>
                    </td>
                </tr>
            </table>
            <div class="kop-line"></div>
        </div>

        <!-- 2. JUDUL LAPORAN -->
        <div class="report-title">
            <h4>REKAPITULASI PRESENSI GERBANG SISWA</h4>
            <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $endOfWeek->format('d/m/Y') }}</p>
        </div>

        <!-- 3. INFORMASI DETAIL KELAS -->
        <table class="meta-info">
            <tr>
                <td style="width: 100px;">KELAS</td>
                <td style="width: 10px;">:</td>
                <td>{{ strtoupper($classroom->name ?? '') }}</td>
                <td style="text-align: right;">
                    KONSENTRASI KEAHLIAN : <span>{{ strtoupper($classroom->major->name ?? '-') }}</span>
                </td>
            </tr>
        </table>

        <!-- 4. TABEL REKAP UTAMA -->
        <table class="table-recap">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">NO</th>
                    <th rowspan="2" class="col-nis">NIS</th>
                    <th rowspan="2" class="col-nama">NAMA PESERTA DIDIK</th>
                    <th rowspan="2" class="col-lp">L/P</th>

                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                        <th colspan="2">
                            {{ $day }}<br>
                            <span style="font-size: 6.5px; font-weight: normal;">{{ \Carbon\Carbon::parse($weekDates[$day])->format('d/m/Y') }}</span>
                        </th>
                    @endforeach

                    <th colspan="5">REKAPITULASI</th>
                </tr>
                <tr>
                    @for($i = 0; $i < 5; $i++)
                        <th class="col-time">MASUK</th>
                        <th class="col-time">PULANG</th>
                    @endfor
                    <th class="col-stat">H</th>
                    <th class="col-stat">T</th>
                    <th class="col-stat">S</th>
                    <th class="col-stat">I</th>
                    <th class="col-stat">A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $st)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $st->nis ?? '-' }}</td>
                        <td class="text-left"><strong>{{ strtoupper($st->name ?? '') }}</strong></td>
                        <td>{{ substr($st->gender ?? 'L', 0, 1) }}</td>

                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                            @php
                                $dDate = $weekDates[$day];
                                $rec = $attendanceMap[$st->id][$dDate] ?? null;
                            @endphp
                            <td>
                                @if($rec)
                                    <span class="{{ $rec['status'] == 'terlambat' ? 'txt-late' : '' }}">{{ $rec['arrival'] }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $rec ? $rec['departure'] : '-' }}</td>
                        @endforeach

                        <td>{{ $summary[$st->id]['hadir'] ?: '' }}</td>
                        <td class="txt-late">{{ $summary[$st->id]['terlambat'] ?: '' }}</td>
                        <td>{{ $summary[$st->id]['sakit'] ?: '' }}</td>
                        <td>{{ $summary[$st->id]['izin'] ?: '' }}</td>
                        <td class="txt-alpa">{{ $summary[$st->id]['alpa'] ?: '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="19" style="padding: 10px;">Tidak ada data siswa terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- 5. AREA PENGESAHAN TANDA TANGAN -->
        <table class="signature-section">
            <tr>
                <td style="width: 33%;">
                    Mengetahui :<br>
                    Kepala Sekolah,
                    <div class="signature-space"></div>
                    <strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>
                    NIP. {{ $settings['kepsek_nip'] }}
                </td>
                <td style="width: 33%;">
                    <br>
                    Guru BP / BK,
                    <div class="signature-space"></div>
                    <strong><u>{{ $officers['bk_nama'] }}</u></strong><br>
                    NIP. {{ $officers['bk_nip'] }}
                </td>
                <td style="width: 33%;">
                    Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Wali Kelas,
                    <div class="signature-space"></div>
                    <strong><u>{{ $officers['walas_nama'] }}</u></strong><br>
                    NIP. {{ $officers['walas_nip'] }}
                </td>
            </tr>
        </table>

    </div>

</body>
</html>