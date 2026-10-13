@php
    $selectedPrayer = strtolower($prayerName ?? '');
@endphp

<table class="table table-hover align-middle mb-0 text-center">
    <thead class="table-light">
        <tr>
            <th class="d-print-none" style="width: 3%;">
                <input type="checkbox" id="select-all-students" class="form-check-input" checked onclick="toggleSelectAll(this)">
            </th>
            <th class="text-start ps-3" style="width: 5%;">No</th>
            <th class="text-start" style="width: 25%;">Nama Siswa</th>
            <th style="width: 15%;">Kelas</th>

            @if(!$selectedPrayer || $selectedPrayer == 'subuh') <th>Subuh</th> @endif
            @if(!$selectedPrayer || $selectedPrayer == 'dzuhur') <th>Dzuhur</th> @endif
            @if(!$selectedPrayer || $selectedPrayer == 'ashar') <th>Ashar</th> @endif
            @if(!$selectedPrayer || $selectedPrayer == 'maghrib') <th>Maghrib</th> @endif
            @if(!$selectedPrayer || $selectedPrayer == 'isya') <th>Isya</th> @endif
            @if(!$selectedPrayer || $selectedPrayer == 'tarawih') <th class="bg-warning-subtle text-dark">Tarawih</th> @endif

            <th class="bg-primary-subtle text-primary">Total Hadir</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $index => $std)
            @php
                $stats = $recapData[$std->id] ?? [
                    'subuh' => 0, 'dzuhur' => 0, 'ashar' => 0, 
                    'maghrib' => 0, 'isya' => 0, 'tarawih' => 0, 'total' => 0
                ];
            @endphp
            <tr class="student-row" data-student-id="{{ $std->id }}">
                <td class="d-print-none">
                    <input type="checkbox" class="form-check-input student-checkbox" value="{{ $std->id }}" checked onchange="updateExportLink()">
                </td>
                <td class="text-start ps-3 fw-medium text-muted">{{ $index + 1 }}</td>
                <td class="text-start fw-bold text-dark">{{ $std->name }}</td>
                <td>
                    <span class="badge bg-light text-dark border">
                        {{ $std->classroom->name ?? '-' }}
                    </span>
                </td>

                @if(!$selectedPrayer || $selectedPrayer == 'subuh')
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $stats['subuh'] }}</span></td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'dzuhur')
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $stats['dzuhur'] }}</span></td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'ashar')
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $stats['ashar'] }}</span></td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'maghrib')
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $stats['maghrib'] }}</span></td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'isya')
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $stats['isya'] }}</span></td>
                @endif
                @if(!$selectedPrayer || $selectedPrayer == 'tarawih')
                    <td class="bg-warning-subtle fw-bold text-dark">{{ $stats['tarawih'] }}</td>
                @endif

                <td class="bg-primary-subtle fw-bold text-primary">{{ $stats['total'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-2x mb-2 text-secondary"></i>
                    <p class="mb-0">Tidak ada data siswa atau rekap absensi pada periode/kriteria ini.</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>