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
                        $unreadCount = auth()->user()->unreadNotifications->count();
                        $notifications = auth()->user()->unreadNotifications->take(8);
                    @endphp

                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if($unreadCount > 0)
                                <span class="alert-count">{{ $unreadCount }}</span>
                            @endif
                            <i class='bx bx-bell'></i>
                        </a>
                        
                        <!-- Dropdown Menu Notifikasi yang Dirapikan -->
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 p-0" style="width: 320px; z-index: 1060;">
                            
                            <!-- Header Notifikasi -->
                            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top">
                                <h6 class="mb-0 fw-bold text-dark fs-6">Notifikasi</h6>
                                @if($unreadCount > 0)
                                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 11px;">{{ $unreadCount }} Baru</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2 py-1" style="font-size: 11px;">0 Baru</span>
                                @endif
                            </div>

                            <!-- Isi Daftar Notifikasi -->
                            <div class="header-notifications-list" style="max-height: 300px; overflow-y: auto;">
                                @forelse($notifications as $notification)
                                    <a class="dropdown-item py-2.5 px-3 border-bottom text-wrap" 
                                       href="{{ $notification->data['url'] ?? 'javascript:;' }}" 
                                       data-id="{{ $notification->id }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="notify bg-light-primary text-primary mt-1 p-1 rounded-circle flex-shrink-0">
                                                <i class='bx {{ $notification->data["icon"] ?? "bx-bell" }} fs-5'></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold text-dark" style="font-size: 13px;">
                                                        {{ $notification->data['title'] ?? 'Pemberitahuan' }}
                                                    </span>
                                                    <small class="text-muted" style="font-size: 10px;">
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
                                        <p class="mb-0 small fw-semibold">Tidak ada notifikasi baru</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Footer Notifikasi -->
                            @if($unreadCount > 0)
                                <div class="text-center p-2 bg-light border-top rounded-bottom">
                                    <a href="{{ route('notifications.readAll') }}" class="text-primary text-decoration-none fw-bold small">
                                        Tandai Semua Dibaca
                                    </a>
                                </div>
                            @endif

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