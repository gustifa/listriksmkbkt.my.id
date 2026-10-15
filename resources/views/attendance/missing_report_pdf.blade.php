<!DOCTYPE html>
<html>
<head>
    <title>Laporan Monitoring Siswa Belum Absen</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h3 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0; color: #555; }
        .section-title { background: #f2f2f2; padding: 6px 10px; font-weight: bold; margin-top: 15px; margin-bottom: 8px; border-left: 4px solid #0d6efd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-center { text-align: center; }
        .badge-danger { color: #dc3545; font-weight: bold; }
        .badge-warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h3>Laporan Monitoring Siswa Belum Presensi</h3>
        <p>Tanggal: {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- SECTION 1: BELUM MASUK -->
    <div class="section-title">1. SISWA BELUM ABSEN MASUK GERBANG ({{ count($noArrivalStudents) }} Siswa)</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">NIS</th>
                <th>Nama Siswa</th>
                <th width="25%">Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($noArrivalStudents as $idx => $s)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $s->nis ?? '-' }}</td>
                    <td><strong>{{ $s->name }}</strong></td>
                    <td>{{ $s->classroom->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Semua siswa sudah absen masuk.</td></tr>
            @empty
            @endforelse
        </tbody>
    </table>

    <!-- SECTION 2: BELUM PULANG -->
    <div class="section-title">2. SISWA BELUM ABSEN PULANG GERBANG ({{ count($noDepartureStudents) }} Siswa)</div>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">NIS</th>
                <th>Nama Siswa</th>
                <th width="25%">Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($noDepartureStudents as $idx => $s)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $s->nis ?? '-' }}</td>
                    <td><strong>{{ $s->name }}</strong></td>
                    <td>{{ $s->classroom->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Tidak ada siswa tertinggal/belum absen pulang.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- SECTION 3: BELUM PRESENSI MAPEL -->
    <div class="section-title">3. SISWA BELUM PRESENSI MATA PELAJARAN</div>
    @forelse($missingSubjectStudents as $item)
        <p style="margin-bottom: 4px; font-weight: bold;">
            Mapel: {{ $item['schedule']->subject->name ?? '-' }} ({{ $item['schedule']->classroom->name ?? '-' }}) - Guru: {{ $item['schedule']->teacher->name ?? '-' }}
        </p>
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="20%">NIS</th>
                    <th>Nama Siswa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item['students'] as $idx => $s)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $s->nis ?? '-' }}</td>
                        <td><strong>{{ $s->name }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="text-center" style="color: #666;">Tidak ada siswa yang terlewat presensi mata pelajaran.</p>
    @endforelse

</body>
</html>