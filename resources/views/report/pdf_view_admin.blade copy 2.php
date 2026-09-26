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
        /* Mengatur Margin Halaman secara Dinamis dari Database */
        @page {
            margin-top: {{ $school['margin_top'] ?? '2cm' }};
            margin-right: {{ $school['margin_right'] ?? '2cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2cm' }};
            margin-left: {{ $school['margin_left'] ?? '2cm' }};
        }
        body { font-family: sans-serif; font-size: 12px; }

        /* Layout Kop Surat menggunakan Tabel agar rapi di PDF */
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }

        /* Logo harus menggunakan public_path agar terbaca oleh DOMPDF */
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }

        /* Tabel Data */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px; text-align: left; }
        table.data th { background-color: #eee; text-align: center; font-weight: bold; }

        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 10px; text-transform: uppercase; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid; /* Jangan potong tanda tangan ke halaman baru sendirian */
        }

        /* Gaya Khusus Jurnal */
        .journal-box {
            margin-top: 25px;
            border: 1px solid #000;
            padding: 10px;
            page-break-inside: avoid;
            background-color: #fafafa;
        }
        .journal-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .journal-table td {
            border: none;
            padding: 2px 5px;
            vertical-align: top;
        }
    </style>
</head>
<body>

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
                <h1>{{ $school['name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['address'] ?? 'Alamat sekolah belum diatur.' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
                <p>Website: {{ $school['web'] ?? '-' }}</p>
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

    <h3 class="text-center" style="text-transform: uppercase;">LAPORAN ABSENSI SISWA</h3>
    <h4 class="text-center" style="margin-top: 0; font-weight: normal;">{{ $labelPeriode ?? 'Periode Laporan' }}</h4>

    @if(isset($labelTambahan))
        <h5 class="text-center" style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
            {{ $labelTambahan }}
        </h5>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 10%">Jam</th>
                <th style="width: 15%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th>Mata Pelajaran</th>
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
                <td>{{ $row->student->classroom->name ?? '-' }}</td>
                <td>{{ $row->schedule->subject->name ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = 'bg-alpa';
                        if ($row->status == 'hadir') $statusClass = 'bg-hadir';
                        elseif ($row->status == 'terlambat') $statusClass = 'bg-terlambat';
                        elseif ($row->status == 'izin') $statusClass = 'bg-izin';
                        elseif ($row->status == 'sakit') $statusClass = 'bg-sakit';
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    Tidak ada data absensi yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FITUR BARU: JURNAL PEMBELAJARAN (Tampil jika data jurnal ada) -->
    @if(isset($journal) && $journal)
    <div class="journal-box">
        <div class="journal-title">JURNAL PEMBELAJARAN</div>
        <table class="journal-table" style="width: 100%;">
            <tr>
                <td style="width: 130px; font-weight: bold;">Materi / Topik</td>
                <td style="width: 10px;">:</td>
                <td>{{ $journal->topic ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Aktivitas</td>
                <td>:</td>
                <td>{{ $journal->activity ?? '-' }}</td>
            </tr>
            @if(!empty($journal->notes))
            <tr>
                <td style="font-weight: bold;">Catatan Guru</td>
                <td>:</td>
                <td>{{ $journal->notes }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    <p>{{ $school['sign_city'] ?? 'Jakarta' }}, {{ date('d F Y') }}</p>

                    @if(isset($isTeacher) && $isTeacher == true)
                        <p>Guru Mata Pelajaran,</p>
                        <br><br><br>
                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                            {{ Auth::user()->name ?? '.........................' }}
                        </p>
                        <p>NIP. {{ Auth::user()->teacher->nip ?? '-' }}</p>
                    @else
                        <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>

                        @if(isset($school['sign_image']) && $school['sign_image'])
                            <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ public_path('storage/'.$school['sign_image']) }}" style="height: 70px; max-width: 100%;">
                            </div>
                        @else
                            <br><br><br>
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

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Hal {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $width = $fontMetrics->get_text_width($text, $font, $size);

            // Hitung posisi X agar mepet kanan (Lebar Halaman - Lebar Teks - Margin Kanan 30pt)
            $x = $pdf->get_width() - $width - 30;

            // Hitung posisi Y (sekitar 30px dari bawah)
            $y = $pdf->get_height() - 30;

            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>

</body>
</html>
