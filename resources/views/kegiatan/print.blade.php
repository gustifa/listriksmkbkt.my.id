<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir - {{ $kegiatan->nama_kegiatan }}</title>
    <style>
        /* Styling khusus agar rapi saat dicetak */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: black;
            background: white;
            margin: 0;
            padding: 20px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .info-kegiatan {
            margin-bottom: 20px;
        }
        .info-kegiatan table {
            width: 100%;
        }
        .info-kegiatan td {
            padding: 3px 0;
        }
        table.tabel-absensi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.tabel-absensi th, table.tabel-absensi td {
            border: 1px solid black;
            padding: 8px 10px;
            vertical-align: middle;
        }
        table.tabel-absensi th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .ttd-container {
            height: 80px;
            display: flex;
            align-items: center;
        }
        .img-ttd {
            max-height: 50px;
            max-width: 120px;
        }
        
        /* Instruksi CSS ketika diprint (CTRL+P) */
        @media print {
            body { padding: 0; }
            button { display: none; } /* Sembunyikan tombol cetak */
            @page { margin: 2cm; } /* Margin kertas */
        }
        
        .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>

    <!-- Tombol cetak yang akan hilang saat masuk mode print -->
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen</button>

    <div class="kop-surat">
        <h2>NAMA SEKOLAH / INSTANSI ANDA</h2>
        <p style="margin: 0;">Jalan Contoh Alamat No. 123, Kota, Provinsi, 12345</p>
        <p style="margin: 0;">Telp: (021) 1234567 | Email: info@sekolah.sch.id</p>
    </div>

    <div class="judul">
        DAFTAR HADIR PESERTA
    </div>

    <div class="info-kegiatan">
        <table border="0" style="width: 50%;">
            <tr>
                <td width="35%"><strong>Nama Kegiatan</strong></td>
                <td width="5%">:</td>
                <td width="60%">{{ $kegiatan->nama_kegiatan }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="tabel-absensi">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Lengkap</th>
                <th width="15%">Peran</th>
                <th width="20%">Waktu Hadir</th>
                <th width="30%">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $index => $absen)
                @php
                    $signaturePath = null;
                    if($absen->user->jenis_user === 'guru' && $absen->user->teacher) {
                        $signaturePath = $absen->user->teacher->signature;
                    } elseif($absen->user->jenis_user === 'siswa' && $absen->user->student) {
                        $signaturePath = $absen->user->student->signature;
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $absen->user->name }}</td>
                    <td style="text-align: center; text-transform: capitalize;">{{ $absen->user->jenis_user }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($absen->waktu_hadir)->format('H:i:s') }}</td>
                    <td>
                        <div class="ttd-container" style="justify-content: {{ ($index % 2 == 0) ? 'flex-start' : 'flex-end' }};">
                            <!-- Letak TTD zigzag seperti daftar hadir manual -->
                            <span style="font-size: 14px; color: #000000; margin-right: 5px;">{{ $index + 1 }}.</span>
                            
                            @if($signaturePath)
                                <img src="{{ asset('storage/' . $signaturePath) }}" alt="TTD" class="img-ttd">
                            @else
                                <span style="color: red; font-size: 10px;">(Belum set TTD)</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Belum ada peserta yang hadir.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; padding-right: 50px;">
        <p>Mengetahui,</p>
        <br><br><br>
        <p><strong>Ketua Panitia / Penanggung Jawab</strong></p>
    </div>

</body>
</html>