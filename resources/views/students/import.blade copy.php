<!DOCTYPE html>
<html>
<head>
    <title>Import Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>Import Data Siswa</h4>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Format Excel Wajib:</strong> <br>
                        Pastikan baris pertama file Excel Anda memiliki judul kolom sebagai berikut (huruf kecil semua):<br>
                        <code>nis</code> | <code>nama_siswa</code> | <code>kelas</code>
                    </div>

                    <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label>Pilih File Excel (.xlsx / .xls)</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Import Sekarang</button>
                        </div>
                    </form>

                </div>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">Contoh isi file Excel:</small>
                <table class="table table-bordered table-sm mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>nis</th>
                            <th>nama_siswa</th>
                            <th>kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1001</td>
                            <td>Budi Santoso</td>
                            <td>XII RPL 1</td>
                        </tr>
                        <tr>
                            <td>1002</td>
                            <td>Siti Aminah</td>
                            <td>XII TKJ 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>