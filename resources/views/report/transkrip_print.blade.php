<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $downloadTitle ?? 'Transkrip Absensi' }}</title>
    <style>
        /* Base Setup A4 */
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Container Siswa (Tiap Siswa 1 Halaman Rapi) */
        .student-page {
            page-break-after: always;
            box-sizing: border-box;
        }
        .student-page:last-child {
            page-break-after: avoid;
        }

        /* Header / Kop */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .header h3 { margin: 0; text-transform: uppercase; font-size: 11pt; }
        .header h2 { margin: 2px 0; text-transform: uppercase; font-size: 13pt; }
        .header p { margin: 2px 0; font-size: 8.5pt; }

        /* Data Siswa Table */
        .info-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .info-table td { padding: 2px 4px; vertical-align: top; font-size: 9pt; }
        .info-label { font-weight: bold; width: 100px; }

        /* Judul Seksi */
        .section-title {
            font-weight: bold;
            font-size: 9.5pt;
            margin-top: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
            page-break-inside: auto;
        }
        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: center;
        }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }
        table.data-table td.text-left { text-align: left; }

        /* Footer Tanda Tangan Locked Block */
        .footer-signature {
            margin-top: 15px;
            page-break-inside: avoid !important; /* Mencegah tanda tangan terpotong halaman */
            break-inside: avoid !important;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 9pt;
        }
        .signature-table td {
            vertical-align: top;
            padding: 2px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak / Kembali -->
    <div class="no-print" style="margin: 15px;">
        <button onclick="window.history.back()" style="padding: 8px 16px; cursor: pointer;">&larr; Kembali</button>
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer; font-weight: bold;">Cetak Document</button>
    </div>

    @foreach($reports as $report)
    @php
        $st = $report['student'];
        $gate = $report['gate'];
        $learning = $report['learning'];
        $prayer = $report['prayer'];
        $homeroomTeacher = $report['homeroomTeacher'];
        $counselingTeacher = $report['counselingTeacher'];
    @endphp

    <div class="student-page">
        <!-- KOP SURAT SEKOLAH -->
        <div class="header">
            <p><strong>{{ $settings['provinsi_name'] }}</strong></p>
            <h2>{{ $settings['school_name'] }}</h2>
            <p>{{ $settings['school_address'] }}</p>
            <p>Email: {{ $settings['school_email'] }} | Web: {{ $settings['school_web'] }}</p>
            <h3 style="margin-top: 8px;">TRANSKRIP REKAPITULASI KEHADIRAN SISWA</h3>
            <p>Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>
        </div>

        <!-- INFORMASI SISWA -->
        <table class="info-table">
            <tr>
                <td class="info-label">Nama Siswa</td>
                <td>: <strong>{{ $st->name }}</strong></td>
                <td class="info-label">Kelas</td>
                <td>: {{ $st->classroom->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">NIS / NISN</td>
                <td>: {{ $st->nis ?? '-' }} / {{ $st->nisn ?? '-' }}</td>
                <td class="info-label">Jenis Kelamin</td>
                <td>: {{ $st->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
        </table>

        <!-- BAGIAN 1: ABSENSI GERBANG -->
        @if($includeGate)
        <div class="section-title">I. REKAPITULASI ABSENSI GERBANG (DAILY ATTENDANCE)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Tanggal</th>
                    <th width="25%">Jam Datang</th>
                    <th width="25%">Jam Pulang</th>
                    <th width="25%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gate as $idx => $g)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($g->date)->format('d/m/Y') }}</td>
                    <td>{{ $g->arrival_time ?? '-' }}</td>
                    <td>{{ $g->departure_time ?? '-' }}</td>
                    <td>{{ $g->status }}</td>
                </tr>
                @empty
                <tr><td colspan="5">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        @endif

        <!-- BAGIAN 2: ABSENSI PEMBELAJARAN (KBM) -->
        @if($includeLearning)
        <div class="section-title">II. REKAPITULASI ABSENSI PEMBELAJARAN (KBM)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th class="text-left">Mata Pelajaran & Guru</th>
                    <th width="20%">Jam Absen</th>
                    <th width="20%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($learning as $idx => $l)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($l->date)->format('d/m/Y') }}</td>
                    <td class="text-left">
                        <strong>{{ $l->subject_name ?? ($l->schedule->subject->name ?? '-') }}</strong><br>
                        <small>Guru: {{ $l->teacher->name ?? ($l->schedule->teacher->name ?? '-') }}</small>
                    </td>
                    <td>{{ isset($l->check_in_time) && $l->check_in_time != '-' ? \Carbon\Carbon::parse($l->check_in_time)->format('H:i:s') : '-' }}</td>
                    <td>{{ $l->status }}</td>
                </tr>
                @empty
                <tr><td colspan="5">Tidak ada data KBM.</td></tr>
                @endforelse
            </tbody>
        </table>
        @endif

        <!-- BAGIAN 3: ABSENSI SHOLAT -->
        @if($includePrayer)
        <div class="section-title">III. REKAPITULASI ABSENSI SHOLAT & IBADAH</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Tanggal</th>
                    <th class="text-left">Nama Sholat / Ibadah</th>
                    <th width="25%">Jam Absen</th>
                    <th width="25%">Status Ibadah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prayer as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                    <td class="text-left">{{ $p->prayer_name ?? 'Sholat Ibadah Harian' }}</td>
                    <td>{{ isset($p->check_in_time) && $p->check_in_time != '-' ? \Carbon\Carbon::parse($p->check_in_time)->format('H:i:s') : '-' }}</td>
                    <td>{{ $p->status ?? 'Tanpa Keterangan' }}</td>
                </tr>
                @empty
                <tr><td colspan="5">Tidak ada data ibadah.</td></tr>
                @endforelse
            </tbody>
        </table>
        @endif

        <!-- FOOTER TANDA TANGAN (DIKUNCI AGAR TIDAK TERPOTONG) -->
        <div class="footer-signature">
            <table class="signature-table">
                <tr>
                    <td width="33%">
                        Mengetahui,<br>
                        <strong>Kepala Sekolah</strong>
                        <br><br><br><br>
                        <u><strong>{{ $settings['sign_name'] }}</strong></u><br>
                        NIP. {{ $settings['sign_nip'] }}
                    </td>
                    <td width="34%">
                        <br>
                        <strong>Guru Bimbingan Konseling (BK)</strong>
                        <br><br><br><br>
                        <u><strong>{{ $counselingTeacher->name ?? '........................' }}</strong></u><br>
                        NIP. {{ $counselingTeacher->nip ?? '........................' }}
                    </td>
                    <td width="33%">
                        {{ $settings['sign_city'] }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}<br>
                        <strong>Wali Kelas</strong>
                        <br><br><br><br>
                        <u><strong>{{ $homeroomTeacher->name ?? '........................' }}</strong></u><br>
                        NIP. {{ $homeroomTeacher->nip ?? '........................' }}
                    </td>
                </tr>
            </table>
        </div>

    </div>
    @endforeach

</body>
</html>