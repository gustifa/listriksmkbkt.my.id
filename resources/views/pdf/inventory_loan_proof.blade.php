<!DOCTYPE html>
<html>
<head>
    <title>Bukti Peminjaman - {{ $loan->borrower_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; }

        /* Kop Surat Sederhana */
        .header-table { width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 5px; }
        .logo-img { width: 60px; height: auto; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 9pt; }

        .title { text-align: center; font-weight: bold; text-decoration: underline; font-size: 12pt; margin-bottom: 20px; text-transform: uppercase; }

        /* Tabel Detail */
        .info-table { width: 100%; margin-bottom: 10px; }
        .info-table td { padding: 3px; vertical-align: top; }
        .label { width: 140px; font-weight: bold; }

        /* Tabel Barang */
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 5px; text-align: left; }
        .item-table th { background-color: #f0f0f0; text-align: center; }
        .text-center { text-align: center; }

        /* Tanda Tangan */
        .footer-section { width: 100%; margin-top: 30px; }
        .ttd-box { width: 35%; text-align: center; float: left; }
        .ttd-box-right { width: 35%; text-align: center; float: right; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                <h1>{{ $school['school_name'] }}</h1>
                <p>{{ $school['school_address'] }}</p>
                <p>Telp: {{ $school['school_phone'] }}</p>
            </td>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <div class="title">BUKTI PEMINJAMAN ALAT / BAHAN</div>

    <table class="info-table">
        <tr>
            <td class="label">ID Transaksi</td>
            <td>: #{{ substr($loan->id, 0, 8) }}</td>
        </tr>
        <tr>
            <td class="label">Nama Peminjam</td>
            <td>: {{ $loan->borrower_name }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pinjam</td>
            <td>: {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td>:
                @if($loan->status == 'dipinjam')
                    <span style="color: red; font-weight: bold;">BELUM KEMBALI</span>
                @else
                    <span style="color: green; font-weight: bold;">SUDAH KEMBALI</span>
                    <small>({{ \Carbon\Carbon::parse($loan->return_date)->translatedFormat('d/m/Y H:i') }})</small>
                @endif
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang / Alat</th>
                <th>Lokasi / Bengkel</th>
                <th width="15%">Jumlah</th>
                <th>Kondisi / Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong>{{ $loan->inventory->name ?? 'Item Dihapus' }}</strong><br>
                    <small>Kode: {{ $loan->inventory->code ?? '-' }}</small>
                </td>
                <td>{{ $loan->inventory->room->name ?? '-' }}</td>
                <td class="text-center">{{ $loan->quantity }} {{ $loan->inventory->unit ?? 'Unit' }}</td>
                <td>{{ $loan->notes ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-section">
        <div class="ttd-box">
            <p>Peminjam,</p>
            <br>
            <p style="text-decoration: underline; font-weight: bold;">{{ $loan->borrower_name }}</p>
        </div>

        <div class="ttd-box-right">
            <p>{{ $school['sign_city'] }}, {{ date('d F Y') }}</p>
            <p>Petugas Bengkel,</p>
            <br>
            <p style="text-decoration: underline; font-weight: bold;">( .................................... )</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
