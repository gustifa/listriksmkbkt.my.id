<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Bukti Peminjaman - {{ $loan->inventory->code ?? 'LOAN' }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .ticket-wrapper {
            max-width: 400px;
            margin: 0 auto;
            border: 1px dashed #333;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        .table-info {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .table-info td {
            padding: 3px 0;
            vertical-align: top;
        }
        .table-info td.label {
            width: 40%;
            color: #444;
        }
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 11px;
        }
        .signature-box {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            text-align: center;
        }
        .signature-space {
            height: 45px;
        }
        @media print {
            body { padding: 0; }
            .ticket-wrapper { border: none; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 400px; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak Dokumen
        </button>
    </div>

    <div class="ticket-wrapper">
        <div class="header">
            <h3>BUKTI PEMINJAMAN ALAT & BAHAN</h3>
            <p>SMK NEGERI 1 BUKITTINGGI</p>
            <p>Bengkel & Sarana Prasarana Kejuruan</p>
        </div>

        <table class="table-info">
            <tr>
                <td class="label">ID Transaksi</td>
                <td>: <b>#{{ strtoupper(substr($loan->id, 0, 8)) }}</b></td>
            </tr>
            <tr>
                <td class="label">Tanggal Pinjam</td>
                <td>: {{ \Carbon\Carbon::parse($loan->borrow_date)->translatedFormat('d F Y H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="label">Peminjam</td>
                <td>: 
                    @if($loan->student)
                        <b>{{ $loan->student->name }}</b> (Siswa / {{ $loan->student->classroom->name ?? '-' }})
                    @elseif($loan->teacher)
                        <b>{{ $loan->teacher->name }}</b> (Guru/Instruktur)
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Bengkel / Ruangan</td>
                <td>: {{ $loan->inventory->room->name ?? '-' }}</td>
            </tr>
        </table>

        <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

        <table class="table-info">
            <tr>
                <td class="label">Kode Inventaris</td>
                <td>: <b>{{ $loan->inventory->code ?? '-' }}</b></td>
            </tr>
            <tr>
                <td class="label">Nama Barang</td>
                <td>: {{ $loan->inventory->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Pinjam</td>
                <td>: <b>{{ $loan->amount ?? 1 }} {{ $loan->inventory->unit ?? 'pcs' }}</b></td>
            </tr>
            <tr>
                <td class="label">Status Stok</td>
                <td>: {{ strtoupper($loan->status === 'borrowed' ? 'DIPINJAM' : 'DIKEMBALIKAN') }}</td>
            </tr>
            @if($loan->notes)
            <tr>
                <td class="label">Catatan / PBM</td>
                <td>: {{ $loan->notes }}</td>
            </tr>
            @endif
        </table>

        <div class="signature-box">
            <div>
                <p>Peminjam,</p>
                <div class="signature-space"></div>
                <p>( {{ $loan->student->name ?? ($loan->teacher->name ?? 'Peminjam') }} )</p>
            </div>
            <div>
                <p>Petugas Bengkel,</p>
                <div class="signature-space"></div>
                <p>( Toolman / Kabeng )</p>
            </div>
        </div>

        <div class="footer">
            <p>Simpan struk ini sebagai bukti resmi peminjaman dan tunjukkan saat pengembalian alat/bahan.</p>
            <p style="margin-top: 5px;"><i>Dicetak pada: {{ date('d-m-Y H:i:s') }}</i></p>
        </div>
    </div>

    <script>
        // Otomatis memicu dialog cetak saat halaman dibuka
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>