<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REKAPITULASI PENYERAHAN TUGAS SISWA</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; }
        .title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 10pt; margin-bottom: 15px; }
        .meta-info { margin-bottom: 10px; width: 100%; }
        .meta-info td { font-size: 9pt; padding: 2px 0; }
        
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        table.data th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-left { text-align: left !important; }
        .vertical-text {
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            height: 90px;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="title">REKAPITULASI PENYERAHAN TUGAS SISWA</div>
    @if($activeAcademicYear)
        <div class="subtitle">Tahun Ajaran: {{ $activeAcademicYear->year }} (Semester {{ $activeAcademicYear->semester }})</div>
    @endif

    <table class="meta-info">
        <tr>
            <td style="width: 15%;"><strong>Kelas</strong></td>
            <td style="width: 35%;">: {{ $classroom->name ?? '-' }}</td>
            <td style="width: 15%;"><strong>Mata Pelajaran</strong></td>
            <td style="width: 35%;">: {{ $subject->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">NO</th>
                <th rowspan="2" style="width: 80px;">NISN / INDUK</th>
                <th rowspan="2">NAMA PESERTA DIDIK</th>
                
                @forelse($assignments as $assignment)
                    <th class="vertical-text">
                        [{{ $assignment->learningObjective->code ?? 'TP' }}] {{ $assignment->title }}
                    </th>
                @empty
                    <th rowspan="2">Belum ada tugas</th>
                @endforelse

                <th colspan="3">REKAP STATUS</th>
            </tr>
            <tr>
                @foreach($assignments as $index => $assignment)
                    <th style="font-size: 8pt;">{{ $index + 1 }}</th>
                @endforeach
                <th style="width: 25px; color: green;">H</th>
                <th style="width: 25px; color: orange;">T</th>
                <th style="width: 25px; color: red;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['nisn'] }}</td>
                <td class="text-left">{{ $row['nama'] }}</td>

                @foreach($assignments as $assignment)
                    @php $status = $row['tugas'][$assignment->id] ?? 'A'; @endphp
                    <td>
                        @if($status == 'H')
                            <strong style="color: green;">✓</strong>
                        @elseif($status == 'T')
                            <strong style="color: orange;">✓(T)</strong>
                        @else
                            <strong style="color: red;">X</strong>
                        @endif
                    </td>
                @endforeach

                <td><strong>{{ $row['total_h'] }}</strong></td>
                <td><strong>{{ $row['total_t'] }}</strong></td>
                <td><strong>{{ $row['total_a'] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>