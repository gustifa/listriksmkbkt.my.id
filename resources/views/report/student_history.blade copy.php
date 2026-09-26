<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }

        /* Header Kop Surat */
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }

        /* Biodata Siswa */
        .bio-table { width: 100%; margin-bottom: 20px; }
        .bio-table td { padding: 4px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }

        /* Statistik Box */
        .stats-box {
            display: flex; width: 100%; margin-bottom: 20px;
            border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;
        }
        .stat-item { margin-right: 30px; }
        .stat-val { font-size: 16px; font-weight: bold; color: #0056b3; }

        /* Tabel Data Main */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 8px; text-align: left; }
        .data-table th { background-color: #eee; text-align: center; }
        .text-center { text-align: center; }

        /* Status Badge Text */
        .status-hadir { color: green; font-weight: bold; }
        .status-terlambat { color: orange; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="header">
        <h1>SMK Negeri Digital Indonesia</h1>
        <p>Jl. Teknologi No. 1, Jakarta | Telp: (021) 123-4567</p>
        <p>Laporan Riwayat Kehadiran Siswa</p>
    </div>

    <!-- Biodata -->
    <table class="bio-table">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: {{ $student->name }}</td>
            <td class="label">Periode Data</td>
            <td>: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk (NIS)</td>
            <td>: {{ $student->nis }}</td>
            <td class="label">Dicetak Pada</td>
            <td>: {{ date('d F Y, H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Kelas Saat Ini</td>
            <td>: {{ $student->classroom->name ?? '-' }}</td>
            <td class="label">Wali Kelas</td>
            <td>: -</td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; background: #f8f9fa;">
        <strong>RINGKASAN KEHADIRAN:</strong> &nbsp;&nbsp;
        Total Masuk: <b>{{ $summary['total'] }}</b> &nbsp;|&nbsp;
        Tepat Waktu: <b>{{ $summary['hadir'] }}</b> &nbsp;|&nbsp;
        Terlambat: <b style="color: orange;">{{ $summary['terlambat'] }}</b>
    </div>

    <!-- Tabel Detail -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 15%">Jam Scan</th>
                <th>Mata Pelajaran</th>
                <th>Guru Pengampu</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $row->check_in_time }}</td>
                <td>
                    <!-- Ambil nama mapel dari relasi subject -->
                    {{ $row->schedule->subject->name ?? '-' }}
                </td>
                <td>
                    <!-- Ambil nama guru -->
                    {{ $row->schedule->teacher->name ?? '-' }}
                </td>
                <td class="text-center">
                    @if($row->status == 'hadir')
                        <span class="status-hadir">HADIR</span>
                    @else
                        <span class="status-terlambat">TERLAMBAT</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada riwayat absensi untuk siswa ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div style="margin-top: 50px; float: right; width: 200px; text-align: center;">
        <p>Mengetahui,</p>
        <p>Kepala Tata Usaha</p>
        <br><br><br>
        <p><strong>{{ Auth::user()->name }}</strong></p>
    </div>

</body>
</html>
