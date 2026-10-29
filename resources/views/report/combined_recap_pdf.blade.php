<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Mingguan Terpadu Siswa</title>
    <style>
        @page { size: A4 landscape; margin: 3mm; }
        * { box-sizing: border-box; font-family: "Helvetica", "Arial", sans-serif; }
        body { background: #fff; margin: 0; padding: 0; font-size: 5pt; line-height: 1; }
        
        .page-break { page-break-after: always; }

        .kop-container { width: 100%; margin-bottom: 2px; }
        .header-kop { width: 100%; border-collapse: collapse; }
        .header-kop td { border: none !important; padding: 0; vertical-align: middle; }
        .kop-logo-left, .kop-logo-right { width: 35px; text-align: center; }
        .kop-logo-left img, .kop-logo-right img { max-width: 28px; max-height: 28px; }

        .kop-title { text-align: center; line-height: 1; }
        .kop-title .provinsi { font-size: 6.5pt; font-weight: bold; margin: 0; }
        .kop-title .dinas { font-size: 5.5pt; font-weight: bold; margin: 0; }
        .kop-title .sekolah { font-size: 7.5pt; font-weight: bold; margin: 0; }
        .kop-title .alamat { font-size: 4.5pt; font-style: italic; margin-top: 1px; }

        .kop-line { border-top: 1.5px solid #000; border-bottom: 0.5px solid #000; height: 1px; margin-top: 1px; margin-bottom: 2px; }

        .report-title { text-align: center; margin-bottom: 2px; }
        .report-title h3 { margin: 0; font-size: 8pt; text-transform: uppercase; text-decoration: underline; }
        .report-title p { margin: 1px 0 0 0; font-size: 6pt; font-weight: bold; }

        .meta-table { width: 100%; margin-bottom: 2px; font-weight: bold; font-size: 6pt; }
        .meta-table td { border: none !important; padding: 1px 0; }

        .table-recap { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table-recap th, .table-recap td {
            border: 0.3pt solid #000; padding: 1px 0; text-align: center; vertical-align: middle; font-size: 4.5pt; line-height: 1;
        }
        .table-recap thead th { font-weight: bold; background: #f2f2f2; text-transform: uppercase; }
        .text-left { text-align: left !important; padding-left: 2px !important; }

        .txt-alpa { color: #d9534f; font-weight: bold; }
        .txt-ok { color: #198754; font-weight: bold; }
        .txt-late { color: #e67e22; font-weight: bold; }

        .signature-table { width: 100%; margin-top: 5px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { border: none !important; text-align: center; vertical-align: top; font-size: 6pt; line-height: 1; }
        .sig-space { height: 18px; }
    </style>
</head>
<body>

    <!-- ========================================================================= -->
    <!-- HALAMAN 1: REKAPITULASI PRESENSI PEMBELAJARAN ( KELAS ) -->
    <!-- ========================================================================= -->
    <div class="kop-container">
        <table class="header-kop">
            <tr>
                <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}">@endif</td>
                <td class="kop-title">
                    <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                    <p class="dinas">DINAS PENDIDIKAN</p>
                    <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                    <p class="alamat">{{ $settings['school_address'] }}</p>
                </td>
                <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}">@endif</td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <div class="report-title">
        <h3>LAPORAN MINGGUAN REKAPITULASI PRESENSI PEMBELAJARAN</h3>
        <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $startOfWeek->copy()->addDays(4)->format('d/m/Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td>KELAS : {{ strtoupper($classroom->name ?? '') }}</td>
            <td style="text-align: right;">KONSENTRASI : {{ strtoupper($classroom->major->name ?? '-') }}</td>
        </tr>
    </table>

    <table class="table-recap">
        <thead>
            <tr>
                <th rowspan="3" style="width: 2.2%;">NO</th>
                <th rowspan="3" style="width: 4.8%;">NIS</th>
                <th rowspan="3" style="width: 18%;">NAMA PESERTA DIDIK</th>
                <th rowspan="3" style="width: 2%;">L/P</th>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <th colspan="10">{{ $day }} ({{ $weekDates[$day] }})</th>
                @endforeach
                <th colspan="3" style="width: 6%;">REKAP</th>
            </tr>
            <tr>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    @for($j = 1; $j <= 10; $j++)
                        <th style="font-size: 3.5pt;">{{ $daySubjectMap[$day][$j] ?? '' }}</th>
                    @endfor
                @endforeach
                <th rowspan="2">S</th><th rowspan="2">I</th><th rowspan="2">A</th>
            </tr>
            <tr>
                @for($i = 0; $i < 5; $i++)
                    @for($j = 1; $j <= 10; $j++) <th style="font-size: 3.5pt;">{{ $j }}</th> @endfor
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($students as $idx => $st)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $st->nis ?? '-' }}</td>
                    <td class="text-left"><strong>{{ strtoupper($st->name ?? '') }}</strong></td>
                    <td>{{ substr($st->gender ?? 'L', 0, 1) }}</td>

                    @for($day = 1; $day <= 5; $day++)
                        @for($jam = 1; $jam <= 10; $jam++)
                            @php $val = $learningRecapMap[$st->id][$day][$jam] ?? ''; @endphp
                            <td class="{{ $val == 'X' ? 'txt-alpa' : ($val == 'v' ? 'txt-ok' : '') }}">
                                {{ $val }}
                            </td>
                        @endfor
                    @endfor

                    <td>{{ $learningSummary[$st->id]['sakit'] ?: '' }}</td>
                    <td>{{ $learningSummary[$st->id]['izin'] ?: '' }}</td>
                    <td class="txt-alpa">{{ $learningSummary[$st->id]['alpa'] ?: '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">Mengetahui,<br>Kepala Sekolah<div class="sig-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
            <td style="width: 33%;"><br>Guru BP / BK<div class="sig-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
            <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas<div class="sig-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
        </tr>
    </table>

    <!-- BATAS HALAMAN 1 DENGAN HALAMAN 2 -->
    <div class="page-break"></div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 2: REKAPITULASI PRESENSI GERBANG ( MASUK / PULANG ) -->
    <!-- ========================================================================= -->
    <div class="kop-container">
        <table class="header-kop">
            <tr>
                <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}">@endif</td>
                <td class="kop-title">
                    <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                    <p class="dinas">DINAS PENDIDIKAN</p>
                    <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                    <p class="alamat">{{ $settings['school_address'] }}</p>
                </td>
                <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}">@endif</td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <div class="report-title">
        <h3>REKAPITULASI PRESENSI GERBANG SISWA</h3>
        <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $startOfWeek->copy()->addDays(4)->format('d/m/Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td>KELAS : {{ strtoupper($classroom->name ?? '') }}</td>
            <td style="text-align: right;">KONSENTRASI : {{ strtoupper($classroom->major->name ?? '-') }}</td>
        </tr>
    </table>

    <table class="table-recap">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 6%;">NIS</th>
                <th rowspan="2" style="width: 28%;">NAMA PESERTA DIDIK</th>
                <th rowspan="2" style="width: 3%;">L/P</th>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <th colspan="2">{{ $day }} ({{ $weekDates[$day] }})</th>
                @endforeach
                <th colspan="5" style="width: 12%;">REKAP</th>
            </tr>
            <tr>
                @for($i = 0; $i < 5; $i++)
                    <th style="width: 4.8%;">MASUK</th><th style="width: 4.8%;">PULANG</th>
                @endfor
                <th style="width: 2.4%;">H</th><th style="width: 2.4%;">T</th><th style="width: 2.4%;">S</th><th style="width: 2.4%;">I</th><th style="width: 2.4%;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $idx => $st)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $st->nis ?? '-' }}</td>
                    <td class="text-left"><strong>{{ strtoupper($st->name ?? '') }}</strong></td>
                    <td>{{ substr($st->gender ?? 'L', 0, 1) }}</td>

                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                        @php
                            $dDateRaw = \Carbon\Carbon::parse($startOfWeek)->addDays(match($day) { 'Senin' => 0, 'Selasa' => 1, 'Rabu' => 2, 'Kamis' => 3, 'Jumat' => 4 })->format('Y-m-d');
                            $rec = $gateMap[$st->id][$dDateRaw] ?? null;
                        @endphp
                        <td>
                            @if($rec)
                                @if($rec['status'] == 'alpa') <span class="txt-alpa">X</span>
                                @elseif($rec['status'] == 'terlambat') <span class="txt-late">{{ $rec['arrival'] }}</span>
                                @else {{ $rec['arrival'] }} @endif
                            @else - @endif
                        </td>
                        <td>{{ $rec ? $rec['departure'] : '-' }}</td>
                    @endforeach

                    <td>{{ $gateSummary[$st->id]['hadir'] ?: '' }}</td>
                    <td class="txt-late">{{ $gateSummary[$st->id]['terlambat'] ?: '' }}</td>
                    <td>{{ $gateSummary[$st->id]['sakit'] ?: '' }}</td>
                    <td>{{ $gateSummary[$st->id]['izin'] ?: '' }}</td>
                    <td class="txt-alpa">{{ $gateSummary[$st->id]['alpa'] ?: '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">Mengetahui,<br>Kepala Sekolah<div class="sig-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
            <td style="width: 33%;"><br>Guru BP / BK<div class="sig-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
            <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas<div class="sig-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
        </tr>
    </table>

    <!-- BATAS HALAMAN 2 DENGAN HALAMAN 3 -->
    <div class="page-break"></div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 3: REKAPITULASI PRESENSI SHOLAT ( 7 HARI SENIN - MINGGU ) -->
    <!-- ========================================================================= -->
    <div class="kop-container">
        <table class="header-kop">
            <tr>
                <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}">@endif</td>
                <td class="kop-title">
                    <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                    <p class="dinas">DINAS PENDIDIKAN</p>
                    <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                    <p class="alamat">{{ $settings['school_address'] }}</p>
                </td>
                <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}">@endif</td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <div class="report-title">
        <h3>REKAPITULASI PRESENSI SHOLAT SISWA</h3>
        <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $endOfWeek->format('d/m/Y') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td>KELAS : {{ strtoupper($classroom->name ?? '') }}</td>
            <td style="text-align: right;">KONSENTRASI : {{ strtoupper($classroom->major->name ?? '-') }}</td>
        </tr>
    </table>

    <table class="table-recap">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2.2%;">NO</th>
                <th rowspan="2" style="width: 4.8%;">NIS</th>
                <th rowspan="2" style="width: 18%;">NAMA PESERTA DIDIK</th>
                <th rowspan="2" style="width: 2%;">L/P</th>

                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                    <th colspan="5">{{ $day }} ({{ $weekDates[$day] }})</th>
                @endforeach

                <th colspan="2" style="width: 5%;">TOTAL</th>
            </tr>
            <tr>
                @for($i = 0; $i < 7; $i++)
                    <th style="width: 2.02%; font-size: 3.8pt;">SBH</th>
                    <th style="width: 2.02%; font-size: 3.8pt;">DZH</th>
                    <th style="width: 2.02%; font-size: 3.8pt;">ASH</th>
                    <th style="width: 2.02%; font-size: 3.8pt;">MGB</th>
                    <th style="width: 2.02%; font-size: 3.8pt;">ISY</th>
                @endfor
                <th style="width: 2.5%;">HADIR</th><th style="width: 2.5%;">ALPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $idx => $st)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $st->nis ?? '-' }}</td>
                    <td class="text-left"><strong>{{ strtoupper($st->name ?? '') }}</strong></td>
                    <td>{{ substr($st->gender ?? 'L', 0, 1) }}</td>

                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                        @php
                            $dDateRaw = \Carbon\Carbon::parse($startOfWeek)->addDays(match($day) {
                                'Senin' => 0, 'Selasa' => 1, 'Rabu' => 2, 'Kamis' => 3, 'Jumat' => 4, 'Sabtu' => 5, 'Minggu' => 6
                            })->format('Y-m-d');
                        @endphp

                        @foreach(['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $pr)
                            @php $rec = $prayerMap[$st->id][$dDateRaw][$pr] ?? null; @endphp
                            <td>
                                @if($rec)
                                    @if($rec['status'] == 'alpa') <span class="txt-alpa">X</span>
                                    @else <span class="txt-ok">{{ $rec['time'] }}</span> @endif
                                @else - @endif
                            </td>
                        @endforeach
                    @endforeach

                    <td class="txt-ok">{{ $prayerSummary[$st->id]['hadir'] ?: '' }}</td>
                    <td class="txt-alpa">{{ $prayerSummary[$st->id]['alpa'] ?: '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">Mengetahui,<br>Kepala Sekolah<div class="sig-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
            <td style="width: 33%;"><br>Guru BP / BK<div class="sig-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
            <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas<div class="sig-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
        </tr>
    </table>

</body>
</html>