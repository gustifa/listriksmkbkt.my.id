<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }

        /* Area Filter & Tombol Cetak */
        .filter-container { margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        .filter-container select, .filter-container input, .filter-container button { padding: 5px; margin-right: 10px; }
        .btn-cetak { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; }
        .btn-cetak:hover { background-color: #45a049; }

        /* Tabel Utama */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        table.data-table th { background-color: #f2f2f2; }
        .text-left { text-align: left !important; }

        /* Pewarnaan Kolom PG dan SG */
        .bg-pg { background-color: #e3f2fd !important; }
        .bg-sg { background-color: #e8f5e9 !important; }

        /* Efek Baris Zebra (Selang-seling) */
        table.data-table tbody tr:nth-child(even) td { background-color: #f5f5f5; }
        table.data-table tbody tr:nth-child(even) td.bg-pg { background-color: #bbdefb !important; }
        table.data-table tbody tr:nth-child(even) td.bg-sg { background-color: #c8e6c9 !important; }

        /* Area Tanda Tangan */
        table.signature-table {
            width: 100%; margin-top: 40px; border-collapse: collapse;
            page-break-inside: avoid; /* Mencegah terpotong halaman saat print */
        }
        table.signature-table td {
            border: none !important; text-align: center; padding: 5px;
            background-color: transparent !important; width: 25%; vertical-align: top;
        }

        /* Aturan Print */
        @media print {
            .no-print { display: none !important; }
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

            <label for="start_date">Pilih Tanggal Mulai:</label>
            <input type="date" name="start_date" id="start_date" value="{{ $inputDate }}" required>

            <button type="submit">Tampilkan Laporan</button>
        </form>
    </div>

    @if($selectedClassroom)
        <div class="no-print" style="text-align: right; margin-bottom: 15px;">
            <button onclick="window.print()" class="btn-cetak">🖨️ Cetak Laporan</button>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">LAPORAN KEGIATAN SISWA</h3>
            <h4 style="margin: 5px 0 0 0;">SMK N 1 BUKITTINGGI</h4>
            <p style="margin: 5px 0 0 0;"><strong>KELAS {{ strtoupper($selectedClassroom->name) }}</strong></p>
        </div>

        <table class="data-table">
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

                                $pg = '';
                                $sg = '';

                                // Logika untuk Kedatangan (PG) & Status
                                if ($attendance && $attendance->arrival_time) {
                                    $time = \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i');
                                    $statusStr = strtolower($attendance->status ?? '');

                                    if ($statusStr == 'hadir') {
                                        $pg = $time . ' (H)';
                                    } elseif ($statusStr == 'terlambat') {
                                        $pg = $time . ' (T)';
                                    } else {
                                        $hurufStatus = $statusStr ? strtoupper(substr($statusStr, 0, 1)) : 'H';
                                        $pg = $time . ' (' . $hurufStatus . ')';
                                    }
                                } else {
                                    // Jika tidak absen
                                    $pg = '(A)';
                                }

                                // Logika untuk Kepulangan (SG)
                                if ($attendance && $attendance->departure_time) {
                                    $sg = \Carbon\Carbon::parse($attendance->departure_time)->format('H:i');
                                }
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

        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                </td>
                <td>
                    <br>
                    Kepala Bengkel TITL
                </td>
                <td>
                    <br>
                    Bimbingan Konseling
                </td>
                <td>
                    Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Wali Kelas {{ $selectedClassroom->name }}
                </td>
            </tr>
            <tr>
                <td style="height: 80px;"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <strong>( ......................................... )</strong><br>
                    NIP.
                </td>
                <td>
                    <strong><u>Gustifa Fauzan, S.Pd.</u></strong><br>
                    NIP. .........................................
                </td>
                <td>
                    <strong>
                        @if($selectedClassroom->counselingTeacher)
                            <u>{{ $selectedClassroom->counselingTeacher->name }}</u>
                        @else
                            ( ......................................... )
                        @endif
                    </strong><br>
                    NIP. {{ $selectedClassroom->counselingTeacher->nip ?? '.........................' }}
                </td>
                <td>
                    <strong>
                        @if($selectedClassroom->homeroomTeacher)
                            <u>{{ $selectedClassroom->homeroomTeacher->name }}</u>
                        @else
                            ( ......................................... )
                        @endif
                    </strong><br>
                    NIP. {{ $selectedClassroom->homeroomTeacher->nip ?? '.........................' }}
                </td>
            </tr>
        </table>

    @else
        <div class="no-print" style="text-align: center; margin-top: 50px;">
            <p style="color: #666; font-size: 14px;">Silakan pilih kelas dan tanggal terlebih dahulu, kemudian klik "Tampilkan Laporan".</p>
        </div>
    @endif

</body>
</html>
