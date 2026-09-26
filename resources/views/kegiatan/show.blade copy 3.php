@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 flex justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full text-center">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $kegiatan->nama_kegiatan }}</h1>
        <p class="text-gray-500 mb-6">Tanggal: {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d F Y') }}</p>

        <div class="flex justify-center mb-6 p-4 border-4 border-dashed border-gray-200 rounded-xl inline-block bg-white shadow-sm">
            {!! QrCode::size(250)->generate($scanUrl) !!}
        </div>

        <div class="mb-8">
            <span class="text-gray-700 font-bold text-xl block mb-4">
                Total Hadir: <span id="total-hadir-count" class="text-indigo-600">{{ $kegiatan->absensi()->count() }}</span> Orang
            </span>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 min-h-[100px]">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Peserta yang Baru Hadir:</p>
                <div id="daftar-nama" class="flex flex-wrap justify-center gap-2">
                    @forelse($kegiatan->absensi()->with('user')->latest()->take(10)->get() as $absensi)
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium animate-pulse">
                            {{ $absensi->user->name }}
                        </span>
                    @empty
                        <p id="placeholder-text" class="text-gray-400 text-sm">Belum ada peserta yang hadir.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="border-t pt-6 flex justify-between items-center">
            <p class="text-xs text-gray-400 italic text-left max-w-[200px]">
                Refresh otomatis setiap 3 detik. Gunakan kamera HP untuk scan.
            </p>
            <a href="{{ route('kegiatan.print', $kegiatan->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Laporan
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function checkAbsensiRealtime() {
            $.ajax({
                url: "{{ route('kegiatan.total-hadir', $kegiatan->id) }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // 1. Update Angka Total
                    $('#total-hadir-count').text(response.total);

                    // 2. Update Daftar Nama
                    if (response.names.length > 0) {
                        $('#placeholder-text').hide();
                        let htmlNames = '';
                        response.names.forEach(function(name) {
                            htmlNames += `<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium transition-all duration-500 scale-110">
                                            ${name}
                                          </span>`;
                        });
                        $('#daftar-nama').html(htmlNames);
                    }
                }
            });
        }

        // Cek setiap 3 detik
        setInterval(checkAbsensiRealtime, 3000);
    });
</script>
@endsection