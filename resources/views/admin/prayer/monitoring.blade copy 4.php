@section('title', 'Cetak Kartu Kegiatan')

<x-app-layout>
<div class="page-content">
    <div class="p-4 container-fluid">
        <div class="border-0 shadow-sm card rounded-3">
            <div class="p-4 card-body">

                <!-- Header & Filter -->
                <div class="gap-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h1 class="mb-1 h3 fw-bold text-dark">Monitoring Absensi Sholat</h1>
                        <p class="mb-0 text-muted small">Pantau kedisiplinan ibadah harian seluruh siswa.</p>
                    </div>

                    <!-- Filter Tanggal -->
                    <form action="{{ route('admin.prayer.monitoring') }}" method="GET" class="gap-2 p-2 d-flex bg-light rounded-3">
                        <input type="date" name="date" value="{{ $date }}"
                            class="bg-transparent border-0 shadow-none form-control form-control-sm" style="width: auto;">
                        <button type="submit" class="px-3 btn btn-primary btn-sm rounded-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </form>
                </div>

                <!-- Tabel Monitoring Matriks -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover border-top">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-bottom-0" style="min-width: 200px;">Nama Siswa</th>
                                @foreach($prayerTypes as $type)
                                <th class="py-3 text-center border-bottom-0">{{ $type }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">NIS: {{ $item->nis }}</div>
                                </td>

                                @foreach($prayerTypes as $type)
                                @php
                                    $status = $item->statuses[strtolower($type)];
                                    $badgeClass = match(strtolower($status)) {
                                        'hadir'     => 'bg-success',
                                        'terlambat' => 'bg-warning text-dark',
                                        'alpha'     => 'bg-danger bg-opacity-10 text-danger',
                                        'izin', 'sakit' => 'bg-info text-white',
                                        default     => 'bg-secondary'
                                    };
                                @endphp
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 text-uppercase"
                                          style="font-size: 0.65rem; min-width: 80px; letter-spacing: 0.5px;">
                                        {{ $status }}
                                    </span>
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($prayerTypes) + 1 }}" class="py-5 italic text-center text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Belum ada data absensi untuk tanggal ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Legend / Keterangan Warna -->
                <div class="flex-wrap gap-3 p-3 mt-4 border d-flex align-items-center bg-light rounded-3 border-light">
                    <span class="tracking-wider small fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Keterangan:</span>
                    <div class="gap-1 d-flex align-items-center small">
                        <span class="p-1 badge bg-success rounded-circle"><span class="visually-hidden">.</span></span> Hadir
                    </div>
                    <div class="gap-1 d-flex align-items-center small">
                        <span class="p-1 badge bg-warning rounded-circle"><span class="visually-hidden">.</span></span> Terlambat
                    </div>
                    <div class="gap-1 d-flex align-items-center small">
                        <span class="p-1 bg-opacity-25 badge bg-danger rounded-circle"><span class="visually-hidden">.</span></span> Alpha
                    </div>
                    <div class="gap-1 d-flex align-items-center small">
                        <span class="p-1 badge bg-info rounded-circle"><span class="visually-hidden">.</span></span> Izin/Sakit
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Tambahan agar header tabel tetap rapi saat scroll horizontal */
    .table-responsive {
        border-radius: 8px;
    }
    .table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
    }
    .table tbody td {
        border-color: #f8f9fa;
    }
</style>
</x-app-layout>
