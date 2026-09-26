@section('title', 'Dashboard Siswa') 
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
    
    
    
    
</x-app-layout>