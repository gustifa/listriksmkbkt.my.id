<header>
    <div class="topbar d-flex align-items-center">
        <nav class="gap-3 navbar navbar-expand">
            
            <!-- Tombol Toggle Sidebar -->
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>

            <!-- Nama Sekolah Dynamic -->
            <div class="d-flex justify-content-left align-items-center d-lg-flex d-none" style="width: 100%;">
                <h6 class="fs-4 text-nowrap mb-0">
                    <b>{{ strtoupper(\App\Models\Setting::value('school_name', 'Sekolah')) }}</b>
                </h6>
            </div>

            <div class="top-menu ms-auto">
                <ul class="gap-1 navbar-nav align-items-center">

                    <!-- Dark Mode Toggle -->
                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i></a>
                    </li>

                    <!-- Dynamic Notifications Center (Lonceng) -->
                    @php
                        $notifications = auth()->user()->notifications()->take(8)->get();
                        $unreadCount = auth()->user()->unreadNotifications->count();
                    @endphp

                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if($unreadCount > 0)
                                <span class="alert-count" id="header-unread-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                            <i class='bx bx-bell'></i>
                        </a>
                        
                        <!-- Dropdown Menu Notifikasi -->
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 p-0" style="width: 340px; z-index: 1060;">
                            
                            <!-- Header Notifikasi Dropdown -->
                            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top">
                                <h6 class="mb-0 fw-bold text-dark fs-6">Notifikasi</h6>
                                @if($unreadCount > 0)
                                    <a href="{{ route('notifications.readAll') }}" class="text-primary text-decoration-none fw-semibold small" style="font-size: 11px;">
                                        Tandai Semua Dibaca
                                    </a>
                                @endif
                            </div>

                            <!-- Isi Daftar Notifikasi Dropdown -->
                            <div class="header-notifications-list" style="max-height: 320px; overflow-y: auto;">
                                @forelse($notifications as $notification)
                                    <a class="dropdown-item py-2.5 px-3 border-bottom text-wrap {{ $notification->read_at ? 'bg-white opacity-75' : 'bg-light-subtle fw-semibold' }}" 
                                       href="{{ $notification->data['url'] ?? 'javascript:;' }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="notify {{ $notification->read_at ? 'bg-secondary bg-opacity-10 text-secondary' : 'bg-light-primary text-primary' }} mt-1 p-1 rounded-circle flex-shrink-0">
                                                <i class='bx {{ $notification->data["icon"] ?? "bx-bell" }} fs-5'></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="{{ $notification->read_at ? 'text-secondary fw-normal' : 'fw-bold text-dark' }}" style="font-size: 13px;">
                                                        {{ $notification->data['title'] ?? 'Pemberitahuan' }}
                                                    </span>
                                                    <small class="text-muted ms-1" style="font-size: 10px;">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <p class="mb-0 text-muted" style="font-size: 11.5px; line-height: 1.3;">
                                                    {{ $notification->data['message'] ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 px-3 text-muted">
                                        <i class="bx bx-bell-off fs-1 mb-2 text-secondary opacity-50 d-block"></i>
                                        <p class="mb-0 small fw-semibold">Tidak ada riwayat notifikasi</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Footer Notifikasi Dropdown (Buka Modal AJAX) -->
                            <div class="text-center p-2 bg-light border-top rounded-bottom">
                                <a href="javascript:;" id="btn-open-modal-notif" class="text-primary text-decoration-none fw-bold small">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>

                        </div>
                    </li>

                </ul>
            </div>

            <!-- Profile User Section -->
            @php
                $id = Auth::user()->id;
                $profileData = App\Models\User::find($id);
            @endphp

            <div class="px-3 user-box dropdown">
                <a class="gap-3 d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{(!empty($profileData->photo)) ? url('upload/admin_images/'.$profileData->photo): url('upload/no_image.jpg')}}" class="user-img" alt="user avatar">
                    <div class="user-info">
                        <p class="mb-0 user-name fw-bold">{{$profileData->name}}</p>
                        <p class="mb-0 designattion text-muted" style="font-size: 11px;">{{$profileData->email}}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item d-flex align-items-center py-2" href="{{route('user.profile')}}"><i class="bx bx-user fs-5 me-2"></i><span>Profile</span></a></li>
                    <li><a class="dropdown-item d-flex align-items-center py-2" href="{{route('user.password')}}"><i class="bx bx-cog fs-5 me-2"></i><span>Ganti Password</span></a></li>
                    <li><div class="mb-0 dropdown-divider"></div></li>
                    <li><a class="dropdown-item d-flex align-items-center py-2 text-danger" href="{{route('user.logout')}}"><i class="bx bx-log-out-circle fs-5 me-2"></i><span>Logout</span></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<!-- ========================================================= -->
<!-- MODAL RIWAYAT SEMUA NOTIFIKASI -->
<!-- ========================================================= -->
<div class="modal fade" id="modalAllNotifications" tabindex="-1" aria-hidden="true" style="z-index: 1075;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class='bx bx-bell text-primary fs-5'></i> Semua Riwayat Notifikasi
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="container-modal-notifications" class="list-group list-group-flush">
                    <!-- Data AJAX akan di-render di sini -->
                </div>
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-read-all-modal">
                    <i class='bx bx-check-double me-1'></i> Tandai Semua Dibaca
                </button>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- SCRIPT AJAX MODAL NOTIFIKASI -->
<!-- ========================================================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnOpenModal = document.getElementById('btn-open-modal-notif');
    const container = document.getElementById('container-modal-notifications');
    const btnReadAll = document.getElementById('btn-read-all-modal');

    // Load Data Notifikasi via AJAX
    function loadNotificationsAjax() {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small mb-0">Memuat riwayat notifikasi...</p>
            </div>
        `;

        fetch("{{ route('notifications.ajax') }}")
            .then(response => response.json())
            .then(res => {
                if (!res.data || res.data.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class='bx bx-bell-off fs-1 text-muted d-block mb-2'></i>
                            <p class="text-muted small mb-0">Belum ada riwayat notifikasi.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(item => {
                    const isRead = item.read_at !== null;
                    html += `
                        <a href="${item.url}" class="list-group-item list-group-item-action p-3 border-bottom d-flex align-items-center justify-content-between ${isRead ? 'bg-white opacity-75' : 'bg-light fw-bold'}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 ${isRead ? 'bg-secondary bg-opacity-10 text-secondary' : 'bg-primary bg-opacity-10 text-primary'}" style="width: 42px; height: 42px;">
                                    <i class="bx ${item.icon} fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 ${isRead ? 'text-secondary fw-normal' : 'text-dark fw-bold'}" style="font-size: 13.5px;">${item.title}</h6>
                                    <p class="mb-0 text-muted small lh-sm" style="font-size: 12px;">${item.message}</p>
                                </div>
                            </div>
                            <span class="badge bg-light text-secondary border fw-normal ms-2 flex-shrink-0" style="font-size: 11px;">${item.created_at}</span>
                        </a>
                    `;
                });
                container.innerHTML = html;
            });
    }

    // Event Klik Buka Modal
    if(btnOpenModal) {
        btnOpenModal.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('modalAllNotifications'));
            modal.show();
            loadNotificationsAjax();
        });
    }

    // Event Klik Tandai Semua Dibaca via AJAX
    if(btnReadAll) {
        btnReadAll.addEventListener('click', function () {
            fetch("{{ route('notifications.readAllAjax') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    loadNotificationsAjax();
                    const countBadge = document.getElementById('header-unread-badge');
                    if(countBadge) countBadge.remove();
                }
            });
        });
    }
});
</script>