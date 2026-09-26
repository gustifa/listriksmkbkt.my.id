@section('title', 'Laporan Pembelajaran')
<x-app-layout>
    <div class="page-content">
        <div class="container py-4">

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0 text-gray-800 h3">Jadwal Mengajar (Mapping)</h1>
                    <p class="text-muted small">Kartu kendali beban mengajar guru.</p>
                </div>
                <a href="{{ route('teaching-assignments.create') }}" class="shadow-sm btn btn-primary">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Mapping Baru
                </a>
            </div>

            @php
                // Kita kelompokkan data berdasarkan ID Guru agar menjadi 1 Card per Guru
                $groupedAssignments = $assignments->groupBy(function($item) {
                    return $item->teacher->id;
                });
            @endphp

            @if($groupedAssignments->isEmpty())
                <div class="text-center card py-5 shadow-sm">
                    <div class="card-body">
                        <i class="mb-3 fas fa-clipboard-list fa-3x text-gray-300"></i>
                        <h5 class="text-muted">Belum ada data jadwal mengajar.</h5>
                        <p class="mb-0 text-muted">Silakan tambahkan mapping baru.</p>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($groupedAssignments as $teacherId => $teacherData)
                        @php
                            // Ambil data guru dari item pertama di group ini
                            $teacher = $teacherData->first()->teacher;
                            $countMapel = $teacherData->count();
                        @endphp

                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card shadow-sm h-100 border-top-primary">
                                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div>
                                            <h6 class="m-0 font-weight-bold text-primary">{{ $teacher->user->name ?? 'Guru Terhapus' }}</h6>
                                            <small class="text-muted">
                                                @if(optional($teacher)->major)
                                                    Jurusan: {{ $teacher->major->code }}
                                                @else
                                                    Guru Umum
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-secondary">{{ $countMapel }} Mapel</span>
                                </div>

                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @foreach($teacherData as $assignment)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold text-dark">
                                                        {{ $assignment->subject->name ?? '-' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-door-open me-1"></i> {{ $assignment->classroom->name ?? '-' }}
                                                        @if(optional($assignment->subject)->major)
                                                            <span class="badge bg-light text-dark border ms-1">{{ $assignment->subject->major->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Hapus mapel {{ $assignment->subject->name }} dari guru ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="card-footer bg-light text-end">
                                    <small class="text-muted">TA: {{ $teacherData->first()->academic_year ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Notifikasi Error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif

        // 2. Notifikasi Sukses
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        @endif
    });
    </script>
    
    <style>
        .border-top-primary {
            border-top: 4px solid #4e73df !important; /* Sesuaikan dengan warna primary Anda */
        }
    </style>
</x-app-layout>