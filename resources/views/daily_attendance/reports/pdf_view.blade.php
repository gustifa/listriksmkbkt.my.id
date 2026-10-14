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
        
        .footer-print { position: fixed; bottom: -50px; left: 0; right: 0; text-align: center; font-size: 10px; padding-top: 5px; border-top: 1px solid #ccc; width: 100%; }

        /* Tambahan style untuk tanda tangan agar rata vertikal */
        /* .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-table { width: 100%; margin-top: 20px; }
        .signature-table td { width: 33.33%; text-align: center; vertical-align: top; }
        .signature-table p { margin: 2px 0; } */
        /* Style Tanda Tangan Menyatu & Efisien Halaman */
        .signature-section { 
            margin-top: 20px; 
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .signature-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .signature-table td { 
            text-align: center; 
            vertical-align: top; 
            font-size: 11px; /* Ukuran pas agar hemat ruang vertikal */
            padding: 0 4px;
            word-wrap: break-word;
        }
        .signature-table p { 
            margin: 1px 0; 
            line-height: 1.2;
        }
        .signature-space {
            height: 45px; /* Jarak ruang TTD yang pas & hemat halaman */
        }
        
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>

            <td width="70%" class="school-info">
                <h1>DINAS {{ $school['provinsi_name'] }}</h1>
                <h1>{{ $school['school_name'] }}</h1>
                <p>{{ $school['school_address'] }}</p>
                <p>Telp: {{ $school['school_phone'] }} | Email: {{ $school['school_email'] }}</p>
                <p>{{ $school['school_web'] }}</p>
            </td>

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
                <th style="width: 13%">Tanggal</th>
                <th style="width: 8%">Jam Datang</th>
                <th style="width: 8%">Jam Pulang</th>
                <th style="width: 10%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 10%">Kelas</th>
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
        @if(!$hasClassroomFilter)
            {{-- KONDISI 1: TANPA FILTER KELAS (1 Kolom TTD) --}}
            <table class="signature-table">
                <tr>
                    <td style="width: 60%;"></td>
                    <td style="width: 40%;">
                        <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p><b>Pelapor / Tanggung Jawab</b></p>
                        <div class="signature-space"></div>
                        <p style="text-decoration: underline; font-weight: bold;">{{ Auth::user()->name }}</p>
                        <p>NIP. {{ optional(Auth::user()->teacher)->nip ?? '-' }}</p>
                    </td>
                </tr>
            </table>
        @else
            {{-- KONDISI 2: ADA FILTER KELAS (Dua Baris TTD dalam Satu Tabel Tunggal) --}}
            <table class="signature-table">
                <!-- BARIS 1: BK, WALI KELAS, PELAPOR -->
                <tr>
                    <td style="width: 33.33%;">
                        <p>&nbsp;</p>
                        <p><b>Guru Bimbingan Konseling</b></p>
                        <div class="signature-space"></div>
                        <p style="text-decoration: underline; font-weight: bold;">{{ $guruBkName ?? '(...................................)' }}</p>
                        <p>NIP. {{ $guruBkNip ?? '................................' }}</p>
                    </td>

                    <td style="width: 33.33%;">
                        <p>&nbsp;</p>
                        <p><b>Wali Kelas</b></p>
                        <div class="signature-space"></div>
                        <p style="text-decoration: underline; font-weight: bold;">{{ $waliKelasName ?? '(...................................)' }}</p>
                        <p>NIP. {{ $waliKelasNip ?? '................................' }}</p>
                    </td>

                    <td style="width: 33.33%;">
                        <p>{{ $school['sign_city'] }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p><b>Pelapor</b></p>
                        <div class="signature-space"></div>
                        <p style="text-decoration: underline; font-weight: bold;">{{ Auth::user()->name }}</p>
                        <p>NIP. {{ optional(Auth::user()->teacher)->nip ?? '-' }}</p>
                    </td>
                </tr>

                <!-- SPARATOR / JARAK ANTAR BARIS TTD -->
                <tr>
                    <td colspan="3" style="height: 20px;"></td>
                </tr>

                <!-- BARIS 2: KEPALA SEKOLAH (DITENGAH) -->
                <tr>
                    <td style="width: 33.33%;"></td>
                    <td style="width: 33.33%;">
                        <p>Mengetahui,</p>
                        <p><b>Kepala Sekolah</b></p>
                        
                        <!-- @if(!empty($school['sign_image']))
                            <div style="height: 50px; display: flex; align-items: center; justify-content: center; margin: 2px 0;">
                                <img src="{{ $school['sign_image'] }}" style="height: 45px; max-width: 100%; object-fit: contain;">
                            </div>
                        @else -->
                            <div class="signature-space"></div>
                        <!-- @endif -->

                        <p style="text-decoration: underline; font-weight: bold;">{{ $school['sign_name'] }}</p>
                        <p>NIP. {{ $school['sign_nip'] }}</p>
                    </td>
                    <td style="width: 33.33%;"></td>
                </tr>
            </table>
        @endif
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
</body>
</html>
