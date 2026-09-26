@section('title', 'Dashboard') 
{{-- Menggunakan @section('title', '...') agar lebih ringkas --}}

@php
    // --- Data Pengguna yang Sedang Login ---
    // Gunakan helper Auth::id() atau ambil user langsung
    $user = Auth::user();
    $status = $user->status ?? '0'; // Amankan dari null
    $id = $user->id;
    $userGuru = $user->name;

    // --- Data Statik Umum (Admin) ---
    // Gunakan cache atau Service Provider untuk data ini jika sering diakses.
    $countUser = App\Models\User::count();
    $countStudent = App\Models\Student::count();
    $countTeacher = App\Models\Teacher::count();
    
    // Total Presensi
    $countPresensi = App\Models\Attendance::count(); 
    $countHadir = App\Models\Attendance::where('status', 'hadir')->count();
    $countTerlambat = App\Models\Attendance::where('status', 'terlambat')->count();

    // --- Data Spesifik Guru ---
    $countJadwalGuru = App\Models\Schedule::where('teacher_id', $id)->count();
    // Catatan: $countPresensiGuru tidak perlu dihitung ulang karena sudah ada $countPresensi
    
@endphp

<x-app-layout>
    
    {{-- ============================================= --}}
    {{-- LOGIKA TAMPILAN BERDASARKAN ROLE --}}
    {{-- ============================================= --}}
    
    {{-- 1. Tampilan Admin --}}
    @role('admin')
        @include('dashboard.admin')
    @endrole
    
    {{-- 2. Tampilan Guru --}}
    @role('guru')
        {{-- Perbaikan Logika Status: Pengecekan status (String/Int) --}}
        @if ($status == 1)
            @include('dashboard.guru')
        @else
            <div class="page-content">
                <div class="row">
                    <div class="col-12">
                        <div class="card p-4">
                             <h4>Akun <b>{{ $user->name}} </b> Anda <span class="text-warning">Tidak Aktif</span></h4>
                             <p>Silakan hubungi Administrator untuk mengaktifkan akun Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endrole
    
    {{-- Catatan: Jika ada role lain (misalnya 'siswa'), tambahkan logika @role('siswa'). --}}
    
    {{-- ============================================= --}}
    {{-- SKRIP AJAX (Ditempatkan di Blade Stack) --}}
    {{-- ============================================= --}}

    {{-- Script AJAX ini akan dimuat di bagian bawah layout (sebelum </body>) --}}
    @push('scripts')
    {{-- Anda tidak perlu memuat JQuery lagi di sini karena sudah dimuat di x-app-layout Anda. --}}
    <script>
        // Gunakan jQuery alias ($) di sini karena skrip AJAX Anda menggunakannya
        $(document).ready(function() {
            
            function fetchStats() {
                $.ajax({
                    url: "{{ route('api.stats') }}", // Pastikan route ini ada
                    method: "GET",
                    success: function(response) {
                        // Pastikan ID di HTML Anda cocok dengan ID ini:
                        $('#count-present').text(response.present_count);
                        $('#count-late').text(response.late_count);
                        $('#user-aktif').text(response.user);
                        $('#student').text(response.student);
                        $('#attendance').text(response.attendance);
                        
                        console.log("Data realtime diperbarui.");
                    },
                    error: function(xhr) {
                        console.error("Gagal mengambil data realtime:", xhr.responseText);
                    }
                });
            }

            // Jalankan fungsi pertama kali saat halaman dimuat
            fetchStats();

            // Jalankan ulang setiap 18 detik (18000ms)
            setInterval(fetchStats, 18000);
        });
    </script>
    @endpush
    
</x-app-layout>