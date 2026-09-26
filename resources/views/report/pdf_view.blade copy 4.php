<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
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
        body { font-family: sans-serif; font-size: 12px; }

        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }

        /* TABEL DATA */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px; text-align: left; vertical-align: top; }
        table.data th { background-color: #eee; text-align: center; font-weight: bold; }

        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 10px; text-transform: uppercase; display: inline-block; min-width: 50px; text-align: center; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }

        /* JURNAL SECTION */
        .journal-section { margin-top: 20px; page-break-inside: avoid; }
        .section-header { font-weight: bold; font-size: 13px; margin-bottom: 8px; border-bottom: 1px solid #000; display: block; padding-bottom: 2px; }

        /* TANDA TANGAN */
        .signature-section { margin-top: 40px; page-break-inside: avoid; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(isset($school['logo_left']) && $school['logo_left'])
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                <h1>{{ $school['school_name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['school_address'] ?? 'Alamat sekolah belum diatur.' }}</p>
                <p>Telp: {{ $school['school_phone'] ?? '-' }} | Email: {{ $school['school_email'] ?? '-' }}</p>
                <p>Website: {{ $school['school_web'] ?? '-' }}</p>
            </td>
            <td width="15%" class="text-center">
                @if(isset($school['logo_right']) && $school['logo_right'])
                    <img src="{{ public_path('storage/'.$school['logo_right']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <h3 class="text-center" style="text-transform: uppercase; margin-bottom: 5px;">LAPORAN ABSENSI SISWA</h3>
    <h4 class="text-center" style="margin-top: 0; font-weight: normal;">{{ $labelPeriode ?? 'Periode Laporan' }}</h4>

    @if(isset($labelTambahan))
        <h5 class="text-center" style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
            {{ $labelTambahan }}
        </h5>
    @endif

    <!-- A. TABEL KEHADIRAN SISWA -->
    <div class="section-header">A. DATA KEHADIRAN SISWA</div>
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
                    @php
                        $statusClass = 'bg-alpa';
                        if($row->status == 'hadir') $statusClass = 'bg-hadir';
                        elseif($row->status == 'terlambat') $statusClass = 'bg-terlambat';
                        elseif($row->status == 'izin') $statusClass = 'bg-izin';
                        elseif($row->status == 'sakit') $statusClass = 'bg-sakit';
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">
                    Tidak ada data absensi yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- B. TABEL JURNAL PEMBELAJARAN (FITUR BARU) -->
    @if(isset($journals) && $journals->count() > 0)
    <div class="journal-section">
        <div class="section-header">B. JURNAL PEMBELAJARAN</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 12%">Tanggal</th>
                    <th style="width: 20%">Mata Pelajaran</th>
                    <th style="width: 25%">Materi / Topik</th>
                    <th>Aktivitas / Catatan</th>
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
                        <strong>Akt:</strong> {{ $j->activity }}<br>
                        @if($j->notes) <em>Cat: {{ $j->notes }}</em> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- TANDA TANGAN -->
    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="60%"></td> <!-- Spacer Kosong di Kiri -->
                <td width="40%" class="text-center">
                    <p>{{ $school['sign_city'] ?? 'Jakarta' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>

                    @if(isset($isTeacher) && $isTeacher == true)
                        <!-- TANDA TANGAN GURU (Jika dicetak oleh Guru yang bersangkutan) -->
                        <p>Guru Mata Pelajaran,</p>
                        <br><br><br>
                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                            {{ Auth::user()->name ?? '.........................' }}
                        </p>
                        <!-- Pastikan relasi teacher ada untuk mengambil NIP -->
                        <p>NIP. {{ optional(Auth::user()->teacher)->nip ?? '-' }}</p>

                    @else
                        <!-- TANDA TANGAN KEPALA SEKOLAH (Default Admin) -->
                        <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>

                        @if(isset($school['sign_image']) && $school['sign_image'])
                            <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ public_path('storage/'.$school['sign_image']) }}" style="height: 70px; max-width: 100%;">
                            </div>
                        @else
                            <br><br><br> <!-- Spasi untuk TTD Basah -->
                        @endif

                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                            {{ $school['sign_name'] ?? '.........................' }}
                        </p>
                        <p>NIP. {{ $school['sign_nip'] ?? '-' }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Script Page Number -->
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
