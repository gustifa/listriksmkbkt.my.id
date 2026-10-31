<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Izin - {{ optional($izin->student)->name }}</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 40px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h3, .kop h4 { margin: 0; }
        .content { margin-top: 20px; }
        .table-detail { margin: 15px 0 15px 30px; }
        .table-detail td { padding: 3px 10px; }
        .ttd { margin-top: 40px; float: right; text-align: center; width: 250px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer;">🖨️ Cetak Surat</button>
    </div>

    <div class="kop">
        <h3>SMK NEGERI 1 BUKITTINGGI</h3>
        <h4>SURAT KETERANGAN IZIN TIDAK MASUK SEKOALH</h4>
        <small>Jl. Tanku Nan Renceh, Bukittinggi, Sumatra Barat</small>
    </div>

    <div class="content">
        <p>Bukittinggi, {{ \Carbon\Carbon::parse($izin->created_at)->translatedFormat('d F Y') }}</p>
        <p>Kepada Yth.<br>Bapak/Ibu Wali Kelas / Guru Piket<br>SMKN 1 Bukittinggi<br>di Tempat</p>

        <p>Dengan hormat,</p>
        <p>Dengan ini kami beritahukan bahwa siswa berikut:</p>

        <table class="table-detail">
            <tr>
                <td><strong>Nama Siswa</strong></td>
                <td>:</td>
                <td>{{ optional($izin->student)->name }}</td>
            </tr>
            <tr>
                <td><strong>NISN / NIS</strong></td>
                <td>:</td>
                <td>{{ optional($izin->student)->nisn ?? optional($izin->student)->nis }}</td>
            </tr>
            <tr>
                <td><strong>Kelas / Jurusan</strong></td>
                <td>:</td>
                <td>{{ optional($izin->student->classroom)->name }}</td>
            </tr>
        </table>

        <p>Tidak dapat mengikuti kegiatan pembelajaran pada tanggal <strong>{{ \Carbon\Carbon::parse($izin->date)->translatedFormat('d F Y') }}</strong> dikarenakan: <em>{{ $izin->reason }}</em>.</p>

        <p>Demikian surat permohonan izin ini kami sampaikan. Atas perhatian dan pengertian Bapak/Ibu, kami ucapkan terima kasih.</p>
    </div>

    <div class="ttd">
        <p>Hormat kami,<br>Orang Tua / Wali Siswa</p>
        <br><br><br>
        <p>__________________________</p>
    </div>

</body>
</html>