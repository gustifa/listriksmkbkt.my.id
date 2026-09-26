@section('title', 'Daftar Siswa - Rekap')

<x-app-layout>
    <div class="page-content">
        
        <!-- CSS FIX: Membatasi ukuran icon SVG pada pagination -->
        <style>
            nav[role="navigation"] svg {
                width: 20px;
                height: 20px;
            }
            /* Jika menggunakan class w-5 h-5 dari tailwind default laravel pagination */
            .w-5 { width: 20px; }
            .h-5 { height: 20px; }
            
            /* Penyesuaian agar pagination terlihat rapi */
            .pagination {
                justify-content: center;
                margin-top: 20px;
            }
        </style>

        <div class="mb-3">
            <a href="{{ route('recap.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard Rekap</a>
        </div>

        <div class="shadow-sm card border-0">
            <div class="py-3 text-white card-header bg-success">
                <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i> Pilih Siswa untuk Detail Rekap</h5>
            </div>
            <div class="card-body">
                
                <!-- FILTER -->
                <form method="GET" class="mb-4 row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="small fw-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama atau NISN..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Kelas</label>
                        <select name="classroom_id" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-search"></i> Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="example" class="table align-middle table-hover table-bordered">
                        <thead class="text-center table-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $key => $student)
                                <tr>
                                    <td class="text-center">{{ $students->firstItem() + $key }}</td>
                                    <td class="text-center">{{ $student->nis }}</td>
                                    <td class="fw-bold">{{ $student->name }}</td>
                                    <td class="text-center">{{ $student->classroom->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <!-- Tombol ke Detail Siswa -->
                                        <a href="{{ route('recap.student.detail', $student->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-muted">Data siswa tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- <div class="mt-3">
                    {{-- Tambahkan appends agar filter tidak hilang saat pindah halaman --}}
                    {{ $students->appends(request()->query())->links() }}
                </div> -->
            </div>
        </div>
    </div>
</x-app-layout>