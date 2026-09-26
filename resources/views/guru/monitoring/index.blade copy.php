@section('title', 'Monitoring Siswa')

<x-app-layout>
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-primary"><i class="fas fa-binoculars me-2"></i>Monitoring Siswa</h4>
                <p class="text-muted mb-0">Dashboard Walas & Bimbingan Konseling</p>
            </div>
        </div>

        <div class="row">
            @forelse($myClasses as $class)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm hover-shadow transition h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">{{ $class->name }}</h5>
                                    <small class="text-muted">{{ $class->students()->count() }} Siswa</small>
                                </div>
                                <div class="bg-primary/10 p-2 rounded-circle text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-light text-secondary border">
                                    @if($class->homeroom_teacher_id == Auth::user()->teacher->id)
                                        <i class="fas fa-user-tie me-1"></i> Wali Kelas
                                    @else
                                        <i class="fas fa-heart me-1"></i> Guru BK
                                    @endif
                                </span>
                            </div>

                            <a href="{{ route('teacher.monitoring.show', $class->id) }}" class="btn btn-outline-primary w-100 fw-bold">
                                <i class="fas fa-eye me-1"></i> Lihat Data Hari Ini
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/surr-no-data.svg" class="w-25 opacity-50 mb-3" alt="No Data">
                    <h5 class="text-muted">Anda belum ditugaskan di kelas manapun.</h5>
                    <p class="text-secondary small">Hubungi admin untuk seting Wali Kelas atau Guru BK.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>