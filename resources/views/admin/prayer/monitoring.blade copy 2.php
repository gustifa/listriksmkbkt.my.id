@section('title', 'Monitoring Absensi Sholat')

<x-app-layout>
    <div class="min-h-screen py-12 bg-gray-50">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <!-- Header Section -->
            <div class="flex flex-col justify-between gap-6 mb-8 md:flex-row md:items-center">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Matriks Presensi Sholat</h1>
                    <p class="mt-2 text-sm text-gray-600">Rekapitulasi status ibadah siswa per waktu sholat.</p>
                </div>

                <!-- Filter Tanggal -->
                <div class="p-2 bg-white border border-gray-200 shadow-sm rounded-2xl">
                    <form action="{{ route('admin.prayer.monitoring') }}" method="GET" class="flex items-center gap-2">
                        <input type="date" name="date" value="{{ $date }}"
                               class="w-full py-2 pl-4 pr-10 text-sm transition border-none bg-gray-50 rounded-xl focus:ring-2 focus:ring-blue-500 md:w-auto">
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white transition-all bg-blue-600 shadow-md hover:bg-blue-700 rounded-xl shadow-blue-200 active:scale-95">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Legend / Keterangan Warna -->
            <div class="flex flex-wrap items-center gap-4 p-4 mb-6 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <span class="mr-2 text-xs font-bold tracking-widest text-gray-400 uppercase">Keterangan:</span>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-gray-600">Hadir</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-medium text-gray-600">Terlambat</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <span class="text-xs font-medium text-gray-600">Alpha</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                    <span class="text-xs font-medium text-gray-600">Izin/Sakit</span>
                </div>
            </div>

            <!-- Main Table Section -->
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-3xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="sticky left-0 z-10 px-6 py-5 text-xs font-bold tracking-wider text-gray-500 uppercase bg-gray-50">Data Siswa</th>
                                @foreach($prayerTypes as $type)
                                <th class="px-6 py-5 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">
                                    {{ $type }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($data as $student)
                            <tr class="transition-colors hover:bg-blue-50/30 group">
                                <td class="sticky left-0 z-10 px-6 py-5 transition-colors bg-white border-r group-hover:bg-blue-50/30 border-gray-50">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $student->name }}</span>
                                        <span class="mt-1 font-mono text-xs tracking-tighter text-gray-400">NIS: {{ $student->nis }}</span>
                                    </div>
                                </td>

                                @foreach($prayerTypes as $type)
                                @php
                                    $status = $student->statuses[strtolower($type)] ?? 'Alpha';
                                    $status = strtolower($status);

                                    // Mapping Warna Status
                                    $statusClasses = [
                                        'hadir'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'terlambat' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'alpha'     => 'bg-rose-100 text-rose-700 border-rose-200',
                                        'izin'      => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        'sakit'     => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                    ];

                                    $currentClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                @endphp
                                <td class="px-4 py-5 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border shadow-sm {{ $currentClass }} min-w-[80px]">
                                            {{ $status }}
                                        </span>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($prayerTypes) + 1 }}" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="p-6 mb-4 rounded-full bg-gray-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900">Tidak ada data siswa</h3>
                                        <p class="max-w-xs mx-auto text-gray-500">Silakan periksa kembali konfigurasi data siswa Anda.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Info -->
                <div class="flex flex-col items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 bg-gray-50 md:flex-row">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">
                        Laporan Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                    </p>
                    <div class="flex gap-2">
                         <!-- Tombol Export jika diperlukan di masa depan -->
                         <button class="text-xs font-bold tracking-tighter text-blue-600 uppercase transition hover:text-blue-800">Download PDF</button>
                         <span class="text-gray-300">|</span>
                         <button class="text-xs font-bold tracking-tighter text-blue-600 uppercase transition hover:text-blue-800">Cetak Laporan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
