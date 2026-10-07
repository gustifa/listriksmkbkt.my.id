<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Izin Keluar Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .header h3 { margin: 0; text-transform: uppercase; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #333; }
        th { background-color: #f2f2f2; padding: 6px; text-align: center; font-size: 10px; }
        td { padding: 5px 6px; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h3>{{ $school['name'] }}</h3>
        <p>{{ $school['address'] }} | Telp: {{ $school['phone'] }}</p>
        <h4 style="margin: 10px 0 0 0;">LAPORAN REKAP IZIN KELUAR SEKOLAH</h4>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @if($selectedClass)
            <p><strong>Kelas: {{ $selectedClass->name }}</strong></p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 70px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 70px;">Kelas</th>
                <th style="width: 60px;">Jam Keluar</th>
                <th style="width: 60px;">Jam Kembali</th>
                <th>Alasan</th>
                <th style="width: 60px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($permissions as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $p->student->nis ?? '-' }}</td>
                    <td>{{ $p->student->name ?? '-' }}</td>
                    <td class="text-center">{{ $p->student->classroom->name ?? '-' }}</td>
                    <td class="text-center">{{ $p->time_out }}</td>
                    <td class="text-center">{{ $p->time_back ?? '-' }}</td>
                    <td>{{ $p->reason }}</td>
                    <td class="text-center">{{ $p->time_back ? 'Selesai' : 'Di Luar' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data izin siswa pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ $school['sign_city'] }}, {{ date('d F Y') }}</p>
        <p>{{ $school['sign_title'] }}</p>
        <br><br><br>
        <p><strong>{{ $school['sign_name'] }}</strong></p>
        <p>NIP. {{ $school['sign_nip'] }}</p>
    </div>

</body>
</html>
