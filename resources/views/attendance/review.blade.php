<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Hasil Scan Absensi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .img-preview { max-height: 80vh; object-fit: contain; border-radius: 8px; border: 1px solid #dee2e6; }
        .table-card { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list-check me-2"></i>Verifikasi Hasil Rekap Absensi</h4>
        <a href="{{ route('attendance.scans.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="row g-4">
        <!-- Sisi Kiri: Foto Daftar Hadir Physical -->
        <div class="col-12 col-lg-5 text-center">
            <div class="card p-3 shadow-sm bg-white">
                <h6 class="fw-bold mb-2">Foto Lembar Absensi</h6>
                <img src="{{ asset('storage/' . $scan->image_path) }}" class="img-fluid img-preview" alt="Foto Absensi">
            </div>
        </div>

        <!-- Sisi Kanan: Tabel Hasil Matching AI -->
        <div class="col-12 col-lg-7">
            <div class="card table-card p-4 bg-white">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary text-uppercase">{{ $scan->scan_type }}</span>
                        <span class="text-muted ms-2"><i class="fa-regular fa-calendar me-1"></i>{{ $scan->scan_date }}</span>
                    </div>
                </div>

                <form id="commitAttendanceForm">
                    <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Teks Terbaca (OCR)</th>
                                    <th>Siswa Terdeteksi</th>
                                    <th>Skor Kemiripan</th>
                                    <th>Status Absen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($matchedData as $index => $item)
                                <tr>
                                    <td>
                                        <small class="text-muted fw-mono">{{ $item['raw_text'] }}</small>
                                    </td>
                                    <td>
                                        <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $item['matched_student_id'] }}">
                                        @if($item['matched_student_id'])
                                            <span class="fw-semibold text-dark">{{ $item['matched_student_name'] }}</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Ditemukan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['similarity_score'] >= 85)
                                            <span class="badge bg-success">{{ $item['similarity_score'] }}%</span>
                                        @elseif($item['similarity_score'] >= 75)
                                            <span class="badge bg-warning text-dark">{{ $item['similarity_score'] }}%</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $item['similarity_score'] }}%</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="attendances[{{ $index }}][status]" class="form-select form-select-sm" style="width: 110px;">
                                            <option value="present" {{ $item['status'] == 'present' ? 'selected' : '' }}>Hadir</option>
                                            <option value="late" {{ $item['status'] == 'late' ? 'selected' : '' }}>Terlambat</option>
                                            <option value="permission" {{ $item['status'] == 'permission' ? 'selected' : '' }}>Izin</option>
                                            <option value="sick" {{ $item['status'] == 'sick' ? 'selected' : '' }}>Sakit</option>
                                            <option value="absent" {{ $item['status'] == 'absent' ? 'selected' : '' }}>Alfa</option>
                                        </select>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Tidak ada data siswa yang berhasil diekstraksi dari foto.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" id="btnCommit" class="btn btn-success px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan ke Database Utama
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$('#commitAttendanceForm').on('submit', function(e) {
    e.preventDefault();

    // Cek apakah ada input student_id yang valid
    let hasStudents = $('input[name^="attendances"]').length > 0;
    
    if (!hasStudents) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Kosong!',
            text: 'Tidak ada data siswa untuk disimpan. Silakan lakukan upload ulang foto yang lebih jelas atau periksa service OCR Anda.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Simpan',
        text: 'Apakah Anda yakin ingin menyimpan rekap absensi ini ke database?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ route('attendance.scans.commit', $scan->id) }}",
                type: "POST",
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success').then(() => {
                        window.location.href = "{{ route('attendance.scans.index') }}";
                    });
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan saat menyimpan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', msg, 'error');
                }
            });
        }
    });
});
</script>
</body>
</html>