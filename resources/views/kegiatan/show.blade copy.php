@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 flex justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg w-full text-center">
        
        <!-- Pesan Notifikasi Absen -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-yellow-100 text-yellow-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $kegiatan->nama_kegiatan }}</h1>
        <p class="text-gray-500 mb-8">Tanggal: {{ \Carbon\Carbon::parse($kegiatan->tanggal)->format('d F Y') }}</p>

        <div class="flex justify-center mb-6 p-4 border-4 border-dashed border-gray-200 rounded-xl inline-block">
            <!-- Menampilkan QR Code -->
            {!! QrCode::size(250)->generate($scanUrl) !!}
        </div>

        <p class="text-sm text-gray-600 mb-8">
            Silakan scan QR Code di atas menggunakan kamera HP atau aplikasi scanner untuk melakukan absensi kehadiran.
        </p>

        <div class="border-t pt-6 mt-6 flex justify-between items-center">
            <span class="text-gray-700 font-medium">Total Hadir: {{ $kegiatan->absensi()->count() }} Orang</span>
            
            <a href="{{ route('kegiatan.print', $kegiatan->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Absensi
            </a>
        </div>
    </div>
</div>
@endsection