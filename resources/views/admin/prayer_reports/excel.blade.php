@php
    $selectedPrayer = strtolower($prayerName ?? '');
@endphp

<table>
    <thead>
        <tr>
            <th colspan="10" style="font-weight: bold; font-size: 14pt;">REKAPITULASI ABSENSI SHOLAT SISWA</th>
        </tr>
        <tr>
            <th colspan="10">Periode: {{ $startDate }} s/d {{ $endDate }} {{ $prayerName ? '('.ucfirst($prayerName).')' : '' }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">No</th>
            <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Nama Siswa</th>
            <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Kelas</th>

            @if(!$selectedPrayer || $selectedPrayer == 'subuh')
                <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Subuh</th>
            @endif
            @if(!$selectedPrayer || $selectedPrayer == 'dzuhur')
                <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Dzuhur</th>
            @endif
            @if(!$selectedPrayer || $selectedPrayer == 'ashar')
                <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Ashar</th>
            @endif
            @if(!$selectedPrayer || $selectedPrayer == 'maghrib')
                <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Maghrib</th>
            @endif
            @if(!$selectedPrayer || $selectedPrayer == 'isya')
                <th style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">Isya</th>
            @endif
            @if(!$selectedPrayer || $selectedPrayer == 'tarawih')
                <th style="font-weight: bold; background-color: #fef08a; border: 1px solid #000;">Tarawih</th>
            @endif

            <th style="font-weight: bold; background-color: #bfdbfe; border: 1px solid #000;">Total Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $std)
            @php
                $stats = $recapData[$std->id] ?? [
                    'subuh' => 0, 'dzuhur' => 0, 'ashar' => 0, 
                    'maghrib' => 0, 'isya' => 0, 'tarawih' => 0, 'total' => 0
                ];
            @endphp
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $std->name }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $std->classroom->name ?? '-' }}</td>

                @if(!$selectedPrayer || $selectedPrayer == 'subuh')
                    <td style="border: 1px solid #000; text-align: center;">{{ $stats['subuh'] }}</td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'dzuhur')
                    <td style="border: 1px solid #000; text-align: center;">{{ $stats['dzuhur'] }}</td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'ashar')
                    <td style="border: 1px solid #000; text-align: center;">{{ $stats['ashar'] }}</td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'maghrib')
                    <td style="border: 1px solid #000; text-align: center;">{{ $stats['maghrib'] }}</td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'isya')
                    <td style="border: 1px solid #000; text-align: center;">{{ $stats['isya'] }}</td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'tarawih')
                    <td style="border: 1px solid #000; text-align: center; background-color: #fef9c3;">{{ $stats['tarawih'] }}</td>
                @endif

                <td style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: #dbeafe;">{{ $stats['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>