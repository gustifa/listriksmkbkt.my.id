<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <!-- Gunakan logo dari pengaturan jika ada -->
            @php $appLogo = \App\Models\Setting::value('app_logo'); @endphp
            @if($appLogo)
                <img src="{{ asset('storage/'.$appLogo) }}" class="logo-icon" alt="logo icon">
            @else
                <i class="fas fa-graduation-cap text-primary fs-3"></i>
            @endif
        </div>
        <div>
            <h4 class="logo-text">{{ \App\Models\Setting::value('app_name', 'E-Absensi') }}</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i></div>
    </div>

    <!-- NAVIGATION MENU -->
    <ul class="metismenu" id="menu">
        
        <!-- ============================================== -->
        <!-- DASHBOARD (Berubah otomatis sesuai Role)       -->
        <!-- ============================================== -->
        <li class="menu-label">Menu Utama</li>
        <li>
            <a href="{{ route('dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <!-- ============================================== -->
        <!-- MENU SUPER ADMIN & ADMIN                       -->
        <!-- ============================================== -->
        @hasanyrole('super_admin|admin')
            
            <li class="menu-label">Administrator</li>
            
            <!-- Master Data -->
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-database"></i></div>
                    <div class="menu-title">Data Master</div>
                </a>
                <ul>
                    <li><a href="{{ url('admin/users') }}"><i class="bx bx-right-arrow-alt"></i>Data Pengguna</a></li>
                    <li><a href="{{ url('admin/teachers') }}"><i class="bx bx-right-arrow-alt"></i>Data Guru</a></li>
                    <li><a href="{{ url('admin/students') }}"><i class="bx bx-right-arrow-alt"></i>Data Siswa</a></li>
                    <li><a href="{{ url('admin/classrooms') }}"><i class="bx bx-right-arrow-alt"></i>Data Kelas</a></li>
                    <li><a href="{{ url('admin/subjects') }}"><i class="bx bx-right-arrow-alt"></i>Mata Pelajaran</a></li>
                    <li><a href="{{ route('rooms.index') }}"><i class="bx bx-right-arrow-alt"></i>Ruangan & Bengkel</a></li>
                    <li><a href="{{ route('industries.index') }}"><i class="bx bx-right-arrow-alt"></i>Data DU/DI (PKL)</a></li>
                    <li><a href="{{ route('admin.violation-types.index') }}"><i class="bx bx-right-arrow-alt"></i>Jenis Pelanggaran</a></li>
                </ul>
            </li>

            <!-- Akademik & Kesiswaan -->
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-school"></i></div>
                    <div class="menu-title">Akademik & Kesiswaan</div>
                </a>
                <ul>
                    <li><a href="{{ route('schedule.all') }}"><i class="bx bx-right-arrow-alt"></i>Jadwal Pelajaran</a></li>
                    <li><a href="{{ url('admin/attendance/daily') }}"><i class="bx bx-right-arrow-alt"></i>Absensi Gerbang</a></li>
                    <li><a href="{{ url('admin/reports/daily') }}"><i class="bx bx-right-arrow-alt"></i>Rekap Kehadiran KBM</a></li>
                    <li><a href="{{ route('admin.prayer.monitoring') }}"><i class="bx bx-right-arrow-alt"></i>Monitoring Sholat</a></li>
                    <li><a href="{{ route('admin.permit.index') }}"><i class="bx bx-right-arrow-alt"></i>Izin Siswa (Permit)</a></li>
                    <li><a href="{{ route('admin.mbg.index') }}"><i class="bx bx-right-arrow-alt"></i>Makan Bergizi (MBG)</a></li>
                    <li><a href="{{ route('admin.guidance.index') }}"><i class="bx bx-right-arrow-alt"></i>Bimbingan & Pelanggaran</a></li>
                </ul>
            </li>

            <!-- Prakerin / PKL -->
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="menu-title">Manajemen PKL</div>
                </a>
                <ul>
                    <li><a href="{{ route('admin.pkl.mapping.index') }}"><i class="bx bx-right-arrow-alt"></i>Mapping Kelas PKL</a></li>
                    <li><a href="{{ route('admin.internships.index') }}"><i class="bx bx-right-arrow-alt"></i>Penempatan PKL</a></li>
                    <li><a href="{{ route('admin.timeline.index') }}"><i class="bx bx-right-arrow-alt"></i>Timeline & Agenda</a></li>
                </ul>
            </li>
        @endhasanyrole

        <!-- ============================================== -->
        <!-- MENU INVENTARIS BENGKEL (Admin & Teknisi)      -->
        <!-- ============================================== -->
        @hasanyrole('super_admin|admin|teknisi')
            <li class="menu-label">Sarana & Prasarana</li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-tools"></i></div>
                    <div class="menu-title">Inventaris Bengkel</div>
                </a>
                <ul>
                    <li><a href="{{ route('inventory.index') }}"><i class="bx bx-right-arrow-alt"></i>Data Barang</a></li>
                    <li><a href="{{ url('admin/loans/report') }}"><i class="bx bx-right-arrow-alt"></i>Laporan Peminjaman</a></li>
                </ul>
            </li>
        @endhasanyrole


        <!-- ============================================== -->
        <!-- MENU GURU (Mencakup Walas, BK, Pembimbing)     -->
        <!-- ============================================== -->
        @hasanyrole('guru|walas|bk|pembimbing_pkl|proka')
            <li class="menu-label">Menu Guru</li>
            
            <li>
                <a href="{{ route('schedule.index') }}">
                    <div class="parent-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="menu-title">Jadwal & Absen Kelas</div>
                </a>
            </li>
            
            <li>
                <a href="{{ route('journal.index') }}">
                    <div class="parent-icon"><i class="fas fa-book-open"></i></div>
                    <div class="menu-title">Jurnal Mengajar</div>
                </a>
            </li>

            <!-- Ekstra: Peminjaman Barang -->
            <li>
                <a href="{{ route('loans.index') }}">
                    <div class="parent-icon"><i class="fas fa-box-open"></i></div>
                    <div class="menu-title">Peminjaman Barang</div>
                </a>
            </li>

            <!-- KHUSUS WALAS & BK -->
            @hasanyrole('walas|bk')
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon text-warning"><i class="fas fa-user-shield"></i></div>
                    <div class="menu-title">Tugas Walas / BK</div>
                </a>
                <ul>
                    <li><a href="{{ route('teacher.monitoring.index') }}"><i class="bx bx-right-arrow-alt"></i>Monitoring Absensi</a></li>
                    <li><a href="{{ route('admin.guidance.create') }}"><i class="bx bx-right-arrow-alt"></i>Catat Pelanggaran</a></li>
                </ul>
            </li>
            @endhasanyrole

            <!-- KHUSUS PEMBIMBING PKL -->
            @hasanyrole('pembimbing_pkl')
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon text-info"><i class="fas fa-industry"></i></div>
                    <div class="menu-title">Bimbingan PKL</div>
                </a>
                <ul>
                    <li><a href="{{ route('teacher.internships.index') }}"><i class="bx bx-right-arrow-alt"></i>Penilaian Siswa</a></li>
                    <li><a href="{{ route('teacher.industries.index') }}"><i class="bx bx-right-arrow-alt"></i>Set Titik Lokasi PKL</a></li>
                </ul>
            </li>
            @endhasanyrole
        @endhasanyrole


        <!-- ============================================== -->
        <!-- MENU SISWA                                     -->
        <!-- ============================================== -->
        @hasanyrole('siswa')
            <li class="menu-label">Portal Siswa</li>
            
            <li>
                <a href="{{ route('student.guidance.index') }}">
                    <div class="parent-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="menu-title">Riwayat Kedisiplinan</div>
                </a>
            </li>

            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-mosque"></i></div>
                    <div class="menu-title">Ibadah & Karakter</div>
                </a>
                <ul>
                    <li><a href="{{ route('prayer.index') }}"><i class="bx bx-right-arrow-alt"></i>Absensi Sholat</a></li>
                    <li><a href="{{ route('student.ramadan.index') }}"><i class="bx bx-right-arrow-alt"></i>Jurnal Ramadhan</a></li>
                </ul>
            </li>

            <!-- Menu PKL (Hanya bisa diakses jika kelasnya diaktifkan di mapping) -->
            <li>
                <a href="{{ route('student.internships.index') }}">
                    <div class="parent-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="menu-title">Prakerin (PKL)</div>
                </a>
            </li>
        @endhasanyrole


        <!-- ============================================== -->
        <!-- PENGATURAN SISTEM (Khusus Super Admin/Admin)   -->
        <!-- ============================================== -->
        @hasanyrole('super_admin|admin')
            <li class="menu-label">Konfigurasi</li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="fas fa-cogs"></i></div>
                    <div class="menu-title">Pengaturan Sistem</div>
                </a>
                <ul>
                    <li><a href="{{ route('settings.index') }}"><i class="bx bx-right-arrow-alt"></i>Identitas Sekolah</a></li>
                    <li><a href="{{ route('admin.attendance.setting.index') }}"><i class="bx bx-right-arrow-alt"></i>Jam Absensi Gerbang</a></li>
                    <li><a href="{{ route('admin.prayer.settings.index') }}"><i class="bx bx-right-arrow-alt"></i>Lokasi & API Sholat</a></li>
                    <li><a href="{{ route('admin.roles.index') }}"><i class="bx bx-right-arrow-alt"></i>Role & Hak Akses</a></li>
                </ul>
            </li>
        @endhasanyrole

        <!-- Logout -->
        <li class="menu-label">Akun</li>
        <li>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="parent-icon text-danger"><i class="bx bx-log-out-circle"></i></div>
                <div class="menu-title text-danger">Keluar</div>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</div>