<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi Sholat Mingguan</title>
    <style>
        @page { size: A4 landscape; margin: 4mm; }
        * { box-sizing: border-box; font-family: "Helvetica", "Arial", sans-serif; }
        body { background: #fff; margin: 0; padding: 0; font-size: 5.5pt; line-height: 1; }
        
        .kop-container { width: 100%; margin-bottom: 3px; }
        .header-kop { width: 100%; border-collapse: collapse; }
        .header-kop td { border: none !important; padding: 0; vertical-align: middle; }
        .kop-logo-left, .kop-logo-right { width: 40px; text-align: center; }
        .kop-logo-left img, .kop-logo-right img { max-width: 32px; max-height: 32px; }

        .kop-title { text-align: center; line-height: 1; }
        .kop-title .provinsi { font-size: 7pt; font-weight: bold; margin: 0; }
        .kop-title .dinas { font-size: 6pt; font-weight: bold; margin: 0; }
        .kop-title .sekolah { font-size: 8pt; font-weight: bold; margin: 0; }
        .kop-title .alamat { font-size: 5pt; font-style: italic; margin-top: 1px; }

        .kop-line { border-top: 1.5px solid #000; border-bottom: 0.5px solid #000; height: 1px; margin-top: 1px; margin-bottom: 3px; }

        .report-title { text-align: center; margin-bottom: 3px; }
        .report-title h3 { margin: 0; font-size: 8.5pt; text-transform: uppercase; text-decoration: underline; }
        .report-title p { margin: 2px 0 0 0; font-size: 6.5pt; font-weight: bold; }

        .meta-table { width: 100%; margin-bottom: 3px; font-weight: bold; font-size: 6.5pt; }
        .meta-table td { border: none !important; padding: 1px 0; }

        .table-recap { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table-recap th, .table-recap td {
            border: 0.3pt solid #000;
            padding: 1px 0;
            text-align: center;
            vertical-align: middle;
            font-size: 5pt;
            line-height: 1;
        }
        .table-recap thead th { font-weight: bold; background: #f2f2f2; text-transform: uppercase; }
        .text-left { text-align: left !important; padding-left: 3px !important; }

        /* PERSENTASE PROPORSIONAL DOMPDF (31 KOLOM) */
        .col-no   { width: 2.5%; }
        .col-nis  { width: 5.5%; }
        .col-nama { width: 22%; }
        .col-lp   { width: 2.5%; }
        .col-pr   { width: 2.5%; font-size: 4.2pt; }
        .col-stat { width: 2.5%; font-size: 4.8pt; }

        .txt-alpa { color: #d9534f; font-weight: bold; }
        .txt-ok { color: #198754; font-weight: bold; }

        .signature-table { width: 100%; margin-top: 6px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { border: none !important; text-align: center; vertical-align: top; font-size: 6.5pt; line-height: 1.1; }
        .sig-space { height: 20px; }
    </style>
</head>
<body>

    <div class="kop-container">
        <table class="header-kop">
            <tr>
                <td class="kop-logo-left">
                    @if(!empty($settings['logo_left']))
                        <img src="{{ $settings['logo_left'] }}" alt="Logo Kiri">
                    @endif
                </td>
                <td class="kop-title">
                    <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                    <p class="dinas">DINAS PENDIDIKAN</p>
                    <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                    <p class="alamat">{{ $settings['school_address'] }}</p>
                </td>
                <td class="kop-logo-right">
                    @if(!empty($settings['logo_right']))
                        <img src="{{ $settings['logo_right'] }}" alt="Logo Kanan">
                    @endif
                </td>
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
            <td style="width: 100px;">KELAS</td>
            <td style="width: 10px;">:</td>
            <td>{{ strtoupper($classroom->name ?? '') }}</td>
            <td style="text-align: right;">KONSENTRASI : {{ strtoupper($classroom->major->name ?? '-') }}</td>
        </tr>
    </table>

    <table class="table-recap">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-nis">NIS</th>
                <th rowspan="2" class="col-nama">NAMA PESERTA DIDIK</th>
                <th rowspan="2" class="col-lp">L/P</th>

                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <th colspan="5">
                        {{ $day }}<br>
                        <span style="font-size: 4pt; font-weight: normal;">{{ $weekDates[$day] ?? '' }}</span>
                    </th>
                @endforeach

                <th colspan="2">TOTAL</th>
            </tr>
            <tr>
                @for($i = 0; $i < 5; $i++)
                    <th class="col-pr">SBH</th>
                    <th class="col-pr">DZH</th>
                    <th class="col-pr">ASH</th>
                    <th class="col-pr">MGB</th>
                    <th class="col-pr">ISY</th>
                @endfor
                <th class="col-stat">HADIR</th>
                <th class="col-stat">ALPA</th>
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
                            $dDateRaw = \Carbon\Carbon::parse($startOfWeek)->addDays(match($day) {
                                'Senin' => 0, 'Selasa' => 1, 'Rabu' => 2, 'Kamis' => 3, 'Jumat' => 4
                            })->format('Y-m-d');
                        @endphp

                        @foreach(['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $pr)
                            @php
                                $rec = $prayerMap[$st->id][$dDateRaw][$pr] ?? null;
                            @endphp
                            <td>
                                @if($rec)
                                    @if($rec['status'] == 'alpa')
                                        <span class="txt-alpa">X</span>
                                    @else
                                        <span class="txt-ok">{{ $rec['time'] }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    @endforeach

                    <td class="txt-ok">{{ $summary[$st->id]['hadir'] ?: '' }}</td>
                    <td class="txt-alpa">{{ $summary[$st->id]['alpa'] ?: '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="31" style="padding: 10px;">Tidak ada data siswa terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                Mengetahui,<br>Kepala Sekolah
                <div class="sig-space"></div>
                <strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>
                NIP. {{ $settings['kepsek_nip'] }}
            </td>
            <td style="width: 33%;">
                <br>Guru BP / BK
                <div class="sig-space"></div>
                <strong><u>{{ $officers['bk_nama'] }}</u></strong><br>
                NIP. {{ $officers['bk_nip'] }}
            </td>
            <td style="width: 33%;">
                Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas
                <div class="sig-space"></div>
                <strong><u>{{ $officers['walas_nama'] }}</u></strong><br>
                NIP. {{ $officers['walas_nip'] }}
            </td>
        </tr>
    </table>

</body>
</html>