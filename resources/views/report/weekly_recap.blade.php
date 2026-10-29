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
            padding: 5px;
            font-size: 8.5px;
        }

        .container-print {
            background: #fff;
            padding: 8px 10px;
            margin: auto;
            max-width: 100%;
        }

        .table-recap {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-recap th, 
        .table-recap td {
            border: 1px solid #000;
            padding: 1px 0px;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            word-wrap: break-word;
            line-height: 1.1;
        }

        .table-recap thead th {
            font-weight: bold;
            background-color: #ffffff;
            text-transform: uppercase;
        }

        .text-left {
            text-align: left !important;
            padding-left: 3px !important;
        }

        .col-no { width: 18px; }
        .col-nis { width: 40px; }
        .col-nama { width: 145px; }
        .col-lp { width: 18px; }
        .col-jam { width: 12px; font-size: 7.5px !important; }
        .col-stat { width: 14px; font-size: 7.5px !important; }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding: 1px 0;
            font-weight: bold;
            font-size: 7.5px;
        }

        .subject-cell {
            height: 55px !important;
            vertical-align: bottom !important;
            padding-bottom: 2px !important;
        }

        .subject-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            font-size: 7.5px !important;
            font-weight: bold !important;
            max-height: 50px;
            margin: auto;
            text-transform: uppercase;
        }

        .signature-section {
            width: 100%;
            margin-top: 10px;
            border: none;
            page-break-inside: avoid;
        }

        .signature-section td {
            border: none !important;
            text-align: center;
            vertical-align: top;
            font-size: 9px;
            padding: 0;
        }

        .signature-space {
            height: 32px;
        }

        /* METODE KHUSUS AGAR AUTO PAS 1 HALAMAN SAAT DICETAK */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .container-print {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 4mm 5mm 4mm 5mm;
            }
            
            /* Menjaga seluruh konten tidak pecah ke halaman kedua */
            html, body, .container-print {
                height: 99%;
                overflow: hidden;
            }
        }
    </style>
</head>
<body>

    <div class="container-print">
        
        <!-- HEADER KONTROL (TIDAK DICETAK) -->
        <div class="no-print" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="margin: 0; color: #0d6efd;">Laporan Presensi Mingguan - Kelas {{ $classroom->name ?? 'X TITL' }}</h4>
                <small style="color: #6c757d;">Periode: {{ isset($startOfWeek) ? $startOfWeek->translatedFormat('d F Y') : '-' }} s/d {{ isset($endOfWeek) ? $endOfWeek->translatedFormat('d F Y') : '-' }}</small>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('attendances.filter') }}" style="padding: 6px 14px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 15px; font-weight: bold; font-size: 10px;">
                    ← Kembali Filter
                </a>
                <button onclick="window.print()" style="padding: 6px 18px; background: #0d6efd; color: #fff; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; font-size: 10px;">
                    🖨️ Cetak / Download PDF
                </button>
            </div>
        </div>

        <!-- TABEL REKAP UTAMA -->
        <table class="table-recap">
            <thead>
                <tr>
                    <th rowspan="3" class="col-no">
                        <div class="vertical-text">URUT</div>
                    </th>
                    <th rowspan="3" class="col-nis">
                        <div class="vertical-text">INDUK SISWA</div>
                    </th>
                    <th rowspan="3" class="col-nama">NAMA PESERTA DIDIK</th>
                    <th rowspan="3" class="col-lp">L/P</th>
                    
                    <th colspan="10">SENIN</th>
                    <th colspan="10">SELASA</th>
                    <th colspan="10">RABU</th>
                    <th colspan="10">KAMIS</th>
                    <th colspan="10">JUMAT</th>
                    
                    <th colspan="3" style="font-size: 7px;">JML TDK HDR</th>
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

                        <!-- Matriks Presensi (H, S, I, A) -->
                        @for($day = 1; $day <= 5; $day++)
                            @for($jam = 1; $jam <= 10; $jam++)
                                <td><strong>{{ $recapMap[$student->id][$day][$jam] ?? '' }}</strong></td>
                            @endfor
                        @endfor

                        <!-- Akumulasi Sakit, Izin, Alpa -->
                        <td>{{ isset($summary[$student->id]['sakit']) && $summary[$student->id]['sakit'] > 0 ? $summary[$student->id]['sakit'] : '' }}</td>
                        <td>{{ isset($summary[$student->id]['izin']) && $summary[$student->id]['izin'] > 0 ? $summary[$student->id]['izin'] : '' }}</td>
                        <td>{{ isset($summary[$student->id]['alpa']) && $summary[$student->id]['alpa'] > 0 ? $summary[$student->id]['alpa'] : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="57" style="padding: 10px;">Tidak ada data siswa terdaftar.</td>
                    </tr>
                @endforelse

                <!-- BARIS PARAF GURU -->
                <tr style="font-weight: bold;">
                    <td colspan="4" style="text-align: center; height: 18px;">PARAF GURU</td>
                    @for($i = 0; $i < 50; $i++)
                        <td></td>
                    @endfor
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <!-- AREA PENGESAHAN (DIAMANKAN AGAR SELALU DI HALAMAN YANG SAMA) -->
        <table class="signature-section">
            <tr>
                <td style="width: 28%;">
                    Mengetahui :<br>
                    Kepala SMK N 1 Bukittinggi,
                    <div class="signature-space"></div>
                    <strong><u>GUSTIAN BUDIARTO, M.Pd.</u></strong><br>
                    NIP. 19740731 200801 1 002
                </td>
                <td style="width: 22%;">
                    <br>
                    Guru BP,
                    <div class="signature-space"></div>
                    <strong><u>{{ $classroom->counselingTeacher->name ?? '................................' }}</u></strong><br>
                    NIP. {{ $classroom->counselingTeacher->nip ?? '........................' }}
                </td>
                <td style="width: 22%;">
                    <br>
                    Wali Kelas,
                    <div class="signature-space"></div>
                    <strong><u>{{ $classroom->homeroomTeacher->name ?? '................................' }}</u></strong><br>
                    NIP. {{ $classroom->homeroomTeacher->nip ?? '........................' }}
                </td>
                <td style="width: 28%;">
                    Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Ketua Kelas,
                    <div class="signature-space"></div>
                    <strong><u>{{ $classroom->classLeader->name ?? '................................' }}</u></strong><br>
                    NIS. {{ $classroom->classLeader->nis ?? '........................' }}
                </td>
            </tr>
        </table>

    </div>

</body>
</html>