<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Rekap Presensi Sholat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .filter-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); background: #ffffff; }
        .filter-header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #ffffff; border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 20px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card filter-card">
                <div class="filter-header text-center">
                    <h5 class="mb-1 font-weight-bold">Laporan Presensi Sholat</h5>
                    <p class="mb-0 text-white-50 style-subtitle">Rekapitulasi Sholat Wajib 5 Waktu</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prayer.recap.preview') }}" method="GET" target="_blank">
                        
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

                        <div class="mb-4">
                            <label for="date" class="form-label font-weight-bold">Pilih Tanggal / Minggu <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ request('date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                            <small class="text-muted mt-1 d-block">Menampilkan rekapitulasi 1 minggu (Senin - Jumat) sholat 5 waktu.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2 font-weight-bold">
                                🕌 Pratinjau Rekap Sholat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>