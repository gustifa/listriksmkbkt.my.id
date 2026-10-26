<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Informasi Akun - {{ $user->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .card-account { max-width: 450px; margin: 30px auto; border: 2px dashed #0d6efd; border-radius: 12px; background: #fff; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .card-account { border: 1px solid #000; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="bx bx-printer"></i> Cetak Kartu</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <div class="card card-account shadow-sm p-4">
        <div class="text-center border-bottom pb-3 mb-3">
            <h5 class="fw-bold text-primary mb-1">SMK NEGERI 1 BUKITTINGGI</h5>
            <small class="text-muted">KARTU AKSES LOGIN SISTEM (SISFO)</small>
        </div>

        <table class="table table-borderless align-middle mb-3">
            <tr>
                <td width="35%" class="fw-bold text-muted">Nama Lengkap</td>
                <td width="5%">:</td>
                <td class="fw-bold text-dark">{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-muted">No. Induk / NIP / NIS</td>
                <td>:</td>
                <td><code>{{ $user->nomor_induk ?? $user->student->nis ?? $user->teacher->nip ?? '-' }}</code></td>
            </tr>
            <tr>
                <td class="fw-bold text-muted">Username</td>
                <td>:</td>
                <td class="fw-bold text-primary">{{ $user->username ?? $user->email }}</td>
            </tr>
            <tr>
                <td class="fw-bold text-muted">Hak Akses / Role</td>
                <td>:</td>
                <td><span class="badge bg-info text-dark">{{ ucfirst($user->jenis_user ?? 'User') }}</span></td>
            </tr>
            @if($user->student && $user->student->classroom)
            <tr>
                <td class="fw-bold text-muted">Kelas</td>
                <td>:</td>
                <td>{{ $user->student->classroom->name }}</td>
            </tr>
            @endif
        </table>

        <div class="alert alert-warning p-2 text-center mb-0" style="font-size: 11px;">
            <strong>Catatan Penting:</strong> Jagalah kerahasiaan username dan password Anda. Jangan memberikan akses akun ini kepada siapapun.
        </div>
    </div>

</body>
</html>