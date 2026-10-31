<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Sakit - {{ optional($izin->student)->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.4; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .photo-box { text-align: center; margin: 20px 0; }
        .photo-box img { max-width: 80%; max-height: 500px; border: 1px solid #ccc; padding: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">🖨️ Cetak Dokumen</button>
    </div>

    <div class="header">
        <h2>SMK NEGERI 1 BUKITTINGGI</h2>
        <h3>LAMPIRAN SURAT KETERANGAN SAKIT SISWA</h3>
    </div>

    <table>
        <tr>
            <td width="130"><strong>Nama Siswa</strong></td>
            <td>: {{ optional($izin->student)->name }}</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>: {{ optional($izin->student->classroom)->name }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Sakit</strong></td>
            <td>: {{ \Carbon\Carbon::parse($izin->date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Keterangan</strong></td>
            <td>: {{ $izin->reason }}</td>
        </tr>
    </table>

    <hr style="margin-top: 15px;">

    <div class="photo-box">
        <h4>Dokumen / Surat Dokter yang Diunggah:</h4>
        @if($izin->attachment_path && Storage::disk('public')->exists($izin->attachment_path))
            <img src="{{ asset('storage/' . $izin->attachment_path) }}" alt="Surat Dokter">
        @else
            <p style="color: red; font-style: italic;">(Siswa tidak melampirkan foto surat dokter)</p>
        @endif
    </div>

</body>
</html>