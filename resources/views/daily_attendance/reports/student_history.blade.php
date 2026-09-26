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
    <title>LAPORAN | {{ \App\Models\Setting::value('app_name', 'Sekolah') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
    <style>

        /* Mengatur Margin Halaman secara Dinamis dari Database */
        @page {
            margin-top: {{ $school['margin_top'] ?? '2cm' }};
            margin-right: {{ $school['margin_right'] ?? '2cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2cm' }};
            margin-left: {{ $school['margin_left'] ?? '2cm' }};
        }
        body { font-family: sans-serif; font-size: 12px; color: #333; }

        /* Header Kop Surat */
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }

        /* Biodata Siswa */
        .bio-table { width: 100%; margin-bottom: 20px; }
        .bio-table td { padding: 4px; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }

        /* Statistik Box */
        .stats-box {
            display: flex; width: 100%; margin-bottom: 20px;
            border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;
        }
        .stat-item { margin-right: 30px; }
        .stat-val { font-size: 16px; font-weight: bold; color: #0056b3; }

        /* Tabel Data Main */
        .data-table-laporan { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table-laporan th, .data-table-laporan td { border: 1px solid #333; padding: 8px; text-align: left; }
        .data-table-laporan th { background-color: #eee; text-align: center; }
        .text-center-laporan { text-align: center; }

        /* Status Badge Text */
        .status-hadir { color: green; font-weight: bold; }
        .status-izin { color: #d7e04fff;; font-weight: bold; }
        .status-alfa { color: red; font-weight: bold; }
        .status-terlambat { color: orange; font-weight: bold; }


        /* Layout Kop Surat menggunakan Tabel agar rapi di PDF */
        .header-table-logo { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table-logo td { vertical-align: middle; }

        /* Logo harus menggunakan public_path agar terbaca oleh DOMPDF */
        .logo-img { width: 80px; height: auto; }

        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }

        /* Tabel Data */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px; text-align: left; }
        table.data th { background-color: #eee; text-align: center; font-weight: bold; }

    </style>
</head>
<body>

     <table class="header-table-logo">
        <tr>
            <!-- LOGO KIRI -->
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>

            <!-- TEKS TENGAH (IDENTITAS SEKOLAH) -->
            <td width="70%" class="school-info">
                <h1>DINAS {{ $school['provinsi_name'] }}</h1>
                <h1>{{ $school['school_name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['school_address'] ?? 'Alamat sekolah belum diatur di menu pengaturan.' }}</p>
                <p>Telp: {{ $school['school_phone'] ?? '-' }}  | Email: {{ $school['school_email'] ?? '-' }}</p>
                <p>Website: {{ $school['school_web'] ?? '-' }}</p>
            </td>

            <!-- LOGO KANAN -->
            <td width="15%" class="text-center">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- Biodata -->
    <table class="bio-table">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: {{ $student->name }}</td>
            <td class="label">Periode Data</td>
            <td>: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk (NIS)</td>
            <td>: {{ $student->nis }}</td>
            <td class="label">Dicetak Pada</td>
            <td>: {{ date('d F Y, H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Kelas Saat Ini</td>
            <td>: {{ $student->classroom->name ?? '-' }}</td>
            <td class="label">Wali Kelas</td>
            <td>: -</td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; background: #f8f9fa;">
        <strong>RINGKASAN KEHADIRAN:</strong> &nbsp;&nbsp;
        Total Masuk: <b>{{ $summary['total'] }}</b> &nbsp;|&nbsp;
        Tepat Waktu: <b>{{ $summary['hadir'] }}</b> &nbsp;|&nbsp;
        Sakit: <b style="color: orange;">{{ $summary['sakit'] }}</b> &nbsp;|&nbsp;
        Izin: <b style="color: #d7e04fff;">{{ $summary['izin'] }}</b> &nbsp;|&nbsp;
        Alpa: <b style="color: red;">{{ $summary['alpa'] }}</b> &nbsp;|&nbsp;
        Terlambat: <b style="color: orange;">{{ $summary['terlambat'] }}</b>
    </div>

    <!-- Tabel Detail -->
    <table class="data-table-laporan">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 15%">Jam Masuk</th>
                <th style="width: 15%">Jam Pulang</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center-laporan">{{ $index + 1 }}</td>
                <td class="text-center-laporan">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                <td class="text-center-laporan">{{ $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('H:i') : '-' }}</td>
                <td class="text-center-laporan">{{ $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('H:i') : '-' }}</td>
                <td class="text-center-laporan">
                    @if($row->status == 'hadir')
                        <span class="status-hadir">HADIR</span>
                    @elseif($row->status == 'sakit')
                        <span class="status-sakit">SAKIT</span>
                    @elseif($row->status == 'izin')
                        <span class="status-izin">IZIN</span>
                    @elseif($row->status == 'alpa')
                        <span class="status-alfa">ALPA</span>
                    @else
                        <span class="status-terlambat">TERLAMBAT</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center-laporan">Belum ada riwayat absensi untuk siswa ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN DINAMIS -->
    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="60%"></td> <!-- Spacer Kosong di Kiri -->
                <td width="40%" class="text-center">
                    <p>{{ $school['sign_city'] ?? 'Jakarta' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>
                    <br><br><br>
                    <p style="text-decoration: underline; font-weight: bold; margin-bottom: 1px;">
                        {{ $school['sign_name'] ?? '.........................' }}
                    </p>
                    <p style="margin-top: 1px;">NIP. {{ $school['sign_nip'] ?? '-' }}</p>
                </td>
            </tr>
        </table>
    </div>


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
