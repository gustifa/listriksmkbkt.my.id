<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label - {{ $inventory->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Kartu Label */
        .label-card {
            background: white;
            width: 300px;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .school-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            display: block;
        }

        .item-name {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            line-height: 1.2;
        }

        .qr-code {
            margin: 10px 0;
        }

        .item-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: bold;
            background: #eee;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .item-room {
            margin-top: 10px;
            font-size: 12px;
            color: #555;
        }

        /* Mode Cetak */
        @media print {
            body {
                background: white;
                height: auto;
                display: block;
            }
            .label-card {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
                width: 100%;
                max-width: 8cm; /* Lebar standar stiker */
                border: 1px solid #000;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-card">
        <span class="school-name">{{ $schoolName }}</span>

        <div class="item-name">{{ $inventory->name }}</div>

        <div class="qr-code">
            <!-- Generate QR Code dari Kode Barang -->
            {{-- Menggunakan format SVG agar tajam saat diprint --}}
            {!! QrCode::size(120)->generate($inventory->code) !!}
        </div>

        <div class="item-code">{{ $inventory->code }}</div>

        <div class="item-room">
            Lokasi: <strong>{{ $inventory->room->name ?? 'Gudang Utama' }}</strong>
            <br>
            Tgl: {{ \Carbon\Carbon::parse($inventory->purchase_date)->format('d/m/Y') }}
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; text-align: center; width: 100%;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Cetak Label</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Tutup</button>
    </div>

</body>
</html>
