
@section('title')
   Scan Datanng dan Pulang
@endsection
<x-app-layout>
    <div class="page-content">
    <div class="container-fluid">
    
    <!-- Statistik Cepat -->
    <div class="row mb-4">
        <!-- Card Total Datang -->
        <div class="col-md-6">
            <div class="card shadow border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Datang Hari Ini</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800" id="count-hadir">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Total Pulang -->
        <div class="col-md-6">
            <div class="card shadow border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pulang Hari Ini</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800" id="count-pulang">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Live Monitoring -->
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="m-0 font-weight-bold me-3">
                    <i class="fas fa-satellite-dish me-2 text-danger blink"></i> Live Monitor
                </h5>
                
                <!-- DROPDOWN FILTER KELAS -->
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
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Menghubungkan ke server...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<style>
    /* Styling khusus untuk Dashboard Monitor */
    .border-left-success { border-left: 5px solid #1cc88a !important; }
    .border-left-primary { border-left: 5px solid #4e73df !important; }
    
    /* Animasi Berkedip untuk ikon satelit */
    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 
        50% { opacity: 0; } 
    }

    /* Transisi Highlight untuk Baris Baru */
    .new-row { animation: highlight 2s ease-in-out; }
    @keyframes highlight { 
        0% { background-color: #d1e7dd; } 
        100% { background-color: transparent; } 
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        
        // Fungsi utama mengambil data dari API
        function loadRealtimeData() {
            // Ambil ID Kelas yang dipilih dari dropdown
            let classId = $('#filter-kelas').val();

            $.ajax({
                url: "{{ route('daily.api.latest') }}",
                type: "GET",
                data: {
                    classroom_id: classId // Kirim parameter filter ke controller
                },
                success: function(response) {
                    let rows = '';
                    
                    // 1. Update Statistik (Angka Besar)
                    $('#count-hadir').text(response.summary.hadir);
                    $('#count-pulang').text(response.summary.pulang);

                    // 2. Update Tabel Data
                    if(response.data.length > 0) {
                        response.data.forEach(function(item, index) {
                            // Tambahkan kelas animasi 'new-row' hanya untuk baris pertama (terbaru)
                            // Agar petugas sadar ada data baru masuk
                            let rowClass = index === 0 ? 'new-row fw-bold' : '';
                            
                            rows += `
                                <tr class="${rowClass}">
                                    <td class="text-center font-monospace">${item.time}</td>
                                    <td>${item.nis}</td>
                                    <td>${item.name}</td>
                                    <td>${item.class}</td>
                                    <td class="text-center">
                                        <span class="badge bg-${item.badge_color} px-3 py-2" style="min-width: 80px;">
                                            ${item.status_label}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data absensi hari ini untuk filter terpilih.</td></tr>';
                    }

                    // Render HTML ke tbody
                    $('#live-data').html(rows);
                    
                    // Update Timestamp terakhir
                    let now = new Date();
                    $('#last-updated').text('Last update: ' + now.toLocaleTimeString());
                },
                error: function(xhr) {
                    console.log("Gagal mengambil data realtime. Retrying...");
                }
            });
        }

        // Panggil pertama kali saat halaman selesai dimuat
        loadRealtimeData();

        // Set Interval: Panggil ulang setiap 3 detik (Polling)
        // Ini menciptakan efek realtime tanpa membebani server berlebihan
        setInterval(loadRealtimeData, 3000); 

        // Event Listener: Panggil ulang saat dropdown filter diganti
        $('#filter-kelas').change(function() {
            // Tampilkan loading sebentar agar user tahu sedang proses filter
            $('#live-data').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>');
            loadRealtimeData();
        });
    });
</script>
    </div>
</x-app-layout>