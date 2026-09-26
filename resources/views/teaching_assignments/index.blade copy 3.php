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
                // 1. Kelompokkan data berdasarkan ID Guru
                $groupedAssignments = $assignments->groupBy(function($item) {
                    return $item->teacher->id;
                });

                // 2. Definisikan Palette Warna-warni
                $themes = [
                    ['border' => 'border-primary', 'text' => 'text-primary', 'bg' => 'bg-primary', 'btn' => 'btn-outline-primary'],
                    ['border' => 'border-success', 'text' => 'text-success', 'bg' => 'bg-success', 'btn' => 'btn-outline-success'],
                    ['border' => 'border-info',    'text' => 'text-info',    'bg' => 'bg-info',    'btn' => 'btn-outline-info'],
                    ['border' => 'border-warning', 'text' => 'text-warning', 'bg' => 'bg-warning', 'btn' => 'btn-outline-warning'],
                    ['border' => 'border-danger',  'text' => 'text-danger',  'bg' => 'bg-danger',  'btn' => 'btn-outline-danger'],
                    ['border' => 'border-secondary','text'=> 'text-secondary','bg'=> 'bg-secondary','btn'=> 'btn-outline-secondary'],
                    ['border' => 'border-dark',    'text' => 'text-dark',    'bg' => 'bg-dark',    'btn' => 'btn-outline-dark'],
                ];
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
                            $teacher = $teacherData->first()->teacher;
                            $countMapel = $teacherData->count();
                            
                            // 3. Pilih Tema Berdasarkan Urutan Loop (Modulo)
                            // Agar warna berulang jika jumlah guru lebih banyak dari jumlah warna
                            $theme = $themes[$loop->index % count($themes)];
                        @endphp

                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card shadow-sm h-100 card-colored-top {{ $theme['border'] }}">
                                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle {{ $theme['bg'] }} text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div>
                                            <h6 class="m-0 font-weight-bold {{ $theme['text'] }}">{{ $teacher->user->name ?? 'Guru Terhapus' }}</h6>
                                            <small class="text-muted">
                                                @if(optional($teacher)->major)
                                                    Jurusan: {{ $teacher->major->code }}
                                                @else
                                                    Guru Umum
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $theme['bg'] }}">{{ $countMapel }} Mapel</span>
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
                                                    <button type="submit" class="btn {{ $theme['btn'] }} btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Hapus">
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
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif

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
        /* CSS Custom untuk mempertebal border atas */
        .card-colored-top {
            border-top-width: 4px !important;
            border-top-style: solid !important;
        }
        
        /* Opsional: Efek hover agar kartu sedikit terangkat */
        .card-colored-top:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
    </style>
</x-app-layout>