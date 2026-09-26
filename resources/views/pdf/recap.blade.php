<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi</title>
    <style>
        /* Mengatur Margin Halaman secara Dinamis dari Database */
        @page {
            margin-top: {{ $school['margin_top'] ?? '2cm' }};
            margin-right: {{ $school['margin_right'] ?? '2cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2cm' }};
            margin-left: {{ $school['margin_left'] ?? '2cm' }};
        }
        body { font-family: sans-serif; font-size: 12px; }

        /* Layout Kop Surat menggunakan Tabel agar rapi di PDF */
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }

        /* Logo harus menggunakan public_path agar terbaca oleh DOMPDF */
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 2px; color: #555; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .data-table th { background-color: #f0f0f0; }
        
        .footer { text-align: right; margin-top: 20px; font-size: 10px; color: #777; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 10px; }
        .bg-green { background-color: green; }
        .bg-red { background-color: red; }
        .bg-yellow { background-color: orange; }
    </style>
</head>
<body>
    <!-- KOP SURAT DINAMIS -->
    <table class="header-table">
        <tr>
            <!-- LOGO KIRI -->
            <td width="15%" class="text-center">
                @if(isset($school['logo_left']) && $school['logo_left'])
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>

            <!-- TEKS TENGAH (IDENTITAS SEKOLAH) -->
            <td width="70%" class="school-info">
                <h1>{{ $school['name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['address'] ?? 'Alamat sekolah belum diatur di menu pengaturan.' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
                <p>Website: {{ $school['web'] ?? '-' }}</p>
            </td>

            <!-- LOGO KANAN -->
            <td width="15%" class="text-center">
                @if(isset($school['logo_right']) && $school['logo_right'])
                    <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>


    <div class="header">
        <h2>LAPORAN REKAPITULASI ABSENSI</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Nama Siswa</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ $student->name }}</td>
            <td width="15%"><strong>NIS/NISN</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ $student->nis ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>:</td>
            <!-- FIX: Gunakan optional() agar tidak error jika siswa belum punya kelas -->
            <td>{{ optional($student->classroom)->name ?? 'Belum ada kelas' }}</td>
            <td><strong>Periode</strong></td>
            <td>:</td>
            <td>{{ $periodAwal }} s/d {{ $periodAkhir }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="15%">Hari</th>
                <th width="15%">Jam Masuk</th>
                <th width="20%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $att)
            @php
                $color = 'bg-green';
                if($att->status == 'sakit' || $att->status == 'izin') $color = 'bg-yellow';
                if($att->status == 'alpa' || $att->status == 'terlambat') $color = 'bg-red';
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($att->created_at)->format('d-m-Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($att->created_at)->translatedFormat('l') }}</td>
                <td>{{ \Carbon\Carbon::parse($att->created_at)->format('H:i') }}</td>
                <td><span class="badge {{ $color }}">{{ strtoupper($att->status) }}</span></td>
                <td>{{ $att->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh WhatsApp Bot pada: {{ date('d-m-Y H:i:s') }}
    </div>

</body>
</html>