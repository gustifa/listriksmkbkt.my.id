<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Rekap Presensi Gerbang Harian/Mingguan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .filter-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); background: #ffffff; }
        .filter-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #ffffff; border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 20px; }
        .btn-custom-primary { background-color: #0d6efd; border: none; font-weight: 600; }
        .btn-custom-primary:hover { background-color: #0b5ed7; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card filter-card">
                <div class="filter-header text-center">
                    <h5 class="mb-1 font-weight-bold">Laporan Presensi Gerbang</h5>
                    <p class="mb-0 text-white-50 style-subtitle">Rekapitulasi Masuk & Pulang Siswa</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('daily.recap.preview') }}" method="GET" target="_blank">
                        
                        <!-- PILIH KELAS (HANYA KELAS AKTIF) -->
                        <div class="mb-3">
                            <label for="classroom_id" class="form-label font-weight-bold">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="classroom_id" id="classroom_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                        {{ $classroom->name }} ({{ $classroom->major->name ?? 'Umum' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PILIH TANGGAL -->
                        <div class="mb-4">
                            <label for="date" class="form-label font-weight-bold">Pilih Tanggal / Minggu <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                            <small class="text-muted mt-1 d-block">Sistem akan menampilkan rekapitulasi 1 minggu (Senin - Jumat) dari tanggal yang dipilih.</small>
                        </div>

                        <!-- TOMBOL SUBMIT -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-custom-primary py-2">
                                🔍 Pratinjau Rekap
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>