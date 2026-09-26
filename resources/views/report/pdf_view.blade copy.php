<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }

        /* Kop Surat */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #555; }

        /* Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #eee; }

        /* Utility */
        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 10px; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{$school['name']}}</h1>
        <p>Address: {{$school['address']}} | Telp: {{$school['phone']}}</p>
        <p>Website: {{$school['web']}} | Email: {{$school['email']}}</p>
    </div>

    <h3 class="text-center">LAPORAN ABSENSI SISWA</h3>
<h4 class="text-center" style="margin-top: 0; font-weight: normal;">{{ $labelPeriode }}</h4>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                <td>{{ $row->check_in_time }}</td>
                <td>{{ $row->student->nis }}</td>
                <td>{{ $row->student->name }}</td>
                <td>{{ $row->student->classroom->name }}</td>
                <td>{{ $row->schedule->subject_name ?? '-' }}</td>
                <td class="text-center">
                    @if($row->status == 'hadir')
                        <span class="badge bg-hadir">Hadir</span>
                    @else
                        <span class="badge bg-terlambat">Terlambat</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; float: right; width: 200px; text-align: center;">
        <p>Jakarta, {{ date('d M Y') }}</p>
        <br><br><br>
        <p><strong>{{ Auth::user()->name ?? 'Administrator' }}</strong></p>
        <p>Kepala Tata Usaha</p>
    </div>

</body>
</html>
