<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Presensi Mingguan Siswa</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 3mm 4mm 2mm 4mm;
        }

        * {
            box-sizing: border-box;
            font-family: "Helvetica", "Arial", sans-serif;
        }

        body {
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 5pt;
            line-height: 0.95;
        }

        /* 1. KOP SURAT */
        .kop-container {
            width: 100%;
            margin-bottom: 1px;
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

        .kop-logo-left, .kop-logo-right {
            width: 35px;
            text-align: center;
        }

        .kop-logo-left img, .kop-logo-right img {
            max-width: 28px;
            max-height: 28px;
        }

        .kop-title {
            text-align: center;
            line-height: 1;
        }

        .kop-title .provinsi { font-size: 7pt; font-weight: bold; margin: 0; }
        .kop-title .dinas { font-size: 6pt; font-weight: bold; margin: 0; }
        .kop-title .sekolah { font-size: 8pt; font-weight: bold; margin: 0; }
        .kop-title .alamat { font-size: 5pt; font-style: italic; margin-top: 1px; }

        .kop-line {
            border-top: 1.5px solid #000;
            border-bottom: 0.5px solid #000;
            height: 1px;
            margin-top: 1px;
            margin-bottom: 1px;
        }

        /* 2. JUDUL LAPORAN */
        .report-title {
            text-align: center;
            margin-bottom: 1px;
        }

        .report-title h4 {
            margin: 0;
            font-size: 7pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .report-title p {
            margin: 0;
            font-size: 5.5pt;
            font-weight: bold;
        }

        .meta-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1px;
            font-size: 5pt;
            font-weight: bold;
        }

        .meta-info td {
            border: none !important;
            padding: 0px;
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
            border: 0.25pt solid #000000;
            padding: 0px;
            text-align: center;
            vertical-align: middle;
            font-size: 4.2pt;
            height: 6.8px;
            word-wrap: break-word;
        }

        .table-recap thead th {
            font-weight: bold;
            background-color: #ffffff;
            text-transform: uppercase;
        }

        .text-left {
            text-align: left !important;
            padding-left: 2px !important;
            font-size: 4pt;
            line-height: 0.9;
        }

        /* AKOMODASI PROPORSI PERSENTASE REVISI */
        .col-no   { width: 2.0%; font-size: 3.5pt; }
        .col-nis  { width: 3.5%; font-size: 3.5pt; }
        .col-nama { width: 12.0%; } /* DIPERKECIL DARI 16% KE 12% */
        .col-lp   { width: 1.5%; font-size: 3.5pt; }
        .col-jam  { width: 1.25%; font-size: 3.5pt; } /* DINAIKKAN AGAR KODE MAPEL LEGA (50 JAM X 1.25% = 62.5%) */
        .col-stat { width: 1.8%; font-size: 3.5pt; }

        /* TINGGI SEL MAPEL TINGGI & BISA TERBACA JELAS */
        .subject-cell {
            height: 28px;
            vertical-align: middle;
            text-align: center;
            font-size: 4pt;
            line-height: 0.9;
            font-weight: bold;
        }

        .text-alpa {
            color: #d9534f;
            font-weight: bold;
        }

        .text-hadir {
            color: #000;
            font-weight: normal;
        }

        /* 4. AREA PENGESAHAN */
        .signature-section {
            width: 100%;
            margin-top: 2px;
            border: none;
            border-collapse: collapse;
        }

        .signature-section td {
            border: none !important;
            text-align: center;
            vertical-align: top;
            font-size: 5pt;
            padding: 0;
            line-height: 0.95;
        }

        .signature-space {
            height: 11px;
        }
    </style>
</head>
<body>

    <!-- 1. KOP SURAT -->
    <div class="kop-container">
        <table class="header-kop">
            <tr>
                <td class="kop-logo-left">
                    @if(!empty($settings['logo_left']))
                        <img src="{{ $settings['logo_left'] }}" alt="Logo Left">
                    @endif
                </td>
                <td class="kop-title">
                    <p class="provinsi">{{ strtoupper($settings['provinsi_name']) }}</p>
                    <p class="dinas">DINAS PENDIDIKAN</p>
                    <p class="sekolah">{{ strtoupper($settings['school_name']) }}</p>
                    <p class="alamat">
                        {{ $settings['school_address'] }} {{ $settings['school_phone'] }}<br>
                        email : {{ $settings['school_email'] }}, web site : {{ $settings['school_web'] }}
                    </p>
                </td>
                <td class="kop-logo-right">
                    @if(!empty($settings['logo_right']))
                        <img src="{{ $settings['logo_right'] }}" alt="Logo Right">
                    @endif
                </td>
            </tr>
        </table>
        <div class="kop-line"></div>
    </div>

    <!-- 2. JUDUL LAPORAN -->
    <div class="report-title">
        <h4>DAFTAR HADIR MURID SEMESTER GANJIL</h4>
        <p>TAHUN PELAJARAN 2026/2027</p>
    </div>

    <!-- 3. DETAIL KELAS -->
    <table class="meta-info">
        <tr>
            <td style="width: 100px;">BIDANG KEAHLIAN</td>
            <td style="width: 6px;">:</td>
            <td>{{ strtoupper($classroom->major->bidang_keahlian ?? 'TEKNOLOGI DAN REKAYASA') }}</td>
            <td style="text-align: right; width: 110px;">
                KELAS : <span>{{ strtoupper($classroom->name ?? '') }}</span>
            </td>
        </tr>
        <tr>
            <td>PROGRAM KEAHLIAN</td>
            <td>:</td>
            <td colspan="2">{{ strtoupper($classroom->major->program_keahlian ?? '') }}</td>
        </tr>
        <tr>
            <td>KONSENTRASI KEAHLIAN</td>
            <td>:</td>
            <td colspan="2">{{ strtoupper($classroom->major->name ?? '') }}</td>
        </tr>
    </table>

    <!-- 4. TABEL REKAP UTAMA -->
    <table class="table-recap">
        <thead>
            <tr>
                <th rowspan="4" class="col-no">NO<br>URUT</th>
                <th rowspan="4" class="col-nis">INDUK<br>SISWA</th>
                <th rowspan="4" class="col-nama">NAMA PESERTA DIDIK</th>
                <th rowspan="4" class="col-lp">L/P</th>
                
                <th colspan="10">SENIN</th>
                <th colspan="10">SELASA</th>
                <th colspan="10">RABU</th>
                <th colspan="10">KAMIS</th>
                <th colspan="10">JUMAT</th>
                
                <th colspan="3" rowspan="2" style="font-size: 3.2pt;">JML TDK HDR</th>
            </tr>

            <tr>
                <th colspan="10" style="font-size: 3.5pt; font-weight: normal;">{{ $weekDates['Senin'] ?? '' }}</th>
                <th colspan="10" style="font-size: 3.5pt; font-weight: normal;">{{ $weekDates['Selasa'] ?? '' }}</th>
                <th colspan="10" style="font-size: 3.5pt; font-weight: normal;">{{ $weekDates['Rabu'] ?? '' }}</th>
                <th colspan="10" style="font-size: 3.5pt; font-weight: normal;">{{ $weekDates['Kamis'] ?? '' }}</th>
                <th colspan="10" style="font-size: 3.5pt; font-weight: normal;">{{ $weekDates['Jumat'] ?? '' }}</th>
            </tr>
            
            <tr>
                @php
                    $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                @endphp

                @foreach($daysList as $dayName)
                    @for($jam = 1; $jam <= 10; $jam++)
                        @php
                            $code = $daySubjectMap[$dayName][$jam] ?? '';
                            $verticalCode = !empty($code) ? implode('<br>', str_split($code)) : '';
                        @endphp
                        <th class="col-jam subject-cell">
                            {!! $verticalCode !!}
                        </th>
                    @endfor
                @endforeach

                <th class="col-stat">S</th>
                <th class="col-stat">I</th>
                <th class="col-stat">A</th>
            </tr>

            <tr>
                @for($day = 1; $day <= 5; $day++)
                    @for($jam = 1; $jam <= 10; $jam++)
                        <th class="col-jam">{{ $jam }}</th>
                    @endfor
                @endfor
                <th class="col-stat"></th>
                <th class="col-stat"></th>
                <th class="col-stat"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->nis ?? '-' }}</td>
                    <td class="text-left"><strong>{{ strtoupper($student->name ?? '') }}</strong></td>
                    <td>{{ substr($student->gender ?? 'L', 0, 1) }}</td>

                    @for($day = 1; $day <= 5; $day++)
                        @for($jam = 1; $jam <= 10; $jam++)
                            @php
                                $val = $recapMap[$student->id][$day][$jam] ?? '';
                            @endphp
                            <td>
                                @if($val === 'X')
                                    <span class="text-alpa">X</span>
                                @elseif($val === '✓')
                                    <span class="text-hadir">v</span>
                                @else
                                    <span>{{ $val }}</span>
                                @endif
                            </td>
                        @endfor
                    @endfor

                    <td>{{ !empty($summary[$student->id]['sakit']) ? $summary[$student->id]['sakit'] : '' }}</td>
                    <td>{{ !empty($summary[$student->id]['izin']) ? $summary[$student->id]['izin'] : '' }}</td>
                    <td class="text-alpa">{{ !empty($summary[$student->id]['alpa']) ? $summary[$student->id]['alpa'] : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="57" style="padding: 10px;">Tidak ada data siswa terdaftar.</td>
                </tr>
            @endforelse

            <tr style="font-weight: bold;">
                <td colspan="4" style="text-align: center; height: 6.8px;">PARAF GURU</td>
                @for($i = 0; $i < 50; $i++)
                    <td></td>
                @endfor
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <!-- 5. AREA PENGESAHAN -->
    <table class="signature-section">
        <tr>
            <td style="width: 28%;">
                Mengetahui :<br>
                Kepala SMK N 1 Bukittinggi,
                <div class="signature-space"></div>
                <strong><u>{{ $settings['kepsek_nama'] }}</u></strong><br>
                NIP. {{ $settings['kepsek_nip'] }}
            </td>
            <td style="width: 22%;">
                <br>
                Guru BP,
                <div class="signature-space"></div>
                <strong><u>{{ $officers['bk_nama'] }}</u></strong><br>
                NIP. {{ $officers['bk_nip'] }}
            </td>
            <td style="width: 22%;">
                <br>
                Wali Kelas,
                <div class="signature-space"></div>
                <strong><u>{{ $officers['walas_nama'] }}</u></strong><br>
                NIP. {{ $officers['walas_nip'] }}
            </td>
            <td style="width: 28%;">
                Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Ketua Kelas,
                <div class="signature-space"></div>
                <strong><u>{{ $officers['ketua_nama'] }}</u></strong><br>
                NIS. {{ $officers['ketua_nis'] }}
            </td>
        </tr>
    </table>

</body>
</html>