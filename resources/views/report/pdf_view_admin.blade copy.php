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
        /* CSS SAMA SEPERTI SEBELUMNYA */
        @page {
            margin-top: {{ $school['margin_top'] ?? '2cm' }};
            margin-right: {{ $school['margin_right'] ?? '2cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2cm' }};
            margin-left: {{ $school['margin_left'] ?? '2cm' }};
        }
        body { font-family: sans-serif; font-size: 12px; }
        .header-table { width: 100%; border-bottom: 3px double #333; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-img { width: 80px; height: auto; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 20px; text-transform: uppercase; font-weight: bold; }
        .school-info p { margin: 2px 0; font-size: 11px; }
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
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
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
                        $statusClass = match($row->status) {
                            'hadir' => 'bg-hadir',
                            'terlambat' => 'bg-terlambat',
                            'izin' => 'bg-izin',
                            'sakit' => 'bg-sakit',
                            default => 'bg-alpa',
                        };
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

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    <p>{{ $school['sign_city'] ?? 'Jakarta' }}, {{ date('d F Y') }}</p>

                    {{-- 
                        LOGIKA:
                        Jika $isTeacher TRUE (dari printSchedule), tampilkan Guru Mapel yang login.
                        Jika FALSE (dari print umum), tampilkan Kepala Sekolah dari Database Setting.
                    --}}
                    
                    @if(isset($isTeacher) && $isTeacher == true)
                        {{-- TANDA TANGAN GURU (USER LOGIN) --}}
                        <p>Guru Mata Pelajaran,</p>
                        <br><br><br>
                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                            {{ Auth::user()->name ?? '.........................' }}
                        </p>
                        {{-- Pastikan relasi teacher ada untuk mengambil NIP --}}
                        <p>NIP. {{ Auth::user()->teacher->nip ?? '-' }}</p>

                    @else
                        {{-- TANDA TANGAN KEPALA SEKOLAH (DARI SETTING) --}}
                        <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>

                        {{-- Cek Gambar TTD Kepsek --}}
                        @if(isset($school['sign_image']) && $school['sign_image'])
                            <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                                <img src="{{ public_path('storage/'.$school['sign_image']) }}" style="height: 70px; max-width: 100%;">
                            </div>
                        @else
                            <br><br><br> @endif

                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                            {{ $school['sign_name'] ?? '.........................' }}
                        </p>
                        <p>NIP. {{ $school['sign_nip'] ?? '-' }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>