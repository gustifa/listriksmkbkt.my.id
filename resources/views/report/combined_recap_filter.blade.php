@extends('layouts.app')

<style>
    body { 
        background-color: #f4f6f9; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }
    .filter-card { 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        background: #ffffff; 
        overflow: hidden;
    }
    .filter-header { 
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
        color: #ffffff; 
        padding: 24px 20px; 
    }
    .info-box {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
        border-radius: 4px;
        padding: 12px;
        font-size: 0.85rem;
    }
</style>

@section('title', 'Laporan Mingguan Terpadu')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card filter-card">
                <div class="filter-header text-center">
                    <i class="bi bi-journal-text display-5 mb-2 d-block"></i>
                    <h4 class="mb-1 fw-bold">Laporan Mingguan Terpadu</h4>
                    <p class="mb-0 opacity-75 small">Rekapitulasi 3 Halaman Per Kelas</p>
                </div>
                <div class="card-body p-4">
                    
                    <div class="info-box mb-4">
                        <span class="fw-bold text-primary d-block mb-1">
                            <i class="bi bi-info-circle-fill me-1"></i> Cakupan Laporan 3 Halaman:
                        </span>
                        <ul class="mb-0 ps-3 text-secondary">
                            <li><strong>Halaman 1:</strong> Presensi Pembelajaran (Jam 1 - 10)[cite: 1, 3]</li>
                            <li><strong>Halaman 2:</strong> Presensi Gerbang Harian (Masuk & Pulang)[cite: 1, 2, 7]</li>
                            <li><strong>Halaman 3:</strong> Presensi Sholat 5 Waktu (Senin - Minggu)</li>
                        </ul>
                    </div>

                    <form id="recap-form" action="{{ route('combined.recap.preview') }}" method="GET" target="_blank">
                        
                        <div class="mb-3">
                            <label for="classroom_id" class="form-label fw-semibold">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="classroom_id" id="classroom_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option value="all" style="font-weight: bold; color: #0d6efd;">📦 Semua Kelas (Export ZIP Paket)</option>
                                <option disabled>------------------------------------</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ request('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                        {{ $classroom->name }} ({{ $classroom->major->name ?? 'Umum' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="date" class="form-label fw-semibold">Pilih Tanggal Pekan <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                            <small class="text-muted mt-1 d-block">Sistem akan mengambil rentang 1 pekan penuh sesuai tanggal yang dipilih[cite: 1, 2, 7].</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="btn-submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-eye-fill me-1"></i> Pratinjau Laporan Terpadu
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectClass = document.getElementById('classroom_id');
        const btnSubmit = document.getElementById('btn-submit');
        const recapForm = document.getElementById('recap-form');

        selectClass.addEventListener('change', function () {
            if (this.value === 'all') {
                btnSubmit.className = 'btn btn-success py-2 fw-bold';
                btnSubmit.innerHTML = '<i class="bi bi-file-earmark-zip-fill me-1"></i> Download ZIP Semua Kelas';
                recapForm.action = "{{ route('combined.recap.pdf') }}";
                recapForm.removeAttribute('target');
            } else {
                btnSubmit.className = 'btn btn-primary py-2 fw-bold';
                btnSubmit.innerHTML = '<i class="bi bi-eye-fill me-1"></i> Pratinjau Laporan Terpadu';
                recapForm.action = "{{ route('combined.recap.preview') }}";
                recapForm.setAttribute('target', '_blank');
            }
        });
    });
</script>
@endsection