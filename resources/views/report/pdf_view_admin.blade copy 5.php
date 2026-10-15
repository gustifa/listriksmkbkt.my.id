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
        <link rel="icon" href="{{ public_path('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ public_path('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif

    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2.5cm' }};
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }
        body { font-family: sans-serif; font-size: 11px; }

        /* Kop Surat */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-img { width: 80px; height: auto; object-fit: contain; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 9pt; }

        h3, h4, h5 { margin: 5px 0; text-align: center; text-transform: uppercase; }

        /* Tabel Data */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        table.data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }

        .text-center { text-align: center; }

        /* Badge Status */
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 9px; font-weight: bold; text-transform: uppercase; display: inline-block; min-width: 50px; text-align: center; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }

        /* Section Jurnal */
        .journal-section { margin-top: 20px; page-break-inside: avoid; }
        .section-header { font-weight: bold; font-size: 12px; margin-bottom: 5px; border-bottom: 1px solid #000; display: block; padding-bottom: 2px; }

        /* Tanda Tangan */
        .footer-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; }

        /* Footer Print */
        /* .footer-print { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 5px; } */
    </style>
</head>
<body>

    <!-- KOP SURAT -->
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
                {{-- <div style="font-size: 12pt;">DINAS PENDIDIKAN</div> --}}
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

    <!-- JUDUL -->
    <h3>LAPORAN ABSENSI & JURNAL PEMBELAJARAN</h3>
    <h4>{{ $labelPeriode }}</h4>
    @if(isset($labelTambahan)) <h5>{{ $labelTambahan }}</h5> @endif

    <!-- A. TABEL KEHADIRAN SISWA -->
    <div class="section-header">A. DATA KEHADIRAN SISWA</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 8%">Jam</th>
                <th style="width: 10%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th>Mata Pelajaran</th>
                <th style="width: 10%">Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d/m/y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }}</td>
                <td>{{ $row->student->nis }}</td>
                <td>{{ $row->student->name }}</td>
                <td class="text-center">{{ $row->student->classroom->name ?? '-' }}</td>
                <td>{{ $row->schedule->subject->name ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = match($row->status) {
                            'hadir' => 'bg-hadir', 'terlambat' => 'bg-terlambat',
                            'izin' => 'bg-izin', 'sakit' => 'bg-sakit', default => 'bg-alpa'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">Tidak ada data absensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- B. TABEL JURNAL PEMBELAJARAN -->
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
                    <td class="text-center">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('d/m/y') }}</td>
                    <td>
                        {{ $j->schedule->subject->name ?? '-' }}
                        <br><small>({{ $j->schedule->classroom->name ?? '-' }})</small>
                    </td>
                    <td>{{ $j->topic }}</td>
                    <td>
                        <strong>Akt:</strong> {{ $j->activity }}
                        @if($j->notes) <br><em>Cat: {{ $j->notes }}</em> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- TANDA TANGAN -->
    <div class="footer-section">
        <div class="ttd-box">
            <p>{{ $school['sign_city'] ?? 'Kota' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>

            @if($isTeacher && isset($user->teacher))
                <p>Guru Mata Pelajaran,</p>
                <br><br><br>
                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 1px;">
                    {{ $user->name }}
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
                <p style="margin-top: 1px;">NIP. {{ $school['sign_nip'] }}</p>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Page Number Script -->
    {{-- <script type="text/php">
        if (isset($pdf)) {
            $text = "Hal {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 30, $pdf->get_height() - 20, $text, $font, $size);
        }
    </script> --}}

    {{-- <script type="text/php">
        if (isset($pdf)) {
            // Setting Font
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $size = 8;
            $color = array(0.3, 0.3, 0.3); // Warna abu-abu gelap

            // Posisi Y (dari bawah)
            $y = $pdf->get_height() - 35;

            // 1. Nama Aplikasi (Kiri Bawah)
            // Mengambil nilai app_name dari database via blade injection
            $appName = "{{ \App\Models\Setting::value('app_name', 'GATECH') }} - Generated by System";
            $pdf->page_text(30, $y, $appName, $font, $size, $color);

            // 2. Halaman & Tanggal Print (Kanan Bawah)
            // Format: Dicetak: 24 Januari 2025 10:30 | Hal 1 dari 5
            $date = "{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}";
            $textRight = "Dicetak: " . $date . " | Hal {PAGE_NUM} dari {PAGE_COUNT}";

            // Hitung lebar teks agar bisa rata kanan
            $width = $fontMetrics->get_text_width($textRight, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 30, $y, $textRight, $font, $size, $color);
        }
    </script> --}}

    {{-- <script type="text/php">
        if (isset($pdf)) {
            // Setting Font
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $size = 8;
            $color = array(0.3, 0.3, 0.3); // Warna abu-abu gelap

            // Posisi Y (dari bawah kertas)
            $y = $pdf->get_height() - 35;

            // 1. Nama Aplikasi (Kiri Bawah)
            // Posisi X = 30 (Jarak dari kiri)
            $appName = "{{ \App\Models\Setting::value('app_name', 'GATECH') }} - Generated by System";
            $pdf->page_text(30, $y, $appName, $font, $size, $color);

            // 2. Halaman & Tanggal Print (Kanan Bawah)
            $date = "{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}";
            $textRight = "Dicetak: " . $date . " | Hal {PAGE_NUM} dari {PAGE_COUNT}";

            // Hitung lebar teks
            $width = $fontMetrics->get_text_width($textRight, $font, $size);

            // RUMUS POSISI KANAN: Lebar Kertas - Lebar Teks - Margin Kanan
            // Saya ubah margin kanan dari 30 menjadi 15 agar lebih ke kanan
            $xRight = $pdf->get_width() - $width - 0;

            $pdf->page_text($xRight, $y, $textRight, $font, $size, $color);
        }
    </script> --}}

    {{-- <script type="text/php">
        if (isset($pdf)) {
            // Setting Font
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $size = 8;
            $textColor = array(0.3, 0.3, 0.3); // Warna teks (abu gelap)
            $lineColor = array(0.8, 0.8, 0.8); // Warna garis (abu muda/pudar)

            // Posisi Y Teks (dari bawah kertas)
            $y = $pdf->get_height() - 35;

            // ---------------------------------------------------------
            // 1. GAMBAR GARIS PUDAR
            // ---------------------------------------------------------
            // Posisi Y Garis (sedikit di atas teks, misal dikurangi 10 point)
            $lineY = $y - 10;

            // Syntax: line(x1, y1, x2, y2, color, width)
            // x1: 30 (Mulai dari kiri), x2: Lebar kertas - 30 (Sampai kanan)
            $pdf->line(30, $lineY, $pdf->get_width() - 30, $lineY, $lineColor, 1);

            // ---------------------------------------------------------
            // 2. TEXT FOOTER
            // ---------------------------------------------------------

            // Nama Aplikasi (Kiri Bawah)
            $appName = "{{ \App\Models\Setting::value('app_name', 'GATECH') }} - Generated by System";
            $pdf->page_text(30, $y, $appName, $font, $size, $textColor);

            // Halaman & Tanggal Print (Kanan Bawah)
            $date = "{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}";
            $textRight = "Dicetak: " . $date . " | Hal {PAGE_NUM} dari {PAGE_COUNT}";

            // Hitung posisi X agar rata kanan
            $width = $fontMetrics->get_text_width($textRight, $font, $size);
            $xRight = $pdf->get_width() - $width - 15; // Margin kanan 15

            $pdf->page_text($xRight, $y, $textRight, $font, $size, $textColor);
        }
    </script> --}}

    <script type="text/php">
        if (isset($pdf)) {
            // ===========================================
            // KONFIGURASI
            // ===========================================
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $size = 8;
            $textColor = array(0.3, 0.3, 0.3); // Abu gelap (Teks)
            $lineColor = array(0.8, 0.8, 0.8); // Abu pudar (Garis)

            // Posisi Y dasar (35 point dari bawah)
            $y = $pdf->get_height() - 35;

            // ===========================================
            // 1. GAMBAR GARIS (MENGGUNAKAN KOTAK TIPIS)
            // ===========================================
            // Trik: Gunakan filled_rectangle agar lebih kompatibel daripada line()
            // Rumus: filled_rectangle(x, y, lebar, tinggi, warna)

            $lineY = $y - 10;           // Posisi Y garis
            $lineX = 30;                // Mulai dari kiri
            $lineW = $pdf->get_width() - 60; // Lebar kertas dikurangi margin kiri-kanan (30+30)
            $lineH = 1;                 // Ketebalan garis (1 point)

            $pdf->filled_rectangle($lineX, $lineY, $lineW, $lineH, $lineColor);

            // ===========================================
            // 2. TEXT FOOTER
            // ===========================================

            // KIRI: Nama Aplikasi
            $appName = "{{ \App\Models\Setting::value('app_name', 'GATECH') }} - Generated by System";
            $pdf->page_text(30, $y, $appName, $font, $size, $textColor);

            // KANAN: Halaman & Tanggal
            $date = "{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}";
            $textRight = "Dicetak: " . $date . " | Hal {PAGE_NUM} dari {PAGE_COUNT}";

            // Hitung posisi X agar rata kanan
            $width = $fontMetrics->get_text_width($textRight, $font, $size);

            // Margin kanan 15 (sesuai request sebelumnya)
            $xRight = $pdf->get_width() - $width - 15;

            $pdf->page_text($xRight, $y, $textRight, $font, $size, $textColor);
        }
    </script>

</body>
</html>
