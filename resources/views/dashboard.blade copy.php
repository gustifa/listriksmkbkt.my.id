@section('title')
   Dashboard
@endsection

@php
  $id = Auth::user()->id;
  $guruId = App\Models\User::find($id);
  $status = $guruId->status;


$countUser = App\Models\User::count();
$countStudent = App\Models\Student::count();
$countTeacher = App\Models\Teacher::count();
$countPresensi = App\Models\Attendance::count();
$countHadir = App\Models\Attendance::where('status', 'hadir')->count();
$countTerlambat = App\Models\Attendance::where('status', 'terlambat')->count();


$CountJadwalGuru = App\Models\Schedule::where('teacher_id', $id)->count();
$countPresensiGuru = App\Models\Attendance::count();

@endphp

<x-app-layout>
	
		@role('admin')
			@include('dashboard.admin')
		@endrole
		
		
			@role('guru')
			@if ($status === '1' ?? '0')
				@include('dashboard.guru')
			@else
			<div class="page-content">
				<h4>Akun <b>{{ $guruId->name}} </b><span class="text-warning">Tidak Aktif</span> </h4>
			</div>
		
			@endif
		
		@endrole	
	

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
	<script>
		function fetchStats() {
			$.ajax({
				url: "{{ route('api.stats') }}", // URL Route tadi
				method: "GET",
				success: function(response) {
					// Update angka di HTML berdasarkan ID
					$('#count-present').text(response.present_count);
					$('#count-late').text(response.late_count);
					$('#user-aktif').text(response.user);
					$('#student').text(response.student);
					$('#attendance').text(response.attendance);
				},
				error: function(xhr) {
					console.log("Gagal mengambil data realtime");
				}
			});
		}

		// Jalankan fungsi pertama kali
		fetchStats();

		// Jalankan ulang setiap 3 detik (3000ms)
		setInterval(fetchStats, 18000);
	</script>
	
</x-app-layout>
