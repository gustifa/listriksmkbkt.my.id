<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }} - {{ $classroom->name }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 11pt; }
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .school-info { text-align: center; }
        .school-info h2 { margin: 0; font-size: 14pt; font-weight: normal; text-transform: uppercase; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 5px; font-size: 12pt; text-transform: uppercase; }
        .subtitle { text-align: center; margin-bottom: 20px; font-size: 11pt; }

        .table-data { width: 100%; border-collapse: collapse; font-size: 10pt; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px; text-align: center; }
        .table-data th { background-color: #f0f0f0; }
        .text-left { text-align: left !important; padding-left: 5px; }

        .footer { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center;">
                @if(!empty($school['logo_left'])) <img src="{{ public_path('storage/'.$school['logo_left']) }}" width="70"> @endif
            </td>
            <td width="70%" class="school-info">
                <h2>PEMERINTAH PROVINSI {{ strtoupper($school['provinsi_name']) }}</h2>
                <h2>DINAS PENDIDIKAN</h2>
                <h1>{{ $school['name'] }}</h1>
                <p>{{ $school['address'] }}</p>
            </td>
            <td width="15%" style="text-align: center;">
                @if(!empty($school['logo_right'])) <img src="{{ public_path('storage/'.$school['logo_right']) }}" width="70"> @endif
            </td>
        </tr>
    </table>

    <div class="title">{{ $title }}</div>
    <div class="subtitle">
        Kelas: <strong>{{ $classroom->name }}</strong> | Periode: <strong>{{ $periodLabel }}</strong>
    </div>

    <!-- TABEL DATA -->
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">NIS</th>
                <th>Nama Siswa</th>
                
                @if($request->type == 'gate')
                    <th width="10%">Hadir</th>
                    <th width="10%">Telat</th>
                    <th width="10%">Sakit</th>
                    <th width="10%">Izin</th>
                    <th width="10%">Alpha</th>
                @elseif($request->type == 'learning')
                    <th width="10%">Hadir</th>
                    <th width="10%">Sakit</th>
                    <th width="10%">Izin</th>
                    <th width="10%" style="background-color: #ffe6e6;">Bolos</th>
                    <th width="15%">Total Mapel</th>
                @elseif($request->type == 'prayer')
                    <th width="15%">Melaksanakan</th>
                    <th width="10%">Uzur</th>
                    <th width="10%" style="background-color: #ffe6e6;">Tidak</th>
                    <th width="15%">Total Waktu</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $idx => $row)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row['nis'] }}</td>
                <td class="text-left">{{ strtoupper($row['name']) }}</td>

                @if($request->type == 'gate')
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['terlambat'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td style="{{ $row['alpa'] > 0 ? 'color:red; font-weight:bold;' : '' }}">{{ $row['alpa'] }}</td>
                @elseif($request->type == 'learning')
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td style="{{ $row['alpa'] > 0 ? 'color:red; font-weight:bold;' : '' }}">{{ $row['alpa'] }}</td>
                    <td>{{ $row['total_mapel'] }}</td>
                @elseif($request->type == 'prayer')
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['uzur'] }}</td>
                    <td style="{{ $row['alpha'] > 0 ? 'color:red; font-weight:bold;' : '' }}">{{ $row['alpha'] }}</td>
                    <td>{{ $row['total_sholat'] }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer">
        <div class="ttd-box">
            <p>{{ $school['sign_city'] }}, {{ date('d F Y') }}</p>
            <p>
                @if($classroom->homeroom_teacher_id == $teacher->id) Wali Kelas @else Guru BK @endif
            </p>
            <br><br><br>
            <p style="text-decoration: underline; font-weight: bold;">{{ $teacher->user->name }}</p>
            <p>NIP. {{ $teacher->nip ?? '-' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>