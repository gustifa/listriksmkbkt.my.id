@section('title', 'Monitoring ' . $classroom->name)

<x-app-layout>
    <div class="page-content">
        <!-- Header & Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="mb-3 mb-md-0">
                    <h4 class="fw-bold text-primary mb-1">{{ $classroom->name }}</h4>
                    <p class="text-muted mb-0 small">
                        Walas: <strong>{{ $classroom->homeroomTeacher->name ?? '-' }}</strong> | 
                        BK: <strong>{{ $classroom->homeroomTeacher->name ?? '-' }}</strong> | 
                        Ketua Kelas: <strong>{{ $classroom->classLeader->name ?? '-' }}</strong>
                    </p>
                </div>
                <form action="{{ route('teacher.monitoring.show', $classroom->id) }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <!-- Tabel Monitoring Terpadu -->
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2"></i>Rekap Harian: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="bg-light text-uppercase">
                        <tr>
                            <th rowspan="2" class="align-middle ps-4">Nama Siswa</th>
                            <th colspan="2" class="text-center border-start border-end">Gerbang</th>
                            <th colspan="1" class="text-center border-end">Pembelajaran</th>
                            <th colspan="1" class="text-center">Ibadah</th>
                            <th rowspan="2" class="text-center align-middle border-start">Status</th>
                        </tr>
                        <tr>
                            <th class="text-center border-start"><small>Datang</small></th>
                            <th class="text-center border-end"><small>Pulang</small></th>
                            
                            <th class="text-center border-end"><small>Bolos Mapel</small></th>
                            
                            <th class="text-center"><small>Sholat</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php
                                // Data Gerbang
                                $gate = $gateData[$student->id] ?? null;
                                
                                // Data Pembelajaran (Cari jika ada yang Alpa)
                                $learning = $learningData[$student->id] ?? collect();
                                $bolosCount = $learning->where('status', 'alpa')->count();
                                $learningStatus = $learning->pluck('status')->unique();

                                // Data Sholat
                                $prayers = $prayerData[$student->id] ?? collect();
                                $prayerList = $prayers->pluck('prayer_name')->implode(', ');
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $student->name }}</td>
                                
                                <!-- Gerbang -->
                                <td class="text-center border-start">
                                    @if($gate && $gate->arrival_time)
                                        <span class="text-success fw-bold">{{ \Carbon\Carbon::parse($gate->arrival_time)->format('H:i') }}</span>
                                        <br>
                                        @if($gate->status == 'terlambat') <span class="badge bg-danger" style="font-size:0.6rem">Telat</span> @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center border-end">
                                    @if($gate && $gate->departure_time)
                                        <span class="text-primary fw-bold">{{ \Carbon\Carbon::parse($gate->departure_time)->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Pembelajaran (Indikator Bolos) -->
                                <td class="text-center border-end">
                                    @if($bolosCount > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $bolosCount }} Mapel Alpa</span>
                                    @elseif($learning->count() > 0)
                                        <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Aman</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- Sholat -->
                                <td class="text-center">
                                    @if($prayers->count() > 0)
                                        <span class="badge bg-info text-dark" data-bs-toggle="tooltip" title="{{ $prayerList }}">
                                            {{ $prayers->count() }} Waktu
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- Kesimpulan Status Harian -->
                                <td class="text-center border-start">
                                    @if(!$gate && $bolosCount > 0)
                                        <span class="badge bg-danger">ALPHA</span>
                                    @elseif($gate && $gate->status == 'sakit')
                                        <span class="badge bg-info">SAKIT</span>
                                    @elseif($gate && $gate->status == 'izin')
                                        <span class="badge bg-warning text-dark">IZIN</span>
                                    @elseif($gate)
                                        <span class="badge bg-success">HADIR</span>
                                    @else
                                        <span class="badge bg-secondary">UNKNOWN</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>