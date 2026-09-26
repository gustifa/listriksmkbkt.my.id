@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 flex justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg w-full text-center">
        
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-yellow-100 text-yellow-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $kegiatan->nama_kegiatan }}</h1>
        <p class="text-gray-500 mb-8">Tanggal: {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d F Y') }}</p>

        <div class="flex justify-center mb-6 p-4 border-4 border-dashed border-gray-200 rounded-xl inline-block bg-white">
            {!! QrCode::size(250)->generate($scanUrl) !!}
        </div>

        <p class="text-sm text-gray-600 mb-8 italic">
            <i class="fas fa-camera me-1"></i> Silakan scan QR Code di atas untuk melakukan absensi kehadiran.
        </p>

        <div class="border-t pt-6 mt-6 flex justify-between items-center">
            <span class="text-gray-700 font-bold text-lg">
                Total Hadir: <span id="total-hadir-count" class="text-indigo-600">{{ $kegiatan->absensi()->count() }}</span> Orang
            </span>
            
            <a href="{{ route('kegiatan.print', $kegiatan->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak
            </a>
        </div>
    </div>
</div>

{{-- Tambahkan Script AJAX --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Fungsi untuk mengambil data terbaru dari server
        function checkTotalHadir() {
            $.ajax({
                // Buat route baru atau gunakan route API yang sudah ada
                url: "{{ route('kegiatan.total-hadir', $kegiatan->id) }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Update angka di halaman secara smooth
                    $('#total-hadir-count').text(data.total);
                },
                error: function(xhr, status, error) {
                    console.error("Gagal memperbarui total hadir");
                }
            });
        }

        // Jalankan setiap 3 detik agar terlihat real-time saat siswa scan
        setInterval(checkTotalHadir, 3000);
    });
</script>
@endsection