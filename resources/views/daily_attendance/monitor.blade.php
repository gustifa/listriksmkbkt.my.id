@php
    $setting = App\Models\AttendanceSetting::first();
    $batasTerlambat = $setting ? $setting->late_limit_time : '07:30:00';
    $batasBolehPulang = $setting ? $setting->early_departure_time : '14:30:00';
@endphp

@section('title')
   Live Monitor Absensi - SMK N 1 Bukittinggi
@endsection

<x-app-layout>
    <div class="page-content">

        <div class="mb-3 row">
            <div class="col-12">
                <div class="border-0 shadow-sm card radius-10">
                    <div class="flex-wrap py-3 card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="text-white widgets-icons-2 rounded-circle bg-gradient-cosmic me-3">
                                <i class='bx bxs-dashboard'></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Monitoring Absensi Gerbang</h5>
                                <p class="mb-0 text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} | <span id="live-clock" class="fw-bold text-primary">00:00:00</span></p>
                            </div>
                        </div>

                        <div class="gap-4 mt-2 d-flex mt-md-0">
                            <div class="text-end">
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.65rem;">Batas Terlambat</small>
                                <span class="border badge bg-light-success text-success fw-bold fs-6 border-success">{{ substr($batasTerlambat, 0, 5) }}</span>
                            </div>
                            <div class="opacity-25 vr"></div>
                            <div class="text-end">
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.65rem;">Mulai Pulang</small>
                                <span class="border badge bg-light-primary text-primary fw-bold fs-6 border-primary">{{ substr($batasBolehPulang, 0, 5) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2">
            <div class="col">
                <div class="border-0 border-4 shadow-sm card radius-10 border-start border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-bold">TOTAL SISWA DATANG</p>
                                <h2 class="my-1 text-success" id="count-hadir">0</h2>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class='bx bxs-user-check'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="border-0 border-4 shadow-sm card radius-10 border-start border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-bold">TOTAL SISWA PULANG</p>
                                <h2 class="my-1 text-primary" id="count-pulang">0</h2>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class='bx bxs-user-minus'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="shadow-sm card radius-10">
                    <div class="py-3 bg-white card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-satellite-dish text-danger blink me-2"></i> Log Aktivitas Terakhir
                        </h6>
                        <div class="gap-2 d-flex">
                            <select id="filter-kelas" class="form-select form-select-sm border-primary" style="min-width: 150px;">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive" style="min-height: 400px;">
                            <table class="table mb-0 align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Evidence</th>
                                        <th>Waktu</th>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="live-data">
                                    <tr>
                                        <td colspan="5" class="py-5 text-center">
                                            <div class="spinner-border text-primary"></div>
                                            <p class="mt-2 text-muted">Menghubungkan ke server...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="py-2 bg-white card-footer text-end">
                        <small class="text-muted" id="last-updated">Belum ada data</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border-0 shadow-sm card radius-10">
                    <div class="py-3 text-white card-header bg-primary">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ol me-2"></i> Rekapitulasi Per Kelas</h6>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                            <table class="table mb-0 table-sm table-striped">
                                <thead class="bg-light sticky-top">
                                    <tr class="text-center small">
                                        <th class="text-start ps-3">Nama Kelas</th>
                                        <th class="text-success">Dtg</th>
                                        <th class="text-primary">Plg</th>
                                    </tr>
                                </thead>
                                <tbody id="rekap-kelas-data" class="small">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEvidence" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="border-0 shadow-lg modal-content">
                <div class="text-white modal-header bg-dark">
                    <h5 class="modal-title fw-bold" id="modal-title-text">Evidence Foto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-0 text-center modal-body bg-light">
                    <div id="modal-loader" class="py-5">
                        <div class="spinner-grow text-primary"></div>
                    </div>
                    <img id="modal-img" src="" class="shadow-sm img-fluid d-none" style="width: 100%;">
                </div>
                <div class="bg-white modal-footer border-top-0 d-flex justify-content-between align-items-center">
                    <div class="text-start">
                        <h6 id="modal-name" class="mb-0 fw-bold text-primary">-</h6>
                        <small id="modal-info" class="text-muted">-</small>
                    </div>
                    <button type="button" class="px-4 btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>



<style>
    .bg-gradient-cosmic { background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%); }
    .bg-light-success { background-color: rgba(28, 200, 138, 0.1); }
    .bg-light-primary { background-color: rgba(78, 115, 223, 0.1); }

    .img-evidence-circle {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.2s;
    }
    .img-evidence-circle:hover { transform: scale(1.15); z-index: 5; }

    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }

    .new-row-animation { animation: slideIn 0.8s ease-out; background-color: #fff9e6 !important; }
    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        // 1. JAM LIVE
        setInterval(() => {
            const now = new Date();
            $('#live-clock').text(now.toLocaleTimeString('id-ID'));
        }, 1000);

        // 2. FUNGSI MODAL EVIDENCE
        window.openEvidence = function(url, name, nis, className) {
            const modal = new bootstrap.Modal(document.getElementById('modalEvidence'));
            const $img = $('#modal-img');
            const $loader = $('#modal-loader');

            $img.addClass('d-none').attr('src', '');
            $loader.removeClass('d-none');
            $('#modal-name').text(name);
            $('#modal-info').text(nis + " | " + className);
            $('#modal-title-text').text("Foto " + name);

            modal.show();

            $img.attr('src', url).on('load', function() {
                $loader.addClass('d-none');
                $img.removeClass('d-none');
            });
        }

        // 3. FUNGSI LOAD DATA AJAX
        function loadRealtimeData() {
            let classId = $('#filter-kelas').val();

            $.ajax({
                url: "{{ route('daily.api.latest') }}",
                type: "GET",
                data: { classroom_id: classId },
                dataType: "json",
                success: function(res) {
                    // Update Summary
                    $('#count-hadir').text(res.summary.hadir);
                    $('#count-pulang').text(res.summary.pulang);

                    // Update Table Live Feed
                    let html = '';
                    if(res.data.length > 0) {
                        res.data.forEach((item, index) => {
                            let animation = (index === 0) ? 'new-row-animation' : '';
                            html += `
                                <tr class="${animation}">
                                    <td class="ps-3">
                                        <img src="${item.photo_url}" class="rounded-circle img-evidence-circle"
                                             onclick="openEvidence('${item.photo_url}', '${item.name}', '${item.nis}', '${item.class}')">
                                    </td>
                                    <td class="font-monospace fw-bold text-primary">${item.time}</td>
                                    <td>
                                        <div class="fw-bold">${item.name}</div>
                                        <small class="text-muted">${item.nis}</small>
                                    </td>
                                    <td><span class="border badge bg-light text-dark">${item.class}</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-${item.badge_color} text-uppercase" style="font-size: 0.65rem;">
                                            ${item.status_label}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="py-5 text-center text-muted">Belum ada aktivitas terekam hari ini.</td></tr>';
                    }
                    $('#live-data').html(html);

                    // Update Rekap Kelas
                    let rekapHtml = '';
                    if(res.rekap_kelas.length > 0) {
                        res.rekap_kelas.forEach(r => {
                            rekapHtml += `
                                <tr>
                                    <td class="ps-3 fw-bold">${r.nama_kelas}</td>
                                    <td class="text-center text-success fw-bold">${r.total_datang}</td>
                                    <td class="text-center text-primary fw-bold">${r.total_pulang}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#rekap-kelas-data').html(rekapHtml);
                    $('#last-updated').text('Sinkronisasi Terakhir: ' + new Date().toLocaleTimeString('id-ID'));
                },
                error: function(err) {
                    console.error("Gagal sinkronisasi data.");
                }
            });
        }

        // Jalankan awal dan interval 3 detik
        loadRealtimeData();
        setInterval(loadRealtimeData, 3000);

        // Filter event
        $('#filter-kelas').change(function() {
            $('#live-data').html('<tr><td colspan="5" class="py-5 text-center"><div class="spinner-border text-primary"></div></td></tr>');
            loadRealtimeData();
        });
    });
</script>
</x-app-layout>
