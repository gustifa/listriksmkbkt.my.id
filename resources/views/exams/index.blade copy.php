@extends('layouts.app')

@section('title', 'Daftar Kelola Ujian')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Page & View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Daftar Kelola Ujian</h3>
            <p class="text-muted small mb-0">Kelola seluruh data jadwal dan informasi ujian di sistem</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Toggle Switch Table / Card -->
            <div class="btn-group" role="group" aria-label="Layout Switcher">
                <button type="button" class="btn btn-outline-primary active" id="btn-table-view" onclick="switchView('table')">
                    <i class="bi bi-table me-1"></i> Tabel
                </button>
                <button type="button" class="btn btn-outline-primary" id="btn-card-view" onclick="switchView('card')">
                    <i class="bi bi-grid-fill me-1"></i> Card
                </button>
            </div>

            <!-- Tombol Buat Ujian -->
            <a href="{{ route('exams.create') }}" class="btn btn-primary px-3 py-2 fw-semibold">
                + Buat Ujian Baru
            </a>
        </div>
    </div>

    <!-- Alert Status -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODE 1: TAMPILAN TABEL (DEFAULT)           -->
    <!-- ========================================== -->
    <div id="table-view-container" class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-semibold">
                        <tr>
                            <th class="ps-4 py-3" style="width: 5%;">No</th>
                            <th class="py-3">Nama Ujian</th>
                            <th class="py-3">Mata Pelajaran</th>
                            @if(Auth::user()->hasRole('admin'))
                                <th class="py-3">Guru Pengampu</th>
                            @endif
                            <th class="py-3">Kelas</th>
                            <th class="py-3">Durasi</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3" style="width: 18%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($exams as $index => $exam)
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">{{ $exams->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exam->title }}</div>
                                    <span class="badge bg-light text-secondary border small mt-1">
                                        {{ strtoupper(str_replace('_', ' ', $exam->type)) }}
                                    </span>
                                </td>
                                <td><span class="fw-medium text-dark">{{ $exam->subject->name ?? '-' }}</span></td>
                                @if(Auth::user()->hasRole('admin'))
                                    <td>{{ $exam->teacher->name ?? '-' }}</td>
                                @endif
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($exam->classrooms as $cls)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $cls->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $exam->duration_minutes }}</span>
                                    <small class="text-muted">menit</small>
                                </td>
                                <td>
                                    <!-- Form Toggle Status Tabel -->
                                    <form action="{{ route('exams.toggle-status', $exam->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="badge border-0 px-2 py-1 {{ $exam->is_active ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }}"
                                                style="cursor: pointer;"
                                                title="Klik untuk {{ $exam->is_active ? 'non-aktifkan' : 'aktifkan' }} ujian">
                                            <i class="bi {{ $exam->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                            {{ $exam->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-outline-info" title="Detail">Detail</a>
                                        <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-outline-success" title="Import Soal">Import</a>
                                        <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->hasRole('admin') ? 8 : 7 }}" class="text-center py-5 text-muted">
                                    Belum ada data ujian yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODE 2: TAMPILAN CARD (GRID)              -->
    <!-- ========================================== -->
    <div id="card-view-container" class="row g-3 d-none">
        @forelse($exams as $exam)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body d-flex flex-column p-4">
                        <!-- Top Metadata & Toggle Status Button -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                            </span>
                            
                            <!-- Form Toggle Status Card -->
                            <form action="{{ route('exams.toggle-status', $exam->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="badge border-0 px-2 py-1 {{ $exam->is_active ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }}"
                                        style="cursor: pointer;"
                                        title="Klik untuk {{ $exam->is_active ? 'non-aktifkan' : 'aktifkan' }} ujian">
                                    <i class="bi {{ $exam->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                    {{ $exam->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </button>
                            </form>
                        </div>

                        <!-- Judul Ujian -->
                        <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-person me-1"></i> Guru: {{ $exam->teacher->name ?? '-' }}
                        </p>

                        <!-- Detail Kelas -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Target Kelas:</small>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($exam->classrooms as $cls)
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $cls->name }}</span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Info Durasi & Jumlah Soal -->
                        <div class="row text-center border-top border-bottom py-2 mb-3 bg-light-subtle rounded-2">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block">Durasi</small>
                                <strong class="text-dark">{{ $exam->duration_minutes }} Menit</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Jumlah Soal</small>
                                <strong class="text-dark">{{ $exam->questions_count ?? 0 }} Soal</strong>
                            </div>
                        </div>

                        <!-- Action Buttons di Footer Card -->
                        <div class="mt-auto d-flex justify-content-between gap-1 pt-2">
                            <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-sm btn-outline-info flex-fill">
                                Detail
                            </a>
                            <a href="{{ route('guru.questions.import.form', $exam->id) }}" class="btn btn-sm btn-outline-success flex-fill">
                                Import
                            </a>
                            <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ujian ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                Belum ada data ujian yang dibuat.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($exams->hasPages())
        <div class="mt-4">
            {{ $exams->links() }}
        </div>
    @endif
</div>

<!-- JavaScript Switcher -->
<script>
    function switchView(view) {
        const tableView = document.getElementById('table-view-container');
        const cardView = document.getElementById('card-view-container');
        const btnTable = document.getElementById('btn-table-view');
        const btnCard = document.getElementById('btn-card-view');

        if (view === 'card') {
            tableView.classList.add('d-none');
            cardView.classList.remove('d-none');
            btnCard.classList.add('active');
            btnTable.classList.remove('active');
            localStorage.setItem('exam_view_pref', 'card');
        } else {
            cardView.classList.add('d-none');
            tableView.classList.remove('d-none');
            btnTable.classList.add('active');
            btnCard.classList.remove('active');
            localStorage.setItem('exam_view_pref', 'table');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const savedView = localStorage.getItem('exam_view_pref');
        if (savedView === 'card') {
            switchView('card');
        }
    });
</script>
@endsection