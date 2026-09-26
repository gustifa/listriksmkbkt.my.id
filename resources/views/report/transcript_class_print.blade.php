<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Kelas {{ $classroom->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 0; padding: 0; }
        .page-container { padding: 20px; box-sizing: border-box; width: 100%; }

        /* CSS Wajib untuk cetak massal */
        .page-break { page-break-after: always; display: block; height: 0; clear: both; }
        .page-break:last-child { page-break-after: avoid; } /* Jangan break di halaman terakhir */

        /* Styling Tabel & Kop (Sama seperti sebelumnya) */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .header h2 { margin: 0; font-size: 16pt; }
        .header p { margin: 2px 0; font-size: 10pt; }

        .info-table { width: 100%; margin-bottom: 15px; font-size: 10pt; }
        .info-table td { padding: 2px; }

        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #ccc; font-size: 10pt; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9pt; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 4px; text-align: center; }
        table.data-table th { background-color: #eee; }
        table.data-table td.text-left { text-align: left; }

        .summary-box { display: flex; gap: 5px; margin-bottom: 15px; justify-content: center;}
        .summary-item { border: 1px solid #000; padding: 5px 15px; text-align: center; border-radius: 4px;}
        .summary-val { font-size: 12pt; font-weight: bold; display: block; }
        .summary-lbl { font-size: 8pt; text-transform: uppercase; }

        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; size: A4; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol Navigasi -->
    <div class="no-print" style="padding: 20px; background: #f0f0f0; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
        <button onclick="window.history.back()" style="padding: 8px 15px; cursor: pointer; font-size: 14px;">&larr; Kembali ke Filter</button>
        <span style="margin-left: 15px;">Menampilkan <b>{{ count($transcripts) }}</b> siswa dari kelas <b>{{ $classroom->name }}</b>.</span>
    </div>

    @foreach($transcripts as $data)
        @php
            $student = $data['student'];
            $dailyLogs = $data['dailyLogs'];
            $dailySummary = $data['dailySummary'];
            $lessonSummary = $data['lessonSummary'];
        @endphp

        <div class="page-container">
            <!-- KOP SURAT (Diulang tiap halaman) -->
            <div class="header">
                <h2>LAPORAN KEHADIRAN SISWA</h2>
                <p>SMK NEGERI TEKNOLOGI - TAHUN AJARAN {{ $dateObj->year }}</p>
                <p>Periode Laporan: {{ $dateObj->translatedFormat('F Y') }}</p>
            </div>

            <!-- INFO SISWA -->
            <table class="info-table">
                <tr>
                    <td width="15%"><b>Nama Siswa</b></td>
                    <td width="35%">: {{ $student->name }}</td>
                    <td width="15%"><b>Kelas</b></td>
                    <td width="35%">: {{ $student->classroom->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>NIS / NISN</b></td>
                    <td>: {{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
                    <td><b>Wali Kelas</b></td>
                    <td>: _________________</td>
                </tr>
            </table>

            <!-- BAGIAN A: Ringkasan Harian -->
            <div class="section-title">A. RINGKASAN ABSENSI HARIAN</div>
            <div class="summary-box">
                <div class="summary-item"><span class="summary-val">{{ $dailySummary['hadir'] }}</span><span class="summary-lbl">Hadir</span></div>
                <div class="summary-item"><span class="summary-val">{{ $dailySummary['terlambat'] }}</span><span class="summary-lbl">Terlambat</span></div>
                <div class="summary-item"><span class="summary-val">{{ $dailySummary['sakit'] }}</span><span class="summary-lbl">Sakit</span></div>
                <div class="summary-item"><span class="summary-val">{{ $dailySummary['izin'] }}</span><span class="summary-lbl">Izin</span></div>
                <div class="summary-item"><span class="summary-val">{{ $dailySummary['alpa'] }}</span><span class="summary-lbl">Alpa</span></div>
            </div>

            <!-- Tabel Detail Harian (Opsional: Jika ingin hemat kertas, bagian ini bisa dihapus atau dipersingkat) -->
            <!-- <table class="data-table">...Log Harian...</table> -->

            <!-- BAGIAN B: Per Mapel -->
            <div class="section-title">B. REKAPITULASI PER MATA PELAJARAN</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th class="text-left">Mata Pelajaran</th>
                        <th width="10%">Pertemuan</th>
                        <th width="8%">Hadir</th>
                        <th width="8%">Telat</th>
                        <th width="8%">Sakit</th>
                        <th width="8%">Izin</th>
                        <th width="8%">Alpa</th>
                        <th width="10%">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lessonSummary as $idx => $lesson)
                        @php
                            $masuk = $lesson->total_present + $lesson->total_late;
                            $percent = $lesson->total_meetings > 0 ? round(($masuk / $lesson->total_meetings) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="text-left">{{ $lesson->subject_name }}</td>
                            <td>{{ $lesson->total_meetings }}</td>
                            <td>{{ $lesson->total_present }}</td>
                            <td>{{ $lesson->total_late }}</td>
                            <td>{{ $lesson->total_sick }}</td>
                            <td>{{ $lesson->total_permission }}</td>
                            <td style="{{ $lesson->total_alpha > 0 ? 'background:#ffe6e6;font-weight:bold;' : '' }}">{{ $lesson->total_alpha }}</td>
                            <td>{{ $percent }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="9">Belum ada data pembelajaran.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Tanda Tangan (Posisi absolut bawah halaman agar rapi) -->
            <div style="margin-top: 30px; text-align: right;">
                <div style="display: inline-block; text-align: center; width: 200px;">
                    <p>................., {{ date('d F Y') }}</p>
                    <p>Orang Tua / Wali</p>
                    <br><br><br>
                    <p>( ................................. )</p>
                </div>
            </div>
        </div>

        <!-- Pemisah Halaman (Kecuali halaman terakhir) -->
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

</body>
</html>
