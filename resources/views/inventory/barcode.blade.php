<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label - {{ $inventory->name }} ({{ $printQty }}x)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eee;
            margin: 0;
            padding: 20px;
        }

        /* Container Grid untuk tampilan banyak label */
        .label-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            max-width: 210mm; /* Lebar A4 */
            margin: 0 auto;
        }

        /* Kartu Label */
        .label-card {
            background: white;
            padding: 15px;
            border: 2px solid #000;
            border-radius: 6px;
            text-align: center;
            box-sizing: border-box;
            page-break-inside: avoid; /* Mencegah label terpotong beda halaman */
        }

        .school-name {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            display: block;
        }

        .item-name {
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0;
            line-height: 1.2;
            height: 38px; /* Fixed height agar rapi */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .qr-code {
            margin: 8px 0;
        }

        .item-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: bold;
            background: #eee;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .item-room {
            margin-top: 8px;
            font-size: 11px;
            color: #555;
        }

        /* Mode Cetak */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .label-container {
                display: block; /* Fallback untuk browser lama */
                display: grid;
                grid-template-columns: repeat(3, 1fr); /* 3 Kolom di A4 */
                gap: 10px;
                width: 100%;
                max-width: none;
            }
            .no-print {
                display: none;
            }
            @page {
                margin: 1cm; /* Margin kertas printer */
                size: A4;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 0; left: 0; right: 0; background: #333; color: white; padding: 10px; text-align: center; z-index: 999;">
        <span>Mencetak <strong>{{ $printQty }}</strong> Label untuk <strong>{{ $inventory->name }}</strong></span>
        <button onclick="window.print()" style="margin-left: 15px; padding: 5px 15px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
        <button onclick="window.close()" style="margin-left: 5px; padding: 5px 15px; cursor: pointer;">Tutup</button>
    </div>

    <div style="height: 50px;" class="no-print"></div> <!-- Spacer untuk header -->

    <div class="label-container">
        @for($i = 0; $i < $printQty; $i++)
            <div class="label-card">
                <span class="school-name">{{ $schoolName }}</span>

                <div class="item-name">{{ $inventory->name }}</div>

                <div class="qr-code">
                    <!-- Generate QR Code -->
                    {!! QrCode::size(100)->generate($inventory->code) !!}
                </div>

                <div class="item-code">{{ $inventory->code }}</div>

                <div class="item-room">
                    Lokasi: <strong>{{ $inventory->room->name ?? 'Gudang' }}</strong><br>
                    Tgl: {{ $inventory->purchase_date ? \Carbon\Carbon::parse($inventory->purchase_date)->format('d/m/Y') : '-' }}
                    <span style="font-size: 9px; display: block; margin-top: 2px; color: #999;">Item {{ $i + 1 }} / {{ $printQty }}</span>
                </div>
            </div>
        @endfor
    </div>

    <script>
        // Auto print jika dibuka, delay sedikit agar gambar load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>

</body>
</html>
