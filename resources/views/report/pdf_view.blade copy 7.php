<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan | {{ \App\Models\Setting::value('app_name', 'GATECH') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif

    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2.5cm' }};
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }
        body { font-family: sans-serif; font-size: 11px; }

        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-img { width: 80px; height: auto; object-fit: contain; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 2px 0; font-size: 9pt; }

        h3, h4, h5 { margin: 5px 0; text-align: center; text-transform: uppercase; }

        /* TABEL DATA */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; }
        table.data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }

        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 9px; font-weight: bold; display: inline-block; min-width: 40px; text-align: center; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }

        /* TANDA TANGAN */
        .footer-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; }

        /* UTILITAS PAGE BREAK */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT (SAMA UNTUK SEMUA) -->
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                @if(isset($school['provinsi_name']))
                    <div style="font-size: 12pt;">PEMERINTAH {{ strtoupper($school['provinsi_name']) }}</div>
                @endif
                <h1>{{ $school['school_name'] ?? 'SEKOLAH' }}</h1>
                <p>{{ $school['school_address'] ?? 'Alamat Sekolah' }}</p>
                <p>Telp: {{ $school['school_phone'] ?? '-' }} | Email: {{ $school['school_email'] ?? '-' }}</p>
                <p>Website: {{ $school['school_web'] ?? '-' }}</p>
            </td>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- LOGIKA TAMPILAN BERDASARKAN MODE -->

    @if(isset($mode) && $mode == 'teacher_recap')
        {{-- === MODE 1: REKAP JURNAL GURU (HANYA HARI AKTIF) === --}}

        <h3>LAPORAN KEGIATAN PEMBELAJARAN & ABSENSI</h3>
        <h4>{{ $labelPeriode }}</h4>
        <h5 style="margin-bottom: 20px;">GURU: {{ $teacher->user->name ?? $user->name }} (NIP: {{ $teacher->nip ?? '-' }})</h5>

        <table class="data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Hari / Tanggal</th>
                    <th width="15%">Kelas / Mapel</th>
                    <th width="30%">Materi & Aktivitas</th>
                    <th width="15%">Statistik Kehadiran</th>
                    <th>Siswa Tidak Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journals as $index => $j)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($j->date)->translatedFormat('l') }}<br>
                        {{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}
                        <br>
                        <small>{{ \Carbon\Carbon::parse($j->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->schedule->end_time)->format('H:i') }}</small>
                    </td>
                    <td>
                        <strong>{{ $j->schedule->classroom->name ?? '-' }}</strong><br>
                        {{ $j->schedule->subject->name ?? '-' }}
                    </td>
                    <td>
                        <strong>Topik:</strong> {{ $j->topic }}<br>
                        <div style="margin-top: 3px; font-style: italic;">"{{ \Illuminate\Support\Str::limit($j->activity, 100) }}"</div>
                    </td>
                    <td>
                        @php $summ = $j->attendance_summary; @endphp
                        <table style="width: 100%; font-size: 9px; border: none;">
                            <tr><td>Hadir</td><td>: {{ $summ->hadir ?? 0 }}</td></tr>
                            <tr><td>Sakit</td><td>: {{ $summ->sakit ?? 0 }}</td></tr>
                            <tr><td>Izin</td><td>: {{ $summ->izin ?? 0 }}</td></tr>
                            <tr><td>Alpa</td><td>: {{ $summ->alpa ?? 0 }}</td></tr>
                        </table>
                    </td>
                    <td>
                        {{ $j->absent_details }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
        {{-- === MODE 2: LAPORAN ABSENSI STANDAR (LIST SISWA) === --}}

        <h3>LAPORAN ABSENSI & JURNAL PEMBELAJARAN</h3>
        <h4>{{ $labelPeriode }}</h4>
        @if(isset($labelTambahan)) <h5>{{ $labelTambahan }}</h5> @endif

        <!-- A. TABEL KEHADIRAN SISWA -->
        <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #000;">A. DATA KEHADIRAN SISWA</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 15%">Tanggal</th>
                    <th style="width: 10%">Jam Absensi</th>
                    <th style="width: 15%">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }}</td>
                    <td>{{ $row->student->nis }}</td>
                    <td>{{ $row->student->name }}</td>
                    <td class="text-center">
                        <span class="badge {{ 'bg-'.($row->status) }}">{{ ucfirst($row->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- B. TABEL JURNAL (Jika Ada) -->
        @if(isset($journals) && $journals->count() > 0)
        <!-- Page Break agar Jurnal mulai di halaman baru (Opsional) -->
        <div class="page-break"></div>
        <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #000; margin-top: 20px;">B. JURNAL PEMBELAJARAN</div>
        <table class="data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Tanggal</th>
                    <th width="20%">Mapel</th>
                    <th width="25%">Materi</th>
                    <th>Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journals as $idx => $j)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('d/m/Y') }}</td>
                    <td>
                        {{ $j->schedule->subject->name ?? '-' }}
                        <br><small>({{ $j->schedule->classroom->name ?? '-' }})</small>
                    </td>
                    <td>{{ $j->topic }}</td>
                    <td>
                        <strong>Aktivitas:</strong> {{ $j->activity }}<br>
                        @if($j->notes) <em>Catatan: {{ $j->notes }}</em> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @endif

    <!-- TANDA TANGAN (COMMON) -->
    <div class="footer-section">
        <div class="ttd-box">
            <p>{{ $school['sign_city'] ?? 'Kota' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>

            @if($isTeacher)
                <p>Guru Mata Pelajaran,</p>
                <br><br><br>
                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 1px;">
                    {{ $teacher->user->name ?? $user->name }}
                </p>
                <p style="margin-top: 1px;">NIP. {{ $user->teacher->nip ?? '-' }}</p>
            @else
                <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>

                @if(!empty($school['sign_image']))
                    <div style="height: 60px; margin: 5px auto; display: flex; justify-content: center;">
                        <img src="{{ $school['sign_image'] }}" style="height: 60px; max-width: 100%;">
                    </div>
                @else
                    <br><br><br>
                @endif

                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 1px;">{{ $school['sign_name'] }}</p>
                <p style="margin-top: 1px;">NIP. {{ $school['sign_nip'] ?? '-' }}</p>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Page Number -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Hal {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = $pdf->get_width() - $width - 30;
            $y = $pdf->get_height() - 30;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>

</body>
</html>
