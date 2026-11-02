<table>
    <tr>
        <td colspan="{{ 4 + $internships->count() }}" style="font-weight: bold; text-align: center;">
            NILAI PRESENTASI LAPORAN ( UJIAN JURNAL ) PKL {{ $school['name'] }} TP. 2026/2027
        </td>
    </tr>
    <tr>
        <td colspan="2">Program Keahlian</td>
        <td>: {{ $internships->first()->student->classroom->major->name ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2">Nama Guru Penguji</td>
        <td>: {{ $teacher->name }}</td>
    </tr>
    <tr></tr>
    <!-- Header Tabel -->
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center;">No.</th>
            <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center;">ASPEK PENILAIAN</th>
            <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center;">SKOR MAKS</th>
            <th colspan="{{ $internships->count() }}" style="border: 1px solid #000; font-weight: bold; text-align: center;">SKOR PEROLEHAN</th>
        </tr>
        <tr>
            @foreach($internships as $item)
                <th style="border: 1px solid #000; text-align: center;">
                    NAMA: {{ $item->student->name }}<br>
                    NIS: {{ $item->student->nis }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <!-- 1. Laporan -->
        <tr>
            <td style="border: 1px solid #000; text-align: center;">1</td>
            <td colspan="{{ 2 + $internships->count() }}" style="border: 1px solid #000; font-weight: bold;">Laporan</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">1.1. Materi / Isi</td>
            <td style="border: 1px solid #000; text-align: center;">35</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_materi ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">1.2. Gambar</td>
            <td style="border: 1px solid #000; text-align: center;">10</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_gambar ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">1.3. Tata Bahasa / Sistematika Laporan</td>
            <td style="border: 1px solid #000; text-align: center;">10</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_sistematika ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">1.4. Tampilan Laporan</td>
            <td style="border: 1px solid #000; text-align: center;">5</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_tampilan ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: right;">Sub Total Skor</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center;">60</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; font-weight: bold; text-align: center;">{{ $item->exam->subtotal_laporan ?? 0 }}</td>
            @endforeach
        </tr>

        <!-- 2. Presentasi / Wawancara Laporan -->
        <tr>
            <td style="border: 1px solid #000; text-align: center;">2</td>
            <td colspan="{{ 2 + $internships->count() }}" style="border: 1px solid #000; font-weight: bold;">Presentasi / Wawancara laporan</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">2.1. Kerapian berpakaian</td>
            <td style="border: 1px solid #000; text-align: center;">5</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_pakaian ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">2.2. Bahasa Penyampaian</td>
            <td style="border: 1px solid #000; text-align: center;">10</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_bahasa ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">2.3. Penguasaan Materi</td>
            <td style="border: 1px solid #000; text-align: center;">15</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_penguasaan ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000;">2.4. Ketepatan Jawaban terhadap pertanyaan</td>
            <td style="border: 1px solid #000; text-align: center;">10</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; text-align: center;">{{ $item->exam->score_jawaban ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: right;">Sub Total Skor</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center;">40</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; font-weight: bold; text-align: center;">{{ $item->exam->subtotal_presentasi ?? 0 }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="border: 1px solid #000;"></td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: right;">Jumlah Total</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center;">100</td>
            @foreach($internships as $item)
                <td style="border: 1px solid #000; font-weight: bold; text-align: center;">{{ $item->exam->total_score ?? 0 }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

<!-- Tanda Tangan -->
<table>
    <tr></tr>
    <tr>
        <td colspan="2">Mengetahui :</td>
        <td colspan="{{ $internships->count() }}" style="text-align: right;">{{ $school['sign_city'] }}, ............................ 20...</td>
    </tr>
    <tr>
        <td colspan="2">Ketua PKL</td>
        <td colspan="{{ $internships->count() }}" style="text-align: right;">Guru Penguji</td>
    </tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td colspan="2" style="font-weight: bold;"><u>{{ $school['headmaster_name'] }}</u></td>
        <td colspan="{{ $internships->count() }}" style="text-align: right; font-weight: bold;"><u>{{ $teacher->name }}</u></td>
    </tr>
    <tr>
        <td colspan="2">NIP. {{ $school['headmaster_nip'] }}</td>
        <td colspan="{{ $internships->count() }}" style="text-align: right;">NIP. {{ $teacher->nip ?? '-' }}</td>
    </tr>
</table>