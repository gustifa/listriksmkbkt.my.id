@section('title', 'Riwayat Peminjaman')

<x-app-layout>
    <div class="page-content">
        <div class="row justify-content-center">
            <div class="container p-4 mx-auto lg:p-8">
                <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <div class="flex flex-col justify-between gap-4 mb-6 md:flex-row md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Monitoring Absensi Sholat</h1>
                            <p class="text-sm text-gray-500">Pantau kedisiplinan dan lokasi ibadah siswa.</p>
                        </div>

                        <!-- Filter Tanggal -->
                        <form action="{{ route('admin.prayer.monitoring') }}" method="GET" class="flex items-center gap-2">
                            <input type="date" name="date" value="{{ $date }}"
                                class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <button type="submit" class="px-4 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                                Filter
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="p-4 font-semibold text-gray-600 border-b">Nama Siswa</th>
                                    <th class="p-4 font-semibold text-gray-600 border-b">Sholat</th>
                                    <th class="p-4 font-semibold text-gray-600 border-b">Waktu Absen</th>
                                    <th class="p-4 font-semibold text-center text-gray-600 border-b">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($attendances as $item)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800">{{ $item->student->user->name }}</div>
                                        <div class="text-xs text-gray-400">NISN: {{ $item->student->nisn }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium uppercase
                                            {{ $item->prayer_name == 'subuh' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                            {{ $item->prayer_name == 'dzuhur' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $item->prayer_name == 'ashar' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $item->prayer_name == 'maghrib' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $item->prayer_name == 'isya' ? 'bg-purple-100 text-purple-700' : '' }}
                                            {{ $item->prayer_name == 'dhuha' ? 'bg-green-100 text-green-700' : '' }}
                                        ">
                                            {{ $item->prayer_name }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-mono text-sm text-gray-600">
                                        {{ $item->check_in_time }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($item->latitude && $item->longitude)
                                            <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 underline hover:text-blue-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Lihat Peta
                                            </a>
                                        @else
                                            <span class="text-xs italic text-gray-400">Tanpa Koordinat</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-8 italic text-center text-gray-500">
                                        Belum ada data absensi untuk tanggal ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
