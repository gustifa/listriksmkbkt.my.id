<!DOCTYPE html>
<html>
<head>
    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif
    <title>Laporan Kehadiran | {{ \App\Models\Setting::value('app_name', 'Sekolah') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: 3cm;
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }
        body { font-family: sans-serif; font-size: 12px; }
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .logo-img { width: 80px; height: auto; object-fit: contain; }
        .school-info { text-align: center; padding: 0 10px; }
        .school-info h1 { margin: 0; font-size: 18px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 5px; text-align: left; }
        table.data th { background-color: #eee; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 10px; text-transform: uppercase; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }
        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .footer-print { position: fixed; bottom: -50px; left: 0; right: 0; text-align: center; font-size: 10px; padding-top: 5px; border-top: 1px solid #ccc; width: 100%; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <!-- LOGO KIRI (Sudah Base64 dari Controller) -->
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>

            <!-- TENGAH -->
            <td width="70%" class="school-info">
                <h1>DINAS {{ $school['provinsi_name'] }}</h1>
                <h1>{{ $school['school_name'] }}</h1>
                <p>{{ $school['school_address'] }}</p>
                <p>Telp: {{ $school['school_phone'] }} | Email: {{ $school['school_email'] }}</p>
                <p>{{ $school['school_web'] }}</p>
            </td>

            <!-- LOGO KANAN (Sudah Base64 dari Controller) -->
            <td width="15%" class="text-center">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <h3 class="text-center" style="text-transform: uppercase; margin-bottom: 5px;">LAPORAN DATANG & PULANG SISWA</h3>
    <h4 class="text-center" style="margin-top: 0; font-weight: normal; font-size: 12px;">{{ $labelPeriode ?? '' }}</h4>

    @if(isset($labelTambahan))
        <h5 class="text-center" style="margin-top: 5px; font-weight: bold; text-decoration: underline; font-size: 12px;">{{ $labelTambahan }}</h5>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 10%">Jam Datang</th>
                <th style="width: 10%">Jam Pulang</th>
                <th style="width: 15%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d/m/Y') }}</td>

                @if($row->status == 'izin' || $row->status == 'sakit' || $row->status == 'alpa' )
                <td class="text-center">-</td>
                @else
                <td class="text-center">{{ $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('H:i') : '-' }}</td>
                @endif
                @if($row->status == 'izin' || $row->status == 'sakit' || $row->status == 'alpa' )
                <td class="text-center">-</td>
                @else
                <td class="text-center">{{ $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('H:i') : '-' }}</td>
                @endif


                <td>{{ $row->student->nis }}</td>
                <td>{{ $row->student->name }}</td>
                <td>{{ $row->student->classroom->name ?? '-' }}</td>
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
                <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data absensi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>

                    @if(isset($id) && $id == 'guru')
                        <p>Guru Mata Pelajaran,</p>
                        <br><br><br>
                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">{{ Auth::user()->name }}</p>
                        <p>NIP. {{ optional(Auth::user()->teacher)->nip ?? '-' }}</p>
                    @else
                        <p>{{ $school['sign_title'] }},</p>

                        <!-- TANDA TANGAN (Sudah Base64 dari Controller) -->
                        @if(!empty($school['sign_image']))
                            <div style="height: 70px; display: flex; align-items: center; justify-content: center; margin: 5px 0;">
                                <img src="{{ $school['sign_image'] }}" style="height: 120px; max-width: 100%; object-fit: contain;">
                            </div>
                        @else
                            <br><br><br>
                        @endif

                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px; margin-bottom: 1px;">{{ $school['sign_name'] }}</p>
                        <p style="margin-top: 1px;">NIP. {{ $school['sign_nip'] }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- <div class="footer-print">
        <small>dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</small>
    </div> --}}

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

    {{-- <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $color = array(0.5, 0.5, 0.5); // Warna Abu-abu

            // Hitung posisi X agar mepet kanan (Lebar Halaman - Lebar Teks - Margin Kanan 30pt)
            $x = $pdf->get_width() - $width - 30;

            // Hitung posisi Y (sekitar 30px dari bawah)
            $y = $pdf->get_height() - 30;

            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script> --}}
</body>
</html>
