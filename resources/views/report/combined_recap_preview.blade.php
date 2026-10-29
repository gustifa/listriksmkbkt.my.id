<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratinjau Laporan Rekapitulasi Mingguan Terpadu (3 Halaman)</title>
    <style>
        * { box-sizing: border-box; font-family: "Arial", sans-serif; }
        html, body { background-color: #525659; margin: 0; padding: 10px 0; font-size: 8px; }
        
        /* CONTAINER ELEMEN LEMBARAN A4 LANDSCAPE */
        .page-sheet { 
            background: #fff; 
            padding: 10px 14px; 
            margin: 0 auto 20px auto; 
            max-width: 1200px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); 
            border-radius: 2px;
        }

        .kop-container { width: 100%; margin-bottom: 4px; }
        .header-kop { width: 100%; border-collapse: collapse; }
        .header-kop td { border: none !important; padding: 0; vertical-align: middle; }
        .kop-logo-left, .kop-logo-right { width: 50px; text-align: center; }
        .kop-logo-left img, .kop-logo-right img { max-width: 45px; max-height: 45px; object-fit: contain; }
        
        .kop-title { text-align: center; line-height: 1.1; }
        .kop-title .provinsi { font-size: 10px; font-weight: bold; margin: 0; }
        .kop-title .dinas { font-size: 9px; font-weight: bold; margin: 0; }
        .kop-title .sekolah { font-size: 11px; font-weight: bold; margin: 0; }
        .kop-title .alamat { font-size: 7.5px; font-style: italic; margin-top: 1px; }

        .kop-line { border-top: 2px solid #000; border-bottom: 0.5px solid #000; height: 2px; margin-top: 2px; margin-bottom: 4px; }

        .report-title { text-align: center; margin-bottom: 4px; }
        .report-title h4 { margin: 0; font-size: 10px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .report-title p { margin: 2px 0 0 0; font-size: 8px; font-weight: bold; }

        .meta-info { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8px; font-weight: bold; }
        .meta-info td { border: none !important; padding: 1px 0; vertical-align: top; }

        .table-recap { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table-recap th, .table-recap td {
            border: 0.5px solid #000000; padding: 2px 1px; text-align: center; vertical-align: middle; font-size: 7px; word-wrap: break-word; line-height: 1.1;
        }
        .table-recap thead th { font-weight: bold; background-color: #f2f2f2; text-transform: uppercase; }
        .text-left { text-align: left !important; padding-left: 4px !important; }

        .txt-alpa { color: #d9534f; font-weight: bold; }
        .txt-ok { color: #198754; font-weight: bold; }
        .txt-late { color: #e67e22; font-weight: bold; }

        .signature-section { width: 100%; margin-top: 10px; border: none; border-collapse: collapse; page-break-inside: avoid; }
        .signature-section td { border: none !important; text-align: center; vertical-align: top; font-size: 8px; padding: 0; line-height: 1.1; }
        .signature-space { height: 25px; }

        .page-badge {
            display: inline-block;
            background: #1e3c72;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .page-sheet { box-shadow: none; padding: 0; margin: 0 0 20px 0; max-width: 100%; width: 100%; page-break-after: always; }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 4mm; }
            html, body { height: 100%; overflow: visible !important; }
            .table-recap th, .table-recap td { border: 0.5pt solid #000000; }
        }
    </style>
</head>
<body>

    <!-- KONTROL BAR STICKY TOP -->
    <div class="no-print" style="position: sticky; top: 10px; z-index: 9999; margin: 0 auto 15px auto; max-width: 1200px; display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 10px 16px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
        <div>
            <h4 style="margin: 0; color: #1e3c72; font-size: 13px;">Pratinjau Laporan Mingguan Terpadu - Kelas {{ $classroom->name ?? '' }}</h4>
            <small style="color: #6c757d; font-size: 10px;">Periode: {{ isset($startOfWeek) ? $startOfWeek->format('d/m/Y') : '-' }} s/d {{ isset($endOfWeek) ? $endOfWeek->format('d/m/Y') : '-' }} (3 Halaman PDF)</small>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('combined.recap.index') }}" style="padding: 6px 14px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                ← Kembali Filter
            </a>
            <a href="{{ route('combined.recap.pdf', request()->all()) }}" style="padding: 6px 18px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                📥 Download PDF (3 Halaman)
            </a>
            <button onclick="window.print()" style="padding: 6px 14px; background: #198754; color: #fff; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 10px;">
                🖨️ Cetak Native
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 1: REKAPITULASI PRESENSI PEMBELAJARAN -->
    <!-- ========================================================================= -->
    <div class="page-sheet">
        <div class="no-print"><span class="page-badge">HALAMAN 1 : PRESENSI PEMBELAJARAN</span></div>
        
        <div class="kop-container">
            <table class="header-kop">
                <tr>
                    <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}" alt="Logo Kiri">@endif</td>
                    <td class="kop-title">
                        <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                        <p class="dinas">DINAS PENDIDIKAN</p>
                        <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                        <p class="alamat">{{ $settings['school_address'] }}</p>
                    </td>
                    <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}" alt="Logo Kanan">@endif</td>
                </tr>
            </table>
            <div class="kop-line"></div>
        </div>

        <div class="report-title">
            <h4>LAPORAN MINGGUAN REKAPITULASI PRESENSI PEMBELAJARAN</h4>
            <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $startOfWeek->copy()->addDays(4)->format('d/m/Y') }}</p>
        </div>

        <table class="meta-info">
            <tr>
                <td style="width: 100px;">KELAS</td>
                <td style="width: 10px;">:</td>
                <td>{{ strtoupper($classroom->name ?? '') }}</td>
                <td style="text-align: right;">KONSENTRASI KEAHLIAN : <span>{{ strtoupper($classroom->major->name ?? '-') }}</span></td>
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
                            <th style="font-size: 5.5px;">{{ $daySubjectMap[$day][$j] ?? '' }}</th>
                        @endfor
                    @endforeach
                    <th rowspan="2">S</th><th rowspan="2">I</th><th rowspan="2">A</th>
                </tr>
                <tr>
                    @for($i = 0; $i < 5; $i++)
                        @for($j = 1; $j <= 10; $j++) <th style="font-size: 5.5px;">{{ $j }}</th> @endfor
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $st)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $st->nis ?? '-' }}</td>
                        <td class="text-left"><strong>{{ strtoupper($st->name ?? '') }}</strong></td>
                        <td>{{ substr($st->gender ?? 'L', 0, 1) }}</td>

                        @for($day = 1; $day <= 5; $day++)
                            @for($jam = 1; $jam <= 10; $jam++)
                                @php $val = $learningRecapMap[$st->id][$day][$jam] ?? ''; @endphp
                                <td class="{{ $val == 'X' ? 'txt-alpa' : '' }}">{{ $val }}</td>
                            @endfor
                        @endfor

                        <td>{{ $learningSummary[$st->id]['sakit'] ?: '' }}</td>
                        <td>{{ $learningSummary[$st->id]['izin'] ?: '' }}</td>
                        <td class="txt-alpa">{{ $learningSummary[$st->id]['alpa'] ?: '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="57" style="padding: 10px;">Tidak ada data siswa terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="signature-section">
            <tr>
                <td style="width: 33%;">Mengetahui :<br>Kepala Sekolah,<div class="signature-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
                <td style="width: 33%;"><br>Guru BP / BK,<div class="signature-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
                <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas,<div class="signature-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 2: REKAPITULASI PRESENSI GERBANG -->
    <!-- ========================================================================= -->
    <div class="page-sheet">
        <div class="no-print"><span class="page-badge" style="background: #e67e22;">HALAMAN 2 : PRESENSI GERBANG HARIAN</span></div>

        <div class="kop-container">
            <table class="header-kop">
                <tr>
                    <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}" alt="Logo Kiri">@endif</td>
                    <td class="kop-title">
                        <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                        <p class="dinas">DINAS PENDIDIKAN</p>
                        <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                        <p class="alamat">{{ $settings['school_address'] }}</p>
                    </td>
                    <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}" alt="Logo Kanan">@endif</td>
                </tr>
            </table>
            <div class="kop-line"></div>
        </div>

        <div class="report-title">
            <h4>REKAPITULASI PRESENSI GERBANG SISWA</h4>
            <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $startOfWeek->copy()->addDays(4)->format('d/m/Y') }}</p>
        </div>

        <table class="meta-info">
            <tr>
                <td style="width: 100px;">KELAS</td>
                <td style="width: 10px;">:</td>
                <td>{{ strtoupper($classroom->name ?? '') }}</td>
                <td style="text-align: right;">KONSENTRASI KEAHLIAN : <span>{{ strtoupper($classroom->major->name ?? '-') }}</span></td>
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
                    <th colspan="5" style="width: 12%;">REKAPITULASI</th>
                </tr>
                <tr>
                    @for($i = 0; $i < 5; $i++)
                        <th style="width: 4.8%;">MASUK</th><th style="width: 4.8%;">PULANG</th>
                    @endfor
                    <th style="width: 2.4%;">H</th><th style="width: 2.4%;">T</th><th style="width: 2.4%;">S</th><th style="width: 2.4%;">I</th><th style="width: 2.4%;">A</th>
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
                @empty
                    <tr><td colspan="19" style="padding: 10px;">Tidak ada data siswa terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="signature-section">
            <tr>
                <td style="width: 33%;">Mengetahui :<br>Kepala Sekolah,<div class="signature-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
                <td style="width: 33%;"><br>Guru BP / BK,<div class="signature-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
                <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas,<div class="signature-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
            </tr>
        </table>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 3: REKAPITULASI PRESENSI SHOLAT -->
    <!-- ========================================================================= -->
    <div class="page-sheet">
        <div class="no-print"><span class="page-badge" style="background: #198754;">HALAMAN 3 : PRESENSI SHOLAT 5 WAKTU (7 HARI)</span></div>

        <div class="kop-container">
            <table class="header-kop">
                <tr>
                    <td class="kop-logo-left">@if(!empty($settings['logo_left']))<img src="{{ $settings['logo_left'] }}" alt="Logo Kiri">@endif</td>
                    <td class="kop-title">
                        <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                        <p class="dinas">DINAS PENDIDIKAN</p>
                        <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                        <p class="alamat">{{ $settings['school_address'] }}</p>
                    </td>
                    <td class="kop-logo-right">@if(!empty($settings['logo_right']))<img src="{{ $settings['logo_right'] }}" alt="Logo Kanan">@endif</td>
                </tr>
            </table>
            <div class="kop-line"></div>
        </div>

        <div class="report-title">
            <h4>REKAPITULASI PRESENSI SHOLAT SISWA</h4>
            <p>PERIODE {{ $startOfWeek->format('d/m/Y') }} S/D {{ $endOfWeek->format('d/m/Y') }}</p>
        </div>

        <table class="meta-info">
            <tr>
                <td style="width: 100px;">KELAS</td>
                <td style="width: 10px;">:</td>
                <td>{{ strtoupper($classroom->name ?? '') }}</td>
                <td style="text-align: right;">KONSENTRASI KEAHLIAN : <span>{{ strtoupper($classroom->major->name ?? '-') }}</span></td>
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
                        <th style="width: 2.02%; font-size: 5.5px;">SBH</th>
                        <th style="width: 2.02%; font-size: 5.5px;">DZH</th>
                        <th style="width: 2.02%; font-size: 5.5px;">ASH</th>
                        <th style="width: 2.02%; font-size: 5.5px;">MGB</th>
                        <th style="width: 2.02%; font-size: 5.5px;">ISY</th>
                    @endfor
                    <th style="width: 2.5%;">HADIR</th><th style="width: 2.5%;">ALPA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $st)
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
                @empty
                    <tr><td colspan="41" style="padding: 10px;">Tidak ada data siswa terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="signature-section">
            <tr>
                <td style="width: 33%;">Mengetahui :<br>Kepala Sekolah,<div class="signature-space"></div><strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>NIP. {{ $settings['kepsek_nip'] }}</td>
                <td style="width: 33%;"><br>Guru BP / BK,<div class="signature-space"></div><strong><u>{{ $officers['bk_nama'] }}</u></strong><br>NIP. {{ $officers['bk_nip'] }}</td>
                <td style="width: 33%;">Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas,<div class="signature-space"></div><strong><u>{{ $officers['walas_nama'] }}</u></strong><br>NIP. {{ $officers['walas_nip'] }}</td>
            </tr>
        </table>
    </div>

</body>
</html>