<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kehadiran | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
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
            /* PENTING: Set margin-bottom lebih besar dari 1.5cm agar ada ruang untuk footer. */
            /* Kita set menjadi 3cm untuk margin yang aman. */
            margin-bottom: 3cm; 
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

         /* Area Tanda Tangan */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid; /* Jangan potong tanda tangan ke halaman baru sendirian */
        }

        /* CSS FOOTER PERMANEN */
        .footer-print {
            position: fixed;
            /* PENTING: Pindahkan posisi footer ke atas (misalnya, 50px dari tepi bawah fisik halaman) */
            /* -50px dari tepi bawah fisik halaman (margin 0). */
            /* Ini akan menempatkannya di area margin-bottom 3cm yang kita set. */
            bottom: -50px; 
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            padding-top: 5px;
            /* Kita gunakan border-top: 1px solid #ccc; seperti yang Anda inginkan, */
            /* tetapi pastikan elemen ini berada di area margin. */
            border-top: 1px solid #ccc;
            width: 100%;
        }
    </style>
</head>
<body>

   <!-- KOP SURAT DINAMIS -->
    <table class="header-table">
        <tr>
            <!-- LOGO KIRI -->
            <td width="15%" class="text-center">
                @if(isset($school['logo_left']) && $school['logo_left'])
                    <img src="{{ public_path('storage/'.$school['logo_left']) }}" class="logo-img">
                @else
                    <img src="{{ asset('upload/no_image.jpg')}}" class="logo-img">
                @endif
            </td>

            <!-- TEKS TENGAH (IDENTITAS SEKOLAH) -->
            <td width="70%" class="school-info">
                <h1>{{ $school['name'] ?? 'NAMA SEKOLAH BELUM DISET' }}</h1>
                <p>{{ $school['address'] ?? 'Alamat sekolah belum diatur di menu pengaturan.' }}</p>
                <p>Telp: {{ $school['phone'] ?? '-' }} | Email: {{ $school['email'] ?? '-' }}</p>
                <p>Website: {{ $school['web'] ?? '-' }}</p>
            </td>

            <!-- LOGO KANAN -->
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
    <h3 class="text-center" style="text-transform: uppercase;">LAPORAN DATANG & PULANG SISWA</h3>

    <h4 class="text-center" style="margin-top: 0; font-weight: normal;">
        {{ $labelPeriode ?? 'Periode Laporan' }}
    </h4>

    <!-- SUB-JUDUL (Misal: Filter per Kelas/Siswa) -->
    @if(isset($labelTambahan))
        <h5 class="text-center" style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
            {{ $labelTambahan }}
        </h5>
    @endif

    <!-- TABEL DATA ABSENSI -->
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
                <td class="text-center">{{ $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('H:i') : '-' }}</td>
                <td class="text-center">{{ $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('H:i') : '-' }}</td>
                <td>{{ $row->student->nis }}</td>
                <td>{{ $row->student->name }}</td>

                <!-- Mengambil nama kelas via relasi -->
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
                <td colspan="8" class="text-center" style="padding: 20px;">
                    Tidak ada data absensi yang ditemukan pada periode ini.
                </td>
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
                    <p>{{ $school['sign_city'] ?? 'Jakarta' }}, {{ date('d F Y') }}</p>
                    @if($id == 'guru')
                    <p>Guru Mata Pelajaran,</p>
                    <br><br><br>
                     <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                        {{ Auth::user()->name ?? '.........................' }}
                    </p>
                    <p>NIP. {{ Auth::user()->teacher->nip ?? '-' }}</p>

                    @else
                    <p>{{ $school['sign_title'] ?? 'Admin' }},</p>

                    <br><br><br>
                    <!-- <p style="text-decoration: underline; font-weight: bold;">
                        {{ $school['sign_name'] ?? '.........................' }}
                    </p>
                    <p>NIP. {{ $school['sign_nip'] ?? '-' }}</p> -->

                    <!-- LOGIC GAMBAR TANDA TANGAN -->
                    @if(isset($school['sign_image']) && $school['sign_image'])
                        <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ public_path('storage/'.$school['sign_image']) }}" style="height: 70px; max-width: 100%;">
                        </div>
                    @else
                        <br> <!-- Spasi untuk TTD Basah -->
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
    <!-- FOOTER TAMBAHAN -->
       
    <div class="footer-print">
        <small>
            {{-- Catatan: Panggilan Model di view membebani, tapi akan saya biarkan jika Anda membutuhkannya. --}}
            <strong>{{ \App\Models\Setting::value('app_name', 'GATECH') }}</strong> 
            &bull; 
            {{ \App\Models\Setting::value('school_name', 'GATECH') }} 
            &bull; 
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
        </small>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            
            // Hitung posisi X untuk menengahkan teks
            $x = ($pdf->get_width() - $width) / 2;
            // Hitung posisi Y (sekitar 30px dari bawah)
            $y = $pdf->get_height() - 30;
            
            
        }
    </script>

</body>
</html>
