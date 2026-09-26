@php
        $setting = App\Models\AttendanceSetting::first();
        $batasTerlambat = $setting ? $setting->late_limit_time : '07:00:00';
        $batasBolehPulang = $setting ? $setting->early_departure_time : '10:00:00';
@endphp
@section('title')
   Scan Datanng dan Pulang
@endsection
<x-app-layout>
    <div class="page-content">
        
                <div class="row">
                    <div class="col">
                        <div class="alert alert-light border shadow-sm d-flex flex-wrap justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bx bxs-time fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Jadwal Absensi Hari Ini</h5>
                                    <small class="text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-4">
                                <!-- Jam Masuk -->
                                <div class="d-flex align-items-center">
                                    <div class="me-2 text-end">
                                        <small class="d-block text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Absen Datang</small>
                                        <span class="fw-bold text-success fs-5">06:00 - {{$batasTerlambat}}</span>
                                    </div>
                                    <i class="fas fa-sign-in-alt text-success fa-2x opacity-50"></i>
                                </div>

                                <!-- Divider -->
                                <div class="vr opacity-25"></div>

                                <!-- Jam Pulang -->
                                <div class="d-flex align-items-center">
                                    <div class="me-2 text-end">
                                        <small class="d-block text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Absen Pulang</small>
                                        <span class="fw-bold text-primary fs-5">Mulai {{$batasBolehPulang}}</span>
                                    </div>
                                    <i class="fas fa-sign-out-alt text-primary fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- INFO JADWAL ABSENSI (BARU) -->
            
            <!-- Statistik Cepat (Cards) -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-2">
					<div class="col">
						<div class="card radius-10 border-left-success">
							<div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Datang Hari Ini</div>
                                    <div class="h1 mb-4 font-weight-bold text-gray-800" id="count-hadir">0</div>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-log-in-circle'></i>
								</div>
                                <div class="col-auto">
                                    <i class="fas fa-sign-in-alt fa-3x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
						</div>
					</div>

                    <div class="col">
						<div class="card radius-10 border-left-primary">
							<div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pulang Hari Ini</div>
                                    <div class="h1 mb-4 font-weight-bold text-gray-800" id="count-pulang">0</div>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bx-log-out-circle'></i>
								</div>
                                <div class="col-auto">
                                    <i class="fas fa-sign-in-alt fa-3x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
						</div>
					</div>
					
				</div>

            <div class="row">
                
                <!-- KOLOM KIRI: Live Feed (Log Scan Terakhir) -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <h5 class="m-0 font-weight-bold me-3">
                                    <i class="fas fa-satellite-dish me-2 text-danger blink"></i> Live Feed
                                </h5>
                                <select id="filter-kelas" class="form-select form-select-sm text-dark bg-light border-0" style="width: 200px; font-weight: bold;">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-light" id="last-updated">Updating...</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th width="15%" class="text-center">Waktu</th>
                                            <th width="15%">NIS</th>
                                            <th>Nama Siswa</th>
                                            <th width="20%">Kelas</th>
                                            <th width="15%" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="live-data">
                                        <!-- Data akan dimuat via AJAX -->
                                        <tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Rekapitulasi Per Kelas -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-list-ol me-2"></i> Rekap Per Kelas</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Kelas</th>
                                            <th class="text-center text-success">Datang</th>
                                            <th class="text-center text-primary">Pulang</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rekap-kelas-data">
                                        <tr><td colspan="3" class="text-center text-muted">Memuat...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
      
    </div>
<style>
    .border-left-success { border-left: 5px solid #1cc88a !important; }
    .border-left-primary { border-left: 5px solid #4e73df !important; }
    
    /* Animasi Berkedip */
    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }

    /* Transisi Highlight Baris Baru */
    .new-row { animation: highlight 2s ease-in-out; }
    @keyframes highlight { 
        0% { background-color: #d1e7dd; } 
        100% { background-color: transparent; } 
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        
        function loadRealtimeData() {
            let classId = $('#filter-kelas').val();

            $.ajax({
                url: "{{ route('daily.api.latest') }}",
                type: "GET",
                data: { classroom_id: classId },
                success: function(response) {
                    // 1. UPDATE STATISTIK TOTAL
                    $('#count-hadir').text(response.summary.hadir);
                    $('#count-pulang').text(response.summary.pulang);

                    // 2. UPDATE TABEL LIVE FEED
                    let rows = '';
                    if(response.data.length > 0) {
                        response.data.forEach(function(item, index) {
                            // Animasi baris pertama (terbaru)
                            let rowClass = index === 0 ? 'new-row fw-bold' : '';
                            rows += `
                                <tr class="${rowClass}">
                                    <td class="text-center font-monospace">${item.time}</td>
                                    <td>${item.nis}</td>
                                    <td>${item.name}</td>
                                    <td>${item.class}</td>
                                    <td class="text-center">
                                        <span class="badge bg-${item.badge_color} px-2 py-1">
                                            ${item.status_label}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td></tr>';
                    }
                    $('#live-data').html(rows);

                    // 3. UPDATE TABEL REKAP KELAS (KANAN)
                    let rekapRows = '';
                    if(response.rekap_kelas && response.rekap_kelas.length > 0) {
                        response.rekap_kelas.forEach(function(r) {
                            rekapRows += `
                                <tr>
                                    <td class="fw-bold ps-3">${r.nama_kelas}</td>
                                    <td class="text-center text-success fw-bold">${r.total_datang}</td>
                                    <td class="text-center text-primary fw-bold">${r.total_pulang}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rekapRows = '<tr><td colspan="3" class="text-center text-muted small py-2">Belum ada data rekap.</td></tr>';
                    }
                    $('#rekap-kelas-data').html(rekapRows);
                    
                    // 4. Update Waktu
                    let now = new Date();
                    $('#last-updated').text('Update: ' + now.toLocaleTimeString());
                },
                error: function(xhr) {
                    console.log("Gagal mengambil data realtime");
                }
            });
        }

        // Load pertama kali
        loadRealtimeData();

        // Polling setiap 3 detik
        setInterval(loadRealtimeData, 3000); 

        // Update saat filter berubah
        $('#filter-kelas').change(function() {
            $('#live-data').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');
            loadRealtimeData();
        });
    });
</script>

</x-app-layout>