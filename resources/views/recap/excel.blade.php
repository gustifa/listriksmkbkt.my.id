<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        .title { font-size: 16pt; font-weight: bold; text-align: center; }
        .subtitle { font-size: 11pt; text-align: center; }
        .header-bg { background-color: #d9edf7; font-weight: bold; text-align: center; vertical-align: middle; }
        .border-all { border: 1px solid #000000; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
    </style>
</head>
<body>

    <table>
        <!-- Judul Laporan -->
        <tr>
            <td colspan="{{ count($assignments) + 6 }}" class="title">REKAPITULASI PENYERAHAN TUGAS SISWA</td>
        </tr>
        @if($activeAcademicYear)
        <tr>
            <td colspan="{{ count($assignments) + 6 }}" class="subtitle">Tahun Ajaran: {{ $activeAcademicYear->year }} (Semester {{ $activeAcademicYear->semester }})</td>
        </tr>
        @endif
        <tr></tr>
        <tr>
            <td colspan="2"><b>Kelas:</b> {{ $classroom->name ?? '-' }}</td>
            <td colspan="4"><b>Mata Pelajaran:</b> {{ $subject->name ?? '-' }}</td>
        </tr>
        <tr></tr>

        <!-- Table Header (Border Lengkap & Judul Kolom) -->
        <thead>
            <tr>
                <th rowspan="2" class="header-bg border-all">NO</th>
                <th rowspan="2" class="header-bg border-all">INDUK SISWA</th>
                <th rowspan="2" class="header-bg border-all">NAMA PESERTA DIDIK</th>
                
                @foreach($assignments as $assignment)
                    <th class="header-bg border-all">
                        ({{ $assignment->learningObjective->code ?? 'TP' }}) {{ $assignment->title }}
                    </th>
                @endforeach

                <th colspan="3" class="header-bg border-all">JML TDK KUMPUL / STATUS</th>
            </tr>
            <tr>
                @foreach($assignments as $index => $assignment)
                    <th class="header-bg border-all">{{ $index + 1 }}</th>
                @endforeach
                <th class="header-bg border-all" style="color: green;">H</th>
                <th class="header-bg border-all" style="color: orange;">T</th>
                <th class="header-bg border-all" style="color: red;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $row)
            <tr>
                <td class="border-all text-center">{{ $index + 1 }}</td>
                <td class="border-all text-center">'{{ $row['nisn'] }}</td>
                <td class="border-all text-left"><b>{{ $row['nama'] }}</b></td>

                @foreach($assignments as $assignment)
                    @php $status = $row['tugas'][$assignment->id] ?? 'A'; @endphp
                    <td class="border-all text-center">
                        @if($status == 'H')
                            ✓ (Hadir)
                        @elseif($status == 'T')
                            ✓ (Terlambat)
                        @else
                            X (Alpa)
                        @endif
                    </td>
                @endforeach

                <td class="border-all text-center"><b>{{ $row['total_h'] }}</b></td>
                <td class="border-all text-center"><b>{{ $row['total_t'] }}</b></td>
                <td class="border-all text-center"><b>{{ $row['total_a'] }}</b></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>