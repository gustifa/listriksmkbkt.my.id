<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KIR - {{ $room->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h3, .header h4 { margin: 2px 0; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .data-table th { background-color: #e0e0e0; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .signature-table { width: 100%; margin-top: 30px; }
        .signature-table td { text-align: center; vertical-align: top; }
    </style>
</head>
<body>

<div class="header">
    <h3>DINAS PEDIDIKAN PROVINSI SUMATERA BARAT</h3>
    <h4>KARTU INVENTARIS RUANGAN (KIR)</h4>
    <p style="margin:0;">Tahun Ajaran: {{ date('Y') }}/{{ date('Y')+1 }}</p>
</div>

<table class="info-table">
    <tr>
        <td width="15%"><strong>Nama Ruang/Bengkel</strong></td>
        <td width="35%">: {{ $room->name }}</td>
        <td width="15%"><strong>Lokasi Gedung</strong></td>
        <td width="35%">: {{ $room->location ?? 'Gedung Utama' }}</td>
    </tr>
    <tr>
        <td><strong>Kode Ruangan</strong></td>
        <td>: {{ $room->code }}</td>
        <td><strong>Kapasitas</strong></td>
        <td>: {{ $room->capacity ?? '-' }} Siswa</td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="12%">Kode Barang</th>
            <th>Nama Alat / Bahan</th>
            <th width="12%">Merk / Tipe</th>
            <th width="8%">Jenis</th>
            <th width="8%">Jumlah</th>
            <th width="10%">Kondisi</th>
            <th width="10%">Sumber Dana</th>
        </tr>
    </thead>
    <tbody>
        @forelse($inventories as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-center">{{ $item->code }}</td>
            <td><strong>{{ $item->name }}</strong></td>
            <td>{{ $item->brand_model ?? '-' }}</td>
            <td class="text-center">{{ strtoupper($item->type) }}</td>
            <td class="text-center">{{ $item->quantity }} {{ $item->unit }}</td>
            <td class="text-center">{{ ucfirst(str_replace('_', ' ', $item->condition)) }}</td>
            <td class="text-center">{{ $item->funding_source }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Belum ada barang terdaftar di ruangan ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td width="50%">
            Mengetahui,<br>
            <strong>Kepala Program Keahlian (Kaproka)</strong>
            <br><br><br><br>
            ( ________________________ )<br>
            NIP.
        </td>
        <td width="50%">
            Padang/Bukittinggi, {{ date('d F Y') }}<br>
            <strong>Kepala Bengkel / Penanggung Jawab</strong>
            <br><br><br><br>
            ( ________________________ )<br>
            NIP.
        </td>
    </tr>
</table>

</body>
</html>