<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .filter-container { margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        .filter-container select, .filter-container input, .filter-container button { padding: 5px; margin-right: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }

        /* Pewarnaan Kolom PG dan SG */
        .bg-pg {
            background-color: #e3f2fd !important; /* Biru Muda untuk PG */
        }
        .bg-sg {
            background-color: #e8f5e9 !important; /* Hijau Muda untuk SG */
        }

        /* Efek Baris Zebra (Selang-seling) */
        tbody tr:nth-child(even) td {
            background-color: #f5f5f5;
        }
        /* Pastikan efek zebra tidak menimpa warna kolom PG/SG */
        tbody tr:nth-child(even) td.bg-pg {
            background-color: #bbdefb !important; /* Biru yang sedikit lebih gelap untuk baris genap PG */
        }
        tbody tr:nth-child(even) td.bg-sg {
            background-color: #c8e6c9 !important; /* Hijau yang sedikit lebih gelap untuk baris genap SG */
        }

        /* Tombol Cetak */
        .btn-cetak {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }
        .btn-cetak:hover {
            background-color: #45a049;
        }

        /* Aturan Print */
        @media print {
            .no-print { display: none !important; }
            /* Memaksa browser untuk mencetak warna background (zebra, warna PG & SG) */
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="filter-container no-print">
        <form method="GET" action="{{ route('laporan.kegiatan') }}">
            <label for="classroom_id">Pilih Kelas:</label>
            <select name="classroom_id" id="classroom_id" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $kelas)
                    <option value="{{ $kelas->id }}" {{ $classroomId == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->name }}
                    </option>
                @endforeach
            </select>

            <label for="start_date">Pilih Tanggal (Otomatis menyesuaikan ke Senin):</label>
            <input type="date" name="start_date" id="start_date" value="{{ $inputDate }}" required>

            <button type="submit">Tampilkan Laporan</button>
        </form>
    </div>

    @if($selectedClassroom)
        <div class="no-print" style="text-align: right; margin-bottom: 15px;">
            <button onclick="window.print()" class="btn-cetak">🖨️ Cetak Laporan</button>
        </div>

        <h3 style="text-align: center;">LAPORAN KEGIATAN SISWA KELAS {{ strtoupper($selectedClassroom->name) }}</h3>

        <table>
            <thead>
                <tr>
                    <th rowspan="3" style="width: 30px;">NO</th>
                    <th rowspan="3" style="width: 250px;">NAMA</th>
                    <th rowspan="3" style="width: 100px;">KELAS</th>
                    @foreach($days as $day)
                        <th colspan="2">{{ strtoupper($day->translatedFormat('l')) }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($days as $day)
                        <th colspan="2">{{ $day->translatedFormat('d M y') }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($days as $day)
                        <th class="bg-pg">PG</th>
                        <th class="bg-sg">SG</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $student->name }}</td>
                        <td>{{ $student->classroom->name ?? '-' }}</td>

                        @foreach($days as $day)
                            @php
                                $attendance = $student->dailyAttendances
                                    ->where('date', $day->format('Y-m-d'))
                                    ->first();

                                $pg = ($attendance && $attendance->arrival_time)
                                    ? \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i')
                                    : '';

                                $sg = ($attendance && $attendance->departure_time)
                                    ? \Carbon\Carbon::parse($attendance->departure_time)->format('H:i')
                                    : '';
                            @endphp

                            <td class="bg-pg">{{ $pg }}</td>
                            <td class="bg-sg">{{ $sg }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">Tidak ada data siswa di kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <p class="no-print" style="text-align: center; color: #666;">Silakan pilih kelas dan tanggal terlebih dahulu untuk melihat laporan.</p>
    @endif

</body>
</html>
