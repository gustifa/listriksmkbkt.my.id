<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Peminjaman (BAST)</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h3, .header h4 { margin: 2px; text-transform: uppercase; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 20px; font-size: 14pt; }
        table.meta { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        table.meta td { padding: 4px 0; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; }
        table.data th { background-color: #f2f2f2; text-align: center; }
        .ttd-table { width: 100%; margin-top: 40px; }
        .ttd-table td { text-align: center; vertical-align: top; width: 50%; }
    </style>
</head>
<body>

<div class="header">
    <h3>DINAS PENDIDIKAN PROVINSI SUMATERA BARAT</h3>
    <h4>BERITA ACARA SERAH TERIMA (BAST) PEMINJAMAN ALAT & BAHAN</h4>
</div>

<div class="title">LEMBAR BUKTI PEMINJAMAN PRAKTIKUM</div>

<p>Pada hari ini <strong>{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</strong>, telah diserahterimakan alat dan bahan praktikum kepada siswa berikut:</p>

<table class="meta">
    <tr>
        <td width="25%"><strong>Nama Siswa</strong></td>
        <td width="75%">: {{ $student->name }}</td>
    </tr>
    <tr>
        <td><strong>NIS / NISN</strong></td>
        <td>: {{ $student->nis ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Catatan / Modul</strong></td>
        <td>: {{ $loans->first()->notes ?? 'Praktikum Reguler' }}</td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Nama Alat / Bahan</th>
            <th width="15%">Ruang Bengkel</th>
            <th width="12%">Jenis</th>
            <th width="12%">Jumlah</th>
            <th width="15%">Status Transaksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $index => $loan)
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td><strong>{{ $loan->inventory->name }}</strong></td>
            <td>{{ $loan->inventory->room ? $loan->inventory->room->name : '-' }}</td>
            <td style="text-align: center;">{{ strtoupper($loan->inventory->type) }}</td>
            <td style="text-align: center;">{{ $loan->quantity }} {{ $loan->inventory->unit }}</td>
            <td style="text-align: center;">{{ ucfirst(str_replace('_', ' ', $loan->status)) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>Barang-barang tersebut di atas diserahterimakan dalam keadaan baik dan siap dipergunakan untuk kegiatan pembelajaran praktikum.</p>

<table class="ttd-table">
    <tr>
        <td>
            Penerima / Pemimpin Kelompok,<br><br><br><br><br>
            <strong>( {{ $student->name }} )</strong><br>
            NIS. {{ $student->nis ?? '........' }}
        </td>
        <td>
            Petugas Ruang Alat / Toolman,<br><br><br><br><br>
            <strong>( _______________________ )</strong><br>
            NIP.
        </td>
    </tr>
</table>

</body>
</html>