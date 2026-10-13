@extends('layouts.app')

@section('title', 'Form Absensi Sholat')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-primary text-white">Input Absensi Sholat</div>
    <div class="card-body">
        @if($errors->has('location'))
            <div class="alert alert-danger">{{ $errors->first('location') }}</div>
        @endif

        <form action="{{ route('student.prayer-attendances.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Hidden Input Lat/Long GPS -->
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div class="mb-3">
                <label for="prayer_name" class="form-label">Nama Sholat</label>
                <select name="prayer_name" id="prayer_name" class="form-select" required>
                    <option value="Subuh">Subuh</option>
                    <option value="Dhuha">Dhuha</option>
                    <option value="Dzuhur">Dzuhur</option>
                    <option value="Ashar">Ashar</option>
                    <option value="Maghrib">Maghrib</option>
                    <option value="Isya">Isya</option>
                    <option value="Jum'at">Jum'at</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="location_type" class="form-label">Lokasi Sholat</label>
                <select name="location_type" id="location_type" class="form-select" onchange="togglePhotoField()" required>
                    <option value="school">Di Mesjid Sekolah</option>
                    <option value="home">Di Rumah / Mesjid Sekitar Rumah (PJJ)</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="photo_field">
                <label for="photo_evidence" class="form-label">Foto Bukti Sholat (Wajib jika di rumah/PJJ)</label>
                <input type="file" name="photo_evidence" id="photo_evidence" class="form-control" accept="image/*" capture="camera">
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Catatan / Keterangan (Opsional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Masukkan nama mesjid jika di luar sekolah..."></textarea>
            </div>

            <div class="alert alert-info" id="gps_status">
                Sedang mendeteksi posisi GPS Anda...
            </div>

            <button type="submit" id="btn_submit" class="btn btn-success w-100" disabled>Kirim Absensi Sholat</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('gps_status').className = 'alert alert-success';
                document.getElementById('gps_status').innerText = 'Posisi GPS berhasil didapatkan!';
                document.getElementById('btn_submit').removeAttribute('disabled');
            }, function(error) {
                document.getElementById('gps_status').className = 'alert alert-danger';
                document.getElementById('gps_status').innerText = 'Gagal mengakses GPS. Pastikan izin lokasi/GPS browser diaktifkan.';
            });
        } else {
            alert("Browser Anda tidak mendukung fitur Geolocation.");
        }
    });

    function togglePhotoField() {
        const type = document.getElementById('location_type').value;
        const photoField = document.getElementById('photo_field');

        if (type === 'home') {
            photoField.classList.remove('d-none');
        } else {
            photoField.classList.add('d-none');
        }
    }
</script>
@endsection