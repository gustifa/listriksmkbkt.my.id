@extends('layouts.app')

@section('content')
<!-- CSS Select2 & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Monitoring Bot & Notifikasi Telegram</h4>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Pengguna Terdaftar</div>
                    <div class="h3 fw-bold mb-0 text-primary" id="stat-total-subscribers">{{ number_format($totalSubscribers) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Berhasil Terkirim</div>
                    <div class="h3 fw-bold text-success mb-0">{{ number_format($totalSentSuccess) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Gagal Terkirim</div>
                    <div class="h3 fw-bold text-danger mb-0">{{ number_format($totalSentFailed) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 rounded-3">
                <div class="card-body">
                    <div class="text-muted small">Notifikasi Hari Ini</div>
                    <div class="h3 fw-bold text-info mb-0">{{ number_format($todaySentCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigasi Tab --}}
    <ul class="nav nav-tabs mb-3" id="telegramTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ !request()->has('sub_page') ? 'active' : '' }} fw-bold" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                Riwayat Pengiriman Pesan
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request()->has('sub_page') ? 'active' : '' }} fw-bold" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button">
                Daftar Pengguna Terdaftar (<span id="sub-count-tab">{{ $totalSubscribers }}</span>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="telegramTabContent">
        
        {{-- TAB 1: LOG RIWAYAT NOTIFIKASI --}}
        <div class="tab-pane fade {{ !request()->has('sub_page') ? 'show active' : '' }}" id="logs" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Riwayat Pengiriman Pesan</h6>
                        @if($logs->count() > 0)
                        <button type="button" id="btn-clear-all-logs" data-url="{{ route('monitoring.telegram.clearLogs') }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Bersihkan Semua Log
                        </button>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('monitoring.telegram') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, judul, atau Chat ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">-- Semua Kategori --</option>
                                <option value="absensi" {{ request('type') == 'absensi' ? 'selected' : '' }}>Absensi</option>
                                <option value="ujian" {{ request('type') == 'ujian' ? 'selected' : '' }}>Ujian</option>
                                <option value="pengumuman" {{ request('type') == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                                <option value="pelanggaran" {{ request('type') == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">-- Semua Status --</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-logs">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Penerima</th>
                                <th>Chat ID</th>
                                <th>Kategori</th>
                                <th>Judul & Pesan</th>
                                <th>Status</th>
                                <th>Detail Error</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr id="log-row-{{ $log->id }}">
                                <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ $log->recipient_name }}</strong></td>
                                <td><code>{{ $log->chat_id }}</code></td>
                                <td>
                                    @php
                                        $badgeColor = match($log->type) {
                                            'absensi' => 'bg-info',
                                            'ujian' => 'bg-primary',
                                            'pelanggaran' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ strtoupper($log->type) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $log->title }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($log->message, 60) }}</small>
                                </td>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="badge bg-success">Success</span>
                                    @elseif($log->status == 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="small text-danger">{{ $log->error_log ?? '-' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm border-0 btn-delete-log-ajax" 
                                            data-url="{{ route('monitoring.telegram.destroyLog', $log->id) }}" 
                                            data-id="{{ $log->id }}" title="Hapus Log">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-logs-row">
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $logs->appends(request()->except('logs_page'))->links() }}
                </div>
            </div>
        </div>

        {{-- TAB 2: DAFTAR PENGGUNA TERDAFTAR --}}
        <div class="tab-pane fade {{ request()->has('sub_page') ? 'show active' : '' }}" id="subscribers" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <form method="GET" action="{{ route('monitoring.telegram') }}" class="row g-2">
                        <input type="hidden" name="sub_page" value="1">
                        <div class="col-md-4">
                            <input type="text" name="sub_search" class="form-control" placeholder="Cari nama akun, No HP, Chat ID..." value="{{ request('sub_search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-subscribers">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Akun</th>
                                <th>Peran</th>
                                <th>Siswa / Pengguna Terkait</th>
                                <th>Chat ID / No. HP</th>
                                <th>Status Approval</th>
                                <th class="text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $index => $sub)
                            <tr id="sub-row-{{ $sub->id }}">
                                <td>{{ $subscribers->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $sub->parent_name }}</strong><br>
                                    <small class="text-muted">{{ $sub->username ? '@'.$sub->username : '-' }}</small>
                                </td>
                                <td class="col-role">
                                    @if(($sub->user_type ?? '') === 'teacher')
                                        <span class="badge bg-success"><i class="bi bi-person-workspace me-1"></i> Guru / Pegawai</span>
                                    @elseif(($sub->user_type ?? '') === 'student')
                                        <span class="badge text-white" style="background-color: #6f42c1;"><i class="bi bi-person-badge me-1"></i> Siswa</span>
                                    @else
                                        <span class="badge bg-primary"><i class="bi bi-person-heart me-1"></i> Orang Tua</span>
                                    @endif
                                </td>
                                <td class="col-student">
                                    @if($sub->user_type === 'teacher')
                                        <span class="fw-bold text-dark"><i class="fas fa-user-tie text-success me-1"></i> {{ optional($sub->teacher)->name ?? $sub->parent_name }}</span><br>
                                        <small class="text-muted">NIP: {{ optional($sub->teacher)->nip ?? '-' }}</small>
                                    @elseif($sub->student)
                                        <span class="fw-bold">{{ $sub->student->name }}</span><br>
                                        <small class="text-muted">{{ optional($sub->student->classroom)->name ?? '-' }} (NISN: {{ $sub->student->nisn ?? '-' }})</small>
                                    @else
                                        <span class="badge bg-secondary">Belum Memilih Siswa</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub->chat_id)
                                        <a href="tg://openmessage?user_id={{ $sub->chat_id }}" class="text-danger text-decoration-none fw-bold d-block" title="Buka chat Telegram langsung">
                                            <i class="fab fa-telegram-plane me-1"></i> {{ $sub->chat_id }}
                                        </a>
                                    @else
                                        <span class="text-muted d-block">-</span>
                                    @endif

                                    @if($sub->phone_number)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $sub->phone_number);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-primary text-decoration-none small" title="Kirim pesan WhatsApp">
                                            <i class="fab fa-whatsapp text-success me-1"></i> {{ $sub->phone_number }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
    
                                <td class="col-status">
                                    @if($sub->approval_status == 'approved')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Disetujui</span>
                                    @elseif(in_array($sub->approval_status, ['pending_approval', 'pending']))
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Menunggu Persetujuan Admin</span>
                                    @elseif($sub->approval_status == 'pending_nisn')
                                        <span class="badge bg-info text-dark"><i class="bi bi-pencil me-1"></i> Menunggu Input NISN</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Ditolak / Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center col-action">
                                    @if(in_array($sub->approval_status, ['pending', 'pending_approval', 'pending_nisn']))
                                        <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $sub->id }}">
                                            <i class="bi bi-check-circle"></i> Setujui
                                        </button>
                                        
                                        <button type="button" class="btn btn-danger btn-sm btn-reject-ajax" 
                                                data-url="{{ route('monitoring.telegram.rejectSubscriber', $sub->id) }}"
                                                data-id="{{ $sub->id }}">
                                            <i class="bi bi-x-circle"></i> Tolak
                                        </button>

                                        {{-- MODAL APPROVAL --}}
                                        <div class="modal fade text-start" id="approveModal{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('monitoring.telegram.approveSubscriber', $sub->id) }}" method="POST" class="form-approve-ajax" data-id="{{ $sub->id }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Pilih Siswa untuk {{ $sub->parent_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">
                                                                Nomor HP Telegram ini (<code>+{{ $sub->phone_number }}</code>) terhubung dengan pengajuan. Silakan konfirmasi atau pilih siswa terkait.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Pilih Siswa:</label>
                                                                <select name="student_id" class="form-select select2-student" style="width: 100%;" required>
                                                                    <option value="">-- Ketik Nama Siswa / Kelas --</option>
                                                                    @foreach($accessibleStudents as $std)
                                                                        <option value="{{ $std->id }}" {{ $sub->student_id == $std->id ? 'selected' : '' }}>
                                                                            {{ $std->name }} - {{ optional($std->classroom)->name ?? '-' }} (NISN: {{ $std->nisn ?? $std->nis }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-success btn-submit-approve">Setujui & Hubungkan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($sub->approval_status == 'approved' || $sub->is_active)
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-access-ajax" 
                                                data-url="{{ route('monitoring.telegram.cancelSubscriber', $sub->id) }}"
                                                data-id="{{ $sub->id }}">
                                            <i class="bi bi-person-x me-1"></i> Batalkan Akses
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada pengguna yang terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $subscribers->appends(request()->except('sub_page'))->links() }}
                </div>
            </div>
        </div>

    </div>
</div>

{{-- JS Dependencies --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Setup CSRF Token AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toast Notification Config
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Inisialisasi Select2
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.select2-student').select2({
                theme: 'bootstrap-5',
                dropdownParent: $(this),
                placeholder: '-- Ketik Nama Siswa / Kelas --',
                allowClear: true
            });
        });

        // 1. AJAX Hapus Single Log
        $(document).on('click', '.btn-delete-log-ajax', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const logId = $(this).data('id');

            Swal.fire({
                title: 'Hapus Log Pesan?',
                text: "Data riwayat log ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function (res) {
                            $(`#log-row-${logId}`).fadeOut(300, function () {
                                $(this).remove();
                                if ($('#table-logs tbody tr').length === 0) {
                                    $('#table-logs tbody').append('<tr id="empty-logs-row"><td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td></tr>');
                                }
                            });
                            Toast.fire({ icon: 'success', title: res.message || 'Log berhasil dihapus' });
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus log.', 'error');
                        }
                    });
                }
            });
        });

        // 2. AJAX Clear All Logs
        $('#btn-clear-all-logs').on('click', function (e) {
            e.preventDefault();
            const url = $(this).data('url');

            Swal.fire({
                title: 'Bersihkan Semua Log?',
                text: "Seluruh riwayat pengiriman notifikasi akan dihapus dari sistem!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Bersihkan Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function (res) {
                            $('#table-logs tbody').html('<tr id="empty-logs-row"><td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td></tr>');
                            $('#btn-clear-all-logs').hide();
                            Toast.fire({ icon: 'success', title: res.message || 'Semua log berhasil dibersihkan' });
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi masalah pada server.', 'error');
                        }
                    });
                }
            });
        });

        // 3. AJAX Approve Subscriber (Modal Form)
        $(document).on('submit', '.form-approve-ajax', function (e) {
            e.preventDefault();
            const form = $(this);
            const subId = form.data('id');
            const url = form.attr('action');
            const modal = $(`#approveModal${subId}`);

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: function () {
                    form.find('.btn-submit-approve').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                },
                success: function (res) {
                    modal.modal('hide');
                    form.find('.btn-submit-approve').prop('disabled', false).text('Setujui & Hubungkan');
                    
                    // Update tampilan row secara presisi
                    $(`#sub-row-${subId} .col-status`).html('<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Disetujui</span>');
                    $(`#sub-row-${subId} .col-action`).html(`
                        <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-access-ajax" 
                                data-url="/monitoring/telegram/cancel/${subId}" data-id="${subId}">
                            <i class="bi bi-person-x me-1"></i> Batalkan Akses
                        </button>
                    `);

                    Toast.fire({ icon: 'success', title: res.message || 'Pendaftaran berhasil disetujui!' });
                },
                error: function (xhr) {
                    form.find('.btn-submit-approve').prop('disabled', false).text('Setujui & Hubungkan');
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyetujui pendaftaran.', 'error');
                }
            });
        });

        // 4. AJAX Tolak Pendaftaran (Reject)
        $(document).on('click', '.btn-reject-ajax', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const subId = $(this).data('id');

            Swal.fire({
                title: 'Tolak Pendaftaran?',
                text: 'Pengajuan akses Telegram ini akan ditolak!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function (res) {
                            $(`#sub-row-${subId} .col-status`).html('<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Ditolak / Nonaktif</span>');
                            $(`#sub-row-${subId} .col-action`).html('<span class="text-muted small">-</span>');
                            Toast.fire({ icon: 'success', title: res.message || 'Pendaftaran berhasil ditolak' });
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Proses gagal dilakukan.', 'error');
                        }
                    });
                }
            });
        });

        // 5. AJAX Batalkan Akses (Cancel Access)
        $(document).on('click', '.btn-cancel-access-ajax', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const subId = $(this).data('id');

            Swal.fire({
                title: 'Batalkan Akses Telegram?',
                text: 'Akses notifikasi untuk akun ini akan dinonaktifkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Batalkan Akses!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function (res) {
                            $(`#sub-row-${subId} .col-status`).html('<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Ditolak / Nonaktif</span>');
                            $(`#sub-row-${subId} .col-action`).html('<span class="text-muted small">-</span>');
                            Toast.fire({ icon: 'success', title: res.message || 'Akses berhasil dibatalkan' });
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Proses gagal dilakukan.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection