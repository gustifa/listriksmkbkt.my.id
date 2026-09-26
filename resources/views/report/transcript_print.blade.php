<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Absensi - {{ $student->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12pt; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10pt; }

        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px; vertical-align: top; }
        .info-label { font-weight: bold; width: 120px; }

        .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        table.data-table th { background-color: #f0f0f0; }
        table.data-table td.text-left { text-align: left; }

        .summary-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .summary-item { border: 1px solid #000; padding: 10px; flex: 1; text-align: center; }
        .summary-val { font-size: 14pt; font-weight: bold; display: block; }
        .summary-lbl { font-size: 9pt; text-transform: uppercase; }

        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol Kembali -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer;">&larr; Kembali</button>
    </div>

    <!-- KOP SURAT -->
    <div class="header">
        <h2>Laporan Transkrip Kehadiran Siswa</h2>
        <p>{{ $school['name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</p>
        <p>Periode: {{ $dateObj->translatedFormat('F Y') }}</p>
    </div>

    <!-- INFORMASI SISWA -->
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Siswa</td>
            <td>: {{ $student->name }}</td>
            <td class="info-label">Kelas</td>
            <td>: {{ $student->classroom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">NIS / NISN</td>
            <td>: {{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
            <td class="info-label">Tahun Ajaran</td>
            <td>: {{ $dateObj->year }}</td>
        </tr>
    </table>

    <!-- BAGIAN 1: RINGKASAN KEHADIRAN HARIAN (DailyAttendance) -->
    <div class="section-title">A. RINGKASAN KEHADIRAN HARIAN (DATANG & PULANG)</div>

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-val">{{ $dailySummary['hadir'] }}</span>
            <span class="summary-lbl">Hadir</span>
        </div>
        <div class="summary-item">
            <span class="summary-val">{{ $dailySummary['terlambat'] }}</span>
            <span class="summary-lbl">Terlambat</span>
        </div>
        <div class="summary-item">
            <span class="summary-val">{{ $dailySummary['sakit'] }}</span>
            <span class="summary-lbl">Sakit</span>
        </div>
        <div class="summary-item">
            <span class="summary-val">{{ $dailySummary['izin'] }}</span>
            <span class="summary-lbl">Izin</span>
        </div>
        <div class="summary-item">
            <span class="summary-val">{{ $dailySummary['alpa'] }}</span>
            <span class="summary-lbl">Alpha</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="20%">Jam Datang</th>
                <th width="20%">Jam Pulang</th>
                <th width="15%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyLogs as $key => $log)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d/m/Y') }}</td>
                <td>{{ $log->arrival_time ? \Carbon\Carbon::parse($log->arrival_time)->format('H:i') : '-' }}</td>
                <td>{{ $log->departure_time ? \Carbon\Carbon::parse($log->departure_time)->format('H:i') : '-' }}</td>
                <td>
                    {{ ucfirst($log->status) }}
                    @if($log->status == 'late') <br><small>(Terlambat)</small> @endif
                </td>
                <td class="text-left">{{ $log->note ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Tidak ada data kehadiran harian bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- BAGIAN 2: KEHADIRAN PEMBELAJARAN (Attendance / Per Mapel) -->
    <div class="section-title">B. REKAPITULASI KEHADIRAN PER MATA PELAJARAN</div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" class="text-left">Mata Pelajaran</th>
                <th rowspan="2" width="10%">Total Pertemuan</th>
                <th colspan="5">Rincian Kehadiran</th>
                <th rowspan="2" width="10%">% Kehadiran</th>
            </tr>
            <tr>
                <th width="8%">Hadir</th>
                <th width="8%">Telat</th>
                <th width="8%">Sakit</th>
                <th width="8%">Izin</th>
                <th width="8%">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessonSummary as $key => $lesson)
            @php
                // Hitung Persentase Kehadiran (Hadir + Telat dianggap masuk)
                $masuk = $lesson->total_present + $lesson->total_late;
                $percent = $lesson->total_meetings > 0 ? round(($masuk / $lesson->total_meetings) * 100) : 0;
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td class="text-left fw-bold">{{ $lesson->subject_name }}</td>
                <td>{{ $lesson->total_meetings }}</td>
                <td>{{ $lesson->total_present }}</td>
                <td>{{ $lesson->total_late }}</td>
                <td>{{ $lesson->total_sick }}</td>
                <td>{{ $lesson->total_permission }}</td>
                <td style="{{ $lesson->total_alpha > 0 ? 'background-color: #ffe6e6; font-weight:bold;' : '' }}">
                    {{ $lesson->total_alpha }}
                </td>
                <td>{{ $percent }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="9">Belum ada data absensi pembelajaran bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER TANDA TANGAN -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <table width="100%">
            <tr>
                <td width="70%"></td>
                <td width="30%" align="center">
                    <p>................., {{ date('d F Y') }}</p>
                    <p>Wali Kelas</p>
                    <br><br><br>
                    <p>_______________________</p>
                    <p>NIP. .......................</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
