<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow col-md-6 mx-auto">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Cetak Laporan Absensi</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report.print') }}" method="POST" target="_blank">
                @csrf
                <div class="mb-3">
                    <label>Tanggal Awal</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Cetak PDF
                    </button>
                </div>
            </form>
            <br>
            <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>