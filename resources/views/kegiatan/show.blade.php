@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8fafc; }
    .card-modern { border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .bg-gradient-indigo { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    .qr-container { 
        padding: 20px; background: white; border-radius: 20px; 
        display: inline-block; border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    /* Style untuk Badge Nama & Waktu */
    .badge-user {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%; /* Agar rapi saat list memanjang */
        margin-bottom: 8px;
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease;
    }
    .user-info { display: flex; align-items: center; }
    .avatar-circle {
        width: 32px; height: 32px;
        background: #4f46e5; color: white;
        border-radius: 50%; display: flex;
        align-items: center; justify-content: center;
        margin-right: 12px; font-size: 0.8rem; font-weight: bold;
    }
    .absensi-time {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6366f1;
        background: #f5f3ff;
        padding: 4px 10px;
        border-radius: 10px;
    }
    .status-dot {
        height: 10px; width: 10px; background-color: #22c55e;
        border-radius: 50%; display: inline-block; margin-right: 8px;
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-dark mb-2">{{ $kegiatan->nama_kegiatan }}</h1>
        <div class="d-flex justify-content-center align-items-center gap-3 text-muted">
            <span><i class="far fa-calendar-alt me-2"></i>{{ \Carbon\Carbon::parse($kegiatan->tanggal)->translatedFormat('d F Y') }}</span>
            <span class="text-primary fw-bold">|</span>
            <span class="text-indigo fw-bold"><i class="fas fa-clock me-2"></i>Real-time Monitoring</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5 text-center">
            <div class="card card-modern p-4 h-100">
                <div class="qr-container mb-4">
                    {!! QrCode::size(280)->margin(1)->generate($scanUrl) !!}
                </div>
                <h5 class="fw-bold text-dark">Scan untuk Absen</h5>
                <p class="text-muted small">Silakan arahkan kamera Anda ke kode di atas.</p>
                
                <div class="mt-auto pt-4 border-top">
                    <a href="{{ route('kegiatan.print', $kegiatan->id) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-print me-2"></i>Cetak Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-modern bg-gradient-indigo text-white p-4 mb-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase fw-bold opacity-75 small mb-1">Total Hadir</h6>
                        <h2 class="display-4 fw-bold mb-0" id="total-hadir-count">{{ $kegiatan->absensi()->count() }}</h2>
                    </div>
                    <i class="fas fa-user-check fa-4x opacity-25"></i>
                </div>
            </div>

            <div class="card card-modern p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">
                        <span class="status-dot"></span>Log Kehadiran
                    </h5>
                    <span class="badge bg-light text-muted border px-3 py-2">Terupdate Otomatis</span>
                </div>

                <div id="daftar-nama" class="overflow-auto pr-2" style="max-height: 400px;">
                    @forelse($kegiatan->absensi()->with('user')->latest()->get() as $absensi)
                        <div class="badge-user shadow-sm">
                            <div class="user-info">
                                <div class="avatar-circle">{{ strtoupper(substr($absensi->user->name, 0, 1)) }}</div>
                                <span class="fw-bold text-dark">{{ $absensi->user->name }}</span>
                            </div>
                            <span class="absensi-time">
                                <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($absensi->waktu_hadir)->format('H:i:s') }}
                            </span>
                        </div>
                    @empty
                        <div id="placeholder-text" class="py-5 text-center text-muted border rounded-3 border-dashed">
                            Menunggu absensi pertama...
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function checkAbsensiRealtime() {
            $.ajax({
                url: "{{ route('kegiatan.total-hadir', $kegiatan->id) }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // 1. Update Angka Total Hadir
                    $('#total-hadir-count').text(response.total);

                    // 2. Update Daftar Nama & Waktu
                    let container = $('#daftar-nama');
                    
                    // Cek jika ada data yang dikirim (menggunakan response.data)
                    if (response.data && response.data.length > 0) {
                        $('#placeholder-text').hide();
                        let html = '';
                        
                        $.each(response.data, function(index, item) {
                            let initial = item.name.charAt(0).toUpperCase();
                            html += `
                                <div class="badge-user shadow-sm">
                                    <div class="user-info">
                                        <div class="avatar-circle">${initial}</div>
                                        <span class="fw-bold text-dark">${item.name}</span>
                                    </div>
                                    <span class="absensi-time">
                                        <i class="far fa-clock me-1"></i> ${item.waktu}
                                    </span>
                                </div>`;
                        });
                        container.html(html);
                    } else {
                        container.html('<div id="placeholder-text" class="py-5 text-center text-muted border rounded-3 border-dashed">Menunggu absensi pertama...</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Kesalahan AJAX: ", error);
                }
            });
        }

        // Jalankan pengecekan setiap 3 detik
        setInterval(checkAbsensiRealtime, 3000);
    });
</script>
@endsection