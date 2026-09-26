@if($selectedClassroom)

    <style>
        /* Pengaturan ini HANYA berlaku saat dilihat di layar monitor/HP */
        @media screen {
            .report-inner-container { min-width: 1100px; padding: 10px; }
        }
    </style>

    <div class="table-responsive pb-3">

        <div class="report-inner-container">

            <div class="text-center mb-4 print-only-header">
                <h4 class="mb-0 fw-bold">LAPORAN KEGIATAN SISWA</h4>
                <h5 class="mb-0 fw-bold">SMK N 1 BUKITTINGGI</h5>
                <p class="mb-0"><strong>KELAS {{ strtoupper($selectedClassroom->name) }}</strong></p>
            </div>

            <table class="table table-bordered table-hover align-middle data-table mb-0 w-100" style="font-size: 13px;">
                <thead class="table-light text-center">
                    <tr>
                        <th rowspan="3" style="width: 3%; vertical-align: middle;">NO</th>
                        <th rowspan="3" style="width: 20%; vertical-align: middle;">NAMA</th>
                        <th rowspan="3" style="width: 8%; vertical-align: middle;">KELAS</th>
                        @foreach($days as $day)
                            <th colspan="2" style="width: 13.8%;">{{ strtoupper($day->translatedFormat('l')) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($days as $day)
                            <th colspan="2" class="text-nowrap">{{ $day->translatedFormat('d M y') }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($days as $day)
                            <th class="bg-pg">PG</th>
                            <th class="bg-sg">SG</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($students as $index => $student)
                        <tr>
                            <td class="fw-bold">{{ $index + 1 }}</td>
                            <td class="text-start text-nowrap" style="white-space: normal !important; word-break: break-word;">{{ $student->name }}</td>
                            <td>{{ $student->classroom->name ?? '-' }}</td>

                            @foreach($days as $day)
                                @php
                                    $attendance = $student->dailyAttendances->where('date', $day->format('Y-m-d'))->first();
                                    $pg = '';
                                    $sg = '';

                                    if ($attendance && $attendance->arrival_time) {
                                        $time = \Carbon\Carbon::parse($attendance->arrival_time)->format('H:i');
                                        $statusStr = strtolower($attendance->status ?? '');

                                        if ($statusStr == 'hadir') { $pg = $time . ' (H)'; }
                                        elseif ($statusStr == 'terlambat') { $pg = $time . ' (T)'; }
                                        else {
                                            $hurufStatus = $statusStr ? strtoupper(substr($statusStr, 0, 1)) : 'H';
                                            $pg = $time . ' (' . $hurufStatus . ')';
                                        }
                                    } else {
                                        $pg = '<span class="text-danger fw-bold">(A)</span>';
                                    }

                                    if ($attendance && $attendance->departure_time) {
                                        $sg = \Carbon\Carbon::parse($attendance->departure_time)->format('H:i');
                                    }
                                @endphp

                                <td class="bg-pg">{!! $pg !!}</td>
                                <td class="bg-sg">{{ $sg }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-muted">Tidak ada data siswa di kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="signature-table mt-4" style="width: 100%;">
                <tr>
                    <td>Mengetahui,<br>Kepala Sekolah</td>
                    <td><br>Kepala Bengkel TITL</td>
                    <td><br>Bimbingan Konseling</td>
                    <td>Bukittinggi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas {{ $selectedClassroom->name }}</td>
                </tr>
                <tr>
                    <td style="height: 80px;"></td><td></td><td></td><td></td>
                </tr>
                <tr>
                    <td><strong>( ......................................... )</strong><br>NIP. </td>
                    <td><strong><u>Gustifa Fauzan, S.Pd.</u></strong><br>NIP. .........................................</td>
                    <td>
                        <strong>
                            @if($selectedClassroom->counselingTeacher) <u>{{ $selectedClassroom->counselingTeacher->name }}</u>
                            @else ( ......................................... ) @endif
                        </strong><br>NIP. {{ $selectedClassroom->counselingTeacher->nip ?? '.........................' }}
                    </td>
                    <td>
                        <strong>
                            @if($selectedClassroom->homeroomTeacher) <u>{{ $selectedClassroom->homeroomTeacher->name }}</u>
                            @else ( ......................................... ) @endif
                        </strong><br>NIP. {{ $selectedClassroom->homeroomTeacher->nip ?? '.........................' }}
                    </td>
                </tr>
            </table>

        </div>
    </div>
@endif
