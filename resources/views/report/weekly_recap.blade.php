<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Presensi Mingguan Siswa</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: "Arial", sans-serif;
        }

        html, body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 4px;
            font-size: 7px;
        }

        .container-print {
            background: #fff;
            padding: 6px 8px;
            margin: 0 auto;
            max-width: 1000px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* 1. KOP SURAT */
        .kop-container {
            width: 100%;
            margin-bottom: 2px;
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
            width: 50px;
            text-align: center;
        }

        .kop-logo-left img, .kop-logo-right img {
            max-width: 44px;
            max-height: 44px;
            object-fit: contain;
        }

        .kop-title {
            text-align: center;
            line-height: 1.1;
        }

        .kop-title .provinsi {
            font-size: 9.5px;
            font-weight: bold;
            margin: 0;
        }

        .kop-title .dinas {
            font-size: 8.5px;
            font-weight: bold;
            margin: 0;
        }

        .kop-title .sekolah {
            font-size: 10.5px;
            font-weight: bold;
            margin: 0;
        }

        .kop-title .alamat {
            font-size: 7px;
            font-style: italic;
            margin-top: 1px;
        }

        .kop-line {
            border-top: 2px solid #000;
            border-bottom: 0.5px solid #000;
            height: 2px;
            margin-top: 2px;
            margin-bottom: 3px;
        }

        /* 2. JUDUL DAFTAR HADIR & META INFO */
        .report-title {
            text-align: center;
            margin-bottom: 3px;
        }

        .report-title h4 {
            margin: 0;
            font-size: 9.5px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .report-title p {
            margin: 1px 0 0 0;
            font-size: 7.5px;
            font-weight: bold;
        }

        .meta-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .meta-info td {
            border: none !important;
            padding: 0px;
            vertical-align: top;
        }

        /* 3. TABEL REKAP UTAMA (KOLOM RAMPING) */
        .table-recap {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-recap th, 
        .table-recap td {
            border: 0.5px solid #000000;
            padding: 0.5px 0px;
            text-align: center;
            vertical-align: middle;
            font-size: 6.5px;
            word-wrap: break-word;
            line-height: 1;
        }

        .table-recap thead th {
            font-weight: bold;
            background-color: #ffffff;
            text-transform: uppercase;
        }

        .text-left {
            text-align: left !important;
            padding-left: 2px !important;
        }

        /* PENETAPAN LEBAR KOLOM RAMPING */
        .col-no { width: 14px; }
        .col-nis { width: 32px; }
        .col-nama { width: 120px; }
        .col-lp { width: 13px; }
        .col-jam { width: 9px !important; font-size: 6px !important; }
        .col-stat { width: 10px !important; font-size: 6px !important; }

        .vertical-cell {
            height: 30px !important;
            vertical-align: middle !important;
            padding: 0 !important;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            font-weight: bold;
            font-size: 6px;
            margin: 0 auto;
            display: block;
            text-align: center;
        }

        .subject-cell {
            height: 38px !important;
            vertical-align: bottom !important;
            padding-bottom: 1px !important;
        }

        .subject-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            font-size: 6px !important;
            font-weight: bold !important;
            text-transform: uppercase;
            margin: 0 auto;
            display: block;
            text-align: center;
        }

        .text-alpa {
            color: #d9534f !important;
            font-weight: bold;
        }

        /* 4. AREA PENGESAHAN TANDA TANGAN */
        .signature-section {
            width: 100%;
            margin-top: 4px;
            border: none;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-section td {
            border: none !important;
            text-align: center;
            vertical-align: top;
            font-size: 7px;
            padding: 0;
            line-height: 1.1;
        }

        .signature-space {
            height: 20px;
        }

        /* OPSI CETAK A4 LANDSCAPE */
        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .container-print {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 3mm 4mm 2mm 4mm;
            }
            
            html, body {
                height: 100%;
                overflow: visible !important;
            }

            .table-recap th, 
            .table-recap td {
                border: 0.5pt solid #000000;
            }

            .text-alpa {
                color: #d9534f !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER KONTROL (TIDAK DICETAK) -->
    <div class="no-print" style="margin: 10px auto; max-width: 1000px; display: flex; justify-content: space-between; align-items: center; background: #e9ecef; padding: 6px 10px; border-radius: 4px;">
        <div>
            <h4 style="margin: 0; color: #0d6efd;">Laporan Presensi Mingguan - Kelas {{ $classroom->name ?? '' }}</h4>
            <small style="color: #6c757d;">Periode: {{ isset($startOfWeek) ? $startOfWeek->translatedFormat('d F Y') : '-' }} s/d {{ isset($endOfWeek) ? $endOfWeek->translatedFormat('d F Y') : '-' }}</small>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('attendances.filter') }}" style="padding: 6px 14px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                ← Kembali Filter
            </a>
            <a href="{{ route('attendances.weekly-recap.pdf', request()->all()) }}" style="padding: 6px 18px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
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

        <!-- 2. JUDUL DAFTAR HADIR -->
        <div class="report-title">
            <h4>DAFTAR HADIR MURID SEMESTER GANJIL</h4>
            <p>TAHUN PELAJARAN 2026/2027</p>
        </div>

        <!-- 3. INFORMASI DETAIL KELAS & JURUSAN -->
        <table class="meta-info">
            <tr>
                <td style="width: 140px;">BIDANG KEAHLIAN</td>
                <td style="width: 10px;">:</td>
                <td>{{ strtoupper($classroom->major->bidang_keahlian ?? 'TEKNOLOGI DAN REKAYASA') }}</td>
                <td style="text-align: right; width: 150px;">
                    KELAS : <span style="font-size: 8px; font-weight: bold;">{{ strtoupper($classroom->name ?? '') }}</span>
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
                    <th rowspan="4" class="col-no vertical-cell">
                        <div class="vertical-text">URUT</div>
                    </th>
                    <th rowspan="4" class="col-nis vertical-cell">
                        <div class="vertical-text">INDUK SISWA</div>
                    </th>
                    <th rowspan="4" class="col-nama">NAMA PESERTA DIDIK</th>
                    <th rowspan="4" class="col-lp">L/P</th>
                    
                    <!-- BARIS HARI -->
                    <th colspan="10">SENIN</th>
                    <th colspan="10">SELASA</th>
                    <th colspan="10">RABU</th>
                    <th colspan="10">KAMIS</th>
                    <th colspan="10">JUMAT</th>
                    
                    <th colspan="3" rowspan="2" style="font-size: 5px;">JML TDK HDR</th>
                </tr>

                <!-- BARIS TANGGAL -->
                <tr>
                    <th colspan="10" style="font-size: 6px; font-weight: normal;">{{ $weekDates['Senin'] ?? '' }}</th>
                    <th colspan="10" style="font-size: 6px; font-weight: normal;">{{ $weekDates['Selasa'] ?? '' }}</th>
                    <th colspan="10" style="font-size: 6px; font-weight: normal;">{{ $weekDates['Rabu'] ?? '' }}</th>
                    <th colspan="10" style="font-size: 6px; font-weight: normal;">{{ $weekDates['Kamis'] ?? '' }}</th>
                    <th colspan="10" style="font-size: 6px; font-weight: normal;">{{ $weekDates['Jumat'] ?? '' }}</th>
                </tr>
                
                <!-- BARIS KODE MATA PELAJARAN (VERTIKAL) -->
                <tr>
                    @php
                        $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                    @endphp

                    @foreach($daysList as $dayName)
                        @for($jam = 1; $jam <= 10; $jam++)
                            @php
                                $code = $daySubjectMap[$dayName][$jam] ?? '';
                            @endphp
                            <th class="col-jam subject-cell">
                                @if(!empty($code))
                                    <div class="subject-header">{{ $code }}</div>
                                @endif
                            </th>
                        @endfor
                    @endforeach

                    <th class="col-stat">S</th>
                    <th class="col-stat">I</th>
                    <th class="col-stat">A</th>
                </tr>

                <!-- BARIS JAM KE 1-10 -->
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

                        <!-- Matriks Presensi (✓, S, I, X MERAH) -->
                        @for($day = 1; $day <= 5; $day++)
                            @for($jam = 1; $jam <= 10; $jam++)
                                @php
                                    $val = $recapMap[$student->id][$day][$jam] ?? '';
                                @endphp
                                <td>
                                    @if($val === 'X')
                                        <strong class="text-alpa">X</strong>
                                    @else
                                        <strong>{{ $val }}</strong>
                                    @endif
                                </td>
                            @endfor
                        @endfor

                        <!-- Total Sakit, Izin, Alfa (X) -->
                        <td>{{ !empty($summary[$student->id]['sakit']) ? $summary[$student->id]['sakit'] : '' }}</td>
                        <td>{{ !empty($summary[$student->id]['izin']) ? $summary[$student->id]['izin'] : '' }}</td>
                        <td class="text-alpa">{{ !empty($summary[$student->id]['alpa']) ? $summary[$student->id]['alpa'] : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="57" style="padding: 10px;">Tidak ada data siswa terdaftar.</td>
                    </tr>
                @endforelse

                <!-- BARIS PARAF GURU -->
                <tr style="font-weight: bold;">
                    <td colspan="4" style="text-align: center; height: 12px;">PARAF GURU</td>
                    @for($i = 0; $i < 50; $i++)
                        <td></td>
                    @endfor
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <!-- 5. AREA PENGESAHAN TANDA TANGAN -->
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

    </div>

</body>
</html>