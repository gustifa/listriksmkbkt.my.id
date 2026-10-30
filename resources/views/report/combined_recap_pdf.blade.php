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
    .classroom-list-box { 
        max-height: 220px; 
        overflow-y: auto; 
        border: 1px solid #dee2e6; 
        border-radius: 6px; 
        padding: 10px; 
        background: #fff; 
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
                        
                        <!-- PILIHAN CHECKBOX CENTANG KELAS -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">Pilih Kelas <span class="text-danger">*</span></label>
                                <div>
                                    <button type="button" id="btn-select-all" class="btn btn-sm btn-outline-primary py-0 px-2 small">Pilih Semua</button>
                                </div>
                            </div>

                            <div class="classroom-list-box">
                                @foreach($classrooms as $classroom)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input cls-checkbox" type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" id="cls_{{ $classroom->id }}">
                                        <label class="form-check-label" for="cls_{{ $classroom->id }}">
                                            <strong>{{ $classroom->name }}</strong> <span class="text-muted">({{ $classroom->major->name ?? 'Umum' }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small id="selection-hint" class="text-muted mt-1 d-block">Centang 1 kelas untuk Pratinjau, atau centang beberapa kelas untuk Download ZIP Batch[cite: 8].</small>
                        </div>

                        <div class="mb-4">
                            <label for="date" class="form-label fw-semibold">Pilih Tanggal Pekan <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                            <small class="text-muted mt-1 d-block">Sistem akan mengambil rentang 1 pekan penuh sesuai tanggal yang dipilih[cite: 1, 2, 7].</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="btn-submit" class="btn btn-secondary py-2 fw-bold" disabled>
                                Pilih Kelas Terlebih Dahulu
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
        const checkboxes = document.querySelectorAll('.cls-checkbox');
        const btnSelectAll = document.getElementById('btn-select-all');
        const btnSubmit = document.getElementById('btn-submit');
        const recapForm = document.getElementById('recap-form');

        function updateFormState() {
            const checkedBoxes = document.querySelectorAll('.cls-checkbox:checked');
            const checkedCount = checkedBoxes.length;

            if (checkedCount === 0) {
                btnSubmit.disabled = true;
                btnSubmit.className = 'btn btn-secondary py-2 fw-bold';
                btnSubmit.innerHTML = 'Pilih Kelas Terlebih Dahulu';
            } else if (checkedCount === 1) {
                btnSubmit.disabled = false;
                btnSubmit.className = 'btn btn-primary py-2 fw-bold';
                btnSubmit.innerHTML = '<i class="bi bi-eye-fill me-1"></i> Pratinjau Laporan Terpadu';
                
                // Set parameter classroom_id untuk single preview
                let singleInput = recapForm.querySelector('input[name="classroom_id"]');
                if (!singleInput) {
                    singleInput = document.createElement('input');
                    singleInput.type = 'hidden';
                    singleInput.name = 'classroom_id';
                    recapForm.appendChild(singleInput);
                }
                singleInput.value = checkedBoxes[0].value;

                recapForm.action = "{{ route('combined.recap.preview') }}";
                recapForm.setAttribute('target', '_blank');
            } else {
                btnSubmit.disabled = false;
                btnSubmit.className = 'btn btn-success py-2 fw-bold';
                btnSubmit.innerHTML = '<i class="bi bi-file-earmark-zip-fill me-1"></i> Download Paket ZIP (' + checkedCount + ' Kelas)';
                
                // Hapus single classroom_id input jika ada
                const singleInput = recapForm.querySelector('input[name="classroom_id"]');
                if (singleInput) singleInput.remove();

                recapForm.action = "{{ route('combined.recap.pdf') }}";
                recapForm.removeAttribute('target');
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateFormState));

        let allSelected = false;
        btnSelectAll.addEventListener('click', function () {
            allSelected = !allSelected;
            checkboxes.forEach(cb => cb.checked = allSelected);
            btnSelectAll.textContent = allSelected ? 'Batal Semua' : 'Pilih Semua';
            updateFormState();
        });
    });
</script>
@endsection