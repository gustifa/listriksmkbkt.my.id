<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    <title>Surat Izin Keluar - {{ $permit->student->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .border-box { border: 2px solid #000; padding: 20px; width: 14cm; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        h3 { margin: 0; text-transform: uppercase; }
        table { width: 100%; margin-bottom: 10px; }
        td { padding: 5px; vertical-align: top; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="border-box">
        <div class="header">
            <h3>SURAT IZIN MENINGGALKAN SEKOLAH</h3>
            <small>{{$school['name'] ?? 'SMK GATECH'}}</small>
        </div>

        <table>
            <tr><td width="100">Nama</td><td>: <b>{{ $permit->student->name }}</b></td></tr>
            <tr><td>Kelas</td><td>: {{ $permit->student->classroom->name ?? '-' }}</td></tr>
            <tr><td>NIS</td><td>: {{ $permit->student->nis }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($permit->date)->translatedFormat('l, d F Y') }}</td></tr>
            <tr><td>Jam Keluar</td><td>: {{ $permit->time_out }} WIB</td></tr>
            <tr><td>Keperluan</td><td>: {{ $permit->reason }}</td></tr>
        </table>

        <p style="text-align: center; margin-top: 30px;">
            Mengetahui,<br>Guru Piket / Satpam
            <br><br><br><br>
            ( ............................. )
        </p>

        <div style="font-size: 10px; text-align: right; margin-top: 10px;">
            Dicetak otomatis oleh Sistem Barcode
        </div>
    </div>
</body>
</html>
